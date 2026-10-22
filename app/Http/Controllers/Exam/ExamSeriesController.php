<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CourseExam;
use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Models\ExamSeason;
use App\Services\CourseWorkspace;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Exam series of an exam type: Daily, OMR, CA and GK exam series, run in seasons
 * (admin/exam/{exam}/series/{kind} lists the seasons; a season opens its exams in the exam builder).
 * The exams use the same builder as course exams (sections, questions from the question library, instructions, schedule, publish).
 * Behind the scenes each exam type has one internal "series" course that holds them; it never shows in the course list.
 */
class ExamSeriesController extends Controller
{
    use ResolvesExam;

    public const KINDS = [
        'daily' => ['daily-exams', 'Daily Exam Series'], 'omr' => ['omr-exams', 'OMR Exam Series'],
        'ca' => ['premium-daily-ca', 'CA Exam Series'], 'gk' => ['gk-exams', 'GK Exam Series'], 'special' => ['special-exams', 'Special Exam Series'],
    ];

    private function scope(Request $request, $examSlug, $kind): array
    {
        StaffAccess::authorize($request, 'Exam courses manage');
        abort_unless(isset(self::KINDS[$kind]), 404);
        $exam = $this->exam($examSlug);
        $allowed = StaffAccess::examIds($request->user());
        abort_unless($allowed === null || in_array($exam->id, $allowed, true), 403, 'You do not have access to this exam type.');

        $course = self::seriesCourse($exam, $request->user()->id);
        $feature = ExamFeature::where('slug', self::KINDS[$kind][0])->firstOrFail();
        if (!$course->features()->where('exam_features.id', $feature->id)->exists()) {
            $course->features()->attach($feature->id, ['scope' => 'all']);
        }
        $course->setRelation('exam', $exam);

        return [$exam, $course, $feature];
    }

    /** The seasons of a series. */
    public function open(Request $request, $examSlug, $kind)
    {
        [$exam, $course, $feature] = $this->scope($request, $examSlug, $kind);
        $seasons = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->orderByDesc('starts_on')->orderByDesc('id')->get();
        $counts = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->selectRaw('season_id, count(*) c, sum(status = "published") p')->groupBy('season_id')->get()->keyBy('season_id');

        // old KPSC exams placed in a session
        $legacyCounts = ($exam->slug === 'kpsc' && (self::LEGACY[$kind] ?? null) && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams'))
            ? \App\Models\DailyExam::where('section', self::LEGACY[$kind])->whereNotNull('series_season_id')->selectRaw('series_season_id, count(*) c')->groupBy('series_season_id')->pluck('c', 'series_season_id') : collect();

        return view('admin.exam.seasons', [
            'legacyCounts' => $legacyCounts,
            'exam' => $exam, 'layout' => $exam->layout(), 'kind' => $kind, 'title' => self::KINDS[$kind][1], 'seasons' => $seasons, 'counts' => $counts,
            'builder' => CourseWorkspace::featureUrl($course, $feature), 'base' => $exam->examUrl('series/' . $kind),
        ]);
    }

    public function store(Request $request, $examSlug, $kind)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $data = $this->validated($request, $kind);
        $season = ExamSeason::create($data + ['exam_id' => $exam->id, 'kind' => $kind, 'created_by' => $request->user()->id]);
        $season->update(['position' => $season->id]);
        $this->cover($request, $season);
        app(\App\Services\SeasonAccess::class)->syncCourse($season->fresh(), $request->user()->id);

        return redirect($exam->examUrl('series/' . $kind . '/seasons/' . $season->id))->with('success', 'Session created. Now add its exams below.');
    }

    public function update(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $season->update($this->validated($request, $kind));
        $this->cover($request, $season);
        app(\App\Services\SeasonAccess::class)->syncCourse($season->fresh(), $request->user()->id);

        return redirect($request->input('back') === 'session' ? $exam->examUrl('series/' . $kind . '/seasons/' . $season->id) : $exam->examUrl('series/' . $kind))->with('success', 'Session saved.');
    }

