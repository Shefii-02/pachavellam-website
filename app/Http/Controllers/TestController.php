<?php

namespace App\Http\Controllers;

use App\Models\CompetitiveExam;
use App\Models\CourseExam;
use App\Models\CourseExamAttempt;
use App\Services\TestEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Builder exams on the website: instructions, the test itself (sections, timer, palette, pause / resume), result
 * (score, rank, percentile, sectional summary, comparison, leaderboard), solutions, the student's test list and analysis.
 */
class TestController extends Controller
{
    public function __construct(private TestEngine $engine)
    {
    }

    private function exam($id): CourseExam
    {
        return CourseExam::with('course:id,slug,title,price,discount_price,course_type', 'feature:id,name,slug')->where('status', 'published')->findOrFail($id);
    }

    /** The attempt of the signed in student (404 for anyone else's). */
    private function attempt(Request $request, $id): CourseExamAttempt
    {
        return CourseExamAttempt::with('exam')->where('user_id', $request->user()->id)->findOrFail($id);
    }

    // ================================================================= before the test

    public function show(Request $request, $id)
    {
        $exam = $this->exam($id);
        $paper = $this->engine->paper($exam);
        $user = $request->user();
        $attempts = $user ? CourseExamAttempt::where('user_id', $user->id)->where('course_exam_id', $exam->id)->orderByDesc('id')->get() : collect();
        $open = $attempts->first(fn ($a) => $a->status !== 'submitted');
        $done = $attempts->where('status', 'submitted');

        return view('kpsc.tests.show', [
            'title' => $exam->title, 'exam' => $exam, 'paper' => $paper, 'access' => $this->engine->access($user, $exam), 'isOpen' => $this->engine->isOpen($exam),
            'open' => $open, 'done' => $done, 'left' => $exam->max_attempts ? max(0, $exam->max_attempts - $done->count()) : null,
        ]);
    }

    public function start(Request $request, $id)
    {
        $exam = $this->exam($id);
        $back = url('tests/' . $exam->id);
        if (!$this->engine->isOpen($exam)) {
            return redirect($back)->with('test_msg', 'This test opens on ' . $exam->start_at->format('d M Y, h:i a') . '.');
        }
        $access = $this->engine->access($request->user(), $exam);
        if (!$access['ok']) {
            return redirect($access['buy']);
        }
        if (!$this->engine->paper($exam)['count']) {
            return redirect($back)->with('test_msg', 'This test has no questions yet.');
        }
        $lang = $request->input('lang');
        $attempt = $this->engine->start($request->user(), $exam, is_string($lang) && preg_match('/^[a-z]{2,5}$/', $lang) ? $lang : null);
        if (!$attempt) {
            return redirect($back)->with('test_msg', 'You have used all the attempts of this test.');
        }

        return redirect(url('tests/attempt/' . $attempt->id));
    }

    // ================================================================= the test

    public function play(Request $request, $id)
    {
        $a = $this->attempt($request, $id);
        if ($a->isSubmitted()) {
            return redirect(url('tests/attempt/' . $a->id . '/result'));
        }
        $paper = $this->engine->paper($a->exam);
        $this->engine->resume($a);
        // the clock may have run out while away: one save closes the section / submits
        $a = $this->engine->save($a, $paper, [], null, 'ping');
        if ($a->isSubmitted()) {
            return redirect(url('tests/attempt/' . $a->id . '/result'));
        }

        return view('kpsc.tests.play', ['title' => $a->exam->title, 'a' => $a, 'exam' => $a->exam, 'paper' => $paper, 'state' => $this->engine->state($a, $paper)]);
    }

    /** JSON: {answers: {qid: {o, m, t}}, q: current question, action: ping|pause|next_section|submit, lang} */
    public function save(Request $request, $id)
    {
        $a = $this->attempt($request, $id);
        $data = $request->validate(['answers' => ['nullable', 'array', 'max:500'], 'q' => ['nullable', 'integer'], 'action' => ['required', 'in:ping,pause,next_section,submit'], 'lang' => ['nullable', 'string', 'regex:/^[a-z]{2,5}$/']]);
        $paper = $this->engine->paper($a->exam);
        // one save at a time per attempt (two tabs, or a ping racing a submit)
        $a = DB::transaction(function () use ($a, $paper, $data) {
            $locked = CourseExamAttempt::lockForUpdate()->find($a->id)->setRelation('exam', $a->exam);
            if (!empty($data['lang']) && in_array($data['lang'], $paper['langs'], true)) {
                $locked->lang = $data['lang'];
            }

            return $this->engine->save($locked, $paper, $data['answers'] ?? [], $data['q'] ?? null, $data['action']);
        });

        return response()->json($this->engine->state($a, $paper));
    }

