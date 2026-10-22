<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A subject / folder of one exam type; free YouTube classes are filed under it. */
class ExamSubject extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean'];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }
}
