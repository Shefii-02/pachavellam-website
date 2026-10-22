<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

/** A timed study pack: sections of notes / PDFs / voice notes / links / recorded classes, for one or more exam types. */
class RoaringStudy extends Model
{
    use LogsActivity;
    use SoftDeletes;
    protected bool $tracksDeleter = true;
    protected $table = 'roaring_studies';

    protected $guarded = ['id'];

    protected $casts = ['publish_at' => 'datetime', 'end_at' => 'datetime'];

    public const STATUSES = [
        'draft' => ['Draft', 'secondary'],
        'published' => ['Published', 'success'],
        'archived' => ['Archived', 'dark'],
    ];

    public const TYPES = ['text' => 'Text note', 'pdf' => 'PDF', 'voice' => 'Voice note', 'link' => 'Link', 'class' => 'Recorded class'];

    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'roaring_study_exams', 'roaring_id', 'exam_id');
    }

    public function courses()
    {
        return $this->belongsToMany(ExamCourse::class, 'roaring_study_courses', 'roaring_id', 'course_id');
    }

    public function sections()
    {
        return $this->hasMany(RoaringSection::class, 'roaring_id')->orderBy('position')->orderBy('id');
    }

    public function scopeForExam($query, $slug)
    {
        return $query->whereHas('exams', fn ($q) => $q->where('competitive_exams.slug', $slug));
    }

    /** What students may see: published and inside its start / end time. Course-based packs additionally need the course. */
    public function scopeVisible($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }

    public function isGlobal(): bool
    {
        return $this->access_type === 'global';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }
}
