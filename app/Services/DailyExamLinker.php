<?php

namespace App\Services;

use App\Models\CourseExam;
use App\Models\CourseExamQuestion;
use App\Models\DailyExam;
use App\Models\DailyExamdetails;
use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Models\LibraryQuestion;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\DB;

/**
 * Bridges the old KPSC Daily Exam (kpsc_daily_exams / kpsc_daily_exams_details, which the app reads) with the question
 * library and with the course exams:
 *
 *   copyFromLibrary : library questions -> questions of the daily exam (four options, answer = text of the right option)
 *   toCourses       : the daily exam -> one course exam (draft) in each chosen course, with the same questions
 */
class DailyExamLinker
{
    /**
     * Adds library questions to a daily exam. @return array{added: int, skipped: array<int, string>}
     *
     * @param  int[]  $questionIds
     */
    public function copyFromLibrary(DailyExam $exam, array $questionIds): array
    {
        $skipped = [];
        $added = 0;
        $position = (int) DailyExamdetails::where('exam_id', $exam->id)->max('position');
        $have = DailyExamdetails::where('exam_id', $exam->id)->whereNotNull('library_question_id')->pluck('library_question_id')->all();

        $questions = LibraryQuestion::with('texts')->whereIn('id', $questionIds)->get()->keyBy('id');

        foreach ($questionIds as $id) {
            $q = $questions->get((int) $id);
            if (!$q) {
                continue;
            }
            $label = '"' . $q->plainQuestion(60) . '"';
            if (in_array($q->id, $have, true)) {
                $skipped[] = "{$label} is already in this exam.";
                continue;
            }
            if ($q->question_type !== 'single') {
                $skipped[] = "{$label}: only one-right-answer questions fit the daily exam.";
                continue;
            }
            $row = $this->toLegacyRow($q);
            if (is_string($row)) {
                $skipped[] = "{$label}: {$row}";
                continue;
            }

            // inserted directly: the old module's "copy to library" hook must not create a second library copy
            DB::table('kpsc_daily_exams_details')->insert($row + [
                'exam_id' => $exam->id, 'library_question_id' => $q->id, 'position' => ++$position, 'status' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $have[] = $q->id;
            $added++;
        }

        return ['added' => $added, 'skipped' => $skipped];
    }

    /** The old table's columns for a library question, or a reason it does not fit. @return array|string */
    private function toLegacyRow(LibraryQuestion $q)
    {
        $t = $q->texts->first();
        if (!$t) {
            return 'it has no text.';
        }
        $options = [1 => $t->option_1, 2 => $t->option_2, 3 => $t->option_3, 4 => $t->option_4];
        $images = $q->option_images ?? [];
        for ($n = 1; $n <= 4; $n++) {
            if (trim((string) $options[$n]) === '' && empty($images[$n])) {
                return 'the daily exam needs exactly four options.';
            }
        }
        if (trim((string) $t->option_5) !== '' || trim((string) $t->option_6) !== '' || !empty($images[5]) || !empty($images[6])) {
            return 'it has more than four options.';
        }

        $html = fn (?string $text) => $q->is_rich ? (string) $text : nl2br(e((string) $text));
        $img = fn (?string $path) => $path ? '<p><img src="' . asset('storage/' . $path) . '" alt="" style="max-width:100%"></p>' : '';
        $hasImg = fn (?string $path) => (bool) $path;

        $question = $hasImg($q->question_image) ? $html($t->question) . $img($q->question_image) : ($q->is_rich ? (string) $t->question : (string) $t->question);
        $opts = [];
        foreach ($options as $n => $text) {
            $opts[$n] = !empty($images[$n]) ? $html($text) . $img($images[$n]) : (string) $text;
        }
        $notes = $hasImg($q->explanation_image) ? $html($t->explanation) . $img($q->explanation_image) : (string) $t->explanation;

        return [
            'question' => $question,
            'option_1' => $opts[1], 'option_2' => $opts[2], 'option_3' => $opts[3], 'option_4' => $opts[4],
            'currect_ans' => $opts[$q->correct_option],   // the old table stores the TEXT of the right option
            'notes' => $notes,
        ];
    }

    /** Which course feature an old exam becomes: daily exams -> Daily exams, OMR -> OMR exams, CA series -> Premium daily CA. */
    public static function featureSlug(DailyExam $exam): string
    {
        return match ($exam->section) {
            'Model Exam' => 'omr-exams',
            'CA-Daily-Exam' => 'premium-daily-ca',
            default => 'daily-exams',
        };
    }

    /**
     * Makes (or refreshes) a course exam for the daily exam in each course. Returns [courseId => CourseExam].
     *
     * @param  int[]  $courseIds
     */
    public function toCourses(DailyExam $exam, array $courseIds, ?int $userId = null): array
    {
        $feature = ExamFeature::where('slug', self::featureSlug($exam))->firstOrFail();
        $exam->loadMissing('exam_details');
        $made = [];

        // every question needs its library copy first
        $sync = new LegacyQuestionSync();
        $libraryIds = [];
        foreach ($exam->exam_details as $d) {
            $id = $d->library_question_id
                ?: LibraryQuestion::where('legacy_ref', 'daily:' . $d->id)->value('id');
            if (!$id) {
                $sync->syncOne($d->id);
                $id = LibraryQuestion::where('legacy_ref', 'daily:' . $d->id)->value('id');
            }
            if ($id) {
                $libraryIds[] = (int) $id;
            }
        }

        $title = trim((string) ($exam->examtitle ?: $exam->exam_title)) ?: 'Daily exam #' . $exam->id;
        if ($exam->exam_date) {
            $title .= ' · ' . date('d M Y', strtotime($exam->exam_date));
        }

        foreach (ExamCourse::whereIn('id', $courseIds)->get() as $course) {
            if (!$course->features()->where('exam_features.id', $feature->id)->exists()) {
                continue;
            }
            DB::transaction(function () use ($exam, $course, $feature, $title, $libraryIds, $userId, &$made) {
                $ce = CourseExam::where('course_id', $course->id)->where('legacy_exam_id', $exam->id)->first();
                $fields = [
                    'title' => mb_substr($title, 0, 200), 'is_paid' => $exam->access_type === 'premium',
                    'start_at' => $exam->started_at ?: null, 'end_at' => $exam->ended_at ?: null,
                ];
                if (!$ce) {
                    $ce = CourseExam::create($fields + [
                        'exam_id' => $course->exam_id, 'course_id' => $course->id, 'feature_id' => $feature->id, 'legacy_exam_id' => $exam->id,
                        'mode' => 'moderate', 'duration_minutes' => max(10, count($libraryIds)), 'right_mark' => 1, 'negative_mark' => 0,
                        'entry_date' => $feature->slug === 'premium-daily-ca' ? ($exam->exam_date ?: null) : null,
                        'status' => 'draft', 'max_attempts' => 1, 'created_by' => $userId,
                        'position' => (int) CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->max('position') + 1,
                    ]);
                } else {
                    $ce->update($fields);
                }
                CourseExamBuilder::attach($ce, null, $libraryIds);   // questions already in the exam are kept, new ones are added

                DB::table('kpsc_daily_exam_courses')->updateOrInsert(
                    ['exam_id' => $exam->id, 'course_id' => $course->id],
                    ['course_exam_id' => $ce->id, 'updated_at' => now(), 'created_at' => now()]
                );
                $made[$course->id] = $ce;
            });
        }

        return $made;
    }
}
