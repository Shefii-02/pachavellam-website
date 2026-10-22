<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One payment or refund: a Razorpay / PhonePe order, or a cash / UPI entry made by staff. */
class PaymentTransaction extends Model
{
    protected static function booted(): void
    {
        static::updated(function (self $t) {
            if ($t->type === 'payment' && $t->status === 'failed' && $t->wasChanged('status') && $t->isOnline()) {
                \App\Services\StaffNotifier::push('payment_failed', 'Payment failed: ' . ($t->user?->name ?: 'student') . ' · ₹' . number_format($t->amount, 2),
                    $t->failure_reason, url('admin/admissions/' . $t->admission_id), $t->exam_id, $t->admission_id);
            }
        });
    }

    protected $table = 'payment_transactions';

    protected $guarded = ['id'];

    protected $casts = ['amount' => 'float', 'request' => 'array', 'response' => 'array', 'paid_at' => 'datetime'];

    public const GATEWAYS = ['razorpay' => 'Razorpay', 'phonepe' => 'PhonePe', 'cash' => 'Cash', 'upi' => 'UPI / transfer', 'bank' => 'Bank deposit', 'unpaid' => 'Not paid yet', 'free' => 'Free'];

    public const STATUSES = ['created' => 'secondary', 'pending' => 'warning', 'success' => 'success', 'failed' => 'danger'];

    public function admission()
    {
        return $this->belongsTo(CourseAdmission::class, 'admission_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function isOnline(): bool
    {
        return in_array($this->gateway, ['razorpay', 'phonepe'], true);
    }

    public function gatewayLabel(): string
    {
        return self::GATEWAYS[$this->gateway] ?? ucfirst($this->gateway);
    }
}
