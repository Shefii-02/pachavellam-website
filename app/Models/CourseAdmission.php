<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A student's place in a course (online payment, free course, or added by staff). */
class CourseAdmission extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected static function booted(): void
    {
        // the sales team hears about every new admission and every cancellation
        static::saved(function (self $a) {
            $becameActive = $a->status === 'active' && $a->admitted_at && ($a->wasRecentlyCreated || $a->wasChanged('status'));
            if ($becameActive) {
                $who = $a->user?->name ?: 'A student';
                \App\Services\StaffNotifier::push('admission', $who . ' joined ' . ($a->course?->title ?: 'a course'),
                    '₹' . number_format($a->amount_paid, 2) . ' paid · ' . (\App\Services\Payments\AdmissionService::CHANNELS[$a->source] ?? $a->source) . ($a->amount - $a->amount_paid > 0.009 ? ' · balance ₹' . number_format($a->amount - $a->amount_paid, 2) . ' due' : ''),
                    url('admin/admissions/' . $a->id), $a->exam_id, $a->id);
            } elseif ($a->status === 'cancelled' && $a->admitted_at && $a->wasChanged('status')) {
                \App\Services\StaffNotifier::push('cancellation', 'Admission cancelled: ' . ($a->user?->name ?: 'student') . ' · ' . ($a->course?->title ?: 'course'),
                    $a->cancel_reason . ($a->refunded_amount > 0 ? ' · refunded ₹' . number_format($a->refunded_amount, 2) : ''), url('admin/admissions/' . $a->id), $a->exam_id, $a->id);
            }
        });
    }

    protected $table = 'course_admissions';

    protected $guarded = ['id'];

    protected $casts = [
        'original_amount' => 'float', 'discount_amount' => 'float', 'amount' => 'float', 'amount_paid' => 'float',
        'refunded_amount' => 'float', 'commission_percent' => 'float', 'commission_amount' => 'float',
        'admitted_at' => 'datetime', 'valid_until' => 'datetime', 'cancelled_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => ['Payment pending', 'warning'],
        'active' => ['Active', 'success'],
        'expired' => ['Expired', 'secondary'],
        'cancelled' => ['Cancelled', 'danger'],
    ];

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'admission_id')->orderBy('id');
    }

    /** The status to show: an active admission whose time is over reads "expired". */
    public function displayStatus(): string
    {
        if ($this->status === 'active' && $this->valid_until && $this->valid_until->isPast()) {
            return 'expired';
        }

        return $this->status;
    }

    /** Does the student have the course right now? */
    public function grantsAccess(): bool
    {
        return $this->status === 'active' && (!$this->valid_until || $this->valid_until->isFuture());
    }

    /** Money kept after refunds. */
    public function retained(): float
    {
        return round(max(0, $this->amount_paid - $this->refunded_amount), 2);
    }

    /** Retained money minus the platform commission. */
    public function net(): float
    {
        return round($this->retained() - $this->commission_amount, 2);
    }

    public function scopeCounted($query)
    {
        return $query->whereIn('status', ['active', 'cancelled'])->whereNotNull('admitted_at');   // pending ones never became admissions
    }
}
