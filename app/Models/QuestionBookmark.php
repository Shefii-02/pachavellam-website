<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A question a student saved from an MCQ exam (shown in My account > Saved questions). */
class QuestionBookmark extends Model
{
    protected $table = 'question_bookmarks';
    protected $guarded = [];
}
