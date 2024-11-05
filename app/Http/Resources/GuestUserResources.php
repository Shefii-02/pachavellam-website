<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

use App\Http\Resources\DailyExamDetailsResources;

class GuestUserResources extends JsonResource
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
            'name' => $this->full_name ?? 'Unknown Person',
            'image' => url('assets/images/user.png'),
        ];
    }
}
