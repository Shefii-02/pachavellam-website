<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseAdmission;
use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;

/**
 * Joining a course from the app / website.
 *   POST api/course-checkout          {course_id, coupon_code?, gateway?}   starts the payment (or joins at once when the course is free)
 *   POST api/course-payment-verify    razorpay: {merchant_ref, razorpay_order_id, razorpay_payment_id, razorpay_signature}   phonepe: {merchant_ref}
 *   POST api/course-payment-status    {merchant_ref}   the app can ask while the student is on the gateway page
 *   POST api/my-course-admissions     the student's courses
 * Gateways call us back on  api/payments/razorpay/webhook  and  api/payments/phonepe/callback  (no login; checked by signature).
 * Whatever the app says is checked with the gateway again: a payment counts only when the gateway confirms it.
 */
class CoursePaymentController extends Controller
{
    public function __construct(private AdmissionService $admissions, private PaymentManager $gateways)
    {
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer', 'coupon_code' => 'nullable|string|max:60', 'gateway' => 'nullable|in:razorpay,phonepe', 'channel' => 'nullable|in:android,ios,web',
        ]);
        $course = ExamCourse::find($data['course_id']);
        if (!$course) {
            return response()->json(['message' => 'Course not found.', 'status' => 404], 404);
        }

        try {
            $r = $this->admissions->checkout($request->user(), $course, $data['coupon_code'] ?? null, $data['gateway'] ?? null, $data['channel'] ?? 'android');
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 422], 422);
        }

        return response()->json([
            'free' => $r['free'], 'message' => $r['free'] ? 'You have joined the course.' : 'Complete the payment.',
            'admission_id' => $r['admission']->id, 'merchant_ref' => $r['transaction']?->merchant_ref, 'gateway' => $r['gateway'],
            'amount' => $r['amount'], 'original_amount' => $r['admission']->original_amount, 'discount_amount' => $r['admission']->discount_amount,
            'checkout' => $r['checkout'], 'status' => 200,
        ]);
    }

    /** POST api/course-payment-link: a payment link for the logged in student - open it in the browser to pay (the app can share it too). */
    public function paymentLink(Request $request, PaymentLinkService $links)
    {
        $data = $request->validate([
            'course_id' => 'required|integer', 'coupon_code' => 'nullable|string|max:60', 'channel' => 'nullable|in:android,ios,web,whatsapp', 'hours' => 'nullable|integer|min:1|max:720',
        ]);
        $course = ExamCourse::find($data['course_id']);
        if (!$course) {
            return response()->json(['message' => 'Course not found.', 'status' => 404], 404);
        }

        try {
            $link = $links->create($request->user(), $course, $data['channel'] ?? 'android', $data['coupon_code'] ?? null, (int) ($data['hours'] ?? 48), null, null);
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 422], 422);
        }

        return response()->json(['url' => $link->url(), 'whatsapp_url' => $link->whatsappUrl(), 'expires_at' => $link->expires_at?->toIso8601String(), 'status' => 200]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'merchant_ref' => 'required|string|max:40', 'razorpay_order_id' => 'nullable|string', 'razorpay_payment_id' => 'nullable|string', 'razorpay_signature' => 'nullable|string',
        ]);
        $tx = $this->ownTransaction($request, $data['merchant_ref']);
        if (!$tx) {
            return response()->json(['message' => 'Payment not found.', 'status' => 404], 404);
        }

        try {
            if (!$this->admissions->verifyReturn($tx, $data)) {
                return response()->json(['message' => 'The payment could not be verified.', 'paid' => false, 'status' => 422], 422);
            }
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage(), 'paid' => false, 'status' => 422], 422);
        }

        return $this->statusResponse($tx->fresh());
    }

    public function status(Request $request)
    {
        $data = $request->validate(['merchant_ref' => 'required|string|max:40']);
        $tx = $this->ownTransaction($request, $data['merchant_ref']);
        if (!$tx) {
            return response()->json(['message' => 'Payment not found.', 'status' => 404], 404);
        }

        if (in_array($tx->status, ['created', 'pending'], true) && $tx->isOnline()) {
            try {
                $this->admissions->confirm($tx, $this->gateways->gateway($tx->gateway)->fetchStatus($tx));
            } catch (PaymentException $e) {
                // the gateway could not be reached: report what we know
            }
        }

        return $this->statusResponse($tx->fresh());
    }

    public function mine(Request $request)
    {
        $rows = CourseAdmission::with('course:id,title,slug,cover_image,exam_id')->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'cancelled'])->whereNotNull('admitted_at')->orderByDesc('admitted_at')->get()
            ->map(fn ($a) => [
                'admission_id' => $a->id, 'course_id' => $a->course_id, 'title' => $a->course?->title, 'slug' => $a->course?->slug,
                'status' => $a->displayStatus(), 'has_access' => $a->grantsAccess(), 'admitted_at' => $a->admitted_at?->toIso8601String(),
                'valid_until' => $a->valid_until?->toIso8601String(), 'amount_paid' => $a->amount_paid,
            ]);

        return response()->json(['data' => $rows, 'status' => 200]);
    }

    // ------------------------------------------------------------ gateway calls

    public function razorpayWebhook(Request $request)
    {
        $event = $this->gateways->gateway('razorpay')->parseWebhook($request);
        if ($event === null) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }
        $this->admissions->applyGatewayEvent('razorpay', $event);

        return response()->json(['ok' => true]);
    }

    public function phonepeCallback(Request $request)
    {
        $event = $this->gateways->gateway('phonepe')->parseWebhook($request);
        if ($event === null) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }
        $this->admissions->applyGatewayEvent('phonepe', $event);

        return response()->json(['ok' => true]);
    }

    /** PhonePe sends the student's browser here after paying: check the real result with PhonePe and show a plain page the app can close. */
    public function phonepeReturn(Request $request, string $ref)
    {
        $tx = PaymentTransaction::where('gateway', 'phonepe')->where('merchant_ref', $ref)->first();
        $state = 'unknown';
        if ($tx) {
            try {
                $this->admissions->confirm($tx, $this->gateways->gateway('phonepe')->fetchStatus($tx));
            } catch (PaymentException $e) {
                // shown as pending below
            }
            $state = $tx->fresh()->status;
        }
        $text = ['success' => 'Payment received. You can close this page and return to the app.', 'failed' => 'The payment did not go through. You can close this page and try again.'][$state]
            ?? 'We are confirming your payment. You can close this page; your course appears in the app shortly.';

        return response('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment</title>'
            . '<body style="font-family:sans-serif;text-align:center;padding:48px 20px"><h2>' . e($state === 'success' ? 'Thank you' : 'Payment status') . '</h2><p>' . e($text) . '</p></body>');
    }

    // ----------------------------------------------------------------- helpers

    private function ownTransaction(Request $request, string $ref): ?PaymentTransaction
    {
        return PaymentTransaction::where('merchant_ref', $ref)->where('user_id', $request->user()->id)->where('type', 'payment')->first();
    }

    private function statusResponse(PaymentTransaction $tx)
    {
        $a = CourseAdmission::withTrashed()->find($tx->admission_id);

        return response()->json([
            'paid' => $tx->status === 'success', 'payment_status' => $tx->status, 'message' => match ($tx->status) {
                'success' => 'Payment received. You have joined the course.', 'failed' => $tx->failure_reason ?: 'The payment failed.', default => 'Waiting for the payment.',
            },
            'admission_id' => $a?->id, 'has_access' => (bool) $a?->grantsAccess(), 'merchant_ref' => $tx->merchant_ref, 'status' => 200,
        ]);
    }
}
