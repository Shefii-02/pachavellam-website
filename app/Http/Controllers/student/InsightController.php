<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\User;
use App\Services\StudentPerformance;
use App\Services\StudentFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InsightController extends Controller
{
    /** Student analytics: growth, signup methods, mobile coverage, activity segments, locations. */
    public function analytics()
    {
        $data = Cache::remember('student-analytics', 600, function () {
            $students = fn () => User::where('type', 'Student');
            $today = Carbon::today();

            // new students per day (last 30 days), filled with zeros for empty days
            $perDay = $students()->where('created_at', '>=', $today->copy()->subDays(29))
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
            $daily = collect(range(29, 0))->map(function ($i) use ($today, $perDay) {
                $d = $today->copy()->subDays($i);
                return ['label' => $d->format('d M'), 'count' => (int) ($perDay[$d->toDateString()] ?? 0)];
            });

            // new students per month (last 12 months)
            $perMonth = $students()->where('created_at', '>=', $today->copy()->startOfMonth()->subMonths(11))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as c")->groupBy('m')->pluck('c', 'm');
            $monthly = collect(range(11, 0))->map(function ($i) use ($today, $perMonth) {
                $d = $today->copy()->startOfMonth()->subMonths($i);
                return ['label' => $d->format('M y'), 'count' => (int) ($perMonth[$d->format('Y-m')] ?? 0)];
            });

            $total = $students()->count();
            $count = fn (array $over) => StudentFilter::query(array_merge(['type' => 'Student'] + StudentFilter::filtersFrom(new \Illuminate\Http\Request()), $over))->count('users.id');

            $activeNow = $count(['activity' => 'active_30']);
            $notMonth = $count(['activity' => 'inactive_30']);
            $dormant = $count(['activity' => 'inactive_90']);
            $never = $count(['activity' => 'never']);

            return [
                'total' => $total,
                'new_today' => $students()->where('created_at', '>=', $today)->count(),
                'new_7' => $students()->where('created_at', '>=', $today->copy()->subDays(6))->count(),
                'new_30' => $students()->where('created_at', '>=', $today->copy()->subDays(29))->count(),
                'daily' => $daily,
                'monthly' => $monthly,
                'signup' => [
                    'Google' => $count(['signup' => 'google']),
                    'Apple' => $count(['signup' => 'apple']),
                    'Facebook' => $count(['signup' => 'facebook']),
                    'Mobile / email' => $count(['signup' => 'other']),
                ],
                'mobile' => [
                    'Verified' => $count(['mobile' => 'verified']),
                    'Filled, not verified' => $count(['mobile' => 'unverified']),
                    'No mobile number' => $count(['mobile' => 'without']),
                ],
                'activity' => [
                    ['Active (last 30 days)', $activeNow, 'success', 'active_30'],
                    ['Inactive (30-90 days)', max(0, $notMonth - $dormant), 'warning', 'inactive_30'],
                    ['Dormant (90+ days, has history)', max(0, $dormant - $never), 'danger', 'inactive_90'],
                    ['Never active', $never, 'secondary', 'never'],
                ],
                'interests' => DB::table('competitive_exams as e')
                    ->leftJoin('student_exam_interests as i', 'i.exam_id', '=', 'e.id')
                    ->selectRaw('e.name, e.short_name, e.color, e.slug, COUNT(i.id) as c')
                    ->groupBy('e.id', 'e.name', 'e.short_name', 'e.color', 'e.slug', 'e.position')
                    ->orderBy('e.position')->get(),
                'with_interest' => DB::table('student_exam_interests')->distinct()->count('user_id'),
                'states' => $this->topValues('state'),
                'cities' => $this->topValues('city'),
                'gender' => $students()->whereNotNull('gender')->where('gender', '!=', '')
                    ->selectRaw('gender, COUNT(*) as c')->groupBy('gender')->orderByDesc('c')->pluck('c', 'gender'),
                'top_students' => $this->topStudents(),
                'generated_at' => now(),
            ];
        });

        return view('admin.student.analytics', $data);
    }

    /** One student: profile, activity per area and the latest records. */
    public function profile($id)
    {
        $user = User::findOrFail($id);

        $areas = [];
        foreach (StudentFilter::ACTIVITY_SOURCES as $key => [$table, $col, $time, $extra]) {
            $q = DB::table($table)->where($col, $user->id);
            if ($extra) {
                $q->whereRaw($extra);
            }
            $areas[$key] = [
                'label' => StudentFilter::MODULE_LABELS[$key],
                'count' => (clone $q)->count(),
                'last' => (clone $q)->max($time),
            ];
        }

        $last = collect($areas)->pluck('last')->filter()->max();

        // exam interests + performance in each interested exam
        $allExams = CompetitiveExam::orderBy('position')->get();
        $interestIds = $user->examInterests()->pluck('competitive_exams.id')->all();
        $performance = [];
        foreach ($allExams->whereIn('id', $interestIds) as $exam) {
            if (!StudentPerformance::has($exam->performance_source)) {
                $performance[] = ['exam' => $exam, 'data' => null];
                continue;
            }
            $source = $exam->performance_source;
            $cohort = DB::table('student_exam_interests')->where('exam_id', $exam->id)->pluck('user_id')->all();
            $weekly = StudentPerformance::weekly($source, $user->id, 8);

            $performance[] = ['exam' => $exam, 'data' => [
                'label' => StudentPerformance::SOURCES[$source]['label'],
                'all' => StudentPerformance::aggregate($source, [$user->id])->get($user->id),
                'month' => StudentPerformance::aggregate($source, [$user->id], StudentPerformance::since('30'))->get($user->id),
                'standing' => StudentPerformance::standing($source, $cohort, $user->id),
                'standing_month' => StudentPerformance::standing($source, $cohort, $user->id, StudentPerformance::since('30')),
                'weekly' => $weekly,
                'trend' => StudentPerformance::trend($weekly),
            ]];
        }

        return view('admin.student.profile', [
            'user' => $user,
            'referral' => app(\App\Services\ReferralService::class)->adminDetail($user),
            'progress' => app(\App\Services\StudentProgress::class)->summary($user->id),
            'activity' => app(\App\Services\StudentProgress::class)->activity($user->id),
            'trend' => app(\App\Services\StudentProgress::class)->trend($user->id, 'month'),
            'areas' => $areas,
            'devices' => DB::table('user_platform_activity')->where('user_id', $user->id)->orderByDesc('last_active_at')->get(),
            'allExams' => $allExams,
            'interestIds' => $interestIds,
            'performance' => $performance,
            'lastActivity' => $last,
            'dailyExams' => DB::table('kpsc_daily_exams_attempt')->where('user_id', $user->id)->latest('id')->limit(10)->get(),
            'modelExams' => DB::table('kpsc_model_exam_attempt')->where('user_id', $user->id)->latest('id')->limit(10)->get(),
            'comments' => DB::table('capsule_comment')->where('user_id', $user->id)->latest('id')->limit(10)->get(),
            'requests' => DB::table('request_logs')->where('user_id', $user->id)->where('is_api', 1)->latest('id')->limit(10)->get(),
        ]);
    }

    private function topValues(string $column)
    {
        return User::where('type', 'Student')->whereNotNull($column)->where($column, '!=', '')
            ->selectRaw("$column as v, COUNT(*) as c")->groupBy($column)->orderByDesc('c')->limit(10)->pluck('c', 'v');
    }

    /** Students with the most daily-exam attempts. */
    private function topStudents()
    {
        return DB::table('kpsc_daily_exams_attempt as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->selectRaw('u.id, u.name, u.email, u.mobile, COUNT(*) as attempts, MAX(a.created_at) as last_attempt')
            ->groupBy('u.id', 'u.name', 'u.email', 'u.mobile')
            ->orderByDesc('attempts')->limit(10)->get();
    }
}