    // ================================================================= after the test

    public function result(Request $request, $id)
    {
        $a = $this->attempt($request, $id);
        if (!$a->isSubmitted()) {
            return redirect(url('tests/attempt/' . $a->id));
        }
        $exam = $a->exam;
        $paper = $this->engine->paper($exam);
        $board = $this->engine->board($exam);
        $rank = $this->engine->rank($a, $board);

        // each section: you / average / topper, and your rank in that section
        $sections = collect($paper['sections'])->map(function ($s) use ($a, $board) {
            $sid = (string) $s['id'];
            $mine = ($a->section_scores ?: [])[$sid] ?? ['score' => 0, 'max' => 0, 'correct' => 0, 'wrong' => 0, 'skipped' => 0, 'negative' => 0, 'time' => 0, 'count' => count($s['questions'])];
            $rows = $board->map(fn ($b) => ($b->section_scores ?: [])[$sid] ?? null)->filter();
            $acc = fn ($r) => ($r['correct'] + $r['wrong']) ? round($r['correct'] / ($r['correct'] + $r['wrong']) * 100, 1) : 0;
            $others = $board->where('user_id', '!=', $a->user_id)->map(fn ($b) => (($b->section_scores ?: [])[$sid]['score'] ?? 0));
            $avg = fn ($f) => $rows->count() ? round($rows->avg($f), 1) : 0;
            $top = $rows->sortByDesc('score')->first();

            return (object) [
                'id' => $s['id'], 'title' => $s['title'], 'minutes' => $s['minutes'], 'mine' => $mine, 'accuracy' => $acc($mine),
                'rank' => $others->filter(fn ($v) => $v > $mine['score'])->count() + 1, 'total' => $others->count() + 1,
                'percentile' => $others->count() ? round($others->filter(fn ($v) => $v < $mine['score'])->count() / $others->count() * 100, 1) : 100.0,
                'avg' => ['score' => $avg('score'), 'correct' => $avg('correct'), 'wrong' => $avg('wrong'), 'time' => $avg('time'), 'accuracy' => $rows->count() ? round($rows->avg($acc), 1) : 0],
                'top' => $top ? ['score' => $top['score'], 'correct' => $top['correct'], 'wrong' => $top['wrong'], 'time' => $top['time'], 'accuracy' => $acc($top)] : ['score' => 0, 'correct' => 0, 'wrong' => 0, 'time' => 0, 'accuracy' => 0],
            ];
        });

        return view('kpsc.tests.result', [
            'title' => 'Result - ' . $exam->title, 'a' => $a, 'exam' => $exam, 'paper' => $paper, 'rank' => $rank, 'sections' => $sections,
            'leaders' => $board->take(10), 'boardCount' => $board->count(),
            'avgScore' => $board->count() ? round($board->avg('score'), 1) : 0, 'topScore' => $board->max('score') ?? 0,
            'canRetry' => !$exam->max_attempts || CourseExamAttempt::where('user_id', $a->user_id)->where('course_exam_id', $exam->id)->where('status', 'submitted')->count() < $exam->max_attempts,
            'others' => CourseExamAttempt::where('user_id', $a->user_id)->where('course_exam_id', $exam->id)->where('status', 'submitted')->orderBy('attempt_no')->get(['id', 'attempt_no', 'score', 'submitted_at']),
        ]);
    }

    public function solutions(Request $request, $id)
    {
        $a = $this->attempt($request, $id);
        abort_unless($a->isSubmitted(), 403, 'Submit the test to see the solutions.');
        $key = $this->engine->paper($a->exam, true);

        return view('kpsc.tests.solutions', [
            'title' => 'Solutions - ' . $a->exam->title, 'a' => $a, 'exam' => $a->exam, 'paper' => $key,
            'stats' => $this->engine->questionStats($a->exam, $key),
            'avgSec' => $a->exam->averageSeconds(),
        ]);
    }

