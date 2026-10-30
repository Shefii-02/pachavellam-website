<?php

namespace App\Services;

use App\Models\CourseAdmission;
use App\Models\CourseExam;
use App\Models\CourseExamAttempt;
use App\Models\ExamSeason;
use App\Models\LibraryQuestion;
use App\Models\User;
use App\Support\SafeHtml;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Taking a builder exam on the website.
 *
 * Timing: an exam with two or more sections that each have their own time runs section by section ("sectional"): a section
 * closes when its time is over (or the student submits it) and the next one opens; earlier sections cannot be reopened.
 * Any other exam has one clock for the whole paper ("overall"). The server keeps the time: it only counts while the test page
 * is open (each save / ping adds the seconds since the last one, at most PING_CAP), so a closed tab or a pause stops the clock.
 */
class TestEngine
{
    public const PING_CAP = 40;          // seconds one ping may add (the page pings every 15 s)

    private array $papers = [];

    // ================================================================= access

    /** ['ok' => bool, 'state' => free|unlocked|locked, 'buy' => url to unlock] */
    public function access(?User $user, CourseExam $exam): array
    {
        if ($exam->season_id && Schema::hasTable('exam_seasons') && ($season = ExamSeason::find($exam->season_id))) {
            $access = app(SeasonAccess::class);
            $state = $access->state($user, $season, 'course', $exam->id);

            return ['ok' => $state !== 'locked', 'state' => $state, 'buy' => $access->buyUrl($season)];
        }
        if (!$exam->is_paid || $exam->free_access) {
            return ['ok' => true, 'state' => 'free', 'buy' => null];
        }
        $course = $exam->course;
        $owns = $user && $course && ($course->isFree() || CourseAdmission::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'active')->get()->contains(fn ($a) => $a->grantsAccess()));