    public function destroy(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        CourseExam::where('season_id', $season->id)->update(['season_id' => null]);   // its exams stay, without a season
        \App\Models\DailyExam::where('series_season_id', $season->id)->update(['series_season_id' => null]);   // old KPSC exams too
        if ($season->course_id) {
            ExamCourse::where('id', $season->course_id)->update(['status' => 'archived']);   // nobody can buy it any more; past purchases stay on record
        }
        $season->delete();

        return redirect($exam->examUrl('series/' . $kind))->with('success', 'Season deleted. Its exams are kept under "No season".');
    }

    private const LEGACY = ['daily' => 'Daily Exam', 'omr' => 'Model Exam', 'ca' => 'CA-Daily-Exam'];

    /** One session: its details and its exams (add, edit, publish, free access, delete). */
    public function show(Request $request, $examSlug, $kind, $id)
    {
        [$exam, $course, $feature] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $builder = CourseWorkspace::featureUrl($course, $feature);

        $courseExams = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->where('season_id', $season->id)
            ->withCount('links', 'sections')->orderBy('position')->orderByDesc('id')->get();

        // the old KPSC daily / OMR / CA exams can sit in a session too
        $section = self::LEGACY[$kind] ?? null;
        $legacy = collect();
        $attachable = collect();
        if ($exam->slug === 'kpsc' && $section && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams')) {
            $legacy = \App\Models\DailyExam::where('section', $section)->where('series_season_id', $season->id)->orderByDesc('exam_date')->orderByDesc('id')->get();
            $attachable = \App\Models\DailyExam::where('section', $section)->whereNull('series_season_id')->orderByDesc('exam_date')->orderByDesc('id')->limit(150)->get();
        }
        $legacyBase = ['daily' => 'admin/kpsc/daily-exam', 'omr' => 'admin/kpsc/model-exam', 'ca' => 'admin/kpsc/ca-daily-exam'][$kind] ?? null;

        return view('admin.exam.season-show', [
            'exam' => $exam, 'layout' => $exam->layout(), 'kind' => $kind, 'title' => self::KINDS[$kind][1], 'season' => $season, 'courseExams' => $courseExams,
            'legacy' => $legacy, 'attachable' => $attachable, 'legacyBase' => $legacyBase, 'builder' => $builder, 'base' => $exam->examUrl('series/' . $kind),
            'special' => $kind === 'special', 'access' => app(\App\Services\SeasonAccess::class),
        ]);
    }

    /** Put old KPSC exams into this session. */
    public function attach(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $request->validate(['ids' => 'required|array|min:1|max:200', 'ids.*' => 'integer'], ['ids.required' => 'Tick at least one exam.']);
        $n = \App\Models\DailyExam::where('section', self::LEGACY[$kind] ?? '-')->whereNull('series_season_id')->whereIn('id', $data['ids'])->update(['series_season_id' => $season->id]);

        return back()->with('success', $n . ' exam(s) added to this session.');
    }

    /** Take an old exam out of the session (the exam itself is kept). */
    public function detach(Request $request, $examSlug, $kind, $id, $examId)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        \App\Models\DailyExam::where('series_season_id', $id)->where('id', $examId)->update(['series_season_id' => null]);

