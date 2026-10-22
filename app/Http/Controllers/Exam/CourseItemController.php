<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Services\CourseItemSource;
use App\Services\CourseWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Exams and related items of a course:
 *   admin/exam/{exam}/courses/{course}/exams/{feature}     exam features (daily exams, OMR exams, ...)
 *   admin/exam/{exam}/courses/{course}/related/{feature}   section features (free / paid classes, bulletin, ...)
 * Items are picked from the existing banks listed in config/course_item_sources.php.
 */
class CourseItemController extends Controller
{
    use ResolvesExam, \App\Http\Controllers\Concerns\SiblingCourses;

    public function show(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, false);
        $cfg = CourseItemSource::config($feature->slug, $course->exam->slug);
        $canEdit = $course->canBeEditedBy($request->user());

        $attached = DB::table('exam_course_items')->where('course_id', $course->id)->where('feature_id', $feature->id)
            ->orderBy('position')->orderBy('id')->get();
        $bank = $cfg ? CourseItemSource::find($cfg, $attached->pluck('source_id')->all()) : collect();

        $rows = $attached->map(fn ($i) => (object) [
            'id' => $i->id,
            'source_id' => $i->source_id,
            'title' => optional($bank->get($i->source_id))->title ?? '(removed from the bank)',
            'subtitle' => optional($bank->get($i->source_id))->subtitle,
            'published' => $bank->has($i->source_id) ? (bool) $bank->get($i->source_id)->published : null,
            'missing' => !$bank->has($i->source_id),
        ]);

        // the exam bank to pick from (only for people who can edit)
        $candidates = null;
        if ($cfg && $canEdit) {
            $candidates = CourseItemSource::candidates(
                $cfg, $attached->pluck('source_id')->all(), $request->get('q'), max(1, (int) $request->get('page', 1)), 15,
                $request->url(), array_filter(['q' => $request->get('q')])
            );
        }

        $scopeIds = json_decode($feature->pivot->category_ids ?? '[]', true) ?: [];

        return view('admin.exam.course-items.manage', CourseWorkspace::data($course, $request->user()) + [
            'feature' => $feature,
            'cfg' => $cfg,
            'rows' => $rows,
            'candidates' => $candidates,
            'scope' => $feature->pivot->scope,
            'scopeNames' => ExamCategory::whereIn('id', $scopeIds)->pluck('name')->all(),
            'manageUrl' => CourseWorkspace::featureUrl($course, $feature),
            'term' => $request->get('q'),
            'otherCourses' => $canEdit ? $this->siblingCourses($request, $course, $feature) : collect(),
        ]);
    }

    public function add(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $cfg = CourseItemSource::config($feature->slug, $course->exam->slug);
        abort_unless($cfg, 404, 'No bank is connected to this feature for this exam type.');

        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer'], ['ids.required' => 'Tick at least one item to add.']);

        // only ids that really exist in the bank, and only once
        $ids = CourseItemSource::existing($cfg, $request->ids);
        $have = DB::table('exam_course_items')->where('course_id', $course->id)->where('feature_id', $feature->id)->pluck('source_id')->all();
        $new = array_values(array_diff($ids, $have));

        $now = now();
        $targets = collect([$course])->merge($this->tickedSiblings($request, $course, $feature));
        foreach ($targets as $target) {
            $have = DB::table('exam_course_items')->where('course_id', $target->id)->where('feature_id', $feature->id)->pluck('source_id')->all();
            $position = (int) DB::table('exam_course_items')->where('course_id', $target->id)->where('feature_id', $feature->id)->max('position');
            foreach (array_values(array_diff($ids, $have)) as $i => $sourceId) {
                DB::table('exam_course_items')->insertOrIgnore([
                    'course_id' => $target->id, 'feature_id' => $feature->id, 'source_id' => $sourceId,
                    'position' => $position + $i + 1, 'created_by' => $request->user()->id, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        return redirect(CourseWorkspace::featureUrl($course, $feature))
            ->with('message', count($new) . ' ' . $cfg['label'] . (count($new) === 1 ? '' : 's') . ' added' . ($targets->count() > 1 ? ' to ' . $targets->count() . ' courses' : '') . (count($ids) > count($new) ? ' (some were already in the course)' : ''));
    }

    public function remove(Request $request, $examSlug, $courseId, $featureSlug, $itemId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);

        DB::table('exam_course_items')->where('id', $itemId)->where('course_id', $course->id)->where('feature_id', $feature->id)->delete();

        return redirect(CourseWorkspace::featureUrl($course, $feature))->with('message', 'Removed from the course');
    }

    // ------------------------------------------------------------- helpers

    /** The course of this exam type and one of its exam / section features. */
    private function resolve(Request $request, $examSlug, $courseId, $featureSlug, bool $needsEdit): array
    {
        $exam = $this->exam($examSlug);
        $course = ExamCourse::forExam($exam->id)->findOrFail($courseId);
        $course->setRelation('exam', $exam);

        if ($needsEdit) {
            abort_unless($course->canBeEditedBy($request->user()), 403, 'You can view this course but not edit it.');
        } else {
            abort_unless($course->canBeViewedBy($request->user()), 403, 'You do not have access to this course.');
        }

        $feature = $course->features()->where('exam_features.slug', $featureSlug)->first() ?? abort(404, 'This course does not include that feature.');
        abort_if($feature->content_kind !== 'flag', 404);   // folders and files, and exams, have their own screens

        return [$course, $feature];
    }
}
