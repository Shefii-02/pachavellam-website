<?php

namespace App\Services;

use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * What every page inside a course needs for its header and side menu:
 * the course's features with their address and how much each already holds.
 */
class CourseWorkspace
{
    /** Where one feature of a course is managed: content (tree), exams, or related items. */
    public static function featureUrl(ExamCourse $course, ExamFeature $feature): string
    {
        // the exams of the internal series course are managed per series / season, not as a course
        if ($feature->content_kind === 'exam' && $course->slug === 'series-' . $course->exam->slug) {
            $kinds = array_keys(\App\Http\Controllers\Exam\ExamSeriesController::KINDS);
            $at = array_search($feature->slug, array_column(\App\Http\Controllers\Exam\ExamSeriesController::KINDS, 0), true);
            if ($at !== false) {
                return $course->exam->examUrl('series/' . $kinds[$at] . '/exams');
            }
        }
        $base = $course->exam->examUrl('courses/' . $course->id);

        return match ($feature->content_kind) {
            'tree' => "$base/content/{$feature->slug}",
            'exam' => "$base/exams/{$feature->slug}",
            'classes' => "$base/classes/{$feature->slug}",
            default => "$base/related/{$feature->slug}",
        };
    }

    public static function data(ExamCourse $course, User $user): array
    {
        $course->loadMissing('exam');

        $features = $course->features()->get();

        $nodes = DB::table('exam_course_nodes')->where('course_id', $course->id)->where('node_type', '!=', 'folder')
            ->select('feature_id', DB::raw('COUNT(*) as c'))->groupBy('feature_id')->pluck('c', 'feature_id');
        $items = DB::table('exam_course_items')->where('course_id', $course->id)
            ->select('feature_id', DB::raw('COUNT(*) as c'))->groupBy('feature_id')->pluck('c', 'feature_id');

        $exams = DB::table('course_exams')->whereNull('deleted_at')->where('course_id', $course->id)
            ->select('feature_id', DB::raw('COUNT(*) as c'))->groupBy('feature_id')->pluck('c', 'feature_id');

        $classes = DB::table('course_classes')->where('course_id', $course->id)
            ->select('feature_id', DB::raw('COUNT(*) as c'))->groupBy('feature_id')->pluck('c', 'feature_id');

        $nav = $features->map(fn ($f) => (object) [
            'feature' => $f,
            'url' => self::featureUrl($course, $f),
            'count' => (int) match ($f->content_kind) {
                'tree' => $nodes[$f->id] ?? 0,
                'exam' => $exams[$f->id] ?? 0,
                'classes' => $classes[$f->id] ?? 0,
                default => $items[$f->id] ?? 0,
            },
            'hasBank' => $f->content_kind === 'flag' && CourseItemSource::config($f->slug, $course->exam->slug) !== null,
        ]);

        return [
            'exam' => $course->exam,
            'layout' => $course->exam->layout(),
            'course' => $course,
            'navFeatures' => $nav,
            'courseUrl' => $course->exam->examUrl('courses/' . $course->id),
            'listUrl' => $course->exam->examUrl('courses'),
            'canEdit' => $course->canBeEditedBy($user),
            'isAdmin' => \App\Support\StaffAccess::canIn($user, 'Exam courses manage', (int) $course->exam_id),
        ];
    }
}
