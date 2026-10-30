<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** The stop button of the OMR exam timer saves the time taken (the student's latest run) and answers with the average. */
class ExamTimeController extends Controller
{
    public function store(Request $request, $id)
    {
        $data = $request->validate(['seconds' => 'required|integer|min:10|max:43200']);
        abort_unless(DailyExam::where('section', 'Model Exam')->whereKey($id)->exists(), 404);
        DB::table('omr_exam_times')->updateOrInsert(['user_id' => auth()->id(), 'exam_id' => $id], ['seconds' => $data['seconds'], 'updated_at' => now(), 'created_at' => now()]);

        return response()->json(self::stats($id));
    }

    /** Average seconds and number of students (null average until 3 students have timed themselves). */
    public static function stats($examId): array
    {
        $row = DB::table('omr_exam_times')->where('exam_id', $examId)->selectRaw('count(*) c, avg(seconds) a')->first();

        return ['count' => (int) $row->c, 'avg' => $row->c >= 3 ? (int) round($row->a) : null];
    }
}
