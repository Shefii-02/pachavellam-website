<?php

namespace App\Http\Controllers;

use App\Models\CourseExam;
use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Models\ExamCourse;
use App\Models\ExamSeason;
use App\Models\PaymentLink;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use App\Services\SeasonAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** A Season of an exam series: what it holds, its price, and the Buy button. /seasons/{id} */
class SeasonController extends Controller
{
    public const PAGES = ['daily' => 'daily-exams', 'omr' => 'omr-exams', 'ca' => 'ca-exams', 'gk' => 'gk-exams', 'special' => 'special-exams'];

    public function show(Request $request, $id, SeasonAccess $access)
    {
        $season = ExamSeason::with('exam')->where('status', 'published')->findOrFail($id);
        if ($season->slug && $request->isMethod('GET')) {
            return redirect($season->publicUrl() . ($request->getQueryString() ? '?' . $request->getQueryString() : ''), 301);
        }

        return view('kpsc.season', $this->data($request, $id, $access));
    }

    /** /{exam}/{series page}/{season slug}, e.g. /kpsc/omr-exams/typist-omr-2026 */
    public function showBySlug(Request $request, $examSlug, $seasonSlug, $page, SeasonAccess $access)
    {

        // the seasons of this exam and series page (a handful): match the saved slug, else the slug of the title (seasons saved before slugs existed)
        $season = ExamSeason::with('exam')->where('status', 'published')
            ->whereHas('exam', fn ($q) => $q->where('slug', $examSlug))->get()
            ->filter(fn ($s) => (self::PAGES[$s->kind] ?? 'daily-exams') === $page)
            ->first(fn ($s) => $s->slug === $seasonSlug || \Illuminate\Support\Str::slug($s->title) === $seasonSlug);
dd($season,$request->all(),$examSlug,$page,$seasonSlug);
        abort_if(!$season, 404);
        if ($season->slug && $season->slug !== $seasonSlug) {
            return redirect($season->publicUrl(), 301);
        }

        return view('kpsc.season', $this->data($request, $season->id, $access));
    }

