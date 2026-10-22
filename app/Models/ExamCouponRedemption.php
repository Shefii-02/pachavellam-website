<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCouponRedemption extends Model
{
    protected $table = 'exam_coupon_redemptions';

    protected $guarded = ['id'];

    protected $casts = [
        'original_amount' => 'float',
        'discount_amount' => 'float',
        'final_amount' => 'float',
        'reversed_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(ExamCoupon::class, 'coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }
}
