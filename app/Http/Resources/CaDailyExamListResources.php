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
        $attempts = CaDailyExamAttempt::where('user_id', $user_id)->where('exam_id', $this->id);
        
        return [
            'id' => $this->id,
            'day' => $this->examtitle,
            'exam_date' => $this->exam_date,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'examtitle' => $this->examtitle,
            'status' => $this->status,
            'attended' => $this->ca_exam_attened ? true : false,
            'first_attempt' => $this->ca_exam_attened->orderBy('id', 'asc')->pluck('total')->first() ?? 0,
            'last_attempt' => $this->ca_exam_attened->orderBy('id', 'desc')->pluck('total')->first() ?? 0,
            'star' => $$this->ca_exam_attened->orderBy('star', 'desc')->pluck('star')->first() ?? 0,
        ];
    }
}
