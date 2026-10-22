<?php

namespace App\Services\Payments;

use App\Models\CourseAdmission;
use App\Models\ExamCouponRedemption;
use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CouponException;
use App\Services\CouponService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Everything that changes an admission: starting an online payment, confirming it (safe to run twice), adding a student
 * from the backend, cancelling with a refund, and removing the record. Money rules live here and nowhere else.
 */
class AdmissionService
{
    /** Where a student bought from. "app" is kept for admissions made before Android and iOS were told apart. */
    public const CHANNELS = ['web' => 'Website', 'android' => 'Android app', 'ios' => 'iOS app', 'whatsapp' => 'WhatsApp link', 'admin' => 'Added by staff', 'app' => 'App (old)'];

    /** The coupon log only knows app / web / admin. */
    private function couponSource(string $channel): string
    {
        return match ($channel) {
            'web' => 'web',
            'admin' => 'admin',
            default => 'app',
        };
    }

    public function __construct(private PaymentManager $gateways, private CouponService $coupons)
    {
    }

    // ============================================================ online

    /**
     * Starts an admission for the app / website.
     *
     * @return array{free: bool, admission: CourseAdmission, transaction: ?PaymentTransaction, gateway: ?string, checkout: array, amount: float}
     * @throws PaymentException
     */
    public function checkout(User $user, ExamCourse $course, ?string $couponCode = null, ?string $gatewayName = null, string $source = 'android', ?CourseAdmission $order = null): array
    {
        if (!isset(self::CHANNELS[$source])) {
            $source = 'android';
        }
        if ($course->status !== 'published') {
            throw new PaymentException('This course is not open for admission.');
        }
        $current = CourseAdmission::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'active')->get()->first(fn ($a) => $a->grantsAccess());
        if ($current) {
            throw new PaymentException('You are already in this course.');
        }

        $original = $course->effectivePrice();
        $discount = 0.0;
        $code = null;
        if (filled($couponCode)) {
            $check = $this->coupons->check($couponCode, $user, $course);
            if (!$check['ok']) {
                throw new PaymentException($check['message']);
            }
            $discount = $check['discount'];
            $code = $check['coupon']->code;
        }
        $amount = round(max(0, $original - $discount), 2);

        $gateway = null;
        if ($amount > 0) {
            $gateway = $gatewayName ? $this->gateways->gateway($gatewayName) : $this->gateways->active();
            if (!$gateway->configured()) {
                throw new PaymentException('Online payment is not available right now. Please try again later.');
            }
        }

