<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ExamCourse;
use Illuminate\Http\Request;

/** "Also add to other courses": the courses of the same exam type that include the same feature and that the user may edit. */
trait SiblingCourses
{
    protected function siblingCourses(Request $request, ExamCourse $course, $feature)
    {
        return ExamCourse::forExam($course->exam_id)->where('id', '!=', $course->id)
            ->whereHas('features', fn ($q) => $q->where('exam_features.id', $feature->id))
            ->orderBy('title')->get()
            ->filter(fn ($c) => $request->user() && $c->canBeEditedBy($request->user()))->values();
    }

    /** The ticked courses (course_ids[]) that are really allowed targets. */
    protected function tickedSiblings(Request $request, ExamCourse $course, $feature)
    {
        return $this->siblingCourses($request, $course, $feature)->whereIn('id', array_map('intval', (array) $request->input('course_ids', [])));
    }
}
