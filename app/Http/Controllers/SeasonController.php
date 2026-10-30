<?php

namespace App\Http\Controllers;

use App\Models\CourseExam;
use App\Models\DailyExam;
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
            ->filter(fn ($s) => (self::PAGES[$s->kind] ?? 'daily-exams') === $page);
            // ->first(fn ($s) => $s->slug === $seasonSlug || \Illuminate\Support\Str::slug($s->title) === $seasonSlug);
// dd($season,$request->all(),$examSlug,$page,$seasonSlug);
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

        // the exams of the season, oldest first (that is also the order the "first N free" is counted in)
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
        foreach (DailyExam::where('series_season_id', $season->id)->where('status', 1)->orderBy('exam_date')->orderBy('id')->get() as $d) {
            [$t, $c] = $tag($d->started_at, $d->ended_at);
            $state = $access->state($user, $season, 'legacy', $d->id);
            $base = $d->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam';
            $rows[] = (object) ['title' => $d->exam_title ?: ($d->examtitle ?: 'Exam'), 'date' => $d->exam_date ? Carbon::parse($d->exam_date)->format('d M Y') : '', 'tag' => $t, 'tagc' => $c, 'state' => $state, 'done' => false, 'paid' => false,
                'omr' => $d->section === 'Model Exam', 'omrId' => $d->id,
                'answerKey' => $d->section === 'Model Exam' && $state !== 'locked' ? url('kpsc/model-exam/' . $d->exam_date . '/' . $d->id . '/answerkey') : null,
                'url' => $state === 'locked' ? null : url($base . '/' . $d->exam_date . '/' . $d->id), 'sort' => $d->exam_date . sprintf('%08d', $d->id)];
        }
        foreach (CourseExam::with('course:id,slug')->where('season_id', $season->id)->where('status', 'published')->orderByRaw('start_at is null')->orderBy('start_at')->orderBy('id')->get() as $e) {
            [$t, $c] = $tag($e->start_at, $e->end_at);
            $state = $access->state($user, $season, 'course', $e->id);
            $rows[] = (object) ['title' => $e->title, 'date' => $e->start_at ? $e->start_at->format('d M Y, h:i a') : '', 'tag' => $t, 'tagc' => $c, 'state' => $state, 'done' => false, 'paid' => false,
                'url' => $state === 'locked' ? null : url('courses/' . ($e->course->slug ?? $e->course_id)), 'sort' => ($e->start_at ? $e->start_at->format('Y-m-d') : '9999') . sprintf('%08d', $e->id)];
        }
        usort($rows, fn ($a, $b) => strcmp($a->sort, $b->sort));

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
