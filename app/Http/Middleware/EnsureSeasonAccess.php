<?php

namespace App\Http\Middleware;

use App\Models\DailyExam;
use App\Models\ExamSeason;
use App\Services\SeasonAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Old KPSC exam pages (daily / model / CA): an exam in a paid session opens only for the free exams of the session
 * and for students who bought the session. Everyone else is sent to the session page to buy it.
 */
class EnsureSeasonAccess
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->route('id');
        $exam = $id ? DailyExam::find($id) : null;
        if (!$exam || !$exam->series_season_id) {
            return $next($request);
        }
        $season = ExamSeason::find($exam->series_season_id);
        $access = app(SeasonAccess::class);
        if ($season && $access->state($request->user(), $season, 'legacy', $exam->id) === 'locked') {
            $msg = 'This exam is part of the paid session "' . $season->title . '". Buy the session to attempt it.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg, 'buy_url' => $access->buyUrl($season)], 403);
            }

            return redirect($access->buyUrl($season))->with('season_msg', $msg);
        }

        return $next($request);
    }
}
