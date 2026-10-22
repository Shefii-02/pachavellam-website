<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\DailyExam;
use App\Services\SeasonAccess;

/** An exam of a paid session cannot be opened (or its answers saved) by a student who has not bought the session. */
trait GuardsSeasons
{
    protected function seasonLock($examId): ?\Illuminate\Http\JsonResponse
    {
        $exam = $examId ? DailyExam::find($examId) : null;
        if (!$exam) {
            return null;
        }
        $access = app(SeasonAccess::class);
        // the student is the one in the login token - the user_id in the request body can be anyone's
        $season = $access->lockedSeason(auth('api')->user(), $exam);
        if (!$season) {
            return null;
        }

        return response()->json([
            'status' => 403, 'locked' => true, 'season_id' => $season->id, 'season' => $season->title, 'price' => $access->effectivePrice($season),
            'message' => 'This exam is part of the paid session "' . $season->title . '". Buy the session to attempt it.',
        ], 403);
    }
}
