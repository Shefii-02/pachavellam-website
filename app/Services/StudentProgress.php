<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Everything the My account dashboard shows about a student's work on the portal:
 * exam attempts, time spent, coins, overall rank, leaderboard and website activity.
 *
 * Coins are worked out from the attempts (no separate ledger to drift out of step):
 *   COINS_PER_ATTEMPT for every finished attempt + COINS_PER_RIGHT for every correct answer.
 */
class StudentProgress
{
    const COINS_PER_ATTEMPT = 5;
    const COINS_PER_RIGHT = 1;
    const MAX_ATTEMPT_SECONDS = 10800;   // a forgotten open tab must not count as 20 hours of study

    /** One row per finished attempt, from every attempt table, for $userId (or all users when null). */
    private function attemptsQuery(?int $userId)
    {
        $secs = fn ($start, $end) => "least(greatest(coalesce(timestampdiff(second, {$start}, {$end}), 0), 0), " . self::MAX_ATTEMPT_SECONDS . ')';
        $parts = [];
        if (Schema::hasTable('kpsc_daily_exams_attempt')) {
            $parts[] = DB::table('kpsc_daily_exams_attempt as a')->leftJoin('kpsc_daily_exams as e', 'e.id', '=', 'a.exam_id')
                ->whereNotNull('a.attend_ended_at')->where('a.user_id', '>', 0)->when($userId, fn ($q) => $q->where('a.user_id', $userId))
                ->selectRaw("a.user_id, 'Daily exam' as kind, a.exam_id, coalesce(nullif(e.examtitle, ''), 'Daily exam') as title, a.`right` as rights, a.wrong, a.skipped, a.total_mark as score, " . $secs('a.attend_started_at', 'a.attend_ended_at') . ' as secs, a.attend_ended_at as at');
        }
        if (Schema::hasTable('kpsc_ca_daily_exam_attempts')) {
            $parts[] = DB::table('kpsc_ca_daily_exam_attempts as a')->leftJoin('kpsc_daily_exams as e', 'e.id', '=', 'a.exam_id')
                ->whereNotNull('a.attend_ended_at')->where('a.user_id', '>', 0)->when($userId, fn ($q) => $q->where('a.user_id', $userId))
                ->selectRaw("a.user_id, 'CA exam' as kind, a.exam_id, coalesce(nullif(e.examtitle, ''), 'Current affairs exam') as title, a.`right` as rights, a.wrong, a.skipped, null as score, " . $secs('a.attend_started_at', 'a.attend_ended_at') . ' as secs, a.attend_ended_at as at');
        }
        if (Schema::hasTable('kpsc_model_exam_attempt')) {
            $parts[] = DB::table('kpsc_model_exam_attempt as a')->leftJoin('kpsc_daily_exams as e', 'e.id', '=', 'a.exam_id')
                ->where('a.user_id', '>', 0)->when($userId, fn ($q) => $q->where('a.user_id', $userId))
                ->selectRaw("a.user_id, 'Model exam' as kind, a.exam_id, coalesce(nullif(e.examtitle, ''), 'Model exam') as title, a.right_answer as rights, a.wrong_answer as wrong, a.not_attended as skipped, a.total_mark as score, 0 as secs, a.created_at as at");
        }
        $q = array_shift($parts);
        foreach ($parts as $p) {
            $q->unionAll($p);
        }

        return $q;
    }

    /** ['top' => best 50 [user_id, attempts, coins], 'coins' => every student's coins, best first]. Cached small - it scans every attempt. */
    private function board(): array
    {
        return Cache::remember('student_progress_board_v3', 600, function () {
            $sub = $this->attemptsQuery(null);
            if (!$sub) {
                return ['top' => [], 'coins' => []];
            }
            $rows = DB::query()->fromSub($sub, 't')
                ->selectRaw('user_id, count(*) as attempts, count(*) * ' . self::COINS_PER_ATTEMPT . ' + coalesce(sum(rights), 0) * ' . self::COINS_PER_RIGHT . ' as coins')
                ->groupBy('user_id')->get();

            // coins from referrals / bonuses count too (a student with only bonus coins is on the board as well)
            $all = [];
            foreach ($rows as $r) {
                $all[(int) $r->user_id] = [(int) $r->attempts, (int) $r->coins];
            }
            if (Schema::hasTable('coin_ledger')) {
                foreach (DB::table('coin_ledger')->selectRaw('user_id, sum(coins) as s')->groupBy('user_id')->get() as $l) {
                    $all[(int) $l->user_id] = [$all[(int) $l->user_id][0] ?? 0, ($all[(int) $l->user_id][1] ?? 0) + (int) $l->s];
                }
            }
            $list = [];
            foreach ($all as $uid => [$attempts, $coins]) {
                if ($coins > 0) {
                    $list[] = [$uid, $attempts, $coins];
                }
            }
            usort($list, fn ($a, $b) => [$b[2], $a[0]] <=> [$a[2], $b[0]]);

            return ['top' => array_slice($list, 0, 50), 'coins' => array_column($list, 2)];
        });
    }

