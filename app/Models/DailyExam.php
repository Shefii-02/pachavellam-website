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

    /** The exam's own title as the pages show it. */
    public function displayTitle(): string
    {
        return (string) ($this->exam_title ?: ($this->examtitle ?: ''));
    }

    /**
     * Address slug of every exam in one season, by exam id: the slug of the title; a second exam with the same title gets "-{id}" so each address is unique.
     * Built once per season per request.
     *
     * @return array<int, string>
     */
    public static function slugsOfSeason(int $seasonId): array
    {
        static $cache = [];
        if (isset($cache[$seasonId])) {
            return $cache[$seasonId];
        }
        $slugs = [];
        $used = [];
        foreach (self::where('series_season_id', $seasonId)->orderBy('id')->get() as $d) {
            $slug = \Illuminate\Support\Str::slug($d->displayTitle()) ?: 'exam';   // a title with no latin letters (Malayalam only) has no slug of its own
            if (isset($used[$slug])) {
                $slug .= '-' . $d->id;
            }
            $used[$slug] = true;
            $slugs[$d->id] = $slug;
        }

        return $cache[$seasonId] = $slugs;
    }

    public function publicSlug(): string
    {
        $slugs = $this->series_season_id ? self::slugsOfSeason((int) $this->series_season_id) : [];

        return $slugs[$this->id] ?? (\Illuminate\Support\Str::slug($this->displayTitle()) ?: 'exam');
    }

    /** The exam of a season at an address: /kpsc/daily-exams/{season slug}/{exam slug}. */
    public static function findBySlug(int $seasonId, string $slug): ?self
    {
        $id = array_search($slug, self::slugsOfSeason($seasonId), true);

        return $id === false ? null : self::where('series_season_id', $seasonId)->find($id);
    }

    /** The old address by date and id (also what the apps read). */
    public function legacyUrl(): string
    {
        return url(($this->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam') . '/' . $this->exam_date . '/' . $this->id);
    }

    /** The website address: season slug + exam title slug when the exam sits in a season that has a slug, else the old date / id address. */
    public function publicUrl(): string
    {
        static $seasons = [];
        if ($this->series_season_id) {
            $season = $seasons[$this->series_season_id] ??= ExamSeason::with('exam')->find($this->series_season_id) ?: false;
            if ($season && $season->slug && $season->exam) {
                return $season->publicUrl() . '/' . $this->publicSlug();
            }
        }

        return $this->legacyUrl();
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
