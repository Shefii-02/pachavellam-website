<?php

namespace App\Services;

use App\Models\CompetitiveExam;
use Illuminate\Support\Facades\DB;

/**
 * Numbers for the KPSC dashboard. Every block is read on its own, so one missing table or a bad row never breaks the page.
 */
class KpscDashboard
{
    private int $examId;
    private array $problems = [];

    public function __construct()
    {
        $this->examId = (int) CompetitiveExam::where('slug', 'kpsc')->value('id');
    }

    public function data(): array
    {
        $now = now();

        return [
            'students' => $this->safe('students', fn () => $this->students()),
            'learning' => $this->safe('learning', fn () => $this->learning()),
            'library' => $this->safe('library', fn () => $this->library()),
            'classes' => $this->safe('classes', fn () => $this->classes()),
            'exams' => $this->safe('exams', fn () => $this->examSeries()),
            'content' => $this->safe('content', fn () => $this->content()),
            'revenue' => $this->safe('revenue', fn () => ExamDashboard::revenue($this->examId)),
            'trend' => $this->safe('trend', fn () => $this->trend(14)),
            'topExams' => $this->safe('topExams', fn () => $this->topExams()),
            'recent' => $this->safe('recent', fn () => $this->recent()),
            'attention' => $this->safe('attention', fn () => $this->attention()),
            'problems' => $this->problems,
            'asOf' => $now,
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

    // ------------------------------------------------------------- blocks

    private function students(): array
    {
        $base = DB::table('users')->where('type', 'Student');

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'week' => (clone $base)->where('created_at', '>=', now()->subDays(7))->count(),
            'month' => (clone $base)->where('created_at', '>=', now()->subDays(30))->count(),
            'with_mobile' => (clone $base)->whereNotNull('mobile')->where('mobile', '!=', '')->count(),
            'interested' => DB::table('student_exam_interests')->where('exam_id', $this->examId)->count(),
        ];
    }

    private function learning(): array
    {
        $courses = DB::table('exam_courses')->where('exam_id', $this->examId);

        return [
            'categories' => DB::table('exam_categories')->where('exam_id', $this->examId)->count(),
            'courses' => (clone $courses)->count(),
            'published_courses' => (clone $courses)->where('status', 'published')->count(),
            'paid_courses' => (clone $courses)->where('course_type', 'paid')->count(),
            'course_exams' => $this->byStatus('course_exams'),
            'roaring' => DB::table('roaring_studies as r')->whereNull('r.deleted_at')->join('roaring_study_exams as e', 'e.roaring_id', '=', 'r.id')
                ->where('e.exam_id', $this->examId)->select('r.status', DB::raw('count(*) as c'))->groupBy('r.status')->pluck('c', 'status')->all(),
        ];
    }

    private function byStatus(string $table): array
    {
        return DB::table($table)->where('exam_id', $this->examId)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->all();
    }

    private function library(): array
    {
        $q = DB::table('library_questions')->whereNull('deleted_at')->where('exam_id', $this->examId);

        return [
            'questions' => (clone $q)->count(),
            'folders' => DB::table('question_folders')->where('exam_id', $this->examId)->count(),
            'by_type' => (clone $q)->select('question_type', DB::raw('count(*) as c'))->groupBy('question_type')->pluck('c', 'question_type')->all(),
            'by_source' => (clone $q)->select('source', DB::raw('count(*) as c'))->groupBy('source')->orderByDesc('c')->pluck('c', 'source')->all(),
            'used' => DB::table('course_exam_questions as x')->join('course_exams as e', 'e.id', '=', 'x.course_exam_id')->where('e.exam_id', $this->examId)->distinct()->count('x.question_id'),
            'uncategorised' => (clone $q)->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('library_question_categories as c')->whereColumn('c.question_id', 'library_questions.id'))->count(),
            'imports' => DB::table('library_import_batches')->where('exam_id', $this->examId)->where('status', 'done')->count(),
        ];
    }

    private function classes(): array
    {
        $q = DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $this->examId);

