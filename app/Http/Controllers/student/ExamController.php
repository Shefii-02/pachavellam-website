<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Students > Exams & Interests: how many students picked each exam type, and editing one student's interests.
 * The exam types themselves are managed in General > Exam Types.
 */
class ExamController extends Controller
{
    public function index()
    {
        $exams = CompetitiveExam::orderBy('position')->orderBy('id')->get();

        $counts = DB::table('student_exam_interests')->select('exam_id', DB::raw('COUNT(*) as c'))
            ->groupBy('exam_id')->pluck('c', 'exam_id');

        $students = User::where('type', 'Student')->count();
        $withInterest = DB::table('student_exam_interests')->distinct()->count('user_id');

        return view('admin.student.exams.index', compact('exams', 'counts', 'students', 'withInterest'));
    }

    /** Replace one student's interests (profile page). */
    public function saveInterests(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $ids = collect($request->input('exam_ids', []))->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => CompetitiveExam::whereKey($v)->exists())->unique()->values();

        $current = $user->examInterests()->pluck('competitive_exams.id');

        $user->examInterests()->detach($current->diff($ids)->all());
        $user->examInterests()->attach($ids->diff($current)->mapWithKeys(fn ($v) => [$v => ['source' => 'admin']])->all());

        return back()->with('message', 'Interests updated');
    }
}
