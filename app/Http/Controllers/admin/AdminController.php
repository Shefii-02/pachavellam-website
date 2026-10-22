<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

use Hash;
// use App\Models\Role;

use DB;

class AdminController extends Controller
{
    //
    public function dashboard(){
        $today = \Carbon\Carbon::today();

        // one cheap query for the 7 day trend, one for today's numbers
        $trend = \App\Models\RequestLog::where('created_at', '>=', $today->copy()->subDays(6))
            ->selectRaw('DATE(created_at) as day, SUM(is_api = 1) as api, SUM(is_api = 0) as web, SUM(bytes_in + bytes_out) as bytes')
            ->groupBy('day')->get()->keyBy('day');

        $days = collect(range(6, 0))->map(function ($i) use ($today, $trend) {
            $d = $today->copy()->subDays($i);
            $row = $trend->get($d->toDateString());
            return ['label' => $d->format('D'), 'api' => (int) optional($row)->api, 'web' => (int) optional($row)->web, 'bytes' => (int) optional($row)->bytes];
        });

        $todayRows = \App\Models\RequestLog::where('created_at', '>=', $today);
        $stats = [
            'students' => User::where('type', 'Student')->count(),
            'new_users_today' => User::where('created_at', '>=', $today)->count(),
            'app_users' => (clone $todayRows)->where('is_api', true)->whereNotNull('user_id')->distinct()->count('user_id'),
            'api_requests' => (clone $todayRows)->where('is_api', true)->count(),
            'api_errors' => (clone $todayRows)->where('is_api', true)->where('status', '>=', 500)->count(),
            'web_visitors' => (clone $todayRows)->where('is_api', false)->distinct()->count('ip'),
            'bytes' => (int) (clone $todayRows)->sum(DB::raw('bytes_in + bytes_out')),
        ];

        $recentErrors = \App\Models\RequestLog::where('status', '>=', 500)->latest('id')->limit(5)->get();

        // one tab per exam type, and how many categories each has
        $examTabs = \App\Models\CompetitiveExam::active()->get();
        $categoryTotals = DB::table('exam_categories')->select('exam_id', DB::raw('COUNT(*) as c'))->groupBy('exam_id')->pluck('c', 'exam_id');
        $categoryCounts = $examTabs->mapWithKeys(fn ($e) => [($e->short_name ?: $e->name) => [(int) ($categoryTotals[$e->id] ?? 0), $e->examUrl('category')]])->all();

        $recentUsers = User::select('id', 'name', 'email', 'mobile', 'type', 'created_at')->latest('id')->limit(5)->get();

        return view('admin.dashboard', compact('days', 'stats', 'recentErrors', 'categoryCounts', 'recentUsers', 'examTabs'));
    }
    
    public function general_dashboard(){
        return view('admin.general.index');
    }


}
