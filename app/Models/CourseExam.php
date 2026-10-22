<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

/** An exam built inside a course: daily, OMR or special, with one or more sections of library questions. */
class CourseExam extends Model
{
    use LogsActivity;
    use SoftDeletes;
    protected bool $tracksDeleter = true;
    protected $table = 'course_exams';

    protected $guarded = ['id'];

    protected $casts = [
        'right_mark' => 'float',
        'negative_mark' => 'float',
        'total_mark' => 'float',
        'entry_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'unlock_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    public const STATUSES = [
        'draft' => ['Draft', 'secondary'],
        'published' => ['Published', 'success'],
        'archived' => ['Archived', 'dark'],
    ];

    public const MODES = ['easy' => 'Easy', 'moderate' => 'Moderate', 'difficult' => 'Difficult', 'advanced' => 'Advanced'];

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function feature()
    {
        return $this->belongsTo(ExamFeature::class, 'feature_id');
    }

    public function sections()
    {
        return $this->hasMany(CourseExamSection::class, 'course_exam_id')->orderBy('position')->orderBy('id');
    }

    public function links()
    {
        return $this->hasMany(CourseExamQuestion::class, 'course_exam_id')->orderBy('position')->orderBy('id');
    }

    /** What students may see: published, and inside its start / end dates. */
    public function scopeVisible($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }

    public function questionCount(): int
    {
        return $this->links_count ?? $this->links()->count();
    }

    /** Marks a section awards / takes away for one question. */
    public function marksFor(?CourseExamSection $section): array
    {
        return [
            $section?->right_mark ?? $this->right_mark,
            $section?->negative_mark ?? $this->negative_mark,
        ];
    }

    /** Total marks: the typed value, or every question's right mark added up. */
    public function computedTotalMark(): float
    {
        if ($this->total_mark !== null) {
            return (float) $this->total_mark;
        }
        $total = 0.0;
        foreach ($this->sections as $section) {
            $total += $section->links()->count() * (float) ($section->right_mark ?? $this->right_mark);
        }

        return $total;
    }

    /** Total time in minutes: the typed value, or the section times added up. */
    public function computedDuration(): ?int
    {
        if ($this->duration_minutes) {
            return (int) $this->duration_minutes;
        }
        $sum = (int) $this->sections()->sum('duration_minutes');

        return $sum ?: null;
    }

    /** Average seconds per question: typed, or total time / number of questions. */
    public function averageSeconds(): ?int
    {
        if ($this->avg_seconds_per_question) {
            return (int) $this->avg_seconds_per_question;
        }
        $minutes = $this->computedDuration();
        $count = $this->questionCount();

        return ($minutes && $count) ? (int) round($minutes * 60 / $count) : null;
    }

    public function isCa(): bool
    {
        return ($this->relationLoaded('feature') ? $this->feature : $this->feature()->first())?->slug === 'premium-daily-ca';
    }

    /** Problems that stop an exam from being published (empty list = ready). */
    public function publishProblems(): array
    {
        $problems = [];
        $sections = $this->sections()->withCount('links')->get();

        $ca = $this->isCa();
        $hasText = \App\Support\SafeHtml::text($this->body) !== '';
        if ($ca) {
            if (!$hasText && $sections->sum('links_count') === 0) {
                $problems[] = 'Write the content or add questions.';
            }
            if (!$this->entry_date) {
                $problems[] = 'Set the date of the entry.';
            }
        } elseif ($sections->sum('links_count') === 0) {
            $problems[] = 'Add at least one question.';
        }
        if ($sections->count() > 1) {
            foreach ($ca ? [] : $sections as $s) {
                if ($s->links_count === 0) {
                    $problems[] = 'The section "' . ($s->title ?: 'Section ' . ($s->position + 1)) . '" has no questions.';
                }
            }
        }
        if (!$ca && !$this->computedDuration()) {
            $problems[] = 'Set the total duration (or the time of each section).';
        }
        if ($this->start_at && $this->end_at && $this->end_at->lt($this->start_at)) {
            $problems[] = 'The end date is before the start date.';
        }

        return $problems;
    }
}
