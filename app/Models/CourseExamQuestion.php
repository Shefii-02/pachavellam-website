<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A library question placed in a section of a course exam. */
class CourseExamQuestion extends Model
{
    protected $table = 'course_exam_questions';

    protected $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(LibraryQuestion::class, 'question_id');
    }

    public function section()
    {
        return $this->belongsTo(CourseExamSection::class, 'section_id');
    }
}
