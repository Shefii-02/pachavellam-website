<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A folder (and sub folder) of the media library. */
class MediaFolder extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function trail(): array
    {
        $out = [];
        $f = $this;
        for ($i = 0; $i < 12 && $f; $i++) {
            array_unshift($out, $f);
            $f = $f->parent_id ? self::find($f->parent_id) : null;
        }

        return $out;
    }

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
