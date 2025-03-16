<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaDailyExamAttempt extends Model
{
    use HasFactory;
    protected $table = 'kpsc_ca_daily_exam_attempts';
    
    
     public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
