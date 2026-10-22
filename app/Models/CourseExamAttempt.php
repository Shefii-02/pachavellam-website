<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One sitting of a builder exam by a student (see App\Services\TestEngine). */
class CourseExamAttempt extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'answers' => 'array', 'sections' => 'array', 'section_scores' => 'array',
        'score' => 'float', 'max_score' => 'float', 'negative' => 'float',
        'last_tick_at' => 'datetime', 'started_at' => 'datetime', 'submitted_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(CourseExam::class, 'course_exam_id')->withTrashed();   // a deleted exam's results stay readable
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function attempted(): int
    {
        return $this->correct + $this->wrong;
    }

    /** Right answers out of the answered ones, in %. */
    public function accuracy(): float
    {
        return $this->attempted() ? round($this->correct / $this->attempted() * 100, 1) : 0.0;
    }
}
