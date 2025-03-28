<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;
use App\Http\Resources\ModelExamDetailsResources;


class ModelExamResources extends JsonResource
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
           
            'id' => $this->id,
            'section' => $this->section,
            'subject' => $this->subject,
            'exam_date' => $this->exam_date,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'examtitle' => $this->examtitle,
            'status' => $this->status,
            'exam_details' => ModelExamDetailsResources::collection($this->model_exam_details),

        ];
    }
}
