<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/**
 * Category / sub category of one exam type (KPSC, RRB, SSC, ...). A self referencing tree:
 * every child can itself be a parent. For exams like KTET or UGC NET the categories are the subjects.
 */
class ExamCategory extends Model
{
    use LogsActivity;
    protected $table = 'exam_categories';

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
        'status' => 'boolean',
        'is_featured' => 'boolean',
    ];

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
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /** slug/slug/slug from the top category down to this one. */
    public function urlPath(): string
    {
        return $this->ancestors()->push($this)->pluck('slug')->implode('/');
    }

    /** The page of this category (and sub category): its articles, free videos and courses. */
    public function publicUrl(): string
    {
        return url('c/' . $this->urlPath());
    }

    /** Parents from the root down to (and excluding) this category. */
    public function ancestors()
    {
        $list = collect();
        $node = $this->parent;
        while ($node) {
            $list->prepend($node);
            $node = $node->parent;
        }
        return $list;
    }

    /** IDs of every descendant (children, grandchildren, ...). */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];
        while ($frontier) {
            $frontier = self::whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }
        return $ids;
    }

    /** Flat, indented list [id => "— name"] of one exam type's whole tree, for dropdowns and checkbox lists. */
    public static function optionsFor($examId, array $exclude = [], bool $onlyActive = false): array
    {
        $all = self::where('exam_id', $examId)->when($onlyActive, fn ($q) => $q->where('status', 1))
            ->orderBy('position')->orderBy('id')->get()->groupBy('parent_id');
        $out = [];

        $walk = function ($parentId, $level) use (&$walk, $all, $exclude, &$out) {
            foreach ($all->get($parentId, collect()) as $item) {
                if (in_array($item->id, $exclude)) {
                    continue;
                }
                $out[$item->id] = str_repeat('— ', $level) . $item->name;
                $walk($item->id, $level + 1);
            }
        };
        $walk(null, 0);

        return $out;
    }
}