    /** Everything the season page shows (also read by the app API). */
    public function data(Request $request, $id, SeasonAccess $access): array
    {
        $season = ExamSeason::with('exam')->where('status', 'published')->findOrFail($id);
        $user = $request->user();
        $paid = $access->isPaid($season);
        $owns = $access->owns($user, $season);

        // the exams of the season, newest first (the "first N free" is worked out per exam by SeasonAccess, not by this order)
        $rows = [];
        $tag = function ($start, $end) {
            $start = $start ? Carbon::parse($start) : null;
            $end = $end ? Carbon::parse($end) : null;
            if ($start && $start->isFuture()) {
                return ['Upcoming', 'up'];
            }
            if ($end && $end->isPast()) {
                return ['Ended', 'end'];
            }

            return [$start || $end ? 'Live now' : 'Open', 'live'];
        };
        // what this student has attended (guests: nothing)
        $legacyIds = DailyExam::where('series_season_id', $season->id)->pluck('id');
        $attOmr = $user ? \App\Models\ModelExamAttempt::where('user_id', $user->id)->whereIn('exam_id', $legacyIds)->pluck('exam_id')->flip() : collect();
        $attMcq = $user ? DailyExamattempt::where('user_id', $user->id)->where('status', '2')->whereIn('exam_id', $legacyIds)->pluck('exam_id')->flip() : collect();
        $attCourse = $user ? \App\Models\CourseExamAttempt::where('user_id', $user->id)->where('status', 'submitted')->pluck('course_exam_id')->flip() : collect();
        // the tab of a row: ongoing / upcoming, or completed and attended, or completed and not attended
        // start / end of an exam: a label to show and an ISO time (with the zone) for the countdown
        $span = function ($start, $end) {
            $s = $start ? Carbon::parse($start) : null;
            $e = $end ? Carbon::parse($end) : null;

            return ['startAt' => $s?->toIso8601String(), 'endAt' => $e?->toIso8601String(), 'startLbl' => $s?->format('d M Y, h:i a'), 'endLbl' => $e?->format('d M Y, h:i a')];
        };
        // the order of every tab: the exam's date first (newest first), then its start time of day, then the id; an exam with no date goes last
        $sortKey = fn ($date, $start, $id) => ($date ? Carbon::parse($date)->format('Y-m-d') : '0000-00-00') . ' ' . ($start ? Carbon::parse($start)->format('H:i:s') : '00:00:00') . sprintf('%08d', $id);
        $bucket = fn ($tagc, $attended) => $tagc === 'end' ? ($attended ? 'done' : 'missed') : 'live';

        foreach (DailyExam::where('series_season_id', $season->id)->where('status', 1)->orderBy('exam_date')->orderBy('id')->get() as $d) {
            [$t, $c] = $tag($d->started_at, $d->ended_at);
            $isOmr = $d->section === 'Model Exam';
            $attended = $isOmr ? $attOmr->has($d->id) : $attMcq->has($d->id);
            $state = $access->state($user, $season, 'legacy', $d->id);
            $base = $d->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam';
            $rows[] = (object) ($span($d->started_at, $d->ended_at) + ['title' => $d->exam_title ?: ($d->examtitle ?: 'Exam'), 'date' => $d->exam_date ? Carbon::parse($d->exam_date)->format('d M Y') . ($d->started_at ? ', ' . Carbon::parse($d->started_at)->format('h:i a') : '') : ($d->started_at ? Carbon::parse($d->started_at)->format('d M Y, h:i a') : ''), 'tag' => $t, 'tagc' => $c, 'state' => $state, 'done' => $attended, 'paid' => false,
                'attended' => $attended, 'bucket' => $bucket($c, $attended), 'pos' => $d->season_position,
                'omr' => $d->section === 'Model Exam', 'omrId' => $d->id,
                'answerKey' => $d->section === 'Model Exam' && $state !== 'locked' && $c !== 'up' ? url('kpsc/model-exam/' . $d->exam_date . '/' . $d->id . '/answerkey') : null,
                'url' => $state === 'locked' ? null : url($base . '/' . $d->exam_date . '/' . $d->id), 'sort' => $sortKey($d->exam_date ?: $d->started_at, $d->started_at, $d->id)]);
        }
        foreach (CourseExam::with('course:id,slug')->where('season_id', $season->id)->where('status', 'published')->orderByRaw('start_at is null')->orderBy('start_at')->orderBy('id')->get() as $e) {
            [$t, $c] = $tag($e->start_at, $e->end_at);
            $attended = $attCourse->has($e->id);
            $state = $access->state($user, $season, 'course', $e->id);
            $rows[] = (object) ($span($e->start_at, $e->end_at) + ['title' => $e->title, 'date' => $e->start_at ? $e->start_at->format('d M Y, h:i a') : '', 'tag' => $t, 'tagc' => $c, 'state' => $state, 'done' => $attended, 'paid' => false,
                'attended' => $attended, 'bucket' => $bucket($c, $attended), 'pos' => $e->season_position,
                'url' => $state === 'locked' ? null : url('courses/' . ($e->course->slug ?? $e->course_id)), 'sort' => $sortKey($e->start_at, $e->start_at, $e->id)]);
        }
        // the order the admin set by dragging (exams not placed yet first); with none set: newest date and time first
        $manual = collect($rows)->contains(fn ($r) => $r->pos !== null);
        usort($rows, fn ($a, $b) => $manual
            ? ([$a->pos === null ? 0 : 1, (int) $a->pos] <=> [$b->pos === null ? 0 : 1, (int) $b->pos]) ?: strcmp($b->sort, $a->sort)
            : strcmp($b->sort, $a->sort));

        \App\Support\Seo::set(['key' => 'season:' . $season->id, 'title' => $season->title . ' - ' . ($season->exam->short_name ?? '') . ' | Pachavellam', 'description' => $season->tagline ?: strip_tags((string) $season->description) ?: 'Exam season on Pachavellam.',
            'image' => $season->cover_image ? \Illuminate\Support\Facades\Storage::url($season->cover_image) : null]);

        return [
            'season' => $season, 'exam' => $season->exam, 'paid' => $paid, 'owns' => $owns, 'access' => $access, 'rows' => $rows, 'title' => $season->title,
            'freeCount' => collect($rows)->where('state', 'free')->count(), 'back' => url($season->exam->slug . '/' . (self::PAGES[$season->kind] ?? 'daily-exams')),
            'accent' => $season->exam->color ?: '#0a8f5b',
        ];
    }

    /** Buy the season like a course: the payment page opens (one open link per student and season). */
    public function buy(Request $request, $id, SeasonAccess $access, AdmissionService $admissions, PaymentLinkService $links)
    {
        $season = ExamSeason::where('status', 'published')->findOrFail($id);
        $back = url('seasons/' . $season->id);
        if (!$access->isPaid($season)) {
            return redirect($back);
        }
        $access->syncCourse($season);                        // makes sure the purchase course matches the price
        $course = ExamCourse::find($season->fresh()->course_id);
        if (!$course || $course->status !== 'published') {
            return redirect($back)->with('season_msg', 'This season cannot be bought right now.');
        }
        if ($access->owns($request->user(), $season)) {
            return redirect($back)->with('season_ok', 'You already have this season.');
        }
        try {
            $link = PaymentLink::where('user_id', $request->user()->id)->where('course_id', $course->id)->where('channel', 'web')->where('status', 'active')->latest('id')->first();
            if (!$link || !$link->isUsable()) {
                $link = $links->create($request->user(), $course, 'web', null, 48, 'Bought from the website (exam season)', null);
            }

            return redirect($link->url());
        } catch (PaymentException $e) {
            return redirect($back)->with('season_msg', $e->getMessage());
        }
    }
}