    /** Ledger coins of one student (referral rewards, bonuses, corrections). */
    public function bonusCoins(int $userId): int
    {
        return Schema::hasTable('coin_ledger') ? (int) DB::table('coin_ledger')->where('user_id', $userId)->sum('coins') : 0;
    }

    /**
     * Rank list for the public pages: names shortened ("Anu K."), no contact details.
     * $examSlug limits it to one exam type - only exam types with test data have a list (today: kpsc), others return [].
     */
    public function publicBoard(?string $examSlug = null, int $limit = 10): \Illuminate\Support\Collection
    {
        if ($examSlug !== null && $examSlug !== 'kpsc') {
            return collect();
        }
        $limit = max(1, min(50, $limit));
        $rows = collect($this->board()['top'])->take($limit);
        if ($examSlug === 'kpsc') {
            // exam type list: test points only (5 per finished test + 1 per right answer), no referral bonus
            $rows = Cache::remember('student_progress_exam_kpsc_' . $limit, 600, function () use ($limit) {
                $sub = $this->attemptsQuery(null);

                return $sub ? DB::query()->fromSub($sub, 't')
                    ->selectRaw('user_id, count(*) as attempts, count(*) * ' . self::COINS_PER_ATTEMPT . ' + coalesce(sum(rights), 0) * ' . self::COINS_PER_RIGHT . ' as coins')
                    ->groupBy('user_id')->orderByDesc('coins')->orderBy('user_id')->limit($limit)->get()->map(fn ($r) => [(int) $r->user_id, (int) $r->attempts, (int) $r->coins]) : collect();
            });
        }
        $users = DB::table('users')->whereIn('id', $rows->pluck(0)->all() ?: [0])->pluck('name', 'id');
        $pos = 0;
        $prev = null;

        return $rows->values()->map(function ($b, $i) use ($users, &$pos, &$prev) {
            [$uid, $attempts, $coins] = $b;
            $pos = ($prev !== null && $prev === $coins) ? $pos : $i + 1;
            $prev = $coins;
            $p = preg_split('/\s+/', trim((string) ($users[$uid] ?? ''))) ?: [];
            $name = ($p && $p[0] !== '') ? (count($p) > 1 ? $p[0] . ' ' . mb_substr(end($p), 0, 1) . '.' : $p[0]) : 'Student';

            return (object) ['pos' => $pos, 'uid' => $uid, 'name' => $name, 'coins' => $coins, 'attempts' => $attempts];
        });
    }

