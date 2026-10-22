<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic row of any "<prefix>_<section>" content table (rrb_banners, ssc_syllabus, ...).
 * The table is chosen at runtime: (new ExamContent)->setTable('rrb_banners').
 */
class ExamContent extends Model
{
    protected $guarded = ['id'];

    public static function forTable(string $table): self
    {
        return (new static)->setTable($table);
    }
}