        // an earlier attempt that was never paid is closed, so the student has one open order at a time
        CourseAdmission::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'pending')
            ->when($order, fn ($q) => $q->where('id', '!=', $order->id))
            ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Replaced by a new attempt']);

        $fields = ['gateway' => $gateway?->name() ?? 'free', 'original_amount' => $original, 'discount_amount' => $discount, 'amount' => $amount, 'coupon_code' => $code];
        if ($order && $order->status === 'pending' && $order->user_id === $user->id && $order->course_id === $course->id) {
            $order->update($fields);   // the staff order is paid now: prices are re-checked as of today
            $admission = $order;
        } else {
            $admission = CourseAdmission::create($fields + [
                'exam_id' => $course->exam_id, 'course_id' => $course->id, 'user_id' => $user->id, 'status' => 'pending', 'source' => $source,
            ]);
        }

        if ($amount <= 0) {
            $tx = $this->newTransaction($admission, 'free', 0.0);
            $this->confirm($tx, ['status' => 'success', 'payment_id' => null, 'method' => 'free', 'amount' => 0.0, 'reason' => null, 'raw' => []]);

            return ['free' => true, 'admission' => $admission->fresh(), 'transaction' => $tx, 'gateway' => null, 'checkout' => [], 'amount' => 0.0];
        }

        $tx = $this->newTransaction($admission, $gateway->name(), $amount);
        try {
            $params = $gateway->createOrder($tx, $user, $course);
        } catch (PaymentException $e) {
            $tx->update(['status' => 'failed', 'failure_reason' => mb_substr($e->getMessage(), 0, 290)]);
            if (!$order) {
                $admission->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Could not start the payment']);
            }
            throw $e;
        }
        $tx->status = 'pending';
        $tx->request = $params;
        $tx->save();

        return ['free' => false, 'admission' => $admission, 'transaction' => $tx, 'gateway' => $gateway->name(), 'checkout' => $params, 'amount' => $amount];
    }

    /**
     * Staff makes the order first (admission waiting for payment, price fixed); the student pays it later through a link.
     *
     * @throws PaymentException
     */
    public function createOrder(User $student, ExamCourse $course, string $channel, ?string $couponCode, ?User $staff, ?string $notes = null): CourseAdmission
    {
        if ($course->status !== 'published') {
            throw new PaymentException('This course is not open for admission.');
        }
        $current = CourseAdmission::where('user_id', $student->id)->where('course_id', $course->id)->where('status', 'active')->get()->first(fn ($a) => $a->grantsAccess());
        if ($current) {
            throw new PaymentException('This student is already in the course.');
        }
        $original = $course->effectivePrice();
        $discount = 0.0;
        $code = null;
        if (filled($couponCode)) {
            $check = $this->coupons->check($couponCode, $student, $course);
            if (!$check['ok']) {
                throw new PaymentException($check['message']);
            }
            $discount = $check['discount'];
            $code = $check['coupon']->code;
        }

        CourseAdmission::where('user_id', $student->id)->where('course_id', $course->id)->where('status', 'pending')
            ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Replaced by a new order']);

        return CourseAdmission::create([
            'exam_id' => $course->exam_id, 'course_id' => $course->id, 'user_id' => $student->id, 'status' => 'pending',
            'source' => isset(self::CHANNELS[$channel]) ? $channel : 'admin', 'gateway' => null, 'original_amount' => $original, 'discount_amount' => $discount,
            'amount' => round(max(0, $original - $discount), 2), 'coupon_code' => $code, 'notes' => $notes, 'created_by' => $staff?->id,
        ]);
    }

    private function newTransaction(CourseAdmission $admission, string $gateway, float $amount, string $type = 'payment', ?int $by = null): PaymentTransaction
    {
        return PaymentTransaction::create([
            'admission_id' => $admission->id, 'user_id' => $admission->user_id, 'course_id' => $admission->course_id, 'exam_id' => $admission->exam_id,
            'type' => $type, 'gateway' => $gateway, 'channel' => $admission->source, 'merchant_ref' => strtoupper(($type === 'refund' ? 'RF' : 'PV') . base_convert((string) time(), 10, 36) . Str::random(8)),
            'amount' => $amount, 'currency' => config('payments.currency'), 'status' => 'created', 'created_by' => $by,
        ]);
    }

    /**
     * Applies a gateway result to a payment. Safe to call many times (app report, webhook, status check): only the first success counts.
     *
     * @param  array{status: string, payment_id: ?string, method: ?string, amount: ?float, reason: ?string, raw: array}  $result
     */
    public function confirm(PaymentTransaction $tx, array $result): ?CourseAdmission
    {
        return DB::transaction(function () use ($tx, $result) {
            $tx = PaymentTransaction::lockForUpdate()->findOrFail($tx->id);
            $admission = CourseAdmission::withTrashed()->lockForUpdate()->find($tx->admission_id);

            if ($tx->status === 'success' || !$admission) {
                return $admission;   // already counted
            }

            if ($result['status'] === 'failed') {
                $tx->update(['status' => 'failed', 'failure_reason' => mb_substr((string) $result['reason'], 0, 290), 'gateway_payment_id' => $result['payment_id'] ?: $tx->gateway_payment_id, 'response' => $result['raw']]);

                return $admission;
            }
            if ($result['status'] !== 'success') {
                return $admission;   // still pending: nothing to do yet
            }

            // the gateway must have taken the amount we asked for
            if ($result['amount'] !== null && abs($result['amount'] - $tx->amount) > 0.01) {
                $tx->update(['status' => 'failed', 'failure_reason' => 'Amount mismatch: expected ' . $tx->amount . ', received ' . $result['amount'], 'response' => $result['raw']]);

                return $admission;
            }

            $tx->update([
                'status' => 'success', 'paid_at' => now(), 'gateway_payment_id' => $result['payment_id'] ?: $tx->gateway_payment_id,
                'method' => $result['method'], 'response' => $result['raw'], 'failure_reason' => null,
            ]);

            $wasCounted = $admission->admitted_at !== null && $admission->status === 'active';
            $admission->amount_paid = round($admission->amount_paid + $tx->amount, 2);
            $admission->gateway = $tx->gateway;
            $admission->status = 'active';
            $admission->cancelled_at = $admission->cancel_reason = null;
            if (!$wasCounted) {
                $admission->admitted_at = now();
                $admission->valid_until = $this->validUntil($admission->course ?? ExamCourse::find($admission->course_id));
            }
            $this->applyCommission($admission);
            $admission->save();

            \App\Models\PaymentLink::where('admission_id', $admission->id)->where('status', 'active')->update(['status' => 'paid', 'paid_at' => now()]);

            // the coupon is used up only now that the money is in
            if ($admission->coupon_code && !$admission->coupon_redemption_id && ($user = User::find($admission->user_id)) && ($course = ExamCourse::find($admission->course_id))) {
                try {
                    $admission->coupon_redemption_id = $this->coupons->redeem($admission->coupon_code, $user, $course, $this->couponSource($admission->source), $tx->merchant_ref)->id;
                    $admission->save();
                } catch (CouponException $e) {
                    // the coupon ran out between checkout and payment: the student already paid, so the admission stands
                }
            }

            return $admission;
        });
    }

    public function validUntil(?ExamCourse $course): ?\Illuminate\Support\Carbon
    {
        if (!$course) {
            return null;
        }

        return match ($course->validity_type) {
            'days' => now()->addDays((int) $course->validity_days),
            'date' => $course->valid_until ? $course->valid_until->copy()->endOfDay() : null,
            default => null,
        };
    }

    /** Commission is a snapshot of the % in force when the student was admitted, taken from the admission's fee (after refunds). */
    public function applyCommission(CourseAdmission $a): void
    {
        if ($a->commission_percent <= 0 && $a->commission_amount <= 0) {
            $a->commission_percent = (float) config('payments.commission_percent');
        }
        // an admission earns commission on its fee from the moment it is made, paid or not; a cancelled one only on the money that was kept
        $base = $a->status === 'cancelled' ? $a->retained() : max(0, round($a->amount - $a->refunded_amount, 2));
        $a->commission_amount = round($base * $a->commission_percent / 100, 2);
    }

    // ============================================================ backend

    /**
     * Staff adds a student to a course. $method is cash | upi | bank (money taken outside the gateways) or free (complimentary).
     *
     * @param  array{method: string, amount?: float, discount?: float, coupon_code?: ?string, valid_until?: ?string, notes?: ?string, paid_at?: ?string}  $opts
     * @throws PaymentException
     */
    public function addByStaff(User $student, ExamCourse $course, array $opts, User $staff): CourseAdmission
    {
        $method = $opts['method'];
        if (!isset(config('payments.manual_methods')[$method])) {
            throw new PaymentException('Choose how the fee was received.');
        }
        $current = CourseAdmission::where('user_id', $student->id)->where('course_id', $course->id)->where('status', 'active')->get()->first(fn ($a) => $a->grantsAccess());
        if ($current) {
            throw new PaymentException('This student is already in the course.');
        }

        $original = $course->effectivePrice();
        $discount = (float) ($opts['discount'] ?? 0);
        $code = null;
        if (filled($opts['coupon_code'] ?? null)) {
            $check = $this->coupons->check($opts['coupon_code'], $student, $course);
            if (!$check['ok']) {
                throw new PaymentException($check['message']);
            }
            $discount += $check['discount'];
            $code = $check['coupon']->code;
        }
        $payable = round(max(0, $original - $discount), 2);
        $noMoney = in_array($method, ['free', 'unpaid'], true);   // admitted now, nothing received yet (or waived): the fee still counts for commission
        $paid = $noMoney ? 0.0 : round((float) ($opts['amount'] ?? $payable), 2);
        if ($paid < 0) {
            throw new PaymentException('The amount cannot be negative.');
        }
        if ($paid > $payable + 0.001) {
            throw new PaymentException('The amount received (₹' . number_format($paid, 2) . ') is more than the fee (₹' . number_format($payable, 2) . '). Use the discount box to lower the fee.');
        }

        return DB::transaction(function () use ($student, $course, $staff, $method, $original, $discount, $code, $payable, $paid, $opts) {
            CourseAdmission::where('user_id', $student->id)->where('course_id', $course->id)->where('status', 'pending')
                ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Replaced by a staff admission']);

            $a = new CourseAdmission([
                'exam_id' => $course->exam_id, 'course_id' => $course->id, 'user_id' => $student->id, 'status' => 'active', 'source' => 'admin',
                'gateway' => $method, 'original_amount' => $original, 'discount_amount' => $discount, 'amount' => $payable, 'amount_paid' => $paid,
                'coupon_code' => $code, 'admitted_at' => now(), 'notes' => $opts['notes'] ?? null, 'created_by' => $staff->id,
                'valid_until' => !empty($opts['valid_until']) ? \Illuminate\Support\Carbon::parse($opts['valid_until'])->endOfDay() : $this->validUntil($course),
            ]);
            $this->applyCommission($a);
            $a->save();

            if ($paid > 0) {
                $tx = $this->newTransaction($a, $method, $paid, 'payment', $staff->id);
                $tx->update(['status' => 'success', 'paid_at' => !empty($opts['paid_at']) ? \Illuminate\Support\Carbon::parse($opts['paid_at']) : now(), 'method' => $method, 'failure_reason' => null]);
            }

            if ($code) {
                try {
                    $a->coupon_redemption_id = $this->coupons->redeem($code, $student, $course, 'admin', 'AD' . $a->id, $staff->id)->id;
                    $a->save();
                } catch (CouponException $e) {
                    throw new PaymentException($e->getMessage());
                }
            }

            return $a;
        });
    }

    /**
     * Money received later for an admission that was made without full payment (cash, UPI, bank).
     *
     * @throws PaymentException
     */
    public function recordPayment(CourseAdmission $a, string $method, float $amount, ?string $paidAt, ?User $staff): CourseAdmission
    {
        if (!in_array($method, ['cash', 'upi', 'bank'], true)) {
            throw new PaymentException('Choose how the money was received.');
        }
        if ($a->status !== 'active') {
            throw new PaymentException('Only an active admission can receive a payment.');
        }
        $amount = round($amount, 2);
        $due = round($a->amount - $a->amount_paid, 2);
        if ($amount <= 0) {
            throw new PaymentException('Enter the amount received.');
        }
        if ($amount > $due + 0.001) {
            throw new PaymentException('The balance due is only ₹' . number_format($due, 2) . '.');
        }

        return DB::transaction(function () use ($a, $method, $amount, $paidAt, $staff) {
            $a = CourseAdmission::lockForUpdate()->findOrFail($a->id);
            $tx = $this->newTransaction($a, $method, $amount, 'payment', $staff?->id);
            $tx->update(['status' => 'success', 'paid_at' => $paidAt ? \Illuminate\Support\Carbon::parse($paidAt) : now(), 'method' => $method, 'failure_reason' => null]);
            $a->amount_paid = round($a->amount_paid + $amount, 2);
            if (!$a->gateway || in_array($a->gateway, ['free', 'unpaid'], true)) {
                $a->gateway = $method;
            }
            $this->applyCommission($a);
            $a->save();

            return $a;
        });
    }

    // ===================================================== cancel / remove

    /**
     * Cancels an admission (the student loses the course) and records / sends a refund.
     *
     * @param  string  $refundMode  none | manual (record only; money returned outside) | gateway (sent back through Razorpay / PhonePe)
     * @throws PaymentException
     */
    public function cancel(CourseAdmission $a, string $reason, float $refund, string $refundMode, User $by): CourseAdmission
    {
        if ($a->status === 'cancelled') {
            throw new PaymentException('This admission is already cancelled.');
        }
        $refund = round(max(0, $refund), 2);
        $refundable = round($a->amount_paid - $a->refunded_amount, 2);
        if ($refundMode === 'none') {
            $refund = 0.0;
        }
        if ($refund > $refundable + 0.001) {
            throw new PaymentException('The refund cannot be more than ₹' . number_format($refundable, 2) . ' (paid minus already refunded).');
        }

        $reference = null;
        if ($refund > 0 && $refundMode === 'gateway') {
            $payment = $a->transactions()->where('type', 'payment')->where('status', 'success')->whereIn('gateway', ['razorpay', 'phonepe'])->latest('id')->first();
            if (!$payment) {
                throw new PaymentException('There is no online payment to refund. Choose "record a manual refund".');
            }
            $reference = $this->gateways->gateway($payment->gateway)->refund($payment, $refund, $reason);   // throws when the gateway refuses
            $refundGateway = $payment->gateway;
        }

        return DB::transaction(function () use ($a, $reason, $refund, $refundMode, $by, $reference, &$refundGateway) {
            $a = CourseAdmission::lockForUpdate()->findOrFail($a->id);

            if ($refund > 0) {
                $rtx = $this->newTransaction($a, $refundMode === 'gateway' ? $refundGateway : ($a->gateway && $a->gateway !== 'free' ? $a->gateway : 'cash'), $refund, 'refund', $by->id);
                $rtx->update([
                    'status' => 'success', 'paid_at' => now(), 'gateway_payment_id' => $reference['id'] ?? null,
                    'response' => $reference['raw'] ?? null, 'failure_reason' => null,
                ]);
                $a->refunded_amount = round($a->refunded_amount + $refund, 2);
            }

            $wasPending = $a->status === 'pending';
            $a->status = 'cancelled';
            $a->cancelled_at = now();
            $a->cancel_reason = mb_substr($reason, 0, 290);
            $a->cancelled_by = $by->id;
            $this->applyCommission($a);
            $a->save();

            // the coupon use goes back to the coupon
            if ($a->coupon_redemption_id && ($red = ExamCouponRedemption::find($a->coupon_redemption_id))) {
                $this->coupons->reverse($red, 'Admission cancelled');
            }

            return $a;
        });
    }

    /** Deletes the record. Only an admission that is not holding the course: pending or cancelled. Its payment rows stay for the accounts. */
    public function remove(CourseAdmission $a): void
    {
        if ($a->status === 'active') {
            throw new PaymentException('Cancel the admission first, then it can be removed.');
        }
        $a->delete();
    }

    // =========================================================== gateway events

    /**
     * The student's app / page reports a finished payment. Razorpay: the signature must match; PhonePe: PhonePe is asked directly.
     * Either way the money counts only after the gateway has confirmed it. Returns false when the signature is wrong.
     */
    public function verifyReturn(PaymentTransaction $tx, array $data): bool
    {
        if ($tx->gateway === 'razorpay') {
            /** @var RazorpayGateway $gw */
            $gw = $this->gateways->gateway('razorpay');
            $ok = filled($data['razorpay_order_id'] ?? null) && filled($data['razorpay_payment_id'] ?? null) && filled($data['razorpay_signature'] ?? null)
                && $data['razorpay_order_id'] === $tx->gateway_order_id
                && $gw->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);
            if (!$ok) {
                return false;
            }
            try {
                $this->confirm($tx, $gw->fetchStatus($tx));
            } catch (PaymentException $e) {
                // Razorpay's status call failed; the signature already proves the payment
            }
            if ($tx->fresh()->status !== 'success') {
                $this->confirm($tx, ['status' => 'success', 'payment_id' => $data['razorpay_payment_id'], 'method' => null, 'amount' => null, 'reason' => null, 'raw' => ['verified_by' => 'signature']]);
            }

            return true;
        }

        $this->confirm($tx, $this->gateways->gateway($tx->gateway)->fetchStatus($tx));

        return true;
    }

    /** A gateway told us (webhook / return page / status check) what happened to the payment with this reference or order id. */
    public function applyGatewayEvent(string $gateway, array $event): ?CourseAdmission
    {
        if (($event['status'] ?? '') === 'ignored' || ($event['status'] ?? '') === 'refunded') {
            return null;
        }
        $tx = PaymentTransaction::where('gateway', $gateway)->where('type', 'payment')
            ->when(!empty($event['merchant_ref']), fn ($q) => $q->where('merchant_ref', $event['merchant_ref']))
            ->when(empty($event['merchant_ref']) && !empty($event['order_id']), fn ($q) => $q->where('gateway_order_id', $event['order_id']))
            ->first();

        return $tx ? $this->confirm($tx, $event) : null;
    }
}
