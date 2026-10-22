<?php

namespace App\Services;

use App\Models\ClassFolder;
use App\Models\CompetitiveExam;
use App\Models\LibraryClass;
use Illuminate\Support\Facades\DB;

/**
 * Copies the old KPSC free / paid class links (class_link_free, class_link_paid) into the class library:
 *
 *   Free Classes (synced) > category > sub category > class      /      Paid Classes (synced) > ...
 *
 * Safe to run again: a class already copied (legacy_ref "free:ID" / "paid:ID") is updated, not duplicated.
 */
class LegacyClassSync
{
    private ?CompetitiveExam $exam;
    private array $folders = [];

    public function __construct()
    {
        $this->exam = CompetitiveExam::where('slug', 'kpsc')->first();
    }

    /** @return array{created:int, updated:int, skipped:int} */
    public function syncAll(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        if (!$this->exam) {
            return $stats;
        }

        foreach (['free' => 'Free Classes (synced)', 'paid' => 'Paid Classes (synced)'] as $kind => $rootName) {
            $categories = DB::table("class_category_{$kind}")->pluck('category_name', 'id');
            $subs = DB::table("class_subcategory_{$kind}")->pluck('subcategory_name', 'id');

            DB::transaction(function () use ($kind, $rootName, $categories, $subs, &$stats) {
                foreach (DB::table("class_link_{$kind}")->orderBy('id')->get() as $row) {
                    $link = trim((string) $row->link);
                    $title = trim((string) $row->title);
                    if ($link === '' || $title === '') {
                        $stats['skipped']++;
                        continue;
                    }

                    $parent = $this->folder(null, $rootName);
                    if ($row->category_id && isset($categories[(int) $row->category_id])) {
                        $parent = $this->folder($parent, $categories[(int) $row->category_id]);
                        if ($row->subcategory_id && isset($subs[(int) $row->subcategory_id])) {
                            $parent = $this->folder($parent, $subs[(int) $row->subcategory_id]);
                        }
                    }

                    $detected = ClassSource::detect($link);
                    $values = [
                        'folder_id' => $parent, 'title' => mb_substr($title, 0, 250), 'source' => $detected['source'], 'url' => $detected['url'],
                        'status' => $row->status ? 'published' : 'draft', 'view_count' => (int) $row->view_count,
                    ];
                    $ref = $kind . ':' . $row->id;
                    $class = LibraryClass::withTrashed()->where('exam_id', $this->exam->id)->where('legacy_ref', $ref)->first();
                    if ($class && $class->trashed()) {
                        $stats['skipped']++;
                        continue;   // in Trash: leave it there
                    }
                    if ($class) {
                        $class->update($values);
                        $stats['updated']++;
                    } else {
                        LibraryClass::create($values + ['exam_id' => $this->exam->id, 'legacy_ref' => $ref]);
                        $stats['created']++;
                    }
                }
            });
        }

        return $stats;
    }

    private function folder(?int $parentId, string $name): int
    {
        $key = $parentId . '>' . $name;

        return $this->folders[$key] ??= ClassFolder::firstOrCreate(
            ['exam_id' => $this->exam->id, 'parent_id' => $parentId, 'name' => mb_substr($name, 0, 150)],
            ['position' => 0]
        )->id;
    }
}
