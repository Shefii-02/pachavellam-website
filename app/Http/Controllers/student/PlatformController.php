<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClientInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** iOS / Android / web users with their last active time, app version and device. */
class PlatformController extends Controller
{
    private const WINDOWS = [
        '24h' => 'Active in last 24 hours',
        '7d' => 'Active in last 7 days',
        '30d' => 'Active in last 30 days',
        'idle30' => 'Not active for 30+ days',
    ];

    public function index(Request $request)
    {
        $f = $this->filters($request);

        $list = $this->usersQuery($f)->orderByDesc('a.last_active_at')->paginate(per_page(50))->withQueryString();

        $now = Carbon::now();
        $summary = DB::table('user_platform_activity as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->when($f['type'] !== 'all', fn ($q) => $q->where('u.type', $f['type']))
            ->selectRaw('a.platform, COUNT(*) as users, '
                . 'SUM(a.last_active_at >= ?) as d1, SUM(a.last_active_at >= ?) as d7, SUM(a.last_active_at >= ?) as d30, MAX(a.last_active_at) as last_seen',
                [$now->copy()->subDay(), $now->copy()->subDays(7), $now->copy()->subDays(30)])
            ->groupBy('a.platform')->get()->keyBy('platform');

        // distinct people (one person can use the app and the website)
        $people = DB::table('user_platform_activity as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->when($f['type'] !== 'all', fn ($q) => $q->where('u.type', $f['type']))
            ->selectRaw('COUNT(DISTINCT a.user_id) as total, COUNT(DISTINCT CASE WHEN a.last_active_at >= ? THEN a.user_id END) as d1, '
                . 'COUNT(DISTINCT CASE WHEN a.last_active_at >= ? THEN a.user_id END) as d7, COUNT(DISTINCT CASE WHEN a.last_active_at >= ? THEN a.user_id END) as d30',
                [$now->copy()->subDay(), $now->copy()->subDays(7), $now->copy()->subDays(30)])->first();

        $versions = $this->versionTable($f['type']);

        $browsers = DB::table('user_platform_activity as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->when($f['type'] !== 'all', fn ($q) => $q->where('u.type', $f['type']))
            ->where('a.platform', 'web')->whereNotNull('a.device_model')
            ->select('a.device_model', DB::raw('COUNT(*) as users'))->groupBy('a.device_model')->orderByDesc('users')->limit(8)->get();

        return view('admin.student.platforms', [
            'list' => $list,
            'f' => $f,
            'summary' => $summary,
            'people' => $people,
            'versions' => $versions,
            'browsers' => $browsers,
            'windows' => self::WINDOWS,
            'totalUsers' => User::when($f['type'] !== 'all', fn ($q) => $q->where('type', $f['type']))->count(),
            'versionOptions' => DB::table('user_platform_activity')->whereNotNull('app_version')->distinct()->orderBy('app_version')->pluck('app_version'),
            'types' => User::select('type')->distinct()->pluck('type'),
            'trackingSince' => DB::table('user_platform_activity')->min('first_seen_at'),
        ]);
    }

    public function export(Request $request)
    {
        $f = $this->filters($request);
        $query = $this->usersQuery($f)->orderByDesc('a.last_active_at');
        $safe = fn ($v) => is_string($v) && $v !== '' && strpbrk($v[0], '=+-@') ? "'" . $v : $v;

        return response()->streamDownload(function () use ($query, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['User ID', 'Name', 'Email', 'Mobile', 'Type', 'Platform', 'App version', 'Build', 'OS version', 'Device', 'Last active', 'First seen', 'Last IP']);

            $query->chunkById(1000, function ($rows) use ($out, $safe) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->user_id, $safe($r->name), $safe($r->email), $safe($r->mobile), $r->type,
                        ClientInfo::label($r->platform), $r->app_version, $r->app_build, $r->os_version, $safe($r->device_model),
                        $r->last_active_at, $r->first_seen_at, $r->last_ip]);
                }
            }, 'a.id', 'id');
            fclose($out);
        }, 'platform-users-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->get('q')),
            'type' => (string) $request->get('type', 'Student'),
            'platform' => array_key_exists((string) $request->get('platform'), ClientInfo::PLATFORM_LABELS) ? (string) $request->get('platform') : '',
            'version' => (string) $request->get('version'),
            'window' => array_key_exists((string) $request->get('window'), self::WINDOWS) ? (string) $request->get('window') : '',
        ];
    }

    private function usersQuery(array $f)
    {
        $q = DB::table('user_platform_activity as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.id', 'a.user_id', 'a.platform', 'a.app_version', 'a.app_build', 'a.os_version', 'a.device_model',
                'a.last_ip', 'a.first_seen_at', 'a.last_active_at', 'u.name', 'u.email', 'u.mobile', 'u.type');

        if ($f['type'] !== 'all') {
            $q->where('u.type', $f['type']);
        }
        if ($f['platform'] !== '') {
            $q->where('a.platform', $f['platform']);
        }
        if ($f['version'] === 'unknown') {
            $q->whereNull('a.app_version');
        } elseif ($f['version'] !== '') {
            $q->where('a.app_version', $f['version']);
        }
        if ($f['q'] !== '') {
            $like = '%' . $f['q'] . '%';
            $q->where(fn ($w) => $w->where('u.name', 'like', $like)->orWhere('u.email', 'like', $like)->orWhere('u.mobile', 'like', $like));
        }

        $now = Carbon::now();
        match ($f['window']) {
            '24h' => $q->where('a.last_active_at', '>=', $now->copy()->subDay()),
            '7d' => $q->where('a.last_active_at', '>=', $now->copy()->subDays(7)),
            '30d' => $q->where('a.last_active_at', '>=', $now->copy()->subDays(30)),
            'idle30' => $q->where('a.last_active_at', '<', $now->copy()->subDays(30)),
            default => null,
        };

        return $q;
    }

    /** Per mobile platform: every app version in use, how many people, and which is the newest seen. */
    private function versionTable(string $type): array
    {
        $rows = DB::table('user_platform_activity as a')->join('users as u', 'u.id', '=', 'a.user_id')
            ->when($type !== 'all', fn ($q) => $q->where('u.type', $type))
            ->whereIn('a.platform', ['ios', 'android', 'app'])
            ->select('a.platform', 'a.app_version', DB::raw('COUNT(*) as users'), DB::raw('MAX(a.last_active_at) as last_seen'))
            ->groupBy('a.platform', 'a.app_version')->get();

        $out = [];
        foreach ($rows->groupBy('platform') as $platform => $group) {
            $known = $group->whereNotNull('app_version');
            $latest = $known->pluck('app_version')->reduce(fn ($c, $v) => $c === null || version_compare($v, $c, '>') ? $v : $c);
            $total = max(1, $group->sum('users'));

            $out[$platform] = [
                'latest' => $latest,
                'total' => $group->sum('users'),
                'rows' => $group->sort(fn ($a, $b) => version_compare($b->app_version ?? '0', $a->app_version ?? '0'))
                    ->map(fn ($r) => (object) [
                        'version' => $r->app_version,
                        'users' => $r->users,
                        'pct' => round($r->users / $total * 100),
                        'last_seen' => $r->last_seen,
                        'outdated' => $latest && $r->app_version && version_compare($r->app_version, $latest, '<'),
                    ])->values(),
            ];
        }

        return $out;
    }
}