        return [
            'total' => (clone $q)->count(),
            'by_status' => (clone $q)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->all(),
            'by_source' => (clone $q)->select('source', DB::raw('count(*) as c'))->groupBy('source')->pluck('c', 'source')->all(),
            'in_courses' => DB::table('course_classes as cc')->join('library_classes as l', 'l.id', '=', 'cc.class_id')->where('l.exam_id', $this->examId)->distinct()->count('cc.class_id'),
            'minutes' => (int) round((clone $q)->sum('duration_seconds') / 60),
            'watched_minutes' => (int) round(DB::table('class_watch_logs as w')->join('library_classes as l', 'l.id', '=', 'w.class_id')->where('l.exam_id', $this->examId)->sum('w.watched_seconds') / 60),
        ];
    }

    private function examSeries(): array
    {
        $by = DB::table('kpsc_daily_exams')->select('section', DB::raw('count(*) as c'), DB::raw('sum(status = 1) as live'))->groupBy('section')->get()->keyBy('section');
        $since = now()->subDays(7);

        return [
            'series' => collect(['Daily Exam' => 'Daily exams', 'Model Exam' => 'OMR / model exams', 'CA-Daily-Exam' => 'CA exams'])
                ->map(fn ($label, $key) => ['label' => $label, 'total' => (int) ($by[$key]->c ?? 0), 'live' => (int) ($by[$key]->live ?? 0)])->values()->all(),
            'attempts_total' => DB::table('kpsc_daily_exams_attempt')->count() + DB::table('kpsc_model_exam_attempt')->count() + DB::table('kpsc_ca_daily_exam_attempts')->count(),
            'attempts_week' => DB::table('kpsc_daily_exams_attempt')->where('created_at', '>=', $since)->count()
                + DB::table('kpsc_model_exam_attempt')->where('created_at', '>=', $since)->count()
                + DB::table('kpsc_ca_daily_exam_attempts')->where('created_at', '>=', $since)->count(),
            'attempts_today' => DB::table('kpsc_daily_exams_attempt')->whereDate('created_at', today())->count()
                + DB::table('kpsc_model_exam_attempt')->whereDate('created_at', today())->count()
                + DB::table('kpsc_ca_daily_exam_attempts')->whereDate('created_at', today())->count(),
        ];
    }

    private function content(): array
    {
        $count = fn (string $table, ?string $where = null) => DB::table($table)->when($where, fn ($q) => $q->whereRaw($where))->count();
        $kpscTagged = fn (string $pivot, string $fk) => DB::table($pivot)->where('exam_id', $this->examId)->count();

        return [
            'Study material' => $count('material'),
            'PYQ' => $count('prev_questionpaper'),
            'PSC bulletins' => $count('bulletin'),
            'Daily CA' => $count('current_affairs'),
            'PSC news' => $count('psc_news'),
            'PSC results' => $count('psc_results'),
            'Syllabus' => $count('syllabus'),
            'Subjects' => $count('kpsc_subjects'),
            'Notifications' => $count('notification'),
            'Capsules' => $kpscTagged('capsule_exams', 'capsule_id'),
            'Stories' => $kpscTagged('story_exams', 'story_id'),
        ];
    }

    /** Exam attempts per day, last N days. @return array{labels: string[], daily: int[], omr: int[], ca: int[]} */
    private function trend(int $days): array
    {
        $from = today()->subDays($days - 1);
        $per = fn (string $table) => DB::table($table)->where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd')->all();
        $daily = $per('kpsc_daily_exams_attempt');
        $omr = $per('kpsc_model_exam_attempt');
        $ca = $per('kpsc_ca_daily_exam_attempts');
        $students = DB::table('users')->where('type', 'Student')->where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('count(*) as c'))->groupBy('d')->pluck('c', 'd')->all();

        $out = ['labels' => [], 'daily' => [], 'omr' => [], 'ca' => [], 'students' => []];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $out['labels'][] = date('d M', strtotime($d));
            $out['daily'][] = (int) ($daily[$d] ?? 0);
            $out['omr'][] = (int) ($omr[$d] ?? 0);
            $out['ca'][] = (int) ($ca[$d] ?? 0);
            $out['students'][] = (int) ($students[$d] ?? 0);
        }

        return $out;
    }

    /** The exams people attempted most in the last 30 days. */
    private function topExams(): array
    {
        return DB::table('kpsc_daily_exams_attempt as a')->join('kpsc_daily_exams as e', 'e.id', '=', 'a.exam_id')
            ->where('a.created_at', '>=', now()->subDays(30))
            ->select('e.id', 'e.section', DB::raw('COALESCE(NULLIF(e.exam_title, ""), e.examtitle) as title'), DB::raw('count(*) as attempts'), DB::raw('round(avg(a.total_mark), 1) as avg_mark'))
            ->groupBy('e.id', 'e.section', 'e.exam_title', 'e.examtitle')->orderByDesc('attempts')->limit(6)->get()->all();
    }

    private function recent(): array
    {
        return [
            'imports' => DB::table('library_import_batches')->where('exam_id', $this->examId)->orderByDesc('id')->limit(4)->get(['id', 'file_name', 'imported', 'skipped', 'status', 'created_at'])->all(),
            'course_exams' => DB::table('course_exams as e')->whereNull('e.deleted_at')->join('exam_courses as c', 'c.id', '=', 'e.course_id')->where('e.exam_id', $this->examId)
                ->orderByDesc('e.id')->limit(4)->get(['e.id', 'e.title', 'e.status', 'e.course_id', 'e.feature_id', 'c.title as course', 'e.created_at'])->all(),
            'roaring' => DB::table('roaring_studies as r')->whereNull('r.deleted_at')->join('roaring_study_exams as x', 'x.roaring_id', '=', 'r.id')->where('x.exam_id', $this->examId)
                ->orderByDesc('r.publish_at')->limit(4)->get(['r.id', 'r.title', 'r.status', 'r.publish_at', 'r.access_type'])->all(),
            'students' => DB::table('users')->where('type', 'Student')->orderByDesc('id')->limit(5)->get(['id', 'name', 'mobile', 'created_at'])->all(),
        ];
    }

    /** Things an admin probably wants to fix. @return array<int, array{text: string, count: int, url: string, level: string}> */
    private function attention(): array
    {
        $exam = CompetitiveExam::find($this->examId);
        $items = [];
        $add = function (string $text, int $count, string $url, string $level = 'warning') use (&$items) {
            if ($count > 0) {
                $items[] = ['text' => $text, 'count' => $count, 'url' => $url, 'level' => $level];
            }
        };

        $add('course exam(s) in draft', DB::table('course_exams')->whereNull('deleted_at')->where('exam_id', $this->examId)->where('status', 'draft')->count(), $exam->examUrl('courses'), 'info');
        $add('draft class(es) in the class library', DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $this->examId)->where('status', 'draft')->count(), $exam->examUrl('class-library') . '?status=draft', 'info');
        $add('class(es) without a length', DB::table('library_classes')->whereNull('deleted_at')->where('exam_id', $this->examId)->where('status', 'published')->whereNull('duration_seconds')->count(), $exam->examUrl('class-library'), 'warning');
        $add('draft course(s)', DB::table('exam_courses')->where('exam_id', $this->examId)->where('status', 'draft')->count(), $exam->examUrl('courses') . '?status=draft', 'info');
        $add('published course exam(s) already closed', DB::table('course_exams')->whereNull('deleted_at')->where('exam_id', $this->examId)->where('status', 'published')->whereNotNull('end_at')->where('end_at', '<', now())->count(), $exam->examUrl('courses'), 'warning');
        $add('roaring study pack(s) in draft', DB::table('roaring_studies as r')->whereNull('r.deleted_at')->join('roaring_study_exams as x', 'x.roaring_id', '=', 'r.id')->where('x.exam_id', $this->examId)->where('r.status', 'draft')->count(), url('admin/exam/kpsc/roaring-study?status=draft'), 'info');
        $add('question(s) with no category', DB::table('library_questions')->whereNull('deleted_at')->where('exam_id', $this->examId)->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('library_question_categories as c')->whereColumn('c.question_id', 'library_questions.id'))->where('source', '!=', 'daily')->count(), $exam->examUrl('question-library'), 'warning');

        return $items;
    }
}
