<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevToolController extends Controller
{
    /** Paginated api request log with filters. */
    public function apiLogs(Request $request)
    {
        $logs = RequestLog::query()
            ->where('is_api', true)
            ->when($request->filled('q'), fn ($q) => $q->where('path', 'like', '%' . $request->q . '%'))
            ->when($request->filled('method'), fn ($q) => $q->where('method', strtoupper($request->method)))
            ->when($request->filled('status'), function ($q) use ($request) {
                // "2xx", "4xx", "5xx" or an exact code
                $s = $request->status;
                preg_match('/^[1-5]xx$/', $s)
                    ? $q->whereBetween('status', [(int) $s[0] * 100, (int) $s[0] * 100 + 99])
                    : $q->where('status', (int) $s);
            })
            ->when($request->filled('user'), fn ($q) => $q->where('user_id', (int) $request->user))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.general.dev-tools.api-logs', compact('logs'));
    }

    /** Daily usage: app (api) users + requests, website usage, bandwidth. */
    public function analytics(Request $request)
    {
        $days = in_array((int) $request->days, [7, 14, 30, 90]) ? (int) $request->days : 14;
        $from = Carbon::today()->subDays($days - 1);

        $daily = RequestLog::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(is_api = 1) as api_requests')
            ->selectRaw('COUNT(DISTINCT CASE WHEN is_api = 1 THEN user_id END) as app_users')
            ->selectRaw('SUM(is_api = 1 AND status >= 500) as api_5xx')
            ->selectRaw('SUM(is_api = 1 AND status >= 400 AND status < 500) as api_4xx')
            ->selectRaw('ROUND(AVG(CASE WHEN is_api = 1 THEN duration_ms END)) as api_avg_ms')
            ->selectRaw('SUM(is_api = 0) as web_requests')
            ->selectRaw('COUNT(DISTINCT CASE WHEN is_api = 0 THEN ip END) as web_visitors')
            ->selectRaw('SUM(CASE WHEN is_api = 0 THEN bytes_out ELSE 0 END) as web_bytes_out')
            ->selectRaw('SUM(CASE WHEN is_api = 1 THEN bytes_in + bytes_out ELSE 0 END) as api_bytes')
            ->selectRaw('SUM(bytes_in + bytes_out) as total_bytes')
            ->groupBy('day')
            ->orderByDesc('day')
            ->get();

        $today = Carbon::today();
        $todayRows = RequestLog::where('created_at', '>=', $today);

        $topApi = (clone $todayRows)->where('is_api', true)
            ->select('method', 'path', DB::raw('COUNT(*) as hits'), DB::raw('ROUND(AVG(duration_ms)) as avg_ms'),
                DB::raw('SUM(status >= 400) as errors'), DB::raw('SUM(bytes_in + bytes_out) as bytes'))
            ->groupBy('method', 'path')->orderByDesc('hits')->limit(10)->get();

        $topWeb = (clone $todayRows)->where('is_api', false)
            ->select('path', DB::raw('COUNT(*) as hits'), DB::raw('COUNT(DISTINCT ip) as visitors'),
                DB::raw('SUM(bytes_out) as bytes'))
            ->groupBy('path')->orderByDesc('hits')->limit(10)->get();

        $topUsers = (clone $todayRows)->where('is_api', true)->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as hits'), DB::raw('SUM(bytes_in + bytes_out) as bytes'))
            ->groupBy('user_id')->orderByDesc('hits')->limit(10)->get();

        $totals = [
            'app_users' => (clone $todayRows)->where('is_api', true)->whereNotNull('user_id')->distinct()->count('user_id'),
            'api_requests' => (clone $todayRows)->where('is_api', true)->count(),
            'web_requests' => (clone $todayRows)->where('is_api', false)->count(),
            'web_visitors' => (clone $todayRows)->where('is_api', false)->distinct()->count('ip'),
            'bytes' => (int) (clone $todayRows)->sum(DB::raw('bytes_in + bytes_out')),
        ];

        return view('admin.general.dev-tools.analytics', compact('daily', 'days', 'topApi', 'topWeb', 'topUsers', 'totals'));
    }

    /** Delete request logs older than ?days (default 30). */
    public function prune(Request $request)
    {
        $days = max(1, (int) $request->get('days', 30));
        $deleted = RequestLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('message', "Deleted {$deleted} log rows older than {$days} days");
    }
}
