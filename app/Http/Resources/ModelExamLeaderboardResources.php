<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\UserResources;

class ModelExamLeaderboardResources extends JsonResource
{
    /**
     * The position of the resource in the leaderboard.
     *
     * @var int
     */
    public $position;

    /**
     * Create a new instance of the resource.
     *
     * @param  mixed  $resource
     * @param  int  $position
     * @return void
     */
    public function __construct($resource, $position = null)
    {
        parent::__construct($resource);
        $this->position = $position;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'user'          => $this->user ? new UserResources($this->user) : new GuestUserResources($this),
            'exam_id'       => $this->exam_id,
            'right_answer'  => $this->right_answer,
            'wrong_answer'  => $this->wrong_answer,
            'not_attended'  => $this->not_attended,
            'total_mark'    => $this->total_mark,
            'answer_uploaded'=> $this->answer_uploaded,
            'position'       => $this->position, // Use the passed position
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
