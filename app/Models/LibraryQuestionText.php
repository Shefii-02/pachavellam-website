<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryQuestionText extends Model
{
    protected $table = 'library_question_texts';

    protected $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(LibraryQuestion::class, 'question_id');
    }

    /** [1 => text, 2 => text, ...] */
    public function options(): array
    {
        return [1 => $this->option_1, 2 => $this->option_2, 3 => $this->option_3, 4 => $this->option_4, 5 => $this->option_5, 6 => $this->option_6];
    }
}
