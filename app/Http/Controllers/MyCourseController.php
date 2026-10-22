<?php

namespace App\Http\Controllers;

use App\Models\CourseAdmission;
use App\Models\CourseClass;
use App\Models\CourseExam;
use App\Models\ExamCourse;
use App\Models\ExamCourseNode;
use App\Services\CourseItemSource;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A bought course, tab by tab: one tab for every feature the course includes (exams, classes, study material, articles ...).
 * Exams and classes are split into "Upcoming & live" and "Completed". /my-courses/{id}
 */
class MyCourseController extends Controller
{
    public function show(Request $request, $id)
    {
        $data = $this->data($request->user(), $id);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        return view('kpsc.my-course', $data);
    }

    /** The tabs of a bought course (also read by the app API). Returns a redirect when the student has not bought it. */
    public function data($user, $id)
    {
        $course = ExamCourse::with('exam:id,slug,short_name,name,color', 'features')->findOrFail($id);

        $admission = CourseAdmission::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'active')->orderByDesc('admitted_at')->get()->first(fn ($a) => $a->grantsAccess());
        $preview = !$admission && $user->type === 'Admin';          // an administrator may look at any course
        if (!$admission && !$preview) {
            return redirect(url('courses/' . ($course->slug ?: $course->id)))->with('message', 'Buy this course to open it.');
        }
        abort_if(str_starts_with((string) $course->slug, 'series-'), 404);   // the hidden series / session courses have no page

        $now = now();
        $tabs = [];
        foreach ($course->features as $f) {
            if (!$f->status) {
                continue;
            }
            $tab = $this->tab($course, $f, $now);
            if ($tab) {
                $tabs[] = $tab;
            }
        }

        // the numbers on the first tab
        $summary = [
            'live' => collect($tabs)->sum(fn ($t) => collect($t['live'] ?? [])->count()),
            'upcoming' => collect($tabs)->sum(fn ($t) => collect($t['upcoming'] ?? [])->count()),
            'done' => collect($tabs)->sum(fn ($t) => collect($t['done'] ?? [])->count()),
        ];

