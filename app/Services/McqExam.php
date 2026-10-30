<?php

namespace App\Services;

use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Models\DailyExamdetails;
use Illuminate\Support\Facades\DB;

/**
 * The MCQ exam player: questions without answers, autosaved progress, grading on the server, and the result (score, rank, review).
 * Attempt 1 is the one the leaderboard and the averages are built from.
 */
class McqExam
{
    public const NEGATIVE = 0.333;   // same as the old player

    /** The questions of an exam in order. */
    public function questions(DailyExam $exam)
    {
        return DailyExamdetails::where('exam_id', $exam->id)->orderBy('position')->orderBy('id')->get();
    }

    /** Question / option / note html: the markup an admin writes, minus scripts and event handlers. */
    public static function html($s): string
    {
        $s = strip_tags((string) $s, '<b><strong><i><em><u><br><p><sub><sup><ul><ol><li><table><thead><tbody><tr><td><th><img><span><div><h5><h6><blockquote>');

        return preg_replace(['/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '/javascript:/i'], '', $s);
    }

    /** The options of a question that exist, as [index (1-4) => html]. */
    public function options($q): array
    {
        $o = [];
        foreach ([1, 2, 3, 4] as $i) {
            $v = $q->{'option_' . $i};
            if ($v !== null && trim((string) $v) !== '') {
                $o[$i] = self::html($v);
            }
        }

        return $o;
    }

    /** 1-4: which option is the right one (the answer is saved as the option's text). */
    public function correctIndex($q): ?int
    {
        $ans = trim(strip_tags((string) $q->currect_ans));
        foreach ([1, 2, 3, 4] as $i) {
            if (trim(strip_tags((string) $q->{'option_' . $i})) === $ans && $ans !== '') {
                return $i;
            }
        }
        if (preg_match('/^[1-4]$/', $ans)) {
            return (int) $ans;
        }
        if (preg_match('/^[a-dA-D]$/', $ans)) {
            return ord(strtolower($ans)) - 96;
        }

        return null;
    }

    /** What the player needs to open (or resume) an attempt. Answers are never sent. */
    public function playerData(DailyExam $exam, DailyExamattempt $attempt): array
    {
        $saved = json_decode((string) $attempt->summary, true) ?: [];
        $remaining = null;
        if ($exam->duration_min) {
            $remaining = max(0, $exam->duration_min * 60 - (time() - strtotime($attempt->attend_started_at)));
        }

        return [
            'questions' => $this->questions($exam)->map(fn ($q) => ['id' => $q->id, 'q' => self::html($q->question), 'o' => $this->options($q)])->values(),
            'saved' => ['answers' => (object) ($saved['answers'] ?? []), 'marked' => $saved['marked'] ?? [], 'seconds' => (object) ($saved['seconds'] ?? []), 'visited' => $saved['visited'] ?? [], 'cur' => $saved['cur'] ?? 0],
            'remaining' => $remaining, 'elapsed' => time() - strtotime($attempt->attend_started_at),
        ];
    }

    /** Keep the half-done attempt (answers, review marks, time per question) so a refresh or a closed tab loses nothing. */
    public function saveProgress(DailyExamattempt $attempt, array $p): void
    {
        $attempt->summary = json_encode(['answers' => $p['answers'] ?? [], 'marked' => $p['marked'] ?? [], 'seconds' => $p['seconds'] ?? [], 'visited' => $p['visited'] ?? [], 'cur' => (int) ($p['cur'] ?? 0)]);
        $attempt->status = '1';
        $attempt->save();
    }

    /** Grade and finish the attempt. */
    public function submit(DailyExam $exam, DailyExamattempt $attempt, array $p): DailyExamattempt
    {
        $answers = $p['answers'] ?? [];
        $marked = array_flip($p['marked'] ?? []);
        $seconds = $p['seconds'] ?? [];
        $right = $wrong = $skipped = 0;
        $rows = [];
        foreach ($this->questions($exam) as $q) {
            $chosen = isset($answers[$q->id]) ? (int) $answers[$q->id] : null;
            if ($chosen !== null && ($chosen < 1 || $chosen > 4 || trim((string) $q->{'option_' . $chosen}) === '')) {
                $chosen = null;
            }
            $ok = null;
            if ($chosen === null) {
                $skipped++;
            } elseif ($chosen === $this->correctIndex($q)) {
                $ok = true;
                $right++;
            } else {
                $ok = false;
                $wrong++;
            }
            $rows[] = ['attempt_id' => $attempt->id, 'exam_id' => $exam->id, 'question_id' => $q->id, 'chosen' => $chosen, 'is_correct' => $ok, 'marked' => isset($marked[$q->id]), 'seconds' => min(3600, max(0, (int) ($seconds[$q->id] ?? 0)))];
        }
        DB::transaction(function () use ($attempt, $rows, $right, $wrong, $skipped) {
            DB::table('kpsc_daily_exam_answers')->where('attempt_id', $attempt->id)->delete();
            DB::table('kpsc_daily_exam_answers')->insert($rows);
            $attempt->right = $right;
            $attempt->wrong = $wrong;
            $attempt->skipped = $skipped;
            $attempt->attend_ended_at = date('Y-m-d H:i:s');
            $attempt->attempt_time = max(1, time() - strtotime($attempt->attend_started_at));
            $attempt->total_mark = $right - ($wrong * self::NEGATIVE);
            $attempt->summary = json_encode(['right' => $right, 'wrong' => $wrong, 'skipped' => $skipped, 'player' => 'v2']);
            $attempt->status = '2';
            $attempt->save();
        });

        return $attempt;
    }

    /** What a student sees on the instructions page after finishing the exam: every attempt with its marks, the totals, and the question-by-question summary of the latest attempt (null = no finished attempt yet). */
    public function summary(DailyExam $exam, $user): ?array
    {
        $done = DailyExamattempt::where('user_id', $user->id)->where('exam_id', $exam->id)->where('status', '2')->orderBy('attempt_no')->get();
        if ($done->isEmpty()) {
            return null;
        }
        $last = $done->last();
        $r = $this->result($exam, $last, $user);
        $answered = $done->sum(fn ($a) => $a->right + $a->wrong);

        return [
            'attempts' => $done->map(fn ($a) => [
                'no' => (int) $a->attempt_no, 'mark' => round((float) $a->total_mark, 2), 'right' => (int) $a->right, 'wrong' => (int) $a->wrong, 'skipped' => (int) $a->skipped,
                'sec' => (int) $a->attempt_time, 'at' => $a->attend_ended_at ? date('d M Y, h:i a', strtotime($a->attend_ended_at)) : '', 'url' => url('kpsc/mcq/' . $exam->id . '/result?attempt=' . $a->attempt_no),
            ])->reverse()->values()->all(),
            'count' => $done->count(), 'best' => round((float) $done->max('total_mark'), 2), 'avg' => round((float) $done->avg('total_mark'), 2),
            'accuracy' => $answered > 0 ? round($done->sum('right') * 100 / $answered, 1) : 0,
            'avgSec' => (int) round($done->avg('attempt_time')), 'perQ' => $r['total'] ? (int) round($last->attempt_time / $r['total']) : null,
            'latest' => (int) $last->attempt_no, 'rank' => $r['rank'], 'participants' => $r['participants'], 'average' => $r['average'], 'total' => $r['total'],
            'review' => array_map(fn ($q) => $q + ['mineHtml' => $q['o'][$q['chosen']] ?? null, 'ansHtml' => $q['o'][$q['correct']] ?? null], $r['review']),
        ];
    }

    /** Everything the result page shows for one finished attempt. */
    public function result(DailyExam $exam, DailyExamattempt $attempt, $user): array
    {
        $qs = $this->questions($exam);
        $mine = DB::table('kpsc_daily_exam_answers')->where('attempt_id', $attempt->id)->get()->keyBy('question_id');
        // the crowd: first attempts of everybody (the leaderboard is built from these)
        $first = DB::table('kpsc_daily_exams_attempt')->where('exam_id', $exam->id)->where('status', '2')->where('attempt_no', 1);
        $stat = DB::table('kpsc_daily_exam_answers as a')->join('kpsc_daily_exams_attempt as t', 't.id', '=', 'a.attempt_id')
            ->where('a.exam_id', $exam->id)->where('t.attempt_no', 1)->where('t.status', '2')
            ->selectRaw('a.question_id, avg(a.seconds) as avg_sec, sum(a.chosen is not null) as answered, sum(a.is_correct = 1) as ok')->groupBy('a.question_id')->get()->keyBy('question_id');

        $review = [];
        foreach ($qs as $i => $q) {
            $m = $mine->get($q->id);
            $s = $stat->get($q->id);
            $chosen = $m->chosen ?? null;
            $review[] = [
                'n' => $i + 1, 'q' => self::html($q->question), 'o' => $this->options($q), 'chosen' => $chosen, 'correct' => $this->correctIndex($q),
                'status' => $chosen === null ? 'skipped' : ($m->is_correct ? 'correct' : 'wrong'), 'marked' => (bool) ($m->marked ?? false),
                'notes' => self::html($q->notes), 'sec' => (int) ($m->seconds ?? 0),
                'avgSec' => $s ? (int) round($s->avg_sec) : null, 'pct' => $s && $s->answered > 0 ? (int) round($s->ok * 100 / $s->answered) : null,
            ];
        }

        $myFirst = DailyExamattempt::where('user_id', $attempt->user_id)->where('exam_id', $exam->id)->where('attempt_no', 1)->where('status', '2')->first();
        $total = (clone $first)->count();
        $rank = $pct = null;
        if ($myFirst) {
            $rank = (clone $first)->where(fn ($q) => $q->where('total_mark', '>', $myFirst->total_mark)->orWhere(fn ($w) => $w->where('total_mark', $myFirst->total_mark)->where('attempt_time', '<', $myFirst->attempt_time)))->count() + 1;
            $pct = $total > 0 ? round((clone $first)->where('total_mark', '<', $myFirst->total_mark)->count() * 100 / $total, 1) : null;
        }
        $board = (clone $first)->leftJoin('users', 'users.id', '=', 'kpsc_daily_exams_attempt.user_id')
            ->orderByDesc('total_mark')->orderBy('attempt_time')->limit(10)->get(['kpsc_daily_exams_attempt.user_id', 'users.name', 'kpsc_daily_exams_attempt.total_mark', 'kpsc_daily_exams_attempt.attempt_time'])
            ->map(fn ($r, $i) => ['rank' => $i + 1, 'name' => $r->name ?: 'Student', 'mark' => round((float) $r->total_mark, 2), 'sec' => (int) $r->attempt_time, 'me' => $r->user_id == $attempt->user_id]);

        $count = count($review);
        $answered = $attempt->right + $attempt->wrong;
        $cut = $exam->cutoff_mark !== null ? (float) $exam->cutoff_mark : null;

        return [
            'title' => $exam->examtitle ?: ($exam->exam_title ?: 'Exam'), 'date' => $exam->exam_date, 'attemptNo' => (int) $attempt->attempt_no, 'isFirst' => (int) $attempt->attempt_no === 1,
            'at' => $attempt->attend_ended_at ? date('d M Y, h:i a', strtotime($attempt->attend_ended_at)) : '',
            'score' => round((float) $attempt->total_mark, 2), 'max' => $count, 'right' => (int) $attempt->right, 'wrong' => (int) $attempt->wrong, 'skipped' => (int) $attempt->skipped, 'total' => $count,
            'accuracy' => $answered > 0 ? round($attempt->right * 100 / $answered, 1) : 0, 'sec' => (int) $attempt->attempt_time, 'duration' => $exam->duration_min ? $exam->duration_min * 60 : null,
            'negative' => round(self::NEGATIVE, 2), 'rank' => $rank, 'participants' => $total, 'percentile' => $pct,
            'topper' => round((float) (clone $first)->max('total_mark'), 2), 'average' => round((float) (clone $first)->avg('total_mark'), 2),
            'cutoff' => $cut, 'belowCutoff' => $cut !== null && (float) $attempt->total_mark < $cut, 'ended' => !$exam->ended_at || strtotime($exam->ended_at) <= time(),
            'board' => $board, 'review' => $review,
        ];
    }
}
