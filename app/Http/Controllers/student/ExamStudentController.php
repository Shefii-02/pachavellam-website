<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\StudentFilter;
use App\Services\StudentPerformance;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Students interested in one exam, with their performance and rank.
 * Used inside each exam module's sidebar (admin/<exam>/interested-students) and by
 * admin/students/exam/<slug> for exams that have no module of their own yet.
 */
class ExamStudentController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request, $exam)
    {
        $exam = CompetitiveExam::where('slug', $exam)->firstOrFail();
        [$rows, $f, $hasPerformance] = $this->rows($request, $exam);

        $page = max(1, (int) $request->get('page', 1));
        $list = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(), self::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        StudentFilter::attachLastActivity($list->items());

        // headline numbers for the cohort
        $performers = $rows->filter(fn ($r) => $r->stats !== null);
        $summary = [
            'interested' => $rows->count(),
            'with_attempts' => $performers->count(),
            'avg_accuracy' => $performers->count() ? round($performers->avg(fn ($r) => $r->stats->accuracy), 1) : null,
            'attempts' => $performers->sum(fn ($r) => $r->stats->attempts),
        ];

        return view('admin.exam-students.index', [
            'layout' => $exam->layout(),
            'exam' => $exam,
            'list' => $list,
            'f' => $f,
            'summary' => $summary,
            'hasPerformance' => $hasPerformance,
            'baseUrl' => $this->baseUrl($exam),
        ]);
    }

    public function export(Request $request, $exam)
    {
        $exam = CompetitiveExam::where('slug', $exam)->firstOrFail();
        [$rows, , $hasPerformance] = $this->rows($request, $exam);

        $filename = $exam->slug . '-interested-students-' . now()->format('Y-m-d-His') . '.csv';
        $safe = fn ($v) => is_string($v) && $v !== '' && strpbrk($v[0], '=+-@') ? "'" . $v : $v;

        return response()->streamDownload(function () use ($rows, $hasPerformance, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Rank', 'ID', 'Name', 'Email', 'Mobile', 'Joined', 'Attempts', 'Accuracy %', 'Total marks', 'Avg marks', 'Best marks', 'Last attempt']);

            foreach ($rows as $r) {
                $s = $r->stats;
                fputcsv($out, [
                    $s->rank ?? '', $r->id, $safe($r->name), $safe($r->email), $safe($r->mobile),
                    optional($r->created_at)->format('Y-m-d'),
                    $s->attempts ?? '', $s->accuracy ?? '', $s->marks ?? '', $s->avg_marks ?? '', $s->best ?? '', $s->last_attempt ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Builds [rows, filters, hasPerformance]; rows are sorted and carry ->stats (or null). */
    private function rows(Request $request, CompetitiveExam $exam): array
    {
        $f = [
            'q' => trim((string) $request->get('q')),
            'period' => array_key_exists((string) $request->get('period'), StudentPerformance::PERIODS) ? (string) $request->get('period') : 'all',
            'sort' => array_key_exists((string) $request->get('sort'), StudentPerformance::METRICS) ? (string) $request->get('sort') : 'marks',
            'mobile' => (string) $request->get('mobile'),
            'show' => (string) $request->get('show'),   // all | ranked | no_attempts
        ];

        $query = DB::table('student_exam_interests as i')
            ->join('users as u', 'u.id', '=', 'i.user_id')
            ->where('i.exam_id', $exam->id)
            ->select('u.id', 'u.name', 'u.email', 'u.mobile', 'u.mobile_verified_at', 'u.created_at', 'i.created_at as interested_at', 'i.source');

        if ($f['q'] !== '') {
            $like = '%' . $f['q'] . '%';
            $query->where(fn ($w) => $w->where('u.name', 'like', $like)->orWhere('u.email', 'like', $like)->orWhere('u.mobile', 'like', $like));
        }
        if ($f['mobile'] === 'with') {
            $query->whereNotNull('u.mobile')->where('u.mobile', '!=', '');
        } elseif ($f['mobile'] === 'without') {
            $query->where(fn ($w) => $w->whereNull('u.mobile')->orWhere('u.mobile', ''));
        }

        $users = $query->get();
        $hasPerformance = StudentPerformance::has($exam->performance_source);

        $stats = collect();
        if ($hasPerformance && $users->isNotEmpty()) {
            $stats = StudentPerformance::aggregate($exam->performance_source, $users->pluck('id')->all(), StudentPerformance::since($f['period']));
            StudentPerformance::rank($stats, $f['sort']);
        }

        $users->each(function ($u) use ($stats) {
            $u->stats = $stats[$u->id] ?? null;
            $u->created_at = $u->created_at ? \Carbon\Carbon::parse($u->created_at) : null;
        });

        if ($f['show'] === 'ranked') {
            $users = $users->filter(fn ($u) => $u->stats);
        } elseif ($f['show'] === 'no_attempts') {
            $users = $users->filter(fn ($u) => !$u->stats);
        }

        // ranked students first (best rank first), everyone else by newest interest
        $users = $users->sortBy(fn ($u) => [$u->stats ? 0 : 1, $u->stats->rank ?? 0, -strtotime($u->interested_at)])->values();

        return [$users, $f, $hasPerformance];
    }

    private function baseUrl(CompetitiveExam $exam): string
    {
        return match ($exam->slug) {
            'kpsc' => url('admin/kpsc/interested-students'),
            'rrb', 'ssc', 'ktet' => url('admin/' . $exam->slug . '/interested-students'),
            'ugc-net' => url('admin/ugc-net/interested-students'),
            default => url('admin/students/exam/' . $exam->slug),
        };
    }
}
