<?php

namespace App\Services;

use App\Models\ExamCoupon;
use App\Models\ExamCouponRedemption;
use App\Models\ExamCourse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The one place that decides whether a coupon can be used and what it is worth.
 *
 *   $result = app(CouponService::class)->check('ONAM25', $user, $course);   // read only, for the "apply coupon" box
 *   $redemption = app(CouponService::class)->redeem('ONAM25', $user, $course, 'app', $orderId);  // at payment time
 *
 * A coupon can be limited to some exam types (KPSC, RRB, ...) and, within those, to some courses.
 * redeem() locks the coupon row and re-checks every limit inside a transaction, so two people paying at
 * the same moment cannot both take the last use.
 */
class CouponService
{
    /**
     * @return array{ok: bool, message: string, coupon: ?ExamCoupon, original: float, discount: float, final: float}
     */
    public function check(string $code, ?User $user, ExamCourse $course, bool $lock = false): array
    {
        $coupon = ExamCoupon::query()->when($lock, fn ($q) => $q->lockForUpdate())
            ->where('code', ExamCoupon::normalizeCode($code))->first();

        $original = $course->effectivePrice();

        $fail = fn (string $message) => [
            'ok' => false, 'message' => $message, 'coupon' => $coupon,
            'original' => $original, 'discount' => 0.0, 'final' => $original,
        ];

        if (!$coupon) {
            return $fail('This coupon code is not valid.');
        }
        if (!$coupon->is_active) {
            return $fail('This coupon is not active.');
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return $fail('This coupon is not valid yet. It starts on ' . $coupon->starts_at->format('d M Y, h:i A') . '.');
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return $fail('This coupon has expired.');
        }

        if ($course->isFree() || $original <= 0) {
            return $fail('Coupons cannot be used on a free course.');
        }
        if ($course->status !== 'published') {
            return $fail('This course is not open for enrolment.');
        }

        // exam type first (a coupon for KPSC does nothing on an RRB course), then the course list
        if ($coupon->exam_scope === 'selected'
            && !DB::table('exam_coupon_exams')->where('coupon_id', $coupon->id)->where('exam_id', $course->exam_id)->exists()) {
            return $fail('This coupon is not valid for this exam.');
        }
        if ($coupon->applies_to === 'selected'
            && !DB::table('exam_coupon_courses')->where('coupon_id', $coupon->id)->where('course_id', $course->id)->exists()) {
            return $fail('This coupon is not valid for this course.');
        }

        if ($original < $coupon->min_purchase) {
            return $fail('This coupon needs a purchase of at least ₹' . number_format($coupon->min_purchase) . '.');
        }
        if ($coupon->max_purchase !== null && $original > $coupon->max_purchase) {
            return $fail('This coupon is only for purchases up to ₹' . number_format($coupon->max_purchase) . '.');
        }

        if ($coupon->total_limit !== null && $coupon->activeRedemptions()->count() >= $coupon->total_limit) {
            return $fail('This coupon has been used the maximum number of times.');
        }
        if ($user && $coupon->per_user_limit !== null
            && $coupon->activeRedemptions()->where('user_id', $user->id)->count() >= $coupon->per_user_limit) {
            return $fail($coupon->per_user_limit === 1
                ? 'You have already used this coupon.'
                : 'You have used this coupon the maximum number of times (' . $coupon->per_user_limit . ').');
        }

        $discount = $this->discountFor($coupon, $original);

        return [
            'ok' => true, 'message' => 'Coupon applied. You save ₹' . number_format($discount, 2) . '.', 'coupon' => $coupon,
            'original' => $original, 'discount' => $discount, 'final' => round($original - $discount, 2),
        ];
    }

    /** Percent coupons (capped by max_discount) or a fixed amount - never more than the price. */
    public function discountFor(ExamCoupon $coupon, float $amount): float
    {
        $discount = $coupon->discount_type === 'percent'
            ? $amount * $coupon->discount_value / 100
            : $coupon->discount_value;

        if ($coupon->discount_type === 'percent' && $coupon->max_discount !== null) {
            $discount = min($discount, $coupon->max_discount);
        }

        return round(max(0, min($discount, $amount)), 2);
    }

    /**
     * Use the coupon. Throws CouponException with a student friendly message when it cannot be used.
     */
    public function redeem(string $code, User $user, ExamCourse $course, string $source = 'app', ?string $reference = null, ?int $createdBy = null): ExamCouponRedemption
    {
        return DB::transaction(function () use ($code, $user, $course, $source, $reference, $createdBy) {
            $result = $this->check($code, $user, $course, true);

            if (!$result['ok']) {
                throw new CouponException($result['message']);
            }

            return ExamCouponRedemption::create([
                'coupon_id' => $result['coupon']->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'exam_id' => $course->exam_id,
                'code' => $result['coupon']->code,
                'original_amount' => $result['original'],
                'discount_amount' => $result['discount'],
                'final_amount' => $result['final'],
                'status' => 'redeemed',
                'source' => in_array($source, ['app', 'web', 'admin']) ? $source : 'app',
                'reference' => $reference,
                'created_by' => $createdBy,
            ]);
        });
    }

    /** A refund or cancelled order: the use is given back (it no longer counts against any limit). */
    public function reverse(ExamCouponRedemption $redemption, ?string $reason = null): ExamCouponRedemption
    {
        if ($redemption->status === 'reversed') {
            return $redemption;
        }

        $redemption->update(['status' => 'reversed', 'reversed_at' => now(), 'reversed_reason' => $reason]);

        return $redemption;
    }
}
