<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\SubjectCategoryResources;

class SubjectCategoryResources extends JsonResource
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
            'subject_title' => $this->subject_title,
            'slug_name'     => $this->slug_name,
            'description'   => $this->description ?? $this->subject_title.' Subject related more details available our website please click below link',
            'bg_color'      => $this->bg_color,
            'image'         => url('storage/'.$this->image),
            'type'          => $this->type,
            'parent_id'     => $this->parent_id,
            'position'      => $this->position,
            'status'        => $this->status,
            'text_color'    => $this->text_color,
            'link'          => url('kpsc/subject/'.$this->slug_name),
            'children'      => SubjectCategoryResources::collection($this->child),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