    public function summary(int $userId): array
    {
        $sub = $this->attemptsQuery($userId);
        $rows = $sub ? DB::query()->fromSub($sub, 't')->orderByDesc('at')->get() : collect();

        $attempts = $rows->count();
        $rights = (int) $rows->sum('rights');
        $wrong = (int) $rows->sum('wrong');
        $answered = $rights + $wrong;
        $secs = (int) $rows->sum('secs');
        $testCoins = $attempts * self::COINS_PER_ATTEMPT + $rights * self::COINS_PER_RIGHT;
        $bonus = $this->bonusCoins($userId);
        $coins = $testCoins + $bonus;

        // practice streak: consecutive days (ending today or yesterday) with at least one attempt
        $days = $rows->pluck('at')->filter()->map(fn ($d) => Carbon::parse($d)->toDateString())->unique()->values();
        $streak = 0;
        $cursor = $days->contains(today()->toDateString()) ? today() : today()->subDay();
        while ($days->contains($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }
        $best = 0;
        $run = 0;
        $prev = null;
        foreach ($days->sort()->values() as $d) {
            $run = ($prev && Carbon::parse($prev)->addDay()->toDateString() === $d) ? $run + 1 : 1;
            $best = max($best, $run);
            $prev = $d;
        }

        // last 14 days for the little bar chart
        $perDay = $rows->groupBy(fn ($r) => Carbon::parse($r->at)->toDateString())->map->count();
        $chart = collect(range(13, 0))->map(fn ($i) => ['d' => today()->subDays($i), 'n' => (int) ($perDay[today()->subDays($i)->toDateString()] ?? 0)]);

        // by exam kind
        $byKind = $rows->groupBy('kind')->map(fn ($g) => (object) [
            'attempts' => $g->count(), 'rights' => (int) $g->sum('rights'), 'wrong' => (int) $g->sum('wrong'), 'secs' => (int) $g->sum('secs'),
        ]);

        $board = $this->board();
        $rank = null;
        if ($coins > 0) {
            $rank = 1;
            foreach ($board['coins'] as $c) {   // best first, so stop at the first one not ahead of this student
                if ($c <= $coins) {
                    break;
                }
                $rank++;
            }
        }
        $ranked = max(count($board['coins']), $coins > 0 ? 1 : 0);

        return [
            'attempts' => $attempts, 'rights' => $rights, 'wrong' => $wrong, 'skipped' => (int) $rows->sum('skipped'),
            'accuracy' => $answered ? round($rights / $answered * 100) : null,
            'secs' => $secs, 'coins' => $coins, 'testCoins' => $testCoins, 'bonusCoins' => $bonus, 'streak' => $streak, 'bestStreak' => $best,
            'activeDays' => $days->count(), 'chart' => $chart, 'byKind' => $byKind,
            'recent' => $rows->take(15), 'bestScore' => $rows->max('score'),
            'rank' => $rank, 'ranked' => $ranked,
            'topPercent' => $rank && $ranked ? max(1, (int) ceil($rank / $ranked * 100)) : null,
            'level' => $this->level($coins),
        ];
    }

    /** Top students by coins, names only (no phone/email) plus where $userId stands. */
    public function leaderboard(int $userId, int $limit = 10): array
    {
        $board = $this->board();
        $top = collect($board['top'])->take($limit);
        $ids = $top->pluck(0)->push($userId)->unique()->all();
        $users = DB::table('users')->whereIn('id', $ids)->get(['id', 'name', 'image'])->keyBy('id');

        $pos = 0;
        $prev = null;
        $rows = $top->values()->map(function ($b, $i) use (&$pos, &$prev, $users, $userId) {
            [$uid, $attempts, $coins] = $b;
            $pos = ($prev !== null && $prev === $coins) ? $pos : $i + 1;
            $prev = $coins;
            $name = trim((string) ($users[$uid]->name ?? '')) ?: 'Student';

            return (object) ['pos' => $pos, 'name' => $name, 'coins' => $coins, 'attempts' => $attempts, 'me' => $uid === $userId];
        });

        return ['rows' => $rows, 'meInTop' => $rows->contains('me', true)];
    }

    /** What the student did on the website / app lately. */
    public function activity(int $userId): array
    {
        $out = ['platforms' => collect(), 'visits' => 0, 'lastSeen' => null, 'firstSeen' => null, 'classMinutes' => 0, 'classesWatched' => 0, 'comments' => 0, 'likes' => 0, 'discussions' => 0];

        if (Schema::hasTable('user_platform_activity')) {
            $out['platforms'] = DB::table('user_platform_activity')->where('user_id', $userId)->orderByDesc('last_active_at')->get(['platform', 'app_version', 'device_model', 'first_seen_at', 'last_active_at']);
            $out['lastSeen'] = $out['platforms']->max('last_active_at');
            $out['firstSeen'] = $out['platforms']->min('first_seen_at');
        }
        if (Schema::hasTable('request_logs')) {
            $out['visits'] = DB::table('request_logs')->where('user_id', $userId)->where('created_at', '>=', now()->subDays(30))->count();
        }
        if (Schema::hasTable('class_watch_logs')) {
            $w = DB::table('class_watch_logs')->where('user_id', $userId)->selectRaw('coalesce(sum(watched_seconds), 0) s, count(*) c')->first();
            $out['classMinutes'] = (int) round(($w->s ?? 0) / 60);
            $out['classesWatched'] = (int) ($w->c ?? 0);
        }
        foreach (['capsule_comment' => ['comments', 'user_id'], 'capsule_like' => ['likes', 'user_id'], 'daily_exam_discussion' => ['discussions', 'sender_id']] as $table => [$key, $col]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                $out[$key] = DB::table($table)->where($col, $userId)->count();
            }
        }

        return $out;
    }

