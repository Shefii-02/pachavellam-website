<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\ClassFolder;
use App\Models\CourseClass;
use App\Models\ExamCourse;
use App\Models\LibraryClass;
use App\Services\ClassSource;
use App\Services\CourseWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Free / paid classes of a course: admin/exam/{exam}/courses/{course}/classes/{feature}
 * A course holds links to classes of the class library, so one class can be in many courses.
 */
class CourseClassController extends Controller
{
    use ResolvesExam;

    public function index(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, false);

        $links = CourseClass::where('course_id', $course->id)->where('feature_id', $feature->id)
            ->whereHas('libraryClass')->with('libraryClass.folder:id,name')->orderBy('position')->orderBy('id')->get();

        return view('admin.exam.course-classes.index', $this->data($request, $course, $feature) + ['links' => $links]);
    }

    /** The class library, to pick classes into the course. */
    public function pick(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);

        $folderIds = null;
        if ($request->filled('folder')) {
            $folder = ClassFolder::where('exam_id', $course->exam_id)->findOrFail($request->folder);
            $folderIds = array_merge([$folder->id], $folder->descendantIds());
        }
        $inCourse = CourseClass::where('course_id', $course->id)->where('feature_id', $feature->id)->pluck('class_id')->all();

        $classes = LibraryClass::where('exam_id', $course->exam_id)->published()
            ->when($folderIds, fn ($q) => $q->whereIn('folder_id', $folderIds))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%' . $request->q . '%')->orWhere('instructor', 'like', '%' . $request->q . '%')))
            ->when($request->input('hide_used', '1') === '1', fn ($q) => $q->whereNotIn('id', $inCourse))
            ->with('folder:id,name')->orderByDesc('id')->paginate(per_page(30))->withQueryString();

        return view('admin.exam.course-classes.pick', $this->data($request, $course, $feature) + [
            'classes' => $classes, 'inCourse' => $inCourse, 'tree' => ClassFolder::flatTree($course->exam_id),
        ]);
    }

    public function add(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer', 'course_ids' => 'nullable|array', 'course_ids.*' => 'integer'], ['ids.required' => 'Tick at least one class.']);

        $ids = LibraryClass::where('exam_id', $course->exam_id)->published()->whereIn('id', $request->ids)->pluck('id')->all();
        $ids = array_values(array_filter($request->ids, fn ($i) => in_array((int) $i, $ids, true)));   // keep the ticked order

        [$added, $courses] = $this->attachTo($request, $course, $feature, $ids);

        return redirect(CourseWorkspace::featureUrl($course, $feature))->with('success', $this->message($added, $courses));
    }

    /** Write a new class right here: it is saved in the class library (folder "Course Classes") and added to the course(s). */
    public function quick(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $data = $request->validate([
            'title' => 'required|string|max:250', 'url' => ['required', 'string', 'max:2000', 'regex:#^https?://[^\s]+$#i'],
            'duration' => 'nullable|string|max:12', 'instructor' => 'nullable|string|max:150', 'class_date' => 'nullable|date',
            'course_ids' => 'nullable|array', 'course_ids.*' => 'integer',
        ], ['url.regex' => 'The class link must start with http:// or https://.']);

        $duration = null;
        if (trim((string) ($data['duration'] ?? '')) !== '') {
            $duration = ClassSource::parseDuration($data['duration']);
            abort_if($duration === null, 422, 'Write the length as 1:25:30, 45:00 or the minutes.');
        }

        $detected = ClassSource::detect($data['url']);
        $root = ClassFolder::firstOrCreate(['exam_id' => $course->exam_id, 'parent_id' => null, 'name' => 'Course Classes'], ['position' => 0]);
        $folder = ClassFolder::firstOrCreate(['exam_id' => $course->exam_id, 'parent_id' => $root->id, 'name' => mb_substr($course->title, 0, 150)], ['position' => 0]);

        $class = LibraryClass::create([
            'exam_id' => $course->exam_id, 'folder_id' => $folder->id, 'title' => trim($data['title']), 'source' => $detected['source'], 'url' => $detected['url'],
            'duration_seconds' => $duration, 'instructor' => $data['instructor'] ?? null, 'class_date' => $data['class_date'] ?? null,
            'status' => 'published', 'created_by' => $request->user()->id,
        ]);

        [$added, $courses] = $this->attachTo($request, $course, $feature, [$class->id]);

        return redirect(CourseWorkspace::featureUrl($course, $feature))->with('success', 'Class created in the class library. ' . $this->message($added, $courses));
    }

    public function update(Request $request, $examSlug, $courseId, $featureSlug, $linkId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $data = $request->validate(['unlock_at' => 'nullable|date', 'is_preview' => 'nullable|boolean']);

        CourseClass::where('course_id', $course->id)->where('feature_id', $feature->id)->findOrFail($linkId)
            ->update(['unlock_at' => $data['unlock_at'] ?? null, 'is_preview' => (bool) ($data['is_preview'] ?? false)]);

        return back()->with('success', 'Saved.');
    }

    public function remove(Request $request, $examSlug, $courseId, $featureSlug, $linkId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        CourseClass::where('course_id', $course->id)->where('feature_id', $feature->id)->where('id', $linkId)->delete();

        return back()->with('success', 'Class removed from the course (it is still in the class library).');
    }

    public function move(Request $request, $examSlug, $courseId, $featureSlug, $linkId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $dir = $request->validate(['dir' => 'required|in:up,down'])['dir'];

        $ids = CourseClass::where('course_id', $course->id)->where('feature_id', $feature->id)->orderBy('position')->orderBy('id')->pluck('id')->all();
        $i = array_search((int) $linkId, $ids, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $pos => $id) {
                CourseClass::where('id', $id)->update(['position' => $pos + 1]);
            }
        }

        return back();
    }

    // ---------------------------------------------------------------- helpers

    /** Adds the classes to this course and to the other courses ticked on the form. @return array{0:int,1:int} links added, courses */
    private function attachTo(Request $request, ExamCourse $course, $feature, array $classIds): array
    {
        $targets = collect([$course]);
        foreach ($this->otherCourses($request, $course, $feature, true)->whereIn('id', $request->input('course_ids', [])) as $other) {
            $targets->push($other);
        }

        $added = 0;
        DB::transaction(function () use ($targets, $feature, $classIds, $request, &$added) {
            foreach ($targets as $target) {
                $position = (int) CourseClass::where('course_id', $target->id)->where('feature_id', $feature->id)->max('position');
                $have = CourseClass::where('course_id', $target->id)->where('feature_id', $feature->id)->pluck('class_id')->all();
                foreach ($classIds as $classId) {
                    if (in_array((int) $classId, $have, true)) {
                        continue;
                    }
                    CourseClass::create(['course_id' => $target->id, 'feature_id' => $feature->id, 'class_id' => $classId, 'position' => ++$position, 'created_by' => $request->user()->id]);
                    $added++;
                }
            }
        });

        return [$added, $targets->count()];
    }

    private function message(int $added, int $courses): string
    {
        return $added . ' class link' . ($added === 1 ? '' : 's') . ' added' . ($courses > 1 ? " to {$courses} courses." : '.');
    }

    /** Other courses of this exam type that include the same feature and that the user may edit. */
    private function otherCourses(Request $request, ExamCourse $course, $feature, bool $forWrite = false)
    {
        return ExamCourse::forExam($course->exam_id)->where('id', '!=', $course->id)
            ->whereHas('features', fn ($q) => $q->where('exam_features.id', $feature->id))
            ->orderBy('title')->get()
            ->filter(fn ($c) => $c->canBeEditedBy($request->user()))->values();
    }

    private function data(Request $request, ExamCourse $course, $feature): array
    {
        return CourseWorkspace::data($course, $request->user()) + [
            'feature' => $feature,
            'manageUrl' => CourseWorkspace::featureUrl($course, $feature),
            'libraryUrl' => $course->exam->examUrl('class-library'),
            'otherCourses' => $request->user() ? $this->otherCourses($request, $course, $feature) : collect(),
        ];
    }

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
        abort_unless($feature->content_kind === 'classes', 404);

        return [$course, $feature];
    }
}
