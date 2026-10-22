<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capsule extends Model
{
    use HasFactory;
    protected $table = 'capsule';


    public function author()
    {
        return $this->hasOne('App\Models\User','id','author_id');
    }


    public function comments(){
        return $this->hasMany('App\Models\CapsuleComment','cap_id','id');
    }

    public function likes(){
        return $this->hasMany('App\Models\CapsuleLike','cap_id','id');
    }

    /** Exam types this capsule is shown for. */
    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'capsule_exams', 'capsule_id', 'exam_id')->withTimestamps();
    }

    /** Capsules shown for one exam type (slug or id). */
    public function scopeForExam($query, $exam)
    {
        return $query->whereIn('capsule.id', fn ($s) => $s->select('ce.capsule_id')->from('capsule_exams as ce')
            ->join('competitive_exams as e', 'e.id', '=', 'ce.exam_id')
            ->where(fn ($w) => ctype_digit((string) $exam) ? $w->where('e.id', $exam) : $w->where('e.slug', $exam)));
    }
}