        return [
            'course' => $course, 'admission' => $admission, 'preview' => $preview, 'tabs' => $tabs, 'summary' => $summary, 'title' => $course->title,
            'daysLeft' => $admission && $admission->valid_until ? max(0, (int) ceil($now->floatDiffInDays($admission->valid_until, false))) : null,
            'app' => config('app_links.android'),
        ];
    }

    private function icon(string $slug): string
    {
        return ['daily-exams' => 'bi-calendar-check', 'omr-exams' => 'bi-ui-checks-grid', 'gk-exams' => 'bi-lightbulb', 'special-exams' => 'bi-stars', 'premium-daily-ca' => 'bi-newspaper', 'materials' => 'bi-file-earmark-arrow-down',
            'articles' => 'bi-journal-text', 'previous-questions' => 'bi-folder2-open', 'free-classes' => 'bi-play-btn', 'paid-classes' => 'bi-collection-play', 'psc-bulletin' => 'bi-book'][$slug] ?? 'bi-check2-circle';
    }

    private function tab(ExamCourse $course, $f, Carbon $now): ?array
    {
        $base = ['key' => $f->slug, 'name' => $f->name, 'icon' => $this->icon($f->slug), 'kind' => $f->content_kind, 'description' => $f->description];

        return match ($f->content_kind) {
            'exam' => $this->examTab($course, $f, $now, $base),
            'classes' => $this->classTab($course, $f, $now, $base),
            'tree' => $this->treeTab($course, $f, $base),
            default => $this->flagTab($course, $f, $base),
        };
    }

    /** Which section a scheduled thing belongs to: live now, upcoming, or completed. */
    private function bucket($start, $end, Carbon $now): string
    {
        $start = $start ? Carbon::parse($start) : null;
        $end = $end ? Carbon::parse($end) : null;
        if ($end && $end->lt($now)) {
            return 'done';
        }
        if ($start && $start->gt($now)) {
            return 'upcoming';
        }

        return $start || $end ? 'live' : 'live';          // no dates: open any time
    }

    private function examTab(ExamCourse $course, $f, Carbon $now, array $base): array
    {
        $rows = [];
        // exams built inside the course
        foreach (CourseExam::where('course_id', $course->id)->where('feature_id', $f->id)->where('status', 'published')->orderBy('position')->orderBy('start_at')->get() as $e) {
            $unlock = $e->unlock_at && $e->unlock_at->gt($now);
            $b = $unlock ? 'upcoming' : $this->bucket($e->start_at, $e->end_at, $now);
            $rows[] = (object) ['bucket' => $b, 'title' => $e->title, 'when' => $e->start_at ? $e->start_at->format('d M Y, h:i a') : ($e->entry_date ? Carbon::parse($e->entry_date)->format('d M Y') : ''),
                'sub' => collect([$e->duration_minutes ? $e->duration_minutes . ' min' : null, $e->total_mark ? $e->total_mark . ' marks' : null])->filter()->implode(' · '),
                'url' => null, 'app' => true, 'opens' => $unlock ? 'Opens ' . $e->unlock_at->format('d M, h:i a') : null, 'sort' => ($e->start_at ? $e->start_at->timestamp : 0)];
        }
        // old KPSC exams attached from the daily / OMR bank
        $cfg = CourseItemSource::config($f->slug, $course->exam->slug ?? '');
        if ($cfg && $cfg['table'] === 'kpsc_daily_exams') {
            $ids = DB::table('exam_course_items')->where('course_id', $course->id)->where('feature_id', $f->id)->pluck('source_id')->all();
            foreach (\App\Models\DailyExam::whereIn('id', $ids ?: [0])->where('status', 1)->get() as $d) {
                $rows[] = (object) ['bucket' => $this->bucket($d->started_at, $d->ended_at, $now), 'title' => $d->exam_title ?: ($d->examtitle ?: 'Exam'), 'when' => $d->exam_date ? Carbon::parse($d->exam_date)->format('d M Y') : '', 'sub' => '',
                    'url' => url(($d->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam') . '/' . $d->exam_date . '/' . $d->id), 'app' => false, 'opens' => null, 'sort' => $d->started_at ? Carbon::parse($d->started_at)->timestamp : 0];
            }
        }
        $rows = collect($rows);

        return $base + [
            'live' => $rows->where('bucket', 'live')->sortBy('sort')->values()->all(),
            'upcoming' => $rows->where('bucket', 'upcoming')->sortBy('sort')->values()->all(),
            'done' => $rows->where('bucket', 'done')->sortByDesc('sort')->values()->all(),
            'count' => $rows->count(),
        ];
    }

    private function classTab(ExamCourse $course, $f, Carbon $now, array $base): array
    {
        $rows = [];
        $links = CourseClass::with('libraryClass')->where('course_id', $course->id)->where('feature_id', $f->id)->orderBy('position')->get();
        foreach ($links as $l) {
            $c = $l->libraryClass;
            if (!$c || $c->status !== 'published') {
                continue;
            }
            $locked = $l->unlock_at && $l->unlock_at->gt($now);
            $date = $c->class_date ? Carbon::parse($c->class_date) : null;
            $bucket = $locked || ($date && $date->gt($now->copy()->endOfDay())) ? 'upcoming' : ($date && $date->isToday() ? 'live' : 'done');
            $thumb = $c->thumbnail ? (preg_match('#^https?://#', $c->thumbnail) ? $c->thumbnail : Storage::url($c->thumbnail)) : null;
            $rows[] = (object) ['bucket' => $bucket, 'title' => $c->title, 'when' => $locked ? 'Opens ' . $l->unlock_at->format('d M Y, h:i a') : ($date ? $date->format('d M Y') : ''), 'sub' => collect([$c->instructor, $c->durationLabel() ?: null])->filter()->implode(' · '),
                'thumb' => $thumb, 'embed' => $bucket === 'upcoming' ? null : $c->embedUrl(), 'url' => $bucket === 'upcoming' ? null : $c->playUrl(), 'locked' => $locked, 'notes' => $c->notes_url, 'sort' => $date ? $date->timestamp : 0];
        }
        $rows = collect($rows);

        return $base + [
            'live' => $rows->where('bucket', 'live')->sortBy('sort')->values()->all(),
            'upcoming' => $rows->where('bucket', 'upcoming')->sortBy('sort')->values()->all(),
            'done' => $rows->where('bucket', 'done')->sortByDesc('sort')->values()->all(),
            'count' => $rows->count(),
        ];
    }

    private function node(ExamCourseNode $n, int $depth = 0): object
    {
        $url = $n->external_url ?: ($n->file_path ? Storage::url($n->file_path) : null);

        return (object) [
            'id' => $n->id, 'type' => $n->node_type, 'title' => $n->title, 'kind' => $n->file_kind, 'url' => $url, 'size' => $n->file_size ? round($n->file_size / 1048576, 1) . ' MB' : null,
            'audio' => $n->audio_path ? Storage::url($n->audio_path) : null, 'body' => $n->node_type === 'article' ? SafeHtml::clean((string) $n->body) : null, 'free' => (bool) $n->is_free_preview,
            'children' => $depth < 6 ? $n->children->where('status', true)->map(fn ($c) => $this->node($c, $depth + 1))->values()->all() : [],
        ];
    }

    private function treeTab(ExamCourse $course, $f, array $base): array
    {
        $roots = ExamCourseNode::with('children.children.children')->where('course_id', $course->id)->where('feature_id', $f->id)->whereNull('parent_id')->where('status', true)->orderBy('position')->orderBy('id')->get();
        $nodes = $roots->map(fn ($n) => $this->node($n))->values()->all();
        $count = ExamCourseNode::where('course_id', $course->id)->where('feature_id', $f->id)->where('status', true)->where('node_type', '!=', 'folder')->count();

        return $base + ['nodes' => $nodes, 'count' => $count];
    }

    private function flagTab(ExamCourse $course, $f, array $base): array
    {
        $items = [];
        $cfg = CourseItemSource::config($f->slug, $course->exam->slug ?? '');
        if ($cfg) {
            $ids = DB::table('exam_course_items')->where('course_id', $course->id)->where('feature_id', $f->id)->pluck('source_id')->all();
            foreach (CourseItemSource::find($cfg, array_map('intval', $ids))->all() as $r) {
                if ($r->published) {
                    $items[] = (object) ['title' => $r->title, 'sub' => $r->subtitle];
                }
            }
        }
        $link = ['psc-bulletin' => url('kpsc/psc-bulletin')][$f->slug] ?? null;

        return $base + ['items' => $items, 'link' => $link, 'count' => count($items)];
    }
}
