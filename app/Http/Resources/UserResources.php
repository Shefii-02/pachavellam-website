<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\DailyExamDetailsResources;

class UserResources extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'image' => file_exists(url('storage/users/', $this->image)) ? url('storage/users', $this->image) : url('assets/images/user.png'),
            'mobile' => $this->mobile ?? '',
            'type' => $this->type ?? 'Student',
            'status' => $this->status ?? '',
        ];
    }
}
