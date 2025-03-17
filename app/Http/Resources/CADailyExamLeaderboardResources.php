<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\UserResources;

class CADailyExamLeaderboardResources extends JsonResource
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
            'right'         => $this->right,
            'wrong'         => $this->wrong,
            'skipped'       => $this->skipped,
            'star'          => $this->star,
            'total'         => $this->total,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
