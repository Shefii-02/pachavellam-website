<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\UserResources;

class FeedCommentResources extends JsonResource
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
            'cap_id' => $this->cap_id,
            'ip_adress' => $this->ip_adress,
            'comment' => $this->comment,
            'parent_comment' => $this->parent_comment,
            'user'=> $this->author ? new UserResources($this->author) : new GuestUserResources($this),
            'status'=>$this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
