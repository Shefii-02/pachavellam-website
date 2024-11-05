<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class BulletinResources extends JsonResource
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
            'month_year' => date("M-Y", strtotime($this->month_year)),
            'file_name' => url(Storage::url('bullet-in/'.$this->file_name)),
            'issue' => 'Issue-'.$this->issue,
            'download' => $this->download,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
