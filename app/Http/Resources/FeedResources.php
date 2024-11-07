<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;
use App\Http\Resources\{FeedCommentResources, UserResources};

class FeedResources extends JsonResource
{
    protected $user_id;

    // Constructor to accept user_id
    public function __construct($resource, $user_id = null)
    {
        parent::__construct($resource);
        $this->user_id = $user_id;
    }

    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Check if user_id is passed
        $user_id = $this->user_id ?? $request->user_id;

        return [
            'id' => $this->id,
            'image' => url(Storage::url('files/'.$this->image)),
            'type' => $this->type,
            'tag' => $this->tag,
            'position' => $this->position,
            'content' => $this->content,
            'author_id' => $this->author_id,
            'author'=> $this->author ? new UserResources($this->author) : new GuestUserResources($this),
            'comments'=> FeedCommentResources::collection($this->comments),
            'hasLike' => $this->hasLike($user_id), // Call method to check if user liked
            'likes' => $this->likes ?? 0,
            'title' => $this->title ?? null,
            'description' => $this->description ?? null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // Method to check if the user liked the post
    private function hasLike($user_id): bool
    {
        return $this->likes->contains('user_id', $user_id);
    }
}
