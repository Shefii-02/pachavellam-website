<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/** A course of one exam type. */
class ExamCourse extends Model
{
    use LogsActivity;
    protected $table = 'exam_courses';

    protected $guarded = ['id'];

    protected $casts = [
        'highlights' => 'array',
        'requirements' => 'array',
        'tags' => 'array',
        'certificate_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'float',
        'discount_price' => 'float',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'valid_until' => 'date',
    ];

    public const STATUSES = [
        'draft' => ['Draft', 'secondary'],
        'published' => ['Published', 'success'],
        'archived' => ['Archived', 'dark'],
    ];

    public const LEVELS = ['all' => 'All levels', 'beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];

    public const LANGUAGES = ['ml' => 'Malayalam', 'en' => 'English', 'both' => 'Malayalam + English'];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function features()
    {
        return $this->belongsToMany(ExamFeature::class, 'exam_course_features', 'course_id', 'feature_id')
            ->withPivot('id', 'scope', 'category_ids', 'note')->withTimestamps()->orderBy('exam_features.position');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'exam_course_staff', 'course_id', 'user_id')
            ->withPivot('member_role', 'can_edit')->withTimestamps();
    }

    public function nodes()
    {
        return $this->hasMany(ExamCourseNode::class, 'course_id');
    }

    /** Main category (the first one chosen). */
    public function category()
    {
        return $this->belongsTo(ExamCategory::class, 'category_id');
    }

    /** Every category the course belongs to. */
    public function categories()
    {
        return $this->belongsToMany(ExamCategory::class, 'exam_course_categories', 'course_id', 'category_id')->withTimestamps();
    }

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Courses in a category OR in any of its sub categories,
     * so choosing a parent category in a filter also lists the courses of its children.
     */
    public function scopeInCategory($query, $categoryId)
    {
        $category = ExamCategory::find($categoryId);
        if (!$category) {
            return $query->whereRaw('1 = 0');
        }
        $ids = array_merge([$category->id], $category->descendantIds());

        return $query->whereIn('exam_courses.id', fn ($q) => $q->select('course_id')->from('exam_course_categories')->whereIn('category_id', $ids));
    }

    public function isFree(): bool
    {
        return $this->course_type === 'free';
    }

    /** Price a student pays today (discount applies when set and lower). */
    public function effectivePrice(): float
    {
        if ($this->isFree()) {
            return 0.0;
        }

        return ($this->discount_price !== null && $this->discount_price < $this->price) ? $this->discount_price : $this->price;
    }

    public function validityLabel(): string
    {
        return match ($this->validity_type) {
            'days' => $this->validity_days . ' days after joining',
            'date' => 'until ' . optional($this->valid_until)->format('d M Y'),
            default => 'Lifetime',
        };
    }

    /** Admin sees everything; staff only the courses they were added to. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->type === 'Admin') {
            return $query;
        }

        return $query->whereIn('exam_courses.id', fn ($q) => $q->select('course_id')->from('exam_course_staff')->where('user_id', $user->id));
    }

    public function canBeViewedBy(User $user): bool
    {
        return $user->type === 'Admin' || $this->members()->where('users.id', $user->id)->exists();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $user->type === 'Admin' || $this->members()->where('users.id', $user->id)->wherePivot('can_edit', 1)->exists();
    }
}
