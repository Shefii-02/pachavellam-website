<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoaringSection extends Model
{
    protected $table = 'roaring_sections';

    protected $guarded = ['id'];

    protected $casts = ['release_at' => 'datetime'];

    public function study()
    {
        return $this->belongsTo(RoaringStudy::class, 'roaring_id');
    }

    public function items()
    {
        return $this->hasMany(RoaringItem::class, 'section_id')->orderBy('position')->orderBy('id');
    }
}
