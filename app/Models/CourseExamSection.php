<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseExamSection extends Model
{
    protected $table = 'course_exam_sections';

    protected $guarded = ['id'];

    protected $casts = ['right_mark' => 'float', 'negative_mark' => 'float'];

    public function exam()
    {
        return $this->belongsTo(CourseExam::class, 'course_exam_id');
    }

    public function links()
    {
        return $this->hasMany(CourseExamQuestion::class, 'section_id')->orderBy('position')->orderBy('id');
    }
}
