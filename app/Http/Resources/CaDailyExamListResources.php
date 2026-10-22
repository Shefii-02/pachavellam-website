<?php
namespace App\Http\Resources;

use App\Models\CaDailyExamAttempt;
use Illuminate\Http\Resources\Json\JsonResource;

class CaDailyExamListResources extends JsonResource
{
    protected $user_id;

    public function __construct($resource, $user_id = null)
    {
        parent::__construct($resource);
        $this->user_id = $user_id;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $attempts = $this->ca_exam_attened ?? collect(); // Ensure it's a collection

        return [
            'id' => $this->id,
            'day' => intval($this->examtitle),
            'exam_date' => $this->exam_date,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'level' => $this->level,
            'season_id' => $this->season_id,
            'kind' => $this->exam_kind ?: 'daily',
            'title' => $this->exam_title ?: ('Day ' . $this->level),
            'slot_no' => $this->slot_no ?: $this->level,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'status' => $this->status,
            'session_id' => $this->series_season_id,
            'locked' => (bool) app(\App\Services\SeasonAccess::class)->lockedSeason(auth('api')->user(), $this->resource),
            'free_access' => (bool) $this->free_access,
            'day_allow' => (date('Y-m-d',strtotime($this->exam_date)) <= date('Y-m-d')),
            'completed' => $this->ca_exam_attened->count() ? true : false,
            'attended' => $attempts->isNotEmpty(), // Check if the relationship has data
            'first_attempt' => $attempts->sortBy('id')->pluck('total')->first() ?? 0,
            'last_attempt' => $attempts->sortByDesc('id')->pluck('total')->first() ?? 0,
            'star' => $attempts->sortByDesc('star')->pluck('star')->first() ?? 0,
        ];
    }
}
