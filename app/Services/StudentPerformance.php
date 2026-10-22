<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Exam performance of students, per "performance source".
 *
 * A source describes where the graded attempts of an exam live. competitive_exams.performance_source
 * points at one of the keys below. Exams without a source (RRB, SSC, ... until their practice exams
 * exist) simply have no performance data. To add one, register its attempt table here.
 *
 * Ranking: students are ranked by total marks (negative marking included) over the chosen period;
 * accuracy = right / (right + wrong + skipped).
 */
class StudentPerformance
{
    public const SOURCES = [
        'kpsc' => [
            'label' => 'Daily exams',
            'table' => 'kpsc_daily_exams_attempt',
            'user' => 'user_id',
            'time' => 'created_at',
            'right' => 'right',
            'wrong' => 'wrong',
            'skipped' => 'skipped',
            'marks' => 'total_mark',
            'where' => 'status = 2',        // 2 = finished attempt (0 = started, not submitted)
        ],
    ];

    public const PERIODS = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 3 months',
        'all' => 'All time',
    ];

    public const METRICS = [
        'marks' => 'Total marks',
        'accuracy' => 'Accuracy',
        'attempts' => 'Attempts',
    ];

    public static function has(?string $source): bool
    {
        return $source !== null && isset(self::SOURCES[$source]);
    }

    private static function base(string $source, ?array $userIds, ?Carbon $since)
    {
        $s = self::SOURCES[$source];
        $q = DB::table($s['table'])->whereNotNull($s['user'])->whereRaw($s['where']);

        if ($userIds !== null) {
            $q->whereIn($s['user'], $userIds);
        }
        if ($since) {
            $q->where($s['time'], '>=', $since);
        }

        return $q;
    }

    public static function since(string $period): ?Carbon
    {
        return $period === 'all' || !ctype_digit($period) ? null : Carbon::now()->subDays((int) $period);
    }

    /**
     * Per student totals, keyed by user id.
     * Each value: attempts, right, wrong, skipped, marks, best, accuracy, avg_marks, last_attempt
     */
    public static function aggregate(string $source, ?array $userIds = null, ?Carbon $since = null): Collection
    {
        $s = self::SOURCES[$source];

        $rows = self::base($source, $userIds, $since)
            ->selectRaw("{$s['user']} as user_id, COUNT(*) as attempts, SUM(`{$s['right']}`) as r, SUM(`{$s['wrong']}`) as w, "
                . "SUM(`{$s['skipped']}`) as sk, SUM({$s['marks']}) as marks, MAX({$s['marks']}) as best, MAX({$s['time']}) as last_attempt")
            ->groupBy($s['user'])
            ->get();

        return $rows->mapWithKeys(function ($r) {
            $answered = $r->r + $r->w + $r->sk;

            return [$r->user_id => (object) [
                'attempts' => (int) $r->attempts,
                'right' => (int) $r->r,
                'wrong' => (int) $r->w,
                'skipped' => (int) $r->sk,
                'marks' => round((float) $r->marks, 2),
                'best' => round((float) $r->best, 2),
                'avg_marks' => round((float) $r->marks / max(1, $r->attempts), 2),
                'accuracy' => $answered ? round($r->r / $answered * 100, 1) : 0.0,
                'last_attempt' => $r->last_attempt,
            ]];
        });
    }

    /** Adds `rank` (1 = best, ties share a rank) to an aggregate collection for the given metric. */
    public static function rank(Collection $stats, string $metric = 'marks', int $minAttempts = 1): Collection
    {
        $metric = isset(self::METRICS[$metric]) ? $metric : 'marks';
        $eligible = $stats->filter(fn ($s) => $s->attempts >= $minAttempts)->sortByDesc(fn ($s) => [$s->{$metric}, $s->marks]);

        $rank = 0;
        $prev = null;
        $position = 0;
        foreach ($eligible as $s) {
            $position++;
            if ($prev === null || $s->{$metric} != $prev) {
                $rank = $position;
                $prev = $s->{$metric};
            }
            $s->rank = $rank;
        }

        return $stats;
    }

    /** Position of one student among a set of students (for the profile page). */
    public static function standing(string $source, array $cohortIds, int $userId, ?Carbon $since = null): ?array
    {
        $stats = self::aggregate($source, $cohortIds, $since);
        if (!isset($stats[$userId])) {
            return null;
        }
        self::rank($stats, 'marks');

        return ['rank' => $stats[$userId]->rank, 'of' => $stats->filter(fn ($s) => isset($s->rank))->count()];
    }

    /** Last N weeks, oldest first: attempts, marks and accuracy per week for one student. */
    public static function weekly(string $source, int $userId, int $weeks = 8): array
    {
        $s = self::SOURCES[$source];
        $start = Carbon::today()->startOfWeek()->subWeeks($weeks - 1);

        $rows = self::base($source, [$userId], $start)
            ->selectRaw("YEARWEEK({$s['time']}, 1) as wk, COUNT(*) as attempts, SUM({$s['marks']}) as marks, "
                . "SUM(`{$s['right']}`) as r, SUM(`{$s['wrong']}`) as w, SUM(`{$s['skipped']}`) as sk")
            ->groupBy('wk')->get()->keyBy('wk');

        $out = [];
        for ($i = 0; $i < $weeks; $i++) {
            $d = $start->copy()->addWeeks($i);
            $r = $rows->get((int) $d->format('oW'));
            $answered = $r ? $r->r + $r->w + $r->sk : 0;

            $out[] = [
                'label' => $d->format('d M'),
                'attempts' => $r ? (int) $r->attempts : 0,
                'marks' => $r ? round((float) $r->marks, 1) : 0,
                'accuracy' => $answered ? round($r->r / $answered * 100) : 0,
            ];
        }

        return $out;
    }

    /** Short text for a student's trend: compares last 4 weeks to the 4 before. */
    public static function trend(array $weekly): string
    {
        $recent = collect($weekly)->slice(-4)->avg('accuracy');
        $before = collect($weekly)->slice(-8, 4)->avg('accuracy');

        if (!collect($weekly)->sum('attempts')) {
            return 'No recent attempts';
        }

        return match (true) {
            $before == 0 => 'New activity',
            $recent - $before >= 5 => 'Improving',
            $before - $recent >= 5 => 'Declining',
            default => 'Steady',
        };
    }
}
