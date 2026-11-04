<?php

namespace App\Console\Commands;

use App\Models\CompetitiveExam;
use App\Models\ContentFolder;
use App\Models\ContentItem;
use App\Models\ExamCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Moves the old Study Material / Syllabus / Results rows of KPSC, RRB, SSC, KTET and UGC NET into the folder structure
 * (Content > Syllabus / Study Materials / Results).
 *  - KPSC: the flat material, syllabus and psc_results tables: one folder per old type (syllabus: and a sub folder per category no).
 *  - RRB, SSC, KTET, UGC NET: <exam>_study_materials and <exam>_syllabus: the category tree becomes the folder tree, files are copied into the shared files folder.
 * Safe to run again - an item already imported is skipped. Old tables and files are left untouched.
 */
class ImportExamResources extends Command
{
    protected $signature = 'content:import-resources {exam? : kpsc, rrb, ssc, ktet or ugc-net (all when left out)}';

    protected $description = 'Import the old study material, syllabus and results of the exam types into folders';

    public function handle(): int
    {
        $slugs = $this->argument('exam') ? [$this->argument('exam')] : ['kpsc', 'rrb', 'ssc', 'ktet', 'ugc-net'];
        foreach ($slugs as $slug) {
            $exam = CompetitiveExam::where('slug', $slug)->first();
            if (!$exam) {
                $this->warn("$slug: no such exam type, skipped");
                continue;
            }
            $slug === 'kpsc' ? $this->kpsc($exam) : $this->perExam($exam);
        }

        return 0;
    }

    private function kpsc(CompetitiveExam $exam): void
    {
        $subjects = DB::table('kpsc_subjects')->pluck('subject_title', 'id');
        foreach (['material' => 'material', 'syllabus' => 'syllabus', 'psc_results' => 'result'] as $table => $kind) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $added = 0;
            foreach (DB::table($table)->orderBy('id')->get() as $r) {
                if (!$r->file_name || $this->exists($kind, $r->file_name)) {
                    continue;
                }
                $folder = $this->folder($exam->id, $kind, trim((string) ($subjects[$r->type] ?? $r->type)) ?: 'Others', null);
                if ($table === 'syllabus' && trim((string) ($r->category_no ?? '')) !== '') {
                    $folder = $this->folder($exam->id, $kind, 'Category ' . trim($r->category_no), $folder->id);
                }
                $this->item($exam, $kind, $folder, $r->title, $r->file_name, null, $r->date ?? $r->created_at, (int) $r->status === 1);
                $added++;
            }
            $this->info("kpsc $table: $added imported");
        }
    }

    private function perExam(CompetitiveExam $exam): void
    {
        $prefix = str_replace('-', '_', $exam->slug);
        foreach (['study_materials' => 'material', 'syllabus' => 'syllabus'] as $suffix => $kind) {
            $table = $prefix . '_' . $suffix;
            if (!Schema::hasTable($table)) {
                continue;
            }
            $added = 0;
            foreach (DB::table($table)->orderBy('id')->get() as $r) {
                $folder = !empty($r->category_id) ? $this->categoryFolder($exam->id, $kind, (int) $r->category_id) : null;
                $already = ContentItem::withTrashed()->where('kind', $kind)->where('title', $r->title ?: 'Untitled')->where('folder_id', $folder?->id)
                    ->whereIn('id', fn ($q) => $q->select('item_id')->from('content_item_exams')->where('exam_id', $exam->id))->exists();
                if ($already) {
                    continue;
                }
                $attachment = null;
                if ($r->file && Storage::disk('public')->exists($r->file)) {
                    $attachment = 'content-files/' . Str::random(40) . '.' . pathinfo($r->file, PATHINFO_EXTENSION);
                    Storage::disk('public')->copy($r->file, 'files/' . $attachment);
                }
                $this->item($exam, $kind, $folder, $r->title, $attachment, $r->link_url ?? null, $r->created_at, (bool) $r->status, $r->description ?? null);
                $added++;
            }
            $this->info("{$exam->slug} $table: $added imported");
        }
    }

    /** The folder path that mirrors a category's path (created as needed). */
    private function categoryFolder(int $examId, string $kind, int $categoryId): ?ContentFolder
    {
        $chain = [];
        for ($c = ExamCategory::find($categoryId); $c && count($chain) < 6; $c = $c->parent_id ? ExamCategory::find($c->parent_id) : null) {
            array_unshift($chain, $c->name);
        }
        $folder = null;
        foreach ($chain as $name) {
            $folder = $this->folder($examId, $kind, $name, $folder?->id);
        }

        return $folder;
    }

    private function exists(string $kind, string $marker): bool
    {
        return ContentItem::withTrashed()->where('kind', $kind)->where('attachment', $marker)->exists();
    }

    private function item(CompetitiveExam $exam, string $kind, ?ContentFolder $folder, ?string $title, ?string $attachment, ?string $link, $date, bool $shown, ?string $description = null): void
    {
        $item = ContentItem::create(['kind' => $kind, 'title' => $title ?: 'Untitled', 'attachment' => $attachment, 'link_url' => $link, 'description' => $description
            , 'folder_id' => $folder?->id, 'published_at' => $date, 'status' => $shown ? 1 : 0, 'position' => 0]);
        $item->update(['position' => $item->id]);
        $item->exams()->sync([$exam->id]);
    }

    private function folder(int $examId, string $kind, string $title, ?int $parent): ContentFolder
    {
        $f = ContentFolder::firstOrCreate(['exam_id' => $examId, 'kind' => $kind, 'parent_id' => $parent, 'title' => mb_substr($title, 0, 150)]);
        if (!$f->position) {
            $f->update(['position' => $f->id]);
        }

        return $f;
    }
}
