<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyExam extends Model
{
    use HasFactory;

    protected $table = 'kpsc_daily_exams';

    public function exam_details()
    {
        return $this->hasMany('App\Models\DailyExamdetails', 'exam_id', 'id');
    }
    
     public function exam_attend_list()
    {
        return $this->hasMany('App\Models\DailyExamattempt', 'exam_id', 'id');
    }
    
    
    public function model_exam_details(){ 
        return $this->hasMany('App\Models\ModelExamDetails', 'exam_id', 'id');
    }
    
    
    public function model_exam_details_one(){ 
        return $this->hasOne('App\Models\ModelExamDetails', 'exam_id', 'id');
    }
    
    public function model_exam_attened(){ 
        return $this->hasMany('App\Models\ModelExamAttempt', 'exam_id', 'id');
    }
    
}
