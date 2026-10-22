<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Builds the filtered users query used by the student list screen and its CSV export.
 *
 * "Last activity" is the newest of: exam attempts (KPSC daily / CA / model), capsule likes and
 * comments, exam discussion posts and logged-in API requests (request_logs, recorded from now on).
 */
class StudentFilter
{
    /** [table, user column, time column, extra where] */
    public const ACTIVITY_SOURCES = [
        'kpsc_daily_exam' => ['kpsc_daily_exams_attempt', 'user_id', 'created_at', null],
        'kpsc_ca_exam' => ['kpsc_ca_daily_exam_attempts', 'user_id', 'created_at', null],
        'kpsc_model_exam' => ['kpsc_model_exam_attempt', 'user_id', 'created_at', null],
        'capsule_like' => ['capsule_like', 'user_id', 'created_at', null],
        'capsule_comment' => ['capsule_comment', 'user_id', 'created_at', null],
        'exam_discussion' => ['daily_exam_discussion', 'sender_id', 'created_at', null],
        'app_api' => ['request_logs', 'user_id', 'created_at', 'is_api = 1'],
    ];

    public const MODULE_LABELS = [
        'kpsc_daily_exam' => 'KPSC daily exam',
        'kpsc_ca_exam' => 'KPSC current affairs exam',
        'kpsc_model_exam' => 'KPSC model exam',
        'capsule_like' => 'Capsule likes',
        'capsule_comment' => 'Capsule comments',
        'exam_discussion' => 'Exam discussions',
        'app_api' => 'App (API) usage',
    ];

    public const SIGNUP_LABELS = [
        'google' => 'Google',
        'apple' => 'Apple',
        'facebook' => 'Facebook',
        'other' => 'Mobile / email',
    ];

    public static function filtersFrom(Request $request): array
    {
        return [
            'q' => trim((string) $request->get('q')),
            'type' => (string) $request->get('type', 'Student'),
            'mobile' => (string) $request->get('mobile'),          // with | without | verified | unverified
            'signup' => (string) $request->get('signup'),          // google | apple | facebook | other
            'activity' => (string) $request->get('activity'),      // active_30 | inactive_30 | inactive_90 | never
            'module' => (string) $request->get('module'),          // key of ACTIVITY_SOURCES
            'platform' => (string) $request->get('platform'),      // ios | android | web | app
            'interest' => (string) $request->get('interest'),      // competitive_exams.slug, or 'none'
            'from' => (string) $request->get('from'),
            'to' => (string) $request->get('to'),
        ];
    }

