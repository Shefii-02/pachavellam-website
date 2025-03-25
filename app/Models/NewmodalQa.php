<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewmodalQa extends Model
{
    use HasFactory;
    protected $table = 'new_modal_qa';
    protected $fillable = [
                        'question',
                        'options',
                        'currect_ans',
                        'subject',
                        'attempt',
                        'position',
                        'status',
                        ];
}
