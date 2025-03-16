<?php
namespace App\Http\Resources;

use App\Models\CaDailyExamAttempt;
use Illuminate\Http\Resources\Json\JsonResource;

class CaDailyExamListResources extends JsonResource
{
    protected $user_id;

    public function __construct($resource, $user_id = null)
    {
        parent::__construct($resource);
        $this->user_id = $user_id;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $user_id = $this->user_id ?? $request->user_id;

        // Fetch exam attempts for the user
        $attempts = CaDailyExamAttempt::where('user_id', $user_id)
            ->where('exam_id', $this->id);

        return [
            'id' => $this->id,
            'day' => $this->examtitle,
            'exam_date' => $this->exam_date,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'examtitle' => $this->examtitle,
            'status' => $this->status,
            'attended' => $attempts->exists(),
            'first_attempt' => $attempts->clone()->orderBy('id', 'asc')->value('total') ?? 0,
            'last_attempt' => $attempts->clone()->orderBy('id', 'desc')->value('total') ?? 0,
            'star' => $attempts->clone()->orderBy('star', 'desc')->value('star') ?? 0,
        ];
    }
}
