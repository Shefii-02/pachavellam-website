<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFree extends Model
{
    use HasFactory;
    protected $table = 'class_link_free';
    protected $fillable = [
                            'category_id',
                            'subcategory_id',
                            'title',
                            'link',
                            'status',
                            'position',
                            ];
}
