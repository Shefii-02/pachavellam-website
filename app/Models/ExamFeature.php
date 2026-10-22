<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/** Something a course can include (daily exams, OMR exams, materials, ...). Common to every exam type, or limited to some. */
class ExamFeature extends Model
{
    use LogsActivity;
    protected $table = 'exam_features';

    protected $guarded = ['id'];

    protected $casts = [
        'supports_subjects' => 'boolean',
        'is_system' => 'boolean',
        'status' => 'boolean',
    ];

    public const KINDS = [
        'tree' => 'Content (folders, files, articles)',
        'exam' => 'Exams / daily entries (common to all categories)',
        'classes' => 'Classes (video links from the class library)',
        'flag' => 'Section access (included or not)',
    ];

    /** Which node types a tree feature accepts. Unknown / future tree features accept everything. */
    public const NODE_TYPES = [
        'materials' => ['folder', 'file'],
        'articles' => ['folder', 'article'],
        'previous-questions' => ['folder', 'file'],
    ];

    /** Public address of the uploaded picture, or null. */
    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'exam_feature_exams', 'feature_id', 'exam_id')->withTimestamps();
    }

    public function allowedNodeTypes(): array
    {
        return self::NODE_TYPES[$this->slug] ?? ['folder', 'file', 'article'];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->orderBy('position')->orderBy('id');
    }

    /** Features offered on the courses of one exam type: those for all exam types plus those chosen for it. */
    public function scopeForExam($query, $examId)
    {
        return $query->where(fn ($q) => $q->where('exam_scope', 'all')
            ->orWhereIn('id', fn ($s) => $s->select('feature_id')->from('exam_feature_exams')->where('exam_id', $examId)));
    }

    public function availableFor($examId): bool
    {
        return $this->exam_scope === 'all' || $this->exams()->where('competitive_exams.id', $examId)->exists();
    }
}
