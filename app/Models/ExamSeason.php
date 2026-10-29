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

    public const PAGES = ['daily' => 'daily-exams', 'omr' => 'omr-exams', 'ca' => 'ca-exams', 'gk' => 'gk-exams', 'special' => 'special-exams'];

    protected static function booted(): void
    {
        // the address is made once from the title and then never changes, so shared links keep working
        static::saving(function (ExamSeason $s) {
            if ($s->slug || !\Illuminate\Support\Facades\Schema::hasColumn('exam_seasons', 'slug')) {
                return;
            }
            $base = \Illuminate\Support\Str::slug($s->title) ?: 'season';
            $slug = $base;
            for ($i = 2; static::withTrashed()->where('exam_id', $s->exam_id)->where('kind', $s->kind)->where('slug', $slug)->where('id', '!=', $s->id ?? 0)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }
            $s->slug = $slug;
        });
    }

    /** /kpsc/omr-exams/typist-omr-2026 (the old /seasons/{id} when the season has no slug yet). */
    public function publicUrl(): string
    {
        return $this->slug && $this->exam ? url($this->exam->slug . '/' . (self::PAGES[$this->kind] ?? 'daily-exams') . '/' . $this->slug) : url('seasons/' . $this->id);
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function exams()
    {
        return $this->hasMany(CourseExam::class, 'season_id');
    }
}
