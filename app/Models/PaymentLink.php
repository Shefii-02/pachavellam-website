<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/** A web page where one student pays for one course: sent on WhatsApp, or opened from the website / Android / iOS app. */
class PaymentLink extends Model
{
    use LogsActivity;
    protected $table = 'payment_links';

    protected $guarded = ['id'];

    protected $casts = ['expires_at' => 'datetime', 'last_opened_at' => 'datetime', 'paid_at' => 'datetime'];

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function url(): string
    {
        return url('pay/' . $this->token);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /** active / paid / cancelled / expired */
    public function displayStatus(): string
    {
        return ($this->status === 'active' && $this->isExpired()) ? 'expired' : $this->status;
    }

    public function isUsable(): bool
    {
        return $this->displayStatus() === 'active';
    }

    public function order()
    {
        return $this->belongsTo(CourseAdmission::class, 'admission_id')->withTrashed();
    }

    /** The text that goes with the link (WhatsApp / SMS / email). */
    public function text(): string
    {
        return $this->message ?: self::defaultMessage($this->user?->name, $this->course?->title, $this->order?->amount, $this->url());
    }

    public static function defaultMessage(?string $name, ?string $course, $amount, string $link): string
    {
        return 'Hello' . ($name ? ' ' . $name : '') . ', please complete your admission for "' . $course . '"'
            . ($amount ? ' (₹' . number_format((float) $amount, 2) . ')' : '') . " using this secure payment link:\n" . $link;
    }

    private function phone(): string
    {
        $phone = preg_replace('/\D/', '', (string) $this->user?->mobile);

        return strlen($phone) === 10 ? '91' . $phone : $phone;   // Indian numbers saved without the country code
    }

    /** wa.me link that opens WhatsApp with the message ready for the student's number. */
    public function whatsappUrl(): string
    {
        return 'https://wa.me/' . $this->phone() . '?text=' . rawurlencode($this->text());
    }

    public function smsUrl(): string
    {
        return 'sms:' . ($this->user?->mobile ? '+' . $this->phone() : '') . '?&body=' . rawurlencode($this->text());
    }

    public function emailUrl(): string
    {
        return 'mailto:' . ($this->user?->email ?? '') . '?subject=' . rawurlencode('Complete your admission: ' . $this->course?->title) . '&body=' . rawurlencode($this->text());
    }
}
