<?php

namespace App\Support;

use App\Models\CompetitiveExam;
use App\Models\User;

/**
 * The sidebar features of an exam type (Dashboard, Courses, Question Library, Syllabus ...).
 * General > Exam Types chooses which of them an exam type has (competitive_exams.features, null = all).
 * A person sees one in the sidebar when the exam type has it AND they have its View permission (Staff Access / Roles).
 */
class ExamFeatures
{
    /** group => key => [label, permission feature (null = no permission needed), only these exam type slugs (null = all)] */
    private static function base(): array
    {
        $kpsc = ['kpsc'];

        return [
            'Overview' => [
                'dashboard' => ['Dashboard', null, null, true],
                'interested_students' => ['Interested Students', null, null],
                'new_joins' => ['New joins', 'New joins', null],
            ],
            'Courses & Libraries' => [
                'categories' => ['Categories', 'Exam courses', null],
                'courses' => ['Courses', 'Exam courses', null],
                'question_library' => ['Question Library', 'Question library', null],
                'class_library' => ['Class Library', 'Class library', null],
                'roaring_study' => ['Roaring Study', 'Roaring study', null],
            ],
            'Exams' => [
                'daily' => ['Daily Exam Series', 'Exam courses', null],
                'omr' => ['OMR Exam Series', 'Exam courses', null],
                'exam_instructions' => ['Exam instructions', 'Exam courses', $kpsc],
                'exam_reports' => ['Reported errors', 'Exam courses', $kpsc],
                'ca' => ['CA Exam Series', 'Exam courses', null],
                'gk' => ['GK Exam Series', 'Exam courses', null],
                'special' => ['Special Exam Series', 'Exam courses', null],
            ],
            'Study Content' => [
                'syllabus' => ['Syllabus', 'Content', null],
                'study_materials' => ['Study Materials', 'Content', null],
                'subjects' => ['Subjects', 'Content', null],
                'pyq' => ['PYQ', 'Content', $kpsc],
                'psc_bulletin' => ['Psc Bulletin', 'Content', $kpsc],
                'daily_ca' => ['Daily CA', 'Content', $kpsc],
                'youtube_classes' => ['YouTube Classes', 'Content', null],
            ],
            'Results' => [
                'results' => ['Results', 'Content', null],
            ],
        ];
    }

    /** Exam types that have their own module (not KPSC) also show the sections of config/exam_sections.php. */
    private static function hasSections(?CompetitiveExam $exam): bool
    {
        return $exam && $exam->slug !== 'kpsc' && isset(CompetitiveExam::MODULES[$exam->slug]);
    }

    /** The features that apply to the exam type (all of them for a new one), by group. [key => [label, permission feature, locked]] */
    public static function catalog(?CompetitiveExam $exam = null): array
    {
        $out = [];
        foreach (self::base() as $group => $items) {
            foreach ($items as $key => $def) {
                if ($def[2] === null || ($exam && in_array($exam->slug, $def[2], true))) {
                    $out[$group][$key] = ['label' => $def[0], 'permission' => $def[1], 'locked' => $def[3] ?? false];
                }
            }
        }
        if (self::hasSections($exam)) {
            foreach (config('exam_sections') as $key => $sec) {
                if (!isset(config('exam_sections_common')[$key])) {
                    $out['Content & announcements']['section:' . $key] = ['label' => $sec['label'], 'permission' => 'Content', 'locked' => false];
                }
            }
        }

        return $out;
    }

    /** Every key of the catalog. */
    public static function keys(?CompetitiveExam $exam = null): array
    {
        return collect(self::catalog($exam))->flatMap(fn ($g) => array_keys($g))->values()->all();
    }

    /** Does the exam type have the feature (before looking at anybody's permission)? */
    public static function enabled(?CompetitiveExam $exam, string $key): bool
    {
        $def = collect(self::catalog($exam))->first(fn ($g) => isset($g[$key]))[$key] ?? null;
        if ($def && $def['locked']) {
            return true;
        }

        return !$exam || $exam->features === null || in_array($key, (array) $exam->features, true);
    }

    /** Does the person see it in the sidebar: the exam type has it and they have its View permission. */
    public static function shows(?CompetitiveExam $exam, ?User $user, string $key): bool
    {
        if (!self::enabled($exam, $key)) {
            return false;
        }
        $def = collect(self::catalog($exam))->first(fn ($g) => isset($g[$key]))[$key] ?? null;
        $permission = $def['permission'] ?? null;

        return $permission === null || StaffAccess::can($user, $permission . ' view');
    }

    /** What to store when the form is saved: null when every feature is ticked (so features added later show up by themselves). */
    public static function forStorage(?CompetitiveExam $exam, array $selected): ?array
    {
        $all = self::keys($exam);
        $selected = array_values(array_intersect($all, $selected));

        return count($selected) === count($all) ? null : $selected;
    }
}
