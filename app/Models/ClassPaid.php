<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassPaid extends Model
{
    use HasFactory;
    protected $table = 'class_link_paid';
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'title',
        'link',
        'status',
        'position',
        ];
}
