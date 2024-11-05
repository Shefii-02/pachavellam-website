<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;


class ModelExamDetailsResources extends JsonResource
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
            'exam_id' => $this->exam_id,
            'qp_file' => url('storage/model-exam/'.$this->qp_file),
            'answer_file' => url('storage/model-exam/'.$this->answer_file),
        ];
    }
}
