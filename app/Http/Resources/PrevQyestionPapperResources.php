<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;


class PrevQyestionPapperResources extends JsonResource
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
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'title' => $this->title,
            'qstn_paper' => url('storage/files/'.$this->qstn_paper),
            'ans_key' => url('storage/files/'.$this->ans_key),
            'download' => $this->download,
            'position' => $this->position,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
           
        ];
    }
}
