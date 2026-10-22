<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/** A folder or sub folder of an exam type's question library. */
class QuestionFolder extends Model
{
    use LogsActivity;
    protected $table = 'question_folders';

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
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    public function questions()
    {
        return $this->hasMany(LibraryQuestion::class, 'folder_id');
    }

    /** Parents from the top down to (and excluding) this folder. */
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

    /** "History › Kerala › Medieval" */
    public function path(): string
    {
        return $this->ancestors()->push($this)->pluck('name')->implode(' › ');
    }

    /** Every folder of an exam type as a flat list with its depth, in tree order: [id, name, parent_id, depth, path]. */
    public static function flatTree($examId): array
    {
        $all = self::where('exam_id', $examId)->orderBy('position')->orderBy('name')->get()->groupBy('parent_id');
        $out = [];

        $walk = function ($parentId, $depth, $trail) use (&$walk, $all, &$out) {
            foreach ($all->get($parentId, collect()) as $f) {
                $path = $trail === '' ? $f->name : $trail . ' › ' . $f->name;
                $out[] = ['id' => $f->id, 'name' => $f->name, 'parent_id' => $f->parent_id, 'depth' => $depth, 'path' => $path];
                $walk($f->id, $depth + 1, $path);
            }
        };
        $walk(null, 0, '');

        return $out;
    }
}
