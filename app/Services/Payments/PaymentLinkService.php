<?php

namespace App\Services\Payments;

use App\Models\CourseAdmission;
use App\Models\ExamCourse;
use App\Models\PaymentLink;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Support\Str;

/**
 * Payment links: the order is made when the student presses Pay on the link page, so the same link can be reopened after a failed
 * payment. Once paid, the link is closed and the course is the student's.
 */
class PaymentLinkService
{
    public function __construct(private AdmissionService $admissions, private CouponService $coupons)
    {
    }

    /** @throws PaymentException */
    public function create(User $student, ExamCourse $course, string $channel, ?string $coupon, int $hours, ?string $note, ?int $by, ?string $message = null): PaymentLink
    {
        if (!isset(AdmissionService::CHANNELS[$channel]) || $channel === 'app') {
            throw new PaymentException('Unknown channel.');
        }
        if ($course->status !== 'published') {
            throw new PaymentException('This course is not open for admission.');
        }
        if ($course->isFree()) {
            throw new PaymentException('A free course needs no payment link: add the student directly.');
        }
        $already = CourseAdmission::where('user_id', $student->id)->where('course_id', $course->id)->where('status', 'active')->get()->first(fn ($a) => $a->grantsAccess());
        if ($already) {
            throw new PaymentException('This student is already in the course.');
        }
        $staff = $by ? User::find($by) : null;
        // the order exists from now on (an admission waiting for payment) so it shows in Admissions straight away
        $order = $this->admissions->createOrder($student, $course, $channel, $coupon, $staff, $note);

        $link = PaymentLink::create([
            'token' => Str::random(40), 'exam_id' => $course->exam_id, 'course_id' => $course->id, 'user_id' => $student->id, 'channel' => $channel,
            'coupon_code' => $order->coupon_code, 'note' => $note ? mb_substr($note, 0, 290) : null, 'status' => 'active', 'admission_id' => $order->id,
            'expires_at' => now()->addHours(max(1, min($hours, 24 * 60))), 'created_by' => $by,
        ]);
        $link->setRelation('user', $student)->setRelation('course', $course);
        $link->update(['message' => $this->buildMessage($message, $student, $course, $order->amount, $link->url())]);

        return $link;
    }

    /** {name} {course} {amount} {link} in the staff's text are filled in; the link is always added if it was left out. */
    public function buildMessage(?string $text, User $student, ExamCourse $course, $amount, string $url): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return PaymentLink::defaultMessage($student->name, $course->title, $amount, $url);
        }
        $text = strtr($text, ['{name}' => $student->name ?: 'Student', '{course}' => $course->title, '{amount}' => '₹' . number_format((float) $amount, 2), '{link}' => $url]);

        return str_contains($text, $url) ? $text : $text . "\n" . $url;
    }

    /**
     * The student pressed Pay: makes the order (a fresh one each time) and returns what the page needs to open the gateway.
     *
     * @return array{gateway: string, checkout: array, merchant_ref: string, amount: float, free: bool}
     * @throws PaymentException
     */
    public function start(PaymentLink $link, ?string $gateway = null): array
    {
        if (!$link->isUsable()) {
            throw new PaymentException(match ($link->displayStatus()) {
                'paid' => 'This link has already been paid.', 'expired' => 'This link has expired. Ask for a new one.', default => 'This link is no longer valid.',
            });
        }

        $r = $this->admissions->checkout($link->user, $link->course, $link->coupon_code, $gateway, $link->channel, $link->order);
        $link->update(['admission_id' => $r['admission']->id]);
        if ($r['free']) {
            $link->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return ['gateway' => $r['gateway'], 'checkout' => $r['checkout'], 'merchant_ref' => $r['transaction']?->merchant_ref, 'amount' => $r['amount'], 'free' => $r['free']];
    }
}