    public function rate(Request $request, $id)
    {
        $a = $this->attempt($request, $id);
        $a->update(['rating' => $request->validate(['rating' => ['required', 'integer', 'between:1,5']])['rating']]);

        return response()->json(['ok' => true]);
    }

    /** Report a question, or say whether its solution helped. */
    public function report(Request $request, $id)
    {
        $exam = CourseExam::findOrFail($id);
        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:course_exam_questions,question_id,course_exam_id,' . $exam->id],
            'kind' => ['required', 'in:report,helpful,unhelpful'],
            'reason' => ['nullable', 'in:wrong_answer,wrong_question,typo,image,translation,other'], 'note' => ['nullable', 'string', 'max:500'],
        ]);
        DB::table('course_exam_reports')->insert($data + ['user_id' => $request->user()->id, 'course_exam_id' => $exam->id, 'status' => 'new', 'created_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true, 'message' => $data['kind'] === 'report' ? 'Thanks! Our team will check this question.' : 'Thanks for the feedback!']);
    }

    // ================================================================= the student's tests

    /** All / Completed / Incomplete / Unattempted tests, by exam type. */
    public function mine(Request $request)
    {
        $user = $request->user();
        $exams = CompetitiveExam::where('status', 1)->orderBy('position')->get();
        $examType = $request->filled('exam') ? $exams->firstWhere('slug', $request->exam) : null;
        $tab = in_array($request->tab, ['completed', 'incomplete', 'unattempted'], true) ? $request->tab : 'all';

        $attempts = CourseExamAttempt::where('user_id', $user->id)->orderByDesc('id')->get()->groupBy('course_exam_id');
        $interest = $user->examInterests()->pluck('competitive_exams.id')->all();
        // tests of the chosen exam type; with none chosen, of the student's exams (plus any test they already took)
        $tests = CourseExam::with('course:id,slug,title,price,discount_price,course_type', 'feature:id,name')->withCount('links')->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()->addDays(15)))
            ->when($examType, fn ($q) => $q->where('exam_id', $examType->id))
            ->when(!$examType && $interest, fn ($q) => $q->where(fn ($w) => $w->whereIn('exam_id', $interest)->orWhereIn('id', $attempts->keys()->all())))
            ->orderByRaw('start_at is null')->orderByDesc('start_at')->orderByDesc('id')->limit(300)->get()
            ->filter(fn ($t) => $t->links_count > 0)
            ->map(function ($t) use ($attempts, $user) {
                $mine = $attempts->get($t->id, collect());
                $t->open = $mine->first(fn ($a) => $a->status !== 'submitted');
                $t->last = $mine->first(fn ($a) => $a->status === 'submitted');
                $t->state = $t->open ? 'incomplete' : ($t->last ? 'completed' : 'unattempted');
                $t->access = $this->engine->access($user, $t);

                return $t;
            });
        $counts = ['all' => $tests->count(), 'completed' => $tests->where('state', 'completed')->count(), 'incomplete' => $tests->where('state', 'incomplete')->count(), 'unattempted' => $tests->where('state', 'unattempted')->count()];

        return view('kpsc.tests.mine', ['title' => 'My tests', 'exams' => $exams, 'examType' => $examType, 'tab' => $tab, 'counts' => $counts,
            'tests' => $tab === 'all' ? $tests : $tests->where('state', $tab)]);
    }

    /** Performance analysis: attempt %, accuracy %, average time per question by week and difficulty; subject strength. */
    public function analysis(Request $request)
    {
        $user = $request->user();
        $range = in_array($request->range, ['30', '90', '365'], true) ? (int) $request->range : 90;
        $attempts = CourseExamAttempt::with('exam')->where('user_id', $user->id)->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays($range))->orderBy('submitted_at')->get()->filter(fn ($a) => $a->exam)->values();

        // difficulty of every question the student met
        $qids = $attempts->flatMap(fn ($a) => array_keys($a->answers ?: []))->unique()->values();
        $diff = \App\Models\LibraryQuestion::whereIn('id', $qids)->pluck('difficulty', 'id')->map(fn ($d) => in_array(strtolower((string) $d), ['easy', 'moderate', 'difficult'], true) ? strtolower($d) : 'moderate');
        $keys = [];
        $levels = ['overall', 'easy', 'moderate', 'difficult'];
        $weeks = [];
        foreach ($attempts as $a) {
            $wk = $a->submitted_at->copy()->startOfWeek()->format('Y-m-d');
            $paper = $keys[$a->course_exam_id] ??= $this->engine->paper($a->exam, true);
            foreach ($paper['sections'] as $s) {
                foreach ($s['questions'] as $q) {
                    $ans = ($a->answers ?: [])[(string) $q['id']] ?? null;
                    $res = $this->engine->result($q, $ans['o'] ?? []);
                    foreach (['overall', $diff[$q['id']] ?? 'moderate'] as $lv) {
                        $w = &$weeks[$wk][$lv];
                        $w['total'] = ($w['total'] ?? 0) + 1;
                        $w['att'] = ($w['att'] ?? 0) + ($res !== 'skipped' ? 1 : 0);
                        $w['ok'] = ($w['ok'] ?? 0) + ($res === 'correct' ? 1 : 0);
                        $w['time'] = ($w['time'] ?? 0) + ($res !== 'skipped' ? (int) ($ans['t'] ?? 0) : 0);
                        unset($w);
                    }
                }
            }
        }
        ksort($weeks);
        $series = [];
        foreach ($levels as $lv) {
            foreach ($weeks as $wk => $row) {
                $r = $row[$lv] ?? ['total' => 0, 'att' => 0, 'ok' => 0, 'time' => 0];
                $label = \Illuminate\Support\Carbon::parse($wk)->format('d M') . ' - ' . \Illuminate\Support\Carbon::parse($wk)->addDays(6)->format('d M');
                $series[$lv][] = ['w' => $label, 'att' => $r['total'] ? round($r['att'] / $r['total'] * 100, 1) : null, 'un' => $r['total'] ? round(100 - $r['att'] / $r['total'] * 100, 1) : null,
                    'acc' => $r['att'] ? round($r['ok'] / $r['att'] * 100, 1) : null, 'time' => $r['att'] ? round($r['time'] / $r['att'] / 60, 2) : null];
            }
        }

        // subject (section) strength over all attempts
        $subjects = [];
        foreach ($attempts as $a) {
            foreach ($keys[$a->course_exam_id]['sections'] as $s) {
                $r = ($a->section_scores ?: [])[(string) $s['id']] ?? null;
                if (!$r) {
                    continue;
                }
                $name = trim($s['title']);
                $x = &$subjects[$name];
                $x['count'] = ($x['count'] ?? 0) + $r['count'];
                $x['att'] = ($x['att'] ?? 0) + $r['correct'] + $r['wrong'];
                $x['ok'] = ($x['ok'] ?? 0) + $r['correct'];
                $x['time'] = ($x['time'] ?? 0) + $r['time'];
                $x['allowed'] = ($x['allowed'] ?? 0) + ($s['minutes'] ? $s['minutes'] * 60 : 0);
                unset($x);
            }
        }
        $subjects = collect($subjects)->map(function ($x, $name) {
            $acc = $x['att'] ? round($x['ok'] / $x['att'] * 100) : 0;

            return (object) ['name' => $name, 'cumulative' => $x['count'] ? round($x['ok'] / $x['count'] * 100) : 0, 'accuracy' => $acc,
                'time' => $x['allowed'] ? min(100, round($x['time'] / $x['allowed'] * 100)) : null,
                'strength' => $x['att'] < 5 ? 'Unidentified' : ($acc >= 75 ? 'Strong' : ($acc >= 50 ? 'Average' : 'Weak'))];
        })->sortByDesc('accuracy')->values();

        // marks % of each test, oldest first
        $marks = $attempts->map(fn ($a, $i) => ['t' => 'T' . ($i + 1), 'title' => $a->exam->title ?? '', 'pct' => $a->max_score ? round(max(0, $a->score) / $a->max_score * 100, 1) : 0])->values();

        return view('kpsc.tests.analysis', ['title' => 'Test analysis', 'range' => $range, 'series' => $series, 'subjects' => $subjects, 'marks' => $marks, 'count' => $attempts->count()]);
    }
}
