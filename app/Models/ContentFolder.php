<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A parent / child folder of syllabus, study materials or results of one exam type. */
class ContentFolder extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('title');
    }

    /** Root > Parent > Child, as a list of folders. */
    public function trail(): array
    {
        $out = [];
        $f = $this;
        for ($i = 0; $f && $i < 12; $i++) {
            array_unshift($out, $f);
            $f = $f->parent_id ? self::find($f->parent_id) : null;
        }

        return $out;
    }

    public function path(): string
    {
        return implode(' / ', array_map(fn ($f) => $f->title, $this->trail()));
    }

    /** Every folder id below this one (and itself). */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $frontier = [$this->id];
        for ($i = 0; $i < 12 && $frontier; $i++) {
            $frontier = self::whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }
}