        return back()->with('success', 'Exam taken out of the session.');
    }

    /** Free access on / off for one exam of a paid session ($type: course = builder exam, legacy = old KPSC exam). */
    public function free(Request $request, $examSlug, $kind, $id, $type, $examId)
    {
        [$exam, $course] = $this->scope($request, $examSlug, $kind);
        $row = $type === 'legacy'
            ? \App\Models\DailyExam::where('series_season_id', $id)->findOrFail($examId)
            : CourseExam::where('course_id', $course->id)->where('season_id', $id)->findOrFail($examId);
        $row->free_access = !$row->free_access;
        $row->save();

        return back()->with('success', $row->free_access ? 'This exam is now free for everyone.' : 'This exam now follows the session.');
    }

    /** Publish / hide an old KPSC exam from the session page. */
    public function legacyStatus(Request $request, $examSlug, $kind, $id, $examId)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $row = \App\Models\DailyExam::where('series_season_id', $id)->findOrFail($examId);
        $row->status = $row->status ? 0 : 1;
        $row->save();

        return back()->with('success', $row->status ? 'Exam published.' : 'Exam hidden.');
    }

    private function validated(Request $request, string $kind = 'daily'): array
    {
        $paid = $kind === 'special' || $request->boolean('is_paid');           // special exams are always paid
        $d = $request->validate([
            'title' => 'required|string|max:150', 'tagline' => 'nullable|string|max:200', 'description' => 'nullable|string|max:3000',
            'basis' => 'nullable|in:subject,category,exam,other', 'basis_name' => 'nullable|string|max:120',
            'starts_on' => 'nullable|date', 'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'status' => 'required|in:draft,published,archived', 'cover_image' => 'nullable|image|max:3072',
            'price' => [$paid ? 'required' : 'nullable', 'numeric', 'min:' . ($paid ? 1 : 0), 'max:1000000'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'max:1000000', $paid ? 'lt:price' : 'nullable'],
            'validity_type' => 'nullable|in:lifetime,days,date',
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650', 'required_if:validity_type,days'],
            'valid_until' => ['nullable', 'date', 'required_if:validity_type,date'],
            'free_exams' => 'nullable|integer|min:0|max:500',
        ], ['ends_on.after_or_equal' => 'The season end must be on or after its start.', 'price.required' => 'Enter the price of a paid season.', 'price.min' => 'A paid season needs a price of at least 1.',
            'discount_price.lt' => 'The offer price must be lower than the price.', 'validity_days.required_if' => 'Enter the number of days.', 'valid_until.required_if' => 'Choose the last date.']);

        $out = collect($d)->except('cover_image')->all() + ['is_paid' => $paid];
        if (!$paid) {   // a free season keeps no price
            $out = array_merge($out, ['price' => 0, 'discount_price' => null, 'validity_type' => 'lifetime', 'validity_days' => null, 'valid_until' => null, 'free_exams' => 0]);
        } else {
            $out['validity_type'] = $out['validity_type'] ?? 'lifetime';
            $out['free_exams'] = (int) ($out['free_exams'] ?? 0);
        }
        $out['basis'] = ($out['basis'] ?? null) ?: null;
        $out['basis_name'] = $out['basis'] ? ($out['basis_name'] ?? null) : null;

        return $out;
    }

    private function cover(Request $request, ExamSeason $season): void
    {
        if ($request->hasFile('cover_image')) {
            if ($season->cover_image) {
                Storage::disk('public')->delete($season->cover_image);
            }
            $season->update(['cover_image' => $request->file('cover_image')->store('exam-seasons', 'public')]);
        }
    }

    /** The internal course that holds this exam type's series (made on first use). */
    public static function seriesCourse($exam, int $userId): ExamCourse
    {
        $slug = 'series-' . $exam->slug;

        return ExamCourse::where('exam_id', $exam->id)->where('slug', $slug)->first()
            ?? ExamCourse::create([
                'exam_id' => $exam->id, 'title' => ($exam->short_name ?: $exam->name) . ' exam series', 'slug' => $slug,
                'course_type' => 'free', 'status' => 'archived', 'price' => 0, 'created_by' => $userId,
                'short_description' => 'Internal: holds the daily, OMR, CA and GK exam series of this exam type.',
            ]);
    }

    /** id of the internal series course if it exists (for the sidebar highlight). */
    public static function seriesCourseId($exam): ?int
    {
        return ExamCourse::where('exam_id', $exam->id)->where('slug', 'series-' . $exam->slug)->value('id');
    }
}
