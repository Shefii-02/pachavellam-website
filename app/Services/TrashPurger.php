<?php

namespace App\Services;

use App\Models\CourseExam;
use App\Models\LibraryClass;
use App\Models\LibraryQuestion;
use App\Models\RoaringItem;
use App\Models\RoaringStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Items deleted from the library, classes, course exams and roaring studies go to Trash (soft delete) and keep their files.
 * This is the one place that removes them for good, together with their pictures / PDFs / audio.
 */
class TrashPurger
{
    /** type key => [model class, nice name] */
    public const TYPES = [
        'questions' => [LibraryQuestion::class, 'Library questions'],
        'classes' => [LibraryClass::class, 'Classes'],
        'course-exams' => [CourseExam::class, 'Course exams & entries'],
        'roaring' => [RoaringStudy::class, 'Roaring Study packs'],
    ];

    public static function purge(Model $m): void
    {
        foreach (self::files($m) as $path) {
            Storage::disk('public')->delete($path);
        }
        $m->forceDelete();   // texts, sections, items and links go with it (foreign key cascade)
    }

    /** @return string[] */
    private static function files(Model $m): array
    {
        return match (true) {
            $m instanceof LibraryQuestion => $m->imagePaths(),
            $m instanceof LibraryClass => ($m->thumbnail && !preg_match('#^https?://#', $m->thumbnail)) ? [$m->thumbnail] : [],
            $m instanceof CourseExam => array_values(array_filter(array_merge([$m->instruction_pdf], $m->sections()->pluck('instruction_pdf')->all()))),
            $m instanceof RoaringStudy => array_values(array_filter(array_merge(
                [$m->cover_image],
                RoaringItem::whereIn('section_id', $m->sections()->pluck('id'))->pluck('file_path')->all()
            ))),
            default => [],
        };
    }

    /** Removes everything that has been in Trash longer than $days. @return int how many */
    public static function purgeOlderThan(int $days): int
    {
        $n = 0;
        foreach (self::TYPES as [$class]) {
            foreach ($class::onlyTrashed()->where('deleted_at', '<', now()->subDays($days))->get() as $m) {
                self::purge($m);
                $n++;
            }
        }

        return $n;
    }
}
