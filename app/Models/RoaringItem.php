<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoaringItem extends Model
{
    protected $table = 'roaring_items';

    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean'];

    public function section()
    {
        return $this->belongsTo(RoaringSection::class, 'section_id');
    }

    public function libraryClass()
    {
        return $this->belongsTo(LibraryClass::class, 'class_id');
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
