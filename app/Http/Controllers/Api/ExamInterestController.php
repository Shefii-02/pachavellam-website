<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use Illuminate\Http\Request;

/** App side: list the exams and let a student pick the ones they are interested in. */
class ExamInterestController extends Controller
{
    /** GET api/exams  (public) */
    public function exams()
    {
        return response()->json([
            'status' => 200,
            'data' => CompetitiveExam::active()->get(['id', 'slug', 'name', 'short_name', 'description', 'color']),
        ]);
    }

    /** GET api/auth/interests  (logged in) */
    public function mine(Request $request)
    {
        return response()->json([
            'status' => 200,
            'data' => $this->selected($request),
        ]);
    }

    /**
     * POST api/auth/interests   { "exam_ids": [1, 3] }  (logged in)
     * Replaces the student's selection. An empty list clears it.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'exam_ids' => 'present|array',
            'exam_ids.*' => 'integer|exists:competitive_exams,id',
        ]);

        $user = $request->user();
        $valid = CompetitiveExam::active()->whereIn('id', $request->exam_ids)->pluck('id');
        $current = $user->examInterests()->pluck('competitive_exams.id');

        $user->examInterests()->detach($current->diff($valid)->all());
        $added = $valid->diff($current);
        $user->examInterests()->attach($added->mapWithKeys(fn ($id) => [$id => ['source' => 'app']])->all());

        foreach (CompetitiveExam::whereIn('id', $added)->get(['id', 'name', 'short_name']) as $exam) {
            \App\Services\StaffNotifier::push('interest', ($user->name ?: 'A student') . ' is interested in ' . ($exam->short_name ?: $exam->name),
                $user->mobile ? 'Mobile ' . $user->mobile : null, url('admin/new-joins?exam=' . $exam->id), $exam->id, $user->id);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Interests saved',
            'data' => $this->selected($request),
        ]);
    }

    private function selected(Request $request)
    {
        return $request->user()->examInterests()->orderBy('position')->get(['competitive_exams.id', 'slug', 'name', 'short_name', 'color']);
    }
}
