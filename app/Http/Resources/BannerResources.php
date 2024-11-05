<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;


class BannerResources extends JsonResource
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
            'image' => url('storage/'.$this->image),
            'redirection' => $this->redirection,
            'title' => $this->title ?? null,
            'description' => $this->description ?? 'More about this course please click below link',
            'link' => $this->link ?? 'https://wa.me/+917510116655',
            'position' => $this->position,
            'status' => $this->status,
        ];
    }
}