    /**
     * Graph data. $period: day (last 30 days) | month (last 12 months) | year (last 5 years).
     * Series per bucket: tests attempted, minutes on tests, minutes watching classes, coins earned, site/app visits.
     */
    public function trend(int $userId, string $period): array
    {
        $period = in_array($period, ['day', 'month', 'year'], true) ? $period : 'day';
        [$n, $fmt, $sql, $step, $label] = match ($period) {
            'month' => [12, 'Y-m', '%Y-%m', 'subMonths', 'M y'],
            'year' => [5, 'Y', '%Y', 'subYears', 'Y'],
            default => [30, 'Y-m-d', '%Y-%m-%d', 'subDays', 'd M'],
        };
        $start = match ($period) {
            'month' => now()->startOfMonth()->subMonths($n - 1),
            'year' => now()->startOfYear()->subYears($n - 1),
            default => today()->subDays($n - 1),
        };

        $buckets = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            $d = $period === 'day' ? today()->{$step}($i) : now()->{'startOf' . ($period === 'month' ? 'Month' : 'Year')}()->{$step}($i);
            $buckets[$d->format($fmt)] = ['label' => $d->format($label), 'tests' => 0, 'testMin' => 0.0, 'videoMin' => 0.0, 'coins' => 0, 'visits' => 0];
        }

        if ($sub = $this->attemptsQuery($userId)) {
            $rows = DB::query()->fromSub($sub, 't')->where('at', '>=', $start)
                ->selectRaw("date_format(at, '{$sql}') as b, count(*) as n, sum(secs) as s, sum(rights) as r")->groupBy('b')->get();
            foreach ($rows as $r) {
                if (isset($buckets[$r->b])) {
                    $buckets[$r->b]['tests'] = (int) $r->n;
                    $buckets[$r->b]['testMin'] = round($r->s / 60, 1);
                    $buckets[$r->b]['coins'] = (int) $r->n * self::COINS_PER_ATTEMPT + (int) $r->r * self::COINS_PER_RIGHT;
                }
            }
        }
        if (Schema::hasTable('class_watch_logs')) {
            $rows = DB::table('class_watch_logs')->where('user_id', $userId)->where('last_watched_at', '>=', $start)
                ->selectRaw("date_format(last_watched_at, '{$sql}') as b, sum(watched_seconds) as s")->groupBy('b')->get();
            foreach ($rows as $r) {
                if (isset($buckets[$r->b])) {
                    $buckets[$r->b]['videoMin'] = round($r->s / 60, 1);
                }
            }
        }
        if (Schema::hasTable('request_logs')) {
            $rows = DB::table('request_logs')->where('user_id', $userId)->where('created_at', '>=', $start)
                ->selectRaw("date_format(created_at, '{$sql}') as b, count(*) as n")->groupBy('b')->get();
            foreach ($rows as $r) {
                if (isset($buckets[$r->b])) {
                    $buckets[$r->b]['visits'] = (int) $r->n;
                }
            }
        }

        $b = array_values($buckets);

        return [
            'period' => $period, 'labels' => array_column($b, 'label'),
            'tests' => array_column($b, 'tests'), 'testMin' => array_column($b, 'testMin'), 'videoMin' => array_column($b, 'videoMin'),
            'coins' => array_column($b, 'coins'), 'visits' => array_column($b, 'visits'),
        ];
    }

    public function level(int $coins): array
    {
        $tiers = [[0, 'Beginner'], [100, 'Learner'], [500, 'Achiever'], [1500, 'Scholar'], [4000, 'Expert'], [10000, 'Champion']];
        $cur = $tiers[0];
        $next = null;
        foreach ($tiers as $i => $t) {
            if ($coins >= $t[0]) {
                $cur = $t;
                $next = $tiers[$i + 1] ?? null;
            }
        }

        return ['name' => $cur[1], 'next' => $next[1] ?? null, 'need' => $next ? $next[0] - $coins : 0,
            'pct' => $next ? (int) round(($coins - $cur[0]) / ($next[0] - $cur[0]) * 100) : 100];
    }

    public static function duration(int $secs): string
    {
        if ($secs < 60) {
            return $secs . 's';
        }
        $h = intdiv($secs, 3600);
        $m = intdiv($secs % 3600, 60);

        return $h ? $h . 'h ' . $m . 'm' : $m . ' min';
    }
}
