<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\UserResources;

class DailyExamLeaderboardResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'user'          => new UserResources($this->user),
            'exam_id'       => $this->exam_id,
            'attend_started_at' => $this->attend_started_at,
            'attend_ended_at' => $this->attend_ended_at,
            'right'         => $this->right,
            'wrong'         => $this->wrong,
            'skipped'       => $this->skipped,
            'attempt_time'  => $this->attempt_time,
            'total_mark'    => $this->total_mark,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
