<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaSeason extends Model
{
    protected $table = 'kpsc_ca_seasons';

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public const STATUSES = [
        'draft' => ['Draft', 'secondary'],
        'active' => ['Active', 'success'],
        'completed' => ['Completed', 'info'],
        'archived' => ['Archived', 'dark'],
    ];

    public const SCHEDULES = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'weekend' => 'Weekend',
        'mixed' => 'Daily + weekly / weekend',
        'custom' => 'Custom',
    ];

    public const KINDS = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'weekend' => 'Weekend',
        'monthly' => 'Monthly',
        'other' => 'Other',
    ];

    public function exams()
    {
        return $this->hasMany(DailyExam::class, 'season_id')->where('section', 'CA-Daily-Exam');
    }

    /** Active and today is inside its dates (an open end date means "keeps running"). */
    public function isOngoing(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $today = now()->startOfDay();

        return (!$this->starts_on || $this->starts_on->lte($today)) && (!$this->ends_on || $this->ends_on->gte($today));
    }
}