        return ['ok' => (bool) $owns, 'state' => $owns ? 'unlocked' : 'locked', 'buy' => $course ? url('prime/buy/' . $course->id) : url('courses')];
    }

    /** Can the exam be opened at all right now (published and started)? */
    public function isOpen(CourseExam $exam): bool
    {
        return $exam->status === 'published' && (!$exam->start_at || $exam->start_at->isPast());
    }

    // ================================================================= paper

    /**
     * Sections and questions of an exam. Without $withKey the right answers and solutions are left out (what the player gets).
     * Each question: id, sid, type, n (number in the paper), langs: {lang: {q, o: [..], e}}, img, oimg, right, neg (+ key, eimg).
     */
    public function paper(CourseExam $exam, bool $withKey = false): array
    {
        $cacheKey = $exam->id . ($withKey ? 'k' : '');
        if (isset($this->papers[$cacheKey])) {
            return $this->papers[$cacheKey];
        }
        $sections = $exam->sections()->orderBy('position')->orderBy('id')->get();
        $links = \App\Models\CourseExamQuestion::where('course_exam_id', $exam->id)->orderBy('position')->orderBy('id')->get()->groupBy('section_id');
        $questions = LibraryQuestion::with('texts')->whereIn('id', $links->flatten()->pluck('question_id'))->get()->keyBy('id');
        $url = fn ($p) => $p ? (preg_match('#^https?://#', $p) ? $p : Storage::url($p)) : null;
        $html = fn ($q, $s) => $q->is_rich ? SafeHtml::clean((string) $s) : nl2br(e(trim((string) $s)));

        $out = [];
        $n = 0;
        $timedAll = $sections->count() >= 2 && $sections->every(fn ($s) => (int) $s->duration_minutes > 0);
        foreach ($sections as $i => $s) {
            [$right, $neg] = $exam->marksFor($s);
            $qs = [];
            foreach ($links->get($s->id, collect()) as $link) {
                $q = $questions->get($link->question_id);
                if (!$q) {
                    continue;
                }
                $optCount = $this->optionCount($q);
                $langs = [];
                foreach ($q->texts as $t) {
                    $langs[$t->lang ?: 'en'] = ['q' => $html($q, $t->question), 'o' => array_map(fn ($k) => $html($q, $t->{'option_' . $k}), range(1, $optCount))]
                        + ($withKey ? ['e' => $html($q, $t->explanation)] : []);
                }
                $row = [
                    'id' => $q->id, 'sid' => $s->id, 'n' => ++$n, 'type' => $q->question_type ?: 'single', 'langs' => $langs ?: ['en' => ['q' => '', 'o' => array_fill(0, $optCount, '')]],
                    'img' => $url($q->question_image), 'oimg' => array_map(fn ($k) => $url(($q->option_images ?? [])[$k] ?? null), range(1, $optCount)),
                    'right' => (float) $right, 'neg' => (float) $neg, 'diff' => $q->difficulty,
                ];
                if ($withKey) {
                    $row['key'] = $q->correctList();
                    $row['eimg'] = $url($q->explanation_image);
                }
                $qs[] = $row;
            }
            $out[] = ['id' => $s->id, 'title' => $s->title ?: ($sections->count() > 1 ? 'Section ' . ($i + 1) : 'Questions'), 'minutes' => (int) $s->duration_minutes ?: null,
                'right' => (float) $right, 'neg' => (float) $neg, 'instruction' => $s->instruction, 'questions' => $qs];
        }
        $out = array_values(array_filter($out, fn ($s) => count($s['questions'])));
        $sectional = $timedAll && count($out) >= 2;
        $minutes = $sectional ? array_sum(array_column($out, 'minutes')) : $exam->computedDuration();
        $langs = [];
        foreach ($out as $s) {
            foreach ($s['questions'] as $q) {
                $langs += array_fill_keys(array_keys($q['langs']), true);
            }
        }

        return $this->papers[$cacheKey] = [
            'sections' => $out, 'timing' => $sectional ? 'sectional' : 'overall', 'minutes' => $minutes ?: null,
            'count' => $n, 'max' => array_sum(array_map(fn ($s) => count($s['questions']) * $s['right'], $out)), 'langs' => array_keys($langs) ?: ['en'],
        ];
    }

    /** Options shown: up to the last one that has words or a picture (at least 2). */
    private function optionCount(LibraryQuestion $q): int
    {
        if ($q->question_type === 'truefalse') {
            return 2;
        }
        $n = 2;
        foreach ($q->texts as $t) {
            for ($k = LibraryQuestion::MAX_OPTIONS; $k > $n; $k--) {
                if (trim(strip_tags((string) $t->{'option_' . $k})) !== '') {
                    $n = $k;
                    break;
                }
            }
        }
        foreach (array_keys(array_filter($q->option_images ?? [])) as $k) {
            $n = max($n, (int) $k);
        }

        return min($n, LibraryQuestion::MAX_OPTIONS);
    }

    // ================================================================= attempt life

    /** The running / paused attempt, or a new one. Returns null when no attempt is left. */
    public function start(User $user, CourseExam $exam, ?string $lang = null): ?CourseExamAttempt
    {
        $open = CourseExamAttempt::where('user_id', $user->id)->where('course_exam_id', $exam->id)->whereIn('status', ['running', 'paused'])->latest('id')->first();
        if ($open) {
            if ($lang) {
                $open->update(['lang' => $lang]);
            }

            return $open;
        }
        $done = CourseExamAttempt::where('user_id', $user->id)->where('course_exam_id', $exam->id)->where('status', 'submitted')->count();
        if ($exam->max_attempts && $done >= $exam->max_attempts) {
            return null;
        }
        $paper = $this->paper($exam);
        $first = $paper['sections'][0]['id'] ?? null;

        return CourseExamAttempt::create([
            'user_id' => $user->id, 'course_exam_id' => $exam->id, 'attempt_no' => $done + 1, 'status' => 'running', 'timing' => $paper['timing'],
            'lang' => $lang ?: ($paper['langs'][0] ?? 'en'), 'current_section_id' => $first, 'answers' => [], 'sections' => [],
            'started_at' => now(), 'last_tick_at' => now(), 'max_score' => $paper['max'],
        ]);
    }

    /** The test page was opened: the clock runs again from now (time away is not counted). */
    public function resume(CourseExamAttempt $a): void
    {
        if ($a->status !== 'submitted') {
            $a->update(['status' => 'running', 'last_tick_at' => now()]);
        }
    }

    /** Adds the seconds since the last ping to the attempt and its current section. */
    private function tick(CourseExamAttempt $a): void
    {
        if ($a->status !== 'running') {
            return;
        }
        $delta = $a->last_tick_at ? max(0, min(self::PING_CAP, (int) floor(now()->getTimestamp() - $a->last_tick_at->getTimestamp()))) : 0;   // whole seconds
        $sections = $a->sections ?: [];
        $sid = (string) $a->current_section_id;
        $sections[$sid]['used'] = ($sections[$sid]['used'] ?? 0) + $delta;
        $a->sections = $sections;
        $a->time_used += $delta;
        $a->last_tick_at = now();
    }

    /** Seconds left: of the current section (sectional) or of the whole test (overall); null = no time limit. */
    public function remaining(CourseExamAttempt $a, array $paper): ?int
    {
        if ($a->timing === 'sectional') {
            $s = collect($paper['sections'])->firstWhere('id', $a->current_section_id);

            return $s ? max(0, $s['minutes'] * 60 - (int) (($a->sections ?: [])[(string) $s['id']]['used'] ?? 0)) : 0;
        }

        return $paper['minutes'] ? max(0, $paper['minutes'] * 60 - $a->time_used) : null;
    }

    /**
     * A save from the test page: answers changed since the last save, the question on screen, and an action
     * (ping | pause | next_section | submit). Sections whose time is over close by themselves; when no time is left the test is submitted.
     */
    public function save(CourseExamAttempt $a, array $paper, array $answers, ?int $currentQ, string $action): CourseExamAttempt
    {
        if ($a->status === 'submitted') {
            return $a;
        }
        // answers count if the section / test still had time at the previous save (answers given in the last seconds arrive with
        // the save that finds the time over); once the time had already run out, nothing more is taken
        $hadTime = $this->remaining($a, $paper) !== 0 && ($a->timing !== 'sectional' || $this->remaining($a, $paper) > 0);
        $this->tick($a);

        // only questions of the paper, and in a sectional test only those of the open section, can change
        $allowed = !$hadTime ? collect() : collect($paper['sections'])->when($a->timing === 'sectional', fn ($c) => $c->where('id', $a->current_section_id))
            ->flatMap(fn ($s) => $s['questions'])->keyBy('id');
        $stored = $a->answers ?: [];
        foreach ($answers as $qid => $v) {
            $q = $allowed->get((int) $qid);
            if (!$q || !is_array($v)) {
                continue;
            }
            $max = count($q['oimg']);
            $opts = array_values(array_unique(array_filter(array_map('intval', (array) ($v['o'] ?? [])), fn ($o) => $o >= 1 && $o <= $max)));
            if ($q['type'] !== 'multiple') {
                $opts = array_slice($opts, 0, 1);
            }
            $stored[(string) $q['id']] = ['o' => $opts, 'm' => !empty($v['m']) ? 1 : 0, 'v' => 1, 't' => min(36000, max((int) ($stored[(string) $q['id']]['t'] ?? 0), (int) ($v['t'] ?? 0)))];
        }
        $a->answers = $stored;
        if ($currentQ && $allowed->has($currentQ)) {
            $a->current_question_id = $currentQ;
        }

        if ($action === 'pause') {
            $a->status = 'paused';
        }
        if ($action === 'submit') {
            return $this->submit($a, $paper);
        }
        if ($a->timing === 'sectional' && ($action === 'next_section' || $this->remaining($a, $paper) <= 0)) {
            $this->closeSection($a, $paper);
            if ($a->status === 'submitted') {
                return $a;
            }
        }
        if ($a->timing === 'overall' && $this->remaining($a, $paper) === 0) {
            return $this->submit($a, $paper);
        }
        $a->save();

        return $a;
    }

    /** Close the open section and open the next one (or submit after the last). */
    private function closeSection(CourseExamAttempt $a, array $paper): void
    {
        $sections = $a->sections ?: [];
        $sections[(string) $a->current_section_id]['done'] = 1;
        $a->sections = $sections;
        $ids = array_column($paper['sections'], 'id');
        $next = null;
        foreach ($ids as $id) {
            if (empty($sections[(string) $id]['done'])) {
                $next = $id;
                break;
            }
        }
        if (!$next) {
            $this->submit($a, $paper);

            return;
        }
        $a->current_section_id = $next;
        $a->current_question_id = null;
    }

    /** Marks the attempt and closes it. */
    public function submit(CourseExamAttempt $a, array $paper): CourseExamAttempt
    {
        $key = $this->paper($a->exam, true);
        $answers = $a->answers ?: [];
        $tot = ['score' => 0.0, 'negative' => 0.0, 'correct' => 0, 'wrong' => 0, 'skipped' => 0];
        $per = [];
        foreach ($key['sections'] as $s) {
            $r = ['score' => 0.0, 'max' => count($s['questions']) * $s['right'], 'correct' => 0, 'wrong' => 0, 'skipped' => 0, 'negative' => 0.0, 'time' => (int) (($a->sections ?: [])[(string) $s['id']]['used'] ?? 0), 'count' => count($s['questions'])];
            foreach ($s['questions'] as $q) {
                $res = $this->result($q, $answers[(string) $q['id']]['o'] ?? []);
                if ($res === 'correct') {
                    $r['correct']++;
                    $r['score'] += $q['right'];
                } elseif ($res === 'wrong') {
                    $r['wrong']++;
                    $r['score'] -= $q['neg'];
                    $r['negative'] += $q['neg'];
                } else {
                    $r['skipped']++;
                }
            }
            $per[(string) $s['id']] = $r;
            foreach (['score', 'negative', 'correct', 'wrong', 'skipped'] as $f) {
                $tot[$f] += $r[$f];
            }
        }
        $a->fill($tot + ['section_scores' => $per, 'max_score' => $key['max'], 'status' => 'submitted', 'submitted_at' => now()]);
        $a->save();

        return $a;
    }

    /** correct | wrong | skipped */
    public function result(array $q, array $chosen): string
    {
        if (!$chosen) {
            return 'skipped';
        }
        $a = array_map('intval', $chosen);
        $b = array_map('intval', $q['key']);
        sort($a);
        sort($b);

        return $a === $b ? 'correct' : 'wrong';
    }

    /** What the test page needs to show the clock and the open section. */
    public function state(CourseExamAttempt $a, array $paper): array
    {
        return [
            'status' => $a->status, 'timing' => $a->timing, 'section' => $a->current_section_id, 'remaining' => $this->remaining($a, $paper),
            'done' => array_map('intval', array_keys(array_filter($a->sections ?: [], fn ($s) => !empty($s['done'])))),
            'sectionLeft' => collect($paper['sections'])->mapWithKeys(fn ($s) => [$s['id'] => $s['minutes'] ? max(0, $s['minutes'] * 60 - (int) (($a->sections ?: [])[(string) $s['id']]['used'] ?? 0)) : null])->all(),
            'totalLeft' => $paper['minutes'] ? max(0, $paper['minutes'] * 60 - $a->time_used) : null,
            'result' => $a->status === 'submitted' ? url('tests/attempt/' . $a->id . '/result') : null,
        ];
    }

    // ================================================================= results

    /** First submitted attempts of everyone (they decide the rank), best first. */
    public function board(CourseExam $exam): Collection
    {
        return CourseExamAttempt::with('user:id,name,image')->where('course_exam_id', $exam->id)->where('status', 'submitted')->where('attempt_no', 1)
            ->orderByDesc('score')->orderBy('time_used')->orderBy('id')->get(['id', 'user_id', 'score', 'time_used', 'correct', 'wrong', 'skipped', 'section_scores']);
    }

    /** rank, total, percentile of a score among the first attempts (a re-attempt is placed among them too). */
    public function rank(CourseExamAttempt $a, Collection $board): array
    {
        $others = $board->where('user_id', '!=', $a->user_id);   // everyone else's first attempt
        $total = $others->count() + 1;
        $above = $others->where('score', '>', $a->score)->count();
        $below = $others->where('score', '<', $a->score)->count();

        return ['rank' => $above + 1, 'total' => $total, 'percentile' => $total > 1 ? round($below / ($total - 1) * 100, 1) : 100.0];
    }

    /** What a student sees on the instructions page after finishing the test: every attempt with its marks, the totals, and the question-by-question summary of the latest attempt (null = no finished attempt yet). Same shape as McqExam::summary(). */
    public function summary(CourseExam $exam, User $user): ?array
    {
        $done = CourseExamAttempt::where('user_id', $user->id)->where('course_exam_id', $exam->id)->where('status', 'submitted')->orderBy('attempt_no')->get();
        if ($done->isEmpty()) {
            return null;
        }
        $last = $done->last();
        $key = $this->paper($exam, true);
        $stats = $this->questionStats($exam, $key);
        $board = $this->board($exam);
        $rank = $this->rank($done->firstWhere('attempt_no', 1) ?? $done->first(), $board);
        $answers = $last->answers ?: [];
        $say = fn (array $t, array $idx) => implode(', ', array_map(fn ($k) => chr(64 + $k) . '. ' . ($t['o'][$k - 1] ?? ''), $idx));

        $review = [];
        foreach ($key['sections'] as $s) {
            foreach ($s['questions'] as $q) {
                $v = $answers[(string) $q['id']] ?? [];
                $chosen = array_values(array_map('intval', (array) ($v['o'] ?? [])));
                $t = $q['langs'][$last->lang ?? ''] ?? reset($q['langs']);
                $review[] = [
                    'n' => count($review) + 1, 'q' => $t['q'] ?? '', 'status' => $this->result($q, $chosen), 'notes' => $t['e'] ?? '',
                    'mineHtml' => $chosen ? $say($t, $chosen) : null, 'ansHtml' => $say($t, $q['key']),
                    'sec' => (int) ($v['t'] ?? 0), 'avgSec' => $stats[$q['id']]['avg'] ?? null, 'pct' => isset($stats[$q['id']]) ? $stats[$q['id']]['pct'] : null,
                ];
            }
        }
        $answered = $done->sum(fn ($a) => $a->correct + $a->wrong);

        return [
            'attempts' => $done->map(fn ($a) => [
                'no' => (int) $a->attempt_no, 'mark' => round((float) $a->score, 2), 'right' => (int) $a->correct, 'wrong' => (int) $a->wrong, 'skipped' => (int) $a->skipped,
                'sec' => (int) $a->time_used, 'at' => $a->submitted_at ? $a->submitted_at->format('d M Y, h:i a') : '', 'url' => url('tests/attempt/' . $a->id . '/result'),
            ])->reverse()->values()->all(),
            'count' => $done->count(), 'best' => round((float) $done->max('score'), 2), 'avg' => round((float) $done->avg('score'), 2),
            'accuracy' => $answered > 0 ? round($done->sum('correct') * 100 / $answered, 1) : 0,
            'avgSec' => (int) round($done->avg('time_used')), 'perQ' => $key['count'] ? (int) round($last->time_used / $key['count']) : null,
            'latest' => (int) $last->attempt_no, 'rank' => $rank['rank'], 'participants' => $rank['total'], 'average' => $board->count() ? round((float) $board->avg('score'), 2) : 0,
            'total' => $key['count'], 'review' => $review,
        ];
    }

    /** Per question: how many answered it, how many got it right, average seconds (from first attempts). */
    public function questionStats(CourseExam $exam, array $keyPaper): array
    {
        $byId = collect($keyPaper['sections'])->flatMap(fn ($s) => $s['questions'])->keyBy('id');
        $stats = [];
        $rows = CourseExamAttempt::where('course_exam_id', $exam->id)->where('status', 'submitted')->where('attempt_no', 1)->latest('id')->limit(1000)->pluck('answers');
        foreach ($rows as $answers) {
            foreach ((array) $answers as $qid => $v) {
                $q = $byId->get((int) $qid);
                if (!$q) {
                    continue;
                }
                $s = &$stats[$qid];
                $s['seen'] = ($s['seen'] ?? 0) + 1;
                $s['time'] = ($s['time'] ?? 0) + (int) ($v['t'] ?? 0);
                if (!empty($v['o'])) {
                    $s['answered'] = ($s['answered'] ?? 0) + 1;
                    $s['correct'] = ($s['correct'] ?? 0) + ($this->result($q, $v['o']) === 'correct' ? 1 : 0);
                }
                unset($s);
            }
        }
        $people = max(1, $rows->count());

        return collect($stats)->map(fn ($s) => ['avg' => (int) round(($s['time'] ?? 0) / max(1, $s['seen'] ?? 1)), 'pct' => (int) round(($s['correct'] ?? 0) / $people * 100)])->all();
    }
}
