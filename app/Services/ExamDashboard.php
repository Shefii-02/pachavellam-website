<?php

namespace App\Services;

use App\Models\CompetitiveExam;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard numbers for one exam type (RRB, SSC, KTET, UGC NET, any new exam type; KPSC adds its own series on top).
 * Every block is read on its own, so one problem never breaks the page.
 */
class ExamDashboard
{
    private array $problems = [];

    public function __construct(private CompetitiveExam $exam)
    {
    }

    public function data(): array
    {
        return [
            'exam' => $this->exam,
            'learning' => $this->safe('learning', fn () => $this->learning()),
            'students' => $this->safe('students', fn () => $this->students()),
            'library' => $this->safe('library', fn () => $this->library()),
            'classes' => $this->safe('classes', fn () => $this->classes()),
            'revenue' => $this->safe('revenue', fn () => self::revenue($this->exam->id)),
            'trend' => $this->safe('trend', fn () => $this->trend(14)),
            'recent' => $this->safe('recent', fn () => $this->recent()),
            'attention' => $this->safe('attention', fn () => $this->attention()),
            'problems' => $this->problems,
            'asOf' => now(),
        ];
    }

    private function safe(string $name, callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            $this->problems[] = $name;
            report($e);

            return null;
        }
    }

    private function learning(): array
    {
        $id = $this->exam->id;
        $courses = DB::table('exam_courses')->whereNull('deleted_at')->where('exam_id', $id);

        return [
            'categories' => DB::table('exam_categories')->where('exam_id', $id)->count(),
            'courses' => (clone $courses)->count(),
            'published_courses' => (clone $courses)->where('status', 'published')->count(),
            'paid_courses' => (clone $courses)->where('course_type', 'paid')->count(),
            'course_exams' => DB::table('course_exams')->whereNull('deleted_at')->where('exam_id', $id)->select('status', DB::raw('count(*) c'))->groupBy('status')->pluck('c', 'status')->all(),
            'roaring' => DB::table('roaring_studies as r')->whereNull('r.deleted_at')->join('roaring_study_exams as e', 'e.roaring_id', '=', 'r.id')->where('e.exam_id', $id)
                ->select('r.status', DB::raw('count(*) c'))->groupBy('r.status')->pluck('c', 'status')->all(),
        ];
    }

    private function students(): array
    {
        $q = DB::table('student_exam_interests')->where('exam_id', $this->exam->id);

        return [
            'interested' => (clone $q)->count(),
            'week' => (clone $q)->where('created_at', '>=', now()->subDays(7))->count(),
            'month' => (clone $q)->where('created_at', '>=', now()->subDays(30))->count(),
            'admitted' => DB::table('course_admissions')->where('exam_id', $this->exam->id)->where('status', 'active')->whereNull('deleted_at')->distinct()->count('user_id'),
        ];
    }

    private function library(): array
    {
        $q = DB::table('library_questions')->whereNull('deleted_at')->where('exam_id', $this->exam->id);

        return [
            'questions' => (clone $q)->count(),
            'folders' => DB::table('question_folders')->where('exam_id', $this->exam->id)->count(),
            'by_type' => (clone $q)->select('question_type', DB::raw('count(*) c'))->groupBy('question_type')->pluck('c', 'question_type')->all(),
            'used' => DB::table('course_exam_questions as x')->join('course_exams as e', 'e.id', '=', 'x.course_exam_id')->where('e.exam_id', $this->exam->id)->distinct()->count('x.question_id'),
            'imports' => DB::table('library_import_batches')->where('exam_id', $this->exam->id)->where('status', 'done')->count(),
        ];
    }

    private function classes(): array
    {
        $q = DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $this->exam->id);

        return [
            'total' => (clone $q)->count(),
            'by_status' => (clone $q)->select('status', DB::raw('count(*) c'))->groupBy('status')->pluck('c', 'status')->all(),
            'in_courses' => DB::table('course_classes as cc')->join('library_classes as l', 'l.id', '=', 'cc.class_id')->where('l.exam_id', $this->exam->id)->distinct()->count('cc.class_id'),
            'hours' => (int) round((clone $q)->sum('duration_seconds') / 3600),
        ];
    }

    /**
     * Money for an exam type: this month, last month, the last 12 months, and the split by channel.
     * Counted per admission in the month it was admitted (same rule as Admissions & Revenue).
     */
    public static function revenue(int $examId): array
    {
        $base = fn () => DB::table('course_admissions')->where('exam_id', $examId)->whereNull('deleted_at')->whereIn('status', ['active', 'cancelled'])->whereNotNull('admitted_at');
        $sums = 'count(*) as admissions, coalesce(sum(amount_paid),0) as gross, coalesce(sum(refunded_amount),0) as refunds, coalesce(sum(commission_amount),0) as commission';
        $row = fn ($r) => [
            'admissions' => (int) $r->admissions, 'gross' => (float) $r->gross, 'refunds' => (float) $r->refunds, 'commission' => (float) $r->commission,
            'payout' => round($r->gross - $r->refunds - $r->commission, 2),
        ];

        $thisMonth = $row($base()->where('admitted_at', '>=', now()->startOfMonth())->selectRaw($sums)->first());
        $lastMonth = $row($base()->whereBetween('admitted_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->selectRaw($sums)->first());
        $all = $row($base()->selectRaw($sums)->first());

        $from = now()->startOfMonth()->subMonths(11);
        $byMonth = $base()->where('admitted_at', '>=', $from)->selectRaw("DATE_FORMAT(admitted_at, '%Y-%m') as ym, $sums")->groupBy('ym')->get()->keyBy('ym');
        $months = [];
        for ($m = $from->copy(); $m->lte(now()); $m->addMonth()) {
            $r = $byMonth->get($m->format('Y-m'));
            $months[] = ['label' => $m->format('M y'), 'gross' => (float) ($r->gross ?? 0), 'payout' => $r ? round($r->gross - $r->refunds - $r->commission, 2) : 0.0, 'admissions' => (int) ($r->admissions ?? 0)];
        }

        $channels = $base()->selectRaw("source as k, $sums")->groupBy('k')->orderByDesc('gross')->get()
            ->map(fn ($r) => ['label' => \App\Services\Payments\AdmissionService::CHANNELS[$r->k] ?? $r->k] + $row($r))->all();

        return ['this' => $thisMonth, 'last' => $lastMonth, 'all' => $all, 'months' => $months, 'channels' => $channels, 'commission_percent' => (float) config('payments.commission_percent')];
    }

    private function trend(int $days): array
    {
        $from = today()->subDays($days - 1);
        $daily = fn (string $table, string $where = '') => DB::table($table)->where('created_at', '>=', $from)->when($where !== '', fn ($q) => $q->whereRaw($where))
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd')->all();
        $interested = DB::table('student_exam_interests')->where('exam_id', $this->exam->id)->where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd')->all();
        $admissions = DB::table('course_admissions')->where('exam_id', $this->exam->id)->whereNotNull('admitted_at')->where('admitted_at', '>=', $from)->whereNull('deleted_at')
            ->select(DB::raw('DATE(admitted_at) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd')->all();

        $out = ['labels' => [], 'interested' => [], 'admissions' => []];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $out['labels'][] = date('d M', strtotime($d));
            $out['interested'][] = (int) ($interested[$d] ?? 0);
            $out['admissions'][] = (int) ($admissions[$d] ?? 0);
        }

        return $out;
    }

    private function recent(): array
    {
        $id = $this->exam->id;

        return [
            'admissions' => DB::table('course_admissions as a')->join('users as u', 'u.id', '=', 'a.user_id')->join('exam_courses as c', 'c.id', '=', 'a.course_id')
                ->where('a.exam_id', $id)->whereNull('a.deleted_at')->whereNotNull('a.admitted_at')->orderByDesc('a.admitted_at')->limit(5)
                ->get(['a.id', 'u.name', 'c.title as course', 'a.amount_paid', 'a.source', 'a.status', 'a.admitted_at'])->all(),
            'course_exams' => DB::table('course_exams as e')->whereNull('e.deleted_at')->join('exam_courses as c', 'c.id', '=', 'e.course_id')->whereNull('c.deleted_at')->where('e.exam_id', $id)->orderByDesc('e.id')->limit(4)
                ->get(['e.id', 'e.title', 'e.status', 'c.title as course'])->all(),
            'roaring' => DB::table('roaring_studies as r')->whereNull('r.deleted_at')->join('roaring_study_exams as x', 'x.roaring_id', '=', 'r.id')->where('x.exam_id', $id)->orderByDesc('r.publish_at')->limit(4)
                ->get(['r.id', 'r.title', 'r.status', 'r.publish_at', 'r.access_type'])->all(),
        ];
    }

    private function attention(): array
    {
        $id = $this->exam->id;
        $items = [];
        $add = function (string $text, int $count, string $url, string $level = 'info') use (&$items) {
            if ($count > 0) {
                $items[] = ['text' => $text, 'count' => $count, 'url' => $url, 'level' => $level];
            }
        };

        $add('draft course(s)', DB::table('exam_courses')->whereNull('deleted_at')->where('exam_id', $id)->where('status', 'draft')->count(), $this->exam->examUrl('courses') . '?status=draft');
        $add('course exam(s) in draft', DB::table('course_exams')->whereNull('deleted_at')->where('exam_id', $id)->where('status', 'draft')->count(), $this->exam->examUrl('courses'));
        $add('draft class(es)', DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $id)->where('status', 'draft')->count(), $this->exam->examUrl('class-library') . '?status=draft');
        $add('class(es) without a length', DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $id)->where('status', 'published')->whereNull('duration_seconds')->count(), $this->exam->examUrl('class-library'), 'warning');
        $add('published course exam(s) already closed', DB::table('course_exams')->whereNull('deleted_at')->where('exam_id', $id)->where('status', 'published')->whereNotNull('end_at')->where('end_at', '<', now())->count(), $this->exam->examUrl('courses'), 'warning');
        $add('payment(s) started but never completed (last 7 days)', DB::table('payment_transactions')->where('exam_id', $id)->where('type', 'payment')->whereIn('status', ['created', 'pending'])
            ->where('created_at', '<', now()->subHours(2))->where('created_at', '>=', now()->subDays(7))->count(), url('admin/transactions?exam=' . $this->exam->slug . '&status=pending'), 'warning');
        $add('open payment link(s) waiting', DB::table('payment_links')->where('exam_id', $id)->where('status', 'active')->where('expires_at', '>=', now())->count(), url('admin/payment-links?exam=' . $this->exam->slug . '&status=active'));

        return $items;
    }
}
