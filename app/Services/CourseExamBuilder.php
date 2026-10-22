<?php

namespace App\Services;

use App\Models\CourseExam;
use App\Models\CourseExamQuestion;
use App\Models\CourseExamSection;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;

/** Small helpers shared by the course exam screens and the question library form. */
class CourseExamBuilder
{
    /** Library folder that collects the questions typed directly into a course exam: "Course Exams > exam title". */
    public static function autoFolder(CourseExam $exam): QuestionFolder
    {
        $root = QuestionFolder::firstOrCreate(
            ['exam_id' => $exam->exam_id, 'parent_id' => null, 'name' => 'Course Exams'],
            ['description' => 'Questions written inside course exams.', 'position' => 0]
        );

        return QuestionFolder::firstOrCreate(
            ['exam_id' => $exam->exam_id, 'parent_id' => $root->id, 'name' => mb_substr($exam->title, 0, 150)],
            ['position' => 0]
        );
    }

    /** A copy of an exam in another course: same settings, sections and questions (the questions themselves are shared), as a draft. */
    public static function copyTo(CourseExam $source, \App\Models\ExamCourse $target, ?int $userId = null): CourseExam
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($source, $target, $userId) {
            $copyFile = function (?string $path) {
                if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return null;
                }
                $new = 'course-exams/instructions/' . \Illuminate\Support\Str::random(40) . '.pdf';
                \Illuminate\Support\Facades\Storage::disk('public')->copy($path, $new);

                return $new;
            };

            $copy = $source->replicate(['status']);
            $copy->course_id = $target->id;
            $copy->status = 'draft';
            $copy->instruction_pdf = $copyFile($source->instruction_pdf);
            $copy->created_by = $userId ?? $source->created_by;
            $copy->position = (int) CourseExam::where('course_id', $target->id)->where('feature_id', $source->feature_id)->max('position') + 1;
            $copy->save();

            foreach ($source->sections as $section) {
                $newSection = $section->replicate();
                $newSection->course_exam_id = $copy->id;
                $newSection->instruction_pdf = $copyFile($section->instruction_pdf);
                $newSection->save();

                foreach ($section->links as $link) {
                    CourseExamQuestion::create(['course_exam_id' => $copy->id, 'section_id' => $newSection->id, 'question_id' => $link->question_id, 'position' => $link->position]);
                }
            }

            return $copy;
        });
    }

    /** The section questions go into when none is named: the first one (created when the exam has none). */
    public static function section(CourseExam $exam, $sectionId = null): CourseExamSection
    {
        if ($sectionId) {
            $found = $exam->sections()->find($sectionId);
            if ($found) {
                return $found;
            }
        }

        return $exam->sections()->first() ?? CourseExamSection::create(['course_exam_id' => $exam->id, 'position' => 0]);
    }

    /**
     * Puts library questions at the end of a section. A question can be in an exam only once; questions of another
     * exam type are ignored. Returns how many were added.
     *
     * @param  int[]  $questionIds
     */
    public static function attach(CourseExam $exam, $sectionId, array $questionIds): int
    {
        $section = self::section($exam, $sectionId);

        $valid = LibraryQuestion::where('exam_id', $exam->exam_id)->whereIn('id', $questionIds)->pluck('id')->all();
        // keep the order the caller gave
        $valid = array_values(array_filter($questionIds, fn ($id) => in_array((int) $id, $valid, true)));
        $have = CourseExamQuestion::where('course_exam_id', $exam->id)->pluck('question_id')->all();
        $new = array_values(array_unique(array_diff(array_map('intval', $valid), $have)));

        $position = (int) CourseExamQuestion::where('section_id', $section->id)->max('position');
        foreach ($new as $i => $id) {
            CourseExamQuestion::create([
                'course_exam_id' => $exam->id, 'section_id' => $section->id, 'question_id' => $id, 'position' => $position + $i + 1,
            ]);
        }

        return count($new);
    }
}
