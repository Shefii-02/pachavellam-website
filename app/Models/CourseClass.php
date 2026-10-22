<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A library class placed in a course (as a free or a paid class). */
class CourseClass extends Model
{
    protected $table = 'course_classes';

    protected $guarded = ['id'];

    protected $casts = ['unlock_at' => 'datetime', 'is_preview' => 'boolean'];

    public function libraryClass()
    {
        return $this->belongsTo(LibraryClass::class, 'class_id');
    }
}
