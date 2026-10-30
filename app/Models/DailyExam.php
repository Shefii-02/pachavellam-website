<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyExam extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $table = 'kpsc_daily_exams';

    /** What admin code may mass-assign (the season, access, cutoff, duration and instruction settings); everything else is set field by field. */
    protected $fillable = ['series_season_id', 'free_access', 'status', 'cutoff_mark', 'duration_min', 'instruction_id', 'sort_no'];

    /** False until the exam's start time: the question paper, answer key, questions and marks stay closed. No start time = always open. */
    public function hasStarted(): bool
    {
        return !$this->started_at || !\Illuminate\Support\Carbon::parse($this->started_at)->isFuture();
    }

    public function startIso(): ?string
    {
        return $this->started_at ? \Illuminate\Support\Carbon::parse($this->started_at)->toIso8601String() : null;
    }

    public function season()
    {
        return $this->belongsTo(CaSeason::class, 'season_id');
    }

    public function exam_details()
    {
        return $this->hasMany('App\Models\DailyExamdetails', 'exam_id', 'id');
    }

    public function exam_attend_list()
    {
        return $this->hasMany('App\Models\DailyExamattempt', 'exam_id', 'id');
    }

    public function subjectDetailed()
    {
        return $this->hasOne('App\Models\KpscSubject', 'id', 'subject');
    }


    public function model_exam_details()
    {
        return $this->hasMany('App\Models\ModelExamDetails', 'exam_id', 'id');
    }


    public function model_exam_details_one()
    {
        return $this->hasOne('App\Models\ModelExamDetails', 'exam_id', 'id');
    }

    public function model_exam_attened()
    {
        return $this->hasMany('App\Models\ModelExamAttempt', 'exam_id', 'id');
    }

    public function ca_exam_attened()
    {
        return $this->hasMany('App\Models\CaDailyExamAttempt', 'exam_id', 'id');
    }

    
}
