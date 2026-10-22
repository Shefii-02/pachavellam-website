<?php

namespace App\Http\Controllers;

use App\Models\CompetitiveExam;
use App\Models\ContentItem;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use App\Models\PaymentLink;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use Illuminate\Http\Request;

/**
 * Pachavellam Prime: the landing page of every published course (mock tests, live and recorded classes, materials,
 * study plan, Roaring study), filtered by exam type and category. Buying needs a signed in student.
 */
class PrimeController extends Controller
{
    /** What every Prime course can include (shown on the home page band and the Prime page). */
    public const FEATURES = [
        ['bi-ui-checks-grid', 'Mock tests & daily exams', 'Real exam pattern tests with rank, analysis and answer keys'],
        ['bi-broadcast', 'Live classes', 'Interactive live classes with top faculty, doubt clearing on the spot'],
        ['bi-play-btn', 'Recorded classes', 'Missed a class? Watch the recording any time, as many times as you need'],
        ['bi-journal-richtext', 'Study materials', 'Notes, PDFs and previous year papers for every topic'],
        ['bi-calendar2-check', 'Study plan', 'A day-by-day plan till your exam, so you always know what to study next'],
        ['bi-fire', 'Roaring study method', 'Our focused revision method: short daily targets that make every topic stick'],
    ];

    public function index(Request $request)
    {
        $exams = CompetitiveExam::where('status', 1)->orderBy('position')->get();
        $exam = $request->filled('exam') ? $exams->firstWhere('slug', $request->exam) : null;
        $category = $exam && $request->filled('category') ? ExamCategory::where('exam_id', $exam->id)->where('status', 1)->find($request->category) : null;

        $courses = ExamCourse::with('exam:id,slug,short_name,name,color')->where('status', 'published')->where('slug', 'not like', 'series-%')
            ->when($exam, fn ($q) => $q->where('exam_id', $exam->id))
            ->when($category, fn ($q) => $q->inCategory($category->id))
            ->orderByDesc('is_featured')->orderByDesc('id')->limit(60)->get();
        $categories = $exam ? ExamCategory::where('exam_id', $exam->id)->where('status', 1)->whereNull('parent_id')->orderBy('position')->get() : collect();

        $faqs = ContentItem::ofKind('faq')->live()->whereIn('type', ['courses', 'payments', 'terms'])->orderBy('position')->orderBy('id')->get();

        return view('kpsc.prime', [
            'title' => 'Pachavellam Prime', 'exams' => $exams, 'exam' => $exam, 'category' => $category, 'categories' => $categories,
            'courses' => $courses, 'features' => self::FEATURES, 'faqs' => $faqs,
        ]);
    }

    /** Buy now: free courses are joined straight away; paid ones go to the payment page (one open link per student and course). */
    public function buy(Request $request, $id, AdmissionService $admissions, PaymentLinkService $links)
    {
        $course = ExamCourse::where('status', 'published')->findOrFail($id);
        $user = $request->user();
        $back = url('prime' . ($course->exam ? '?exam=' . $course->exam->slug : '')) . '#courses';

        try {
            if ($course->isFree()) {
                $admissions->checkout($user, $course, null, null, 'web');

                return redirect(url('my-courses/' . $course->id))->with('message', 'You have joined this course. Your classes, exams and study material are in the tabs below.');
            }
            $link = PaymentLink::where('user_id', $user->id)->where('course_id', $course->id)->where('channel', 'web')->where('status', 'active')->latest('id')->first();
            if (!$link || !$link->isUsable()) {
                $link = $links->create($user, $course, 'web', null, 48, 'Bought from the website (Prime page)', null);
            }

            return redirect($link->url());
        } catch (PaymentException $e) {
            return redirect($back)->with('prime_error', $e->getMessage());
        }
    }
}
