<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelExamAttempt extends Model
{
    use HasFactory;
    protected $table = 'kpsc_model_exam_attempt';
    
    
    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
