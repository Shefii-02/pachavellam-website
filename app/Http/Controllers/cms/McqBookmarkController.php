<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use App\Models\DailyExamdetails;
use App\Models\QuestionBookmark;
use App\Services\McqExam;
use Illuminate\Http\Request;

/** Saved (bookmarked) MCQ questions: toggle from the player / solutions, and the list in My account. */
class McqBookmarkController extends Controller
{
    public function toggle(Request $request, $id)
    {
        $exam = DailyExam::where('section', '<>', 'Model Exam')->findOrFail($id);
        $d = $request->validate(['question_id' => 'required|integer']);
        $q = DailyExamdetails::where('exam_id', $exam->id)->findOrFail($d['question_id']);
        $row = QuestionBookmark::where('user_id', auth()->id())->where('question_id', $q->id)->first();
        if ($row) {
            $row->delete();
        } else {
            QuestionBookmark::create(['user_id' => auth()->id(), 'exam_id' => $exam->id, 'question_id' => $q->id]);
        }

        return response()->json(['saved' => !$row, 'count' => QuestionBookmark::where('user_id', auth()->id())->count()]);
    }

    /** My account > Saved questions, newest first, grouped by exam. */
    public function index(McqExam $mcq)
    {
        $marks = QuestionBookmark::where('user_id', auth()->id())->orderByDesc('id')->get();
        $qs = DailyExamdetails::whereIn('id', $marks->pluck('question_id'))->get()->keyBy('id');
        $exams = DailyExam::whereIn('id', $marks->pluck('exam_id')->unique())->get()->keyBy('id');
        $groups = [];
        foreach ($marks as $m) {
            $q = $qs->get($m->question_id);
            $e = $exams->get($m->exam_id);
            if (!$q || !$e) {
                continue;
            }
            $groups[$e->id]['exam'] = $e;
            $groups[$e->id]['items'][] = ['id' => $q->id, 'q' => McqExam::html($q->question), 'o' => $mcq->options($q), 'correct' => $mcq->correctIndex($q), 'notes' => McqExam::html($q->notes)];
        }

        return view('kpsc.mcq.bookmarks', ['title' => 'Saved questions', 'groups' => array_values($groups), 'count' => $marks->count()]);
    }
}
