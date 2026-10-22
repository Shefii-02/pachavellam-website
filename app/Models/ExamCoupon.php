<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

class ExamCoupon extends Model
{
    use LogsActivity;
    protected $table = 'exam_coupons';

    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'float',
        'max_discount' => 'float',
        'min_purchase' => 'float',
        'max_purchase' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /** Exam types the coupon is limited to (when exam_scope = selected). */
    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'exam_coupon_exams', 'coupon_id', 'exam_id')->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(ExamCourse::class, 'exam_coupon_courses', 'coupon_id', 'course_id')->withTimestamps();
    }

    public function redemptions()
    {
        return $this->hasMany(ExamCouponRedemption::class, 'coupon_id');
    }

    /** Redemptions that still count against the limits. */
    public function activeRedemptions()
    {
        return $this->redemptions()->where('status', 'redeemed');
    }

    /** Coupons that can be used on an exam type: those for all exam types plus those that list it. */
    public function scopeForExam($query, $examId)
    {
        return $query->where(fn ($q) => $q->where('exam_scope', 'all')
            ->orWhereIn('exam_coupons.id', fn ($s) => $s->select('coupon_id')->from('exam_coupon_exams')->where('exam_id', $examId)));
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', $code));
    }

    public function discountLabel(): string
    {
        $label = $this->discount_type === 'percent'
            ? rtrim(rtrim(number_format($this->discount_value, 2), '0'), '.') . '% off'
            : '₹' . number_format($this->discount_value) . ' off';

        if ($this->discount_type === 'percent' && $this->max_discount) {
            $label .= ' (up to ₹' . number_format($this->max_discount) . ')';
        }

        return $label;
    }

    /**
     * [label, bootstrap colour, usable now?]
     * $used = redemptions that count (pass withCount value to avoid a query per row).
     */
    public function state(?int $used = null): array
    {
        $used ??= $this->activeRedemptions()->count();
        $now = now();

        return match (true) {
            !$this->is_active => ['Paused', 'secondary', false],
            $this->starts_at && $this->starts_at->gt($now) => ['Scheduled', 'info', false],
            $this->expires_at && $this->expires_at->lt($now) => ['Expired', 'dark', false],
            $this->total_limit !== null && $used >= $this->total_limit => ['Used up', 'warning', false],
            default => ['Active', 'success', true],
        };
    }
}
