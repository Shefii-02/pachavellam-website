<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** A story (the banner slider on the app's home screen). */
class Banner extends Model
{
    use HasFactory;
    protected $table = 'banner_slider';

    /** Exam types this story is shown for. */
    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'story_exams', 'story_id', 'exam_id')->withTimestamps();
    }

    /** Stories shown for one exam type (slug or id). */
    public function scopeForExam($query, $exam)
    {
        return $query->whereIn('banner_slider.id', fn ($s) => $s->select('se.story_id')->from('story_exams as se')
            ->join('competitive_exams as e', 'e.id', '=', 'se.exam_id')
            ->where(fn ($w) => ctype_digit((string) $exam) ? $w->where('e.id', $exam) : $w->where('e.slug', $exam)));
    }
}
