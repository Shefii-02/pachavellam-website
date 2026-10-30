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
        $seasons = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->orderBy('position')->orderByDesc('id')->get();
        $counts = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->selectRaw('season_id, count(*) c, sum(status = "published") p')->groupBy('season_id')->get()->keyBy('season_id');

        // old KPSC exams placed in a season
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
        $pos = $data['position'] ?? 1;   // no order typed: the new season shows first
        unset($data['position']);
        $season = ExamSeason::create($data + ['exam_id' => $exam->id, 'kind' => $kind, 'created_by' => $request->user()->id, 'position' => 9999]);
        $this->placeAt($season, $pos);
        $this->cover($request, $season);
        app(\App\Services\SeasonAccess::class)->syncCourse($season->fresh(), $request->user()->id);

        return redirect($exam->examUrl('series/' . $kind . '/seasons/' . $season->id))->with('success', 'Season created. Now add its exams below.');
    }

    /** Put a season at a place (1 = first) among the seasons of its exam and series; the others move down and everything is renumbered 1..n. */
    private function placeAt(ExamSeason $season, int $pos): void
    {
        $others = ExamSeason::where('exam_id', $season->exam_id)->where('kind', $season->kind)->where('id', '!=', $season->id)->orderBy('position')->orderByDesc('id')->get()->all();
        array_splice($others, max(0, min($pos, count($others) + 1) - 1), 0, [$season]);
        foreach ($others as $n => $s) {
            if ((int) $s->position !== $n + 1) {
                $s->position = $n + 1;
                $s->save();
            }
        }
    }

    /** Show a season one place earlier (up) or later (down) in the listing: the positions of the series are renumbered 1..n. */
    public function move(Request $request, $examSlug, $kind, $id, $dir)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $list = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->orderBy('position')->orderByDesc('id')->get()->all();
        $i = collect($list)->search(fn ($s) => $s->id == $id);
        abort_if($i === false, 404);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($list[$j])) {
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
        }
        foreach (array_values($list) as $n => $s) {
            if ((int) $s->position !== $n + 1) {
                $s->position = $n + 1;
                $s->save();
            }
        }

        return back()->with('success', 'Order updated.');
    }

    public function update(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $this->validated($request, $kind);
        $pos = $data['position'] ?? null;
        unset($data['position']);
        $season->update($data);
        if ($pos) {
            $this->placeAt($season, (int) $pos);
        }
        $this->cover($request, $season);
        app(\App\Services\SeasonAccess::class)->syncCourse($season->fresh(), $request->user()->id);

        return redirect($request->input('back') === 'session' ? $exam->examUrl('series/' . $kind . '/seasons/' . $season->id) : $exam->examUrl('series/' . $kind))->with('success', 'Season saved.');
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

    /** One season: its details and its exams (add, edit, publish, free access, delete). */
    public function show(Request $request, $examSlug, $kind, $id)
    {
        [$exam, $course, $feature] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $builder = CourseWorkspace::featureUrl($course, $feature);

        $courseExams = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->where('season_id', $season->id)
            ->withCount('links', 'sections')->orderBy('position')->orderByDesc('id')->get();

        // the old KPSC daily / OMR / CA exams can sit in a season too
        $section = self::LEGACY[$kind] ?? null;
        $legacy = collect();
        $attachable = collect();
        if ($exam->slug === 'kpsc' && $section && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams')) {
            $legacy = \App\Models\DailyExam::with('model_exam_details_one')->where('section', $section)->where('series_season_id', $season->id)->orderByDesc('exam_date')->orderByDesc('id')->get();
            $attachable = \App\Models\DailyExam::where('section', $section)->whereNull('series_season_id')->orderByDesc('exam_date')->orderByDesc('id')->limit(150)->get();
        }
        $otherCourse = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->whereNull('season_id')->orderByDesc('id')->limit(150)->get();
        $otherSeasons = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->where('id', '!=', $season->id)->orderByDesc('starts_on')->orderByDesc('id')->get();
        $legacyBase = ['daily' => 'admin/kpsc/daily-exam', 'omr' => 'admin/kpsc/model-exam', 'ca' => 'admin/kpsc/ca-daily-exam'][$kind] ?? null;

        // how many attempts each exam has (the leaderboard of an exam is built from them)
        $DB = \Illuminate\Support\Facades\DB::class;
        $legacyIds = $legacy->pluck('id');
        $legacyAttempts = collect();
        if ($legacyIds->count()) {
            $q = $kind === 'omr' ? $DB::table('kpsc_model_exam_attempt') : ($kind === 'ca' ? $DB::table('kpsc_ca_daily_exam_attempts') : $DB::table('kpsc_daily_exams_attempt')->where('status', '2'));
            $legacyAttempts = $q->whereIn('exam_id', $legacyIds)->selectRaw('exam_id, count(*) c, count(distinct user_id) u')->groupBy('exam_id')->get()->keyBy('exam_id');
        }
        $courseAttempts = $courseExams->count() ? \App\Models\CourseExamAttempt::whereIn('course_exam_id', $courseExams->pluck('id'))->where('status', 'submitted')->selectRaw('course_exam_id, count(*) c')->groupBy('course_exam_id')->pluck('c', 'course_exam_id') : collect();

        // one list for both kinds of exam, in the order the admin dragged them; an exam not placed yet (a new one) comes first; with nothing placed the old order stays
        $items = [];
        foreach ($courseExams as $e) {
            $items[] = ['type' => 'course', 'model' => $e, 'pos' => $e->season_position, 'i' => count($items)];
        }
        foreach ($legacy as $d) {
            $items[] = ['type' => 'legacy', 'model' => $d, 'pos' => $d->season_position, 'i' => count($items)];
        }
        usort($items, fn ($a, $b) => [$a['pos'] === null ? 0 : 1, (int) $a['pos'], $a['i']] <=> [$b['pos'] === null ? 0 : 1, (int) $b['pos'], $b['i']]);

        return view('admin.exam.season-show', [
            'items' => $items, 'legacyAttempts' => $legacyAttempts, 'courseAttempts' => $courseAttempts,
            'exam' => $exam, 'layout' => $exam->layout(), 'kind' => $kind, 'title' => self::KINDS[$kind][1], 'season' => $season, 'courseExams' => $courseExams,
            'legacy' => $legacy, 'attachable' => $attachable, 'otherCourse' => $otherCourse, 'otherSeasons' => $otherSeasons, 'legacyBase' => $legacyBase, 'builder' => $builder, 'base' => $exam->examUrl('series/' . $kind),
            'special' => $kind === 'special', 'access' => app(\App\Services\SeasonAccess::class),
        ]);
    }

    /** Put old KPSC exams into this season. */
    public function attach(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $request->validate(['ids' => 'required|array|min:1|max:200', 'ids.*' => 'integer'], ['ids.required' => 'Tick at least one exam.']);
        $n = \App\Models\DailyExam::where('section', self::LEGACY[$kind] ?? '-')->whereNull('series_season_id')->whereIn('id', $data['ids'])->update(['series_season_id' => $season->id]);

        return back()->with('success', $n . ' exam(s) added to this season.');
    }

    /**
     * Move or copy exams into this season: the exams of the "Other exams" list (no season) or of another season.
     * Move = the exam now belongs to this season. Copy = a new draft / hidden copy (settings, sections, questions) in this season; the original stays.
     */
    public function place(Request $request, $examSlug, $kind, $id)
    {
        [$exam, $course, $feature] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $request->validate(['mode' => 'required|in:move,copy', 'course_ids' => 'nullable|array|max:200', 'course_ids.*' => 'integer', 'legacy_ids' => 'nullable|array|max:200', 'legacy_ids.*' => 'integer'],
            ['mode.required' => 'Choose move or copy.']);
        $copy = $data['mode'] === 'copy';
        abort_if(empty($data['course_ids']) && empty($data['legacy_ids']), 422, 'Tick at least one exam.');
        $n = 0;

        $mine = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->whereIn('id', $data['course_ids'] ?? [])->get();
        foreach ($mine as $e) {
            if ($copy) {
                $c = \App\Services\CourseExamBuilder::copyTo($e, $course, $request->user()->id);
                $c->update(['season_id' => $season->id]);
                $n++;
            } elseif ($e->season_id !== $season->id) {
                $e->update(['season_id' => $season->id]);
                $n++;
            }
        }

        $section = self::LEGACY[$kind] ?? null;
        if ($exam->slug === 'kpsc' && $section && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams') && !empty($data['legacy_ids'])) {
            foreach (\App\Models\DailyExam::where('section', $section)->whereIn('id', $data['legacy_ids'])->get() as $d) {
                if ($copy) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($d, $season) {
                        $new = $d->replicate();
                        $new->series_season_id = $season->id;
                        $new->status = 0;   // hidden until checked
                        $new->save();
                        foreach ($d->exam_details as $row) {
                            $r = $row->replicate();
                            $r->exam_id = $new->id;
                            $r->save();
                        }
                        foreach ($d->model_exam_details as $row) {
                            $r = $row->replicate();
                            $r->exam_id = $new->id;
                            $r->save();
                        }
                    });
                    $n++;
                } elseif ($d->series_season_id !== $season->id) {
                    $d->series_season_id = $season->id;   // not update([...]): the model has no $fillable
                    $d->save();
                    $n++;
                }
            }
        }

        return back()->with('success', $n ? ($copy ? $n . ' exam(s) copied into this season (drafts / hidden; the originals stay).' : $n . ' exam(s) moved into this season.') : 'Nothing changed.');
    }

    /** Save the order the admin dragged the exams into (both kinds of exam in one list): positions 1..n. */
    public function orderExams(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $request->validate(['items' => 'required|array|max:500', 'items.*.type' => 'required|in:course,legacy', 'items.*.id' => 'required|integer']);
        $course = CourseExam::where('season_id', $season->id)->pluck('id')->flip();
        $legacy = \App\Models\DailyExam::where('series_season_id', $season->id)->pluck('id')->flip();
        foreach ($data['items'] as $i => $it) {   // only exams that really belong to this season
            if ($it['type'] === 'course' && $course->has($it['id'])) {
                CourseExam::whereKey($it['id'])->update(['season_position' => $i + 1]);
            } elseif ($it['type'] === 'legacy' && $legacy->has($it['id'])) {
                \App\Models\DailyExam::whereKey($it['id'])->update(['season_position' => $i + 1]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /** Back to the default order (newest first): every manual place of this season is cleared. */
    public function resetOrder(Request $request, $examSlug, $kind, $id)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        CourseExam::where('season_id', $season->id)->update(['season_position' => null]);
        \App\Models\DailyExam::where('series_season_id', $season->id)->update(['season_position' => null]);

        return back()->with('success', 'The exams are back in the default order (newest first).');
    }

    /** The leaderboard of an old exam of this season, as the admin drawer shows it (top 200: rank, student, marks, time). */
    public function leaderboard(Request $request, $examSlug, $kind, $id, $examId)
    {
        $this->scope($request, $examSlug, $kind);
        if ($request->query('type') === 'course') {   // a builder exam (special / GK / ...): the first attempt of each student, best mark first
            $ce = CourseExam::where('season_id', $id)->findOrFail($examId);
            $q = \App\Models\CourseExamAttempt::where('course_exam_attempts.course_exam_id', $ce->id)->where('course_exam_attempts.status', 'submitted')->where('course_exam_attempts.attempt_no', 1);
            $total = (clone $q)->count();
            $rows = $q->leftJoin('users as u', 'u.id', '=', 'course_exam_attempts.user_id')->orderByDesc('course_exam_attempts.score')->orderBy('course_exam_attempts.time_used')->limit(200)
                ->get(['u.name', 'u.mobile', 'course_exam_attempts.score', 'course_exam_attempts.correct', 'course_exam_attempts.wrong', 'course_exam_attempts.time_used', 'course_exam_attempts.submitted_at'])
                ->values()->map(fn ($r, $i) => [
                    'rank' => $i + 1, 'name' => $r->name ?: 'Student', 'mobile' => $r->mobile, 'mark' => round((float) $r->score, 2), 'ok' => $r->correct, 'bad' => $r->wrong,
                    'secs' => (int) $r->time_used, 'at' => $r->submitted_at ? \Illuminate\Support\Carbon::parse($r->submitted_at)->format('d M, h:i a') : '',
                ]);

            return response()->json(['title' => $ce->title, 'total' => $total, 'rows' => $rows, 'cutoff' => null]);
        }
        $d = \App\Models\DailyExam::where('series_season_id', $id)->findOrFail($examId);
        $DB = \Illuminate\Support\Facades\DB::class;
        $neg = \App\Services\McqExam::NEGATIVE;
        if ($d->section === 'Model Exam') {
            $q = $DB::table('kpsc_model_exam_attempt as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')->where('a.exam_id', $d->id)
                ->selectRaw('coalesce(a.full_name, u.name) as name, coalesce(a.mobile_no, u.mobile) as mobile, a.total_mark as mark, a.right_answer as ok, a.wrong_answer as bad, null as secs, a.answer_uploaded as at')->orderByDesc('a.total_mark')->orderBy('a.created_at');
        } elseif ($d->section === 'CA-Daily-Exam') {
            $q = $DB::table('kpsc_ca_daily_exam_attempts as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')->where('a.exam_id', $d->id)
                ->selectRaw('u.name as name, u.mobile as mobile, (a.`right` - a.wrong * ' . $neg . ') as mark, a.`right` as ok, a.wrong as bad, null as secs, a.attend_ended_at as at')->orderByDesc('mark')->orderBy('a.attend_ended_at');
        } else {   // MCQ: the first attempt of each student is the one the leaderboard counts
            $q = $DB::table('kpsc_daily_exams_attempt as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')->where('a.exam_id', $d->id)->where('a.status', '2')->where('a.attempt_no', 1)
                ->selectRaw('u.name as name, u.mobile as mobile, a.total_mark as mark, a.`right` as ok, a.wrong as bad, a.attempt_time as secs, a.attend_ended_at as at')->orderByDesc('a.total_mark')->orderBy('a.attempt_time');
        }
        $total = (clone $q)->reorder()->count();   // no ordering for the count: the CA branch orders by a computed column
        $rows = $q->limit(200)->get()->values()->map(fn ($r, $i) => [
            'rank' => $i + 1, 'name' => $r->name ?: 'Student', 'mobile' => $r->mobile, 'mark' => round((float) $r->mark, 2), 'ok' => $r->ok, 'bad' => $r->bad,
            'secs' => $r->secs !== null ? (int) $r->secs : null, 'at' => $r->at ? \Illuminate\Support\Carbon::parse($r->at)->format('d M, h:i a') : '',
        ]);

        return response()->json(['title' => $d->examtitle ?: ($d->exam_title ?: 'Exam'), 'total' => $total, 'rows' => $rows, 'cutoff' => $d->cutoff_mark !== null ? (float) $d->cutoff_mark : null]);
    }

    /** Clear the leaderboard of an old exam of this season: every attempt of the exam is removed (the exam and its questions stay). */
    public function clearLeaderboard(Request $request, $examSlug, $kind, $id, $examId)
    {
        $this->scope($request, $examSlug, $kind);
        if ($request->query('type') === 'course') {
            $ce = CourseExam::where('season_id', $id)->findOrFail($examId);
            $n = \App\Models\CourseExamAttempt::where('course_exam_id', $ce->id)->delete();

            return back()->with('success', 'Leaderboard cleared: ' . $n . ' attempt(s) removed from "' . $ce->title . '".');
        }
        $d = \App\Models\DailyExam::where('series_season_id', $id)->findOrFail($examId);
        $DB = \Illuminate\Support\Facades\DB::class;
        if ($d->section === 'Model Exam') {
            $n = $DB::table('kpsc_model_exam_attempt')->where('exam_id', $d->id)->delete();
            $DB::table('omr_exam_times')->where('exam_id', $d->id)->delete();
        } elseif ($d->section === 'CA-Daily-Exam') {
            $n = $DB::table('kpsc_ca_daily_exam_attempts')->where('exam_id', $d->id)->delete();
        } else {
            $DB::table('kpsc_daily_exam_answers')->where('exam_id', $d->id)->delete();
            $n = $DB::table('kpsc_daily_exams_attempt')->where('exam_id', $d->id)->delete();
        }

        return back()->with('success', 'Leaderboard cleared: ' . $n . ' attempt(s) removed from "' . ($d->examtitle ?: 'the exam') . '".');
    }

    /** Take an old exam out of the season (the exam itself is kept). */
    public function detach(Request $request, $examSlug, $kind, $id, $examId)
    {
        [$exam] = $this->scope($request, $examSlug, $kind);
        \App\Models\DailyExam::where('series_season_id', $id)->where('id', $examId)->update(['series_season_id' => null]);

        return back()->with('success', 'Exam taken out of the season.');
    }

    /** Free access on / off for one exam of a paid season ($type: course = builder exam, legacy = old KPSC exam). */
    public function free(Request $request, $examSlug, $kind, $id, $type, $examId)
    {
        [$exam, $course] = $this->scope($request, $examSlug, $kind);
        $row = $type === 'legacy'
            ? \App\Models\DailyExam::where('series_season_id', $id)->findOrFail($examId)
            : CourseExam::where('course_id', $course->id)->where('season_id', $id)->findOrFail($examId);
        $row->free_access = !$row->free_access;
        $row->save();

        return back()->with('success', $row->free_access ? 'This exam is now free for everyone.' : 'This exam now follows the season.');
    }

    /** One action for many ticked exams of the season: publish, unpublish, free, paid, remove (take out of the season). */
    public function bulk(Request $request, $examSlug, $kind, $id)
    {
        [$exam, $course, $feature] = $this->scope($request, $examSlug, $kind);
        $season = ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($id);
        $data = $request->validate([
            'action' => 'required|in:publish,unpublish,free,paid,remove',
            'course_ids' => 'nullable|array|max:200', 'course_ids.*' => 'integer',
            'legacy_ids' => 'nullable|array|max:200', 'legacy_ids.*' => 'integer',
        ]);
        abort_if(empty($data['course_ids']) && empty($data['legacy_ids']), 422, 'Tick at least one exam.');

        $courseQ = CourseExam::where('course_id', $course->id)->where('season_id', $season->id)->whereIn('id', $data['course_ids'] ?? []);
        $legacyQ = \App\Models\DailyExam::where('series_season_id', $season->id)->whereIn('id', $data['legacy_ids'] ?? []);
        $map = [
            'publish'   => [['status' => 'published'], ['status' => 1], 'published'],
            'unpublish' => [['status' => 'draft'], ['status' => 0], 'hidden'],
            'free'      => [['free_access' => 1], ['free_access' => 1], 'made free'],
            'paid'      => [['free_access' => 0], ['free_access' => 0], 'set to follow the season'],
            'remove'    => [['season_id' => null, 'season_position' => null], ['series_season_id' => null, 'season_position' => null], 'taken out of the season'],
        ];
        [$courseSet, $legacySet, $word] = $map[$data['action']];
        $n = 0;
        if (!empty($data['course_ids'])) {
            $n += $courseQ->update($courseSet);
        }
        if (!empty($data['legacy_ids'])) {
            $n += $legacyQ->update($legacySet);
        }

        return back()->with('success', $n . ' exam(s) ' . $word . '.');
    }

    /** Publish / hide an old KPSC exam from the season page. */
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
            'status' => 'required|in:draft,published,archived', 'cover_image' => 'nullable|image|max:3072', 'position' => 'nullable|integer|min:1|max:9999',
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
