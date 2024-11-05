<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentAffairs extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'year',
        'month',
        'day',
        'type',
        'title',
        'file_name',
        'question',
        'answer',
        'note',
        'status',
    ];
}
