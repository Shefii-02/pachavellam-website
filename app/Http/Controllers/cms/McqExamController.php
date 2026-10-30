<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Services\McqExam;
use Illuminate\Http\Request;

/** The MCQ exam player (kpsc/mcq/{exam}/...): questions, autosave, submit, and the result page. */
class McqExamController extends Controller
{
    private function exam($id): DailyExam
    {
        $exam = DailyExam::where('section', '<>', 'Model Exam')->findOrFail($id);
        abort_unless($exam->hasStarted(), 403, 'This exam has not started yet.');

        return $exam;
    }

    private function running($id): DailyExamattempt
    {
        return DailyExamattempt::where('user_id', auth()->id())->where('exam_id', $id)->where('status', '<>', 2)->latest('id')->firstOrFail();
    }

    public function questions(McqExam $mcq, $id)
    {
        return response()->json($mcq->playerData($this->exam($id), $this->running($id)));
    }

    public function progress(Request $request, McqExam $mcq, $id)
    {
        $this->exam($id);
        $mcq->saveProgress($this->running($id), $request->validate(['answers' => 'array', 'marked' => 'array', 'seconds' => 'array', 'visited' => 'array', 'cur' => 'integer'])
            + ['answers' => [], 'marked' => [], 'seconds' => [], 'visited' => [], 'cur' => 0]);

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, McqExam $mcq, $id)
    {
        $exam = $this->exam($id);
        $p = $request->validate(['answers' => 'array', 'marked' => 'array', 'seconds' => 'array']);
        $attempt = $mcq->submit($exam, $this->running($id), $p);

        return response()->json(['redirect' => url('kpsc/mcq/' . $exam->id . '/result?attempt=' . $attempt->attempt_no)]);
    }

    public function result(Request $request, McqExam $mcq, $id)
    {
        $exam = $this->exam($id);
        $finished = DailyExamattempt::where('user_id', auth()->id())->where('exam_id', $id)->where('status', '2')->orderBy('attempt_no')->get();
        if ($finished->isEmpty()) {
            return redirect(url('kpsc/daily-exam/' . date('Y-m-d', strtotime($exam->exam_date)) . '/' . $exam->id));
        }
        $attempt = $finished->firstWhere('attempt_no', (int) $request->query('attempt')) ?? $finished->last();

        return view('kpsc.mcq.app', [
            'mode' => 'result', 'exam' => $exam, 'result' => $mcq->result($exam, $attempt, auth()->user()),
            'attempts' => $finished->map(fn ($a) => ['no' => (int) $a->attempt_no, 'mark' => round((float) $a->total_mark, 2)])->values(),
            'retake' => url('kpsc/daily-exam/' . date('Y-m-d', strtotime($exam->exam_date)) . '/' . $exam->id) . '?start=1',
            'back' => url('kpsc/daily-exams'),
        ]);
    }
}