    public static function query(array $f, bool $withActivity = false): Builder
    {
        $q = User::query()->select('users.*');

        if ($f['type'] !== '' && $f['type'] !== 'all') {
            $q->where('users.type', $f['type']);
        }

        if ($f['q'] !== '') {
            $like = '%' . $f['q'] . '%';
            $q->where(fn ($w) => $w->where('users.name', 'like', $like)
                ->orWhere('users.email', 'like', $like)
                ->orWhere('users.mobile', 'like', $like));
        }

        match ($f['mobile']) {
            'with' => $q->whereNotNull('users.mobile')->where('users.mobile', '!=', ''),
            'without' => $q->where(fn ($w) => $w->whereNull('users.mobile')->orWhere('users.mobile', '')),
            'verified' => $q->whereNotNull('users.mobile_verified_at'),
            'unverified' => $q->whereNotNull('users.mobile')->where('users.mobile', '!=', '')->whereNull('users.mobile_verified_at'),
            default => null,
        };

        match ($f['signup']) {
            'google' => $q->whereNotNull('users.google_id')->where('users.google_id', '!=', ''),
            'apple' => $q->whereNotNull('users.apple_id')->where('users.apple_id', '!=', ''),
            'facebook' => $q->whereNotNull('users.facebook_id')->where('users.facebook_id', '!=', ''),
            'other' => $q->where(fn ($w) => $w->whereNull('users.google_id')->orWhere('users.google_id', ''))
                ->where(fn ($w) => $w->whereNull('users.apple_id')->orWhere('users.apple_id', ''))
                ->where(fn ($w) => $w->whereNull('users.facebook_id')->orWhere('users.facebook_id', '')),
            default => null,
        };

        // platform the user has been seen on (user_platform_activity)
        if ($f['platform'] !== '') {
            $q->whereIn('users.id', fn ($s) => $s->select('user_id')->from('user_platform_activity')->where('platform', $f['platform']));
        }

        // exam interests (student_exam_interests)
        if ($f['interest'] === 'none') {
            $q->whereNotIn('users.id', fn ($s) => $s->select('user_id')->from('student_exam_interests'));
        } elseif ($f['interest'] !== '') {
            $q->whereIn('users.id', fn ($s) => $s->select('i.user_id')->from('student_exam_interests as i')
                ->join('competitive_exams as e', 'e.id', '=', 'i.exam_id')->where('e.slug', $f['interest']));
        }

        if ($f['from'] !== '') {
            $q->whereDate('users.created_at', '>=', $f['from']);
        }
        if ($f['to'] !== '') {
            $q->whereDate('users.created_at', '<=', $f['to']);
        }

        // "used this module" => at least one row of that activity for the user
        if (isset(self::ACTIVITY_SOURCES[$f['module']])) {
            [$table, $col, , $extra] = self::ACTIVITY_SOURCES[$f['module']];
            // IN (distinct ids) is evaluated once; a correlated EXISTS would rescan the
            // activity table for every user because user_id is not indexed there
            $q->whereIn('users.id', function ($s) use ($table, $col, $extra) {
                $s->selectRaw("DISTINCT CAST($col AS UNSIGNED)")->from($table)->whereNotNull($col);
                if ($extra) {
                    $s->whereRaw($extra);
                }
            });
        }

        if ($f['activity'] !== '' || $withActivity) {
            $q->leftJoinSub(self::lastActivitySql(), 'act', 'act.user_id', '=', 'users.id')
                ->addSelect('act.last_activity');
        }

        $now = Carbon::now();
        match ($f['activity']) {
            'active_30' => $q->where('act.last_activity', '>=', $now->copy()->subDays(30)),
            'inactive_30' => $q->where(fn ($w) => $w->whereNull('act.last_activity')->orWhere('act.last_activity', '<', $now->copy()->subDays(30))),
            'inactive_90' => $q->where(fn ($w) => $w->whereNull('act.last_activity')->orWhere('act.last_activity', '<', $now->copy()->subDays(90))),
            'never' => $q->whereNull('act.last_activity'),
            default => null,
        };

        return $q;
    }

    /** Attaches last_activity to already loaded rows (cheap: only for the visible page). */
    public static function attachLastActivity($users): void
    {
        $ids = collect($users)->pluck('id')->all();
        if (!$ids) {
            return;
        }

        $map = DB::query()->fromSub(self::lastActivitySql($ids), 'act')->pluck('last_activity', 'user_id');

        foreach ($users as $u) {
            $u->last_activity = $map[$u->id] ?? null;
        }
    }

    /** SELECT user_id, MAX(last activity) across every activity source. */
    public static function lastActivitySql(array $onlyIds = []): \Illuminate\Database\Query\Builder
    {
        $union = null;

        foreach (self::ACTIVITY_SOURCES as [$table, $col, $time, $extra]) {
            $part = DB::table($table)
                // CAST: daily_exam_discussion.sender_id is a varchar, which would stop the join using an index
                ->selectRaw("CAST($col AS UNSIGNED) as user_id, MAX($time) as ts")
                ->whereNotNull($col)
                ->groupBy($col);

            if ($extra) {
                $part->whereRaw($extra);
            }
            if ($onlyIds) {
                $part->whereIn($col, $onlyIds);
            }

            $union = $union ? $union->unionAll($part) : $part;
        }

        return DB::query()->fromSub($union, 'u')->selectRaw('user_id, MAX(ts) as last_activity')->groupBy('user_id');
    }

    public static function signupMethod($user): string
    {
        return match (true) {
            !empty($user->google_id) => 'Google',
            !empty($user->apple_id) => 'Apple',
            !empty($user->facebook_id) => 'Facebook',
            default => 'Mobile / email',
        };
    }

    /** active (30d) / inactive (30-90d) / dormant (90d+) / never */
    public static function activityStatus($lastActivity): array
    {
        if (!$lastActivity) {
            return ['Never active', 'secondary'];
        }
        $days = Carbon::parse($lastActivity)->diffInDays(now());

        return match (true) {
            $days <= 30 => ['Active', 'success'],
            $days <= 90 => ['Inactive', 'warning text-dark'],
            default => ['Dormant (90d+)', 'danger'],
        };
    }
}
