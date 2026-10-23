<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A season of an exam series (daily / OMR / CA / GK) of one exam type. */
class ExamSeason extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'valid_until' => 'date', 'is_paid' => 'boolean', 'price' => 'float', 'discount_price' => 'float'];

    public const STATUSES = ['draft' => ['Draft', 'secondary'], 'published' => ['Published', 'success'], 'archived' => ['Archived', 'dark']];

    public const BASES = ['subject' => 'Subject based', 'category' => 'Category based', 'exam' => 'Exam based', 'other' => 'Other'];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function exams()
    {
        return $this->hasMany(CourseExam::class, 'season_id');
    }
}
