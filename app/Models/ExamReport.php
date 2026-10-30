<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's "report an error" on an exam. */
class ExamReport extends Model
{
    protected $guarded = [];

    public const TYPES = ['Error in Question', 'Incorrect Answer', 'No Explanation/Instructions', 'Quiz not loading', 'Graphs/Images not loading', 'Other'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(DailyExam::class, 'exam_id');
    }
}
