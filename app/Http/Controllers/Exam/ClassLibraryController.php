<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\ClassFolder;
use App\Models\CompetitiveExam;
use App\Models\LibraryClass;
use App\Services\ClassSource;
use App\Services\LegacyClassSync;
use App\Services\StorageBrowser;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class library of an exam type: admin/exam/{exam}/class-library
 * Folders and sub folders of recorded classes (links to YouTube, a CDN or files on cloud storage), with the details and
 * watch rules of every class. Courses then pick classes from here (free / paid classes).
 */
class ClassLibraryController extends Controller
{
    use ResolvesExam;

    private function folder(CompetitiveExam $exam, $id): ClassFolder
    {
        return ClassFolder::where('exam_id', $exam->id)->findOrFail($id);
    }

    private function klass(CompetitiveExam $exam, $id): LibraryClass
    {
        return LibraryClass::where('exam_id', $exam->id)->findOrFail($id);
    }

    private function data(CompetitiveExam $exam, array $extra = []): array
    {
        return array_merge(['exam' => $exam, 'layout' => $exam->layout(), 'base' => $exam->examUrl('class-library')], $extra);
    }

    // ================================================================ browse

    public function index(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $current = $request->filled('folder') ? $this->folder($exam, $request->folder) : null;

        $flat = ClassFolder::flatTree($exam->id);
        $direct = LibraryClass::where('exam_id', $exam->id)->select('folder_id', DB::raw('count(*) as c'))->groupBy('folder_id')->pluck('c', 'folder_id');
        $childrenOf = collect($flat)->groupBy('parent_id');
        $total = [];
        $sum = function ($id) use (&$sum, $childrenOf, $direct, &$total) {
            $n = (int) ($direct[$id] ?? 0);
            foreach ($childrenOf->get($id, collect()) as $c) {
                $n += $sum($c['id']);
            }

            return $total[$id] = $n;
        };
        foreach ($childrenOf->get(null, collect()) as $root) {
            $sum($root['id']);
        }

        $deep = $request->boolean('deep');
        $folderIds = $current ? ($deep ? array_merge([$current->id], $current->descendantIds()) : [$current->id]) : null;

        $classes = LibraryClass::where('exam_id', $exam->id)
            ->when($folderIds, fn ($q) => $q->whereIn('folder_id', $folderIds))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%' . $request->q . '%')->orWhere('instructor', 'like', '%' . $request->q . '%')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->with('folder:id,name')->withCount('courseLinks')
            ->orderByDesc('id')->paginate(per_page(25))->withQueryString();

        return view('admin.class-library.index', $this->data($exam, [
            'current' => $current,
            'breadcrumb' => $current ? $current->ancestors()->push($current) : collect(),
            'tree' => $flat, 'total' => $total,
            'subfolders' => collect($flat)->where('parent_id', $current?->id)->values(),
            'classes' => $classes, 'deep' => $deep,
            'storage' => new StorageBrowser(),
        ]));
    }

    // ================================================================ folders

    public function storeFolder(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $data = $request->validate(['name' => 'required|string|max:150', 'parent_id' => 'nullable|integer', 'description' => 'nullable|string|max:500']);

        $parent = !empty($data['parent_id']) ? $this->folder($exam, $data['parent_id']) : null;
        $this->nameFree($exam, $parent?->id, $data['name']);

        $folder = ClassFolder::create([
            'exam_id' => $exam->id, 'parent_id' => $parent?->id, 'name' => trim($data['name']), 'description' => $data['description'] ?? null,
            'created_by' => $request->user()->id,
            'position' => (int) ClassFolder::where('exam_id', $exam->id)->where('parent_id', $parent?->id)->max('position') + 1,
        ]);

        return redirect($this->data($exam)['base'] . '?folder=' . $folder->id)->with('success', 'Folder created.');
    }

    public function updateFolder(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $folder = $this->folder($exam, $id);
        $data = $request->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string|max:500', 'parent_id' => 'nullable|integer']);

        $newParent = !empty($data['parent_id']) ? $this->folder($exam, $data['parent_id']) : null;
        if ($newParent && ($newParent->id === $folder->id || in_array($newParent->id, $folder->descendantIds(), true))) {
            return back()->withErrors(['parent_id' => 'A folder cannot be moved inside itself or one of its own sub folders.']);
        }
        $this->nameFree($exam, $newParent?->id, $data['name'], $folder->id);
        $folder->update(['name' => trim($data['name']), 'description' => $data['description'] ?? null, 'parent_id' => $newParent?->id]);

        return redirect($this->data($exam)['base'] . '?folder=' . $folder->id)->with('success', 'Folder saved.');
    }

    public function destroyFolder($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $folder = $this->folder($exam, $id);
        $ids = array_merge([$folder->id], $folder->descendantIds());

        $count = LibraryClass::withTrashed()->whereIn('folder_id', $ids)->count();
        if ($count > 0) {
            return back()->withErrors(['folder' => "This folder (with its sub folders) still has {$count} class(es). Move or delete them first."]);
        }
        $parentId = $folder->parent_id;
        $folder->delete();

        return redirect($this->data($exam)['base'] . ($parentId ? '?folder=' . $parentId : ''))->with('success', 'Folder deleted.');
    }

    private function nameFree(CompetitiveExam $exam, ?int $parentId, string $name, ?int $ignore = null): void
    {
        $taken = ClassFolder::where('exam_id', $exam->id)->where('parent_id', $parentId)->where('name', trim($name))
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->exists();
        if ($taken) {
            throw ValidationException::withMessages(['name' => 'A folder with this name already exists here.']);
        }
    }

    // ================================================================ classes

    public function create(Request $request, $examSlug)
    {
        return view('admin.class-library.form', $this->formData($this->exam($examSlug), null, $request->folder));
    }

    public function edit($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $class = $this->klass($exam, $id)->loadCount('courseLinks');

        return view('admin.class-library.form', $this->formData($exam, $class, $class->folder_id));
    }

    private function formData(CompetitiveExam $exam, ?LibraryClass $class, $folderId): array
    {
        return $this->data($exam, [
            'class' => $class, 'folderId' => $folderId, 'tree' => ClassFolder::flatTree($exam->id),
            'languages' => config('question_library.languages'), 'storage' => new StorageBrowser(),
            'stats' => $class ? [
                'learners' => $class->watchLogs()->count(),
                'seconds' => (int) $class->watchLogs()->sum('watched_seconds'),
                'completed' => $class->watchLogs()->where('completed', true)->count(),
            ] : null,
        ]);
    }

    public function store(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $values = $this->validated($request, $exam);

        $class = LibraryClass::create($values + ['exam_id' => $exam->id, 'created_by' => $request->user()->id]);
        $this->saveThumbnail($request, $class);

        return redirect($this->data($exam)['base'] . '?folder=' . $class->folder_id)->with('success', 'Class added to the library.');
    }

    public function update(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $class = $this->klass($exam, $id);
        $class->update($this->validated($request, $exam, $class));
        $this->saveThumbnail($request, $class);

        return redirect($this->data($exam)['base'] . '?folder=' . $class->folder_id)->with('success', 'Class saved.');
    }

    public function destroy($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $class = $this->klass($exam, $id);
        $folderId = $class->folder_id;
        $class->delete();   // goes to Trash (thumbnail kept); courses stop showing it

        return redirect($this->data($exam)['base'] . '?folder=' . $folderId)->with('success', 'Class deleted.');
    }

    /** Move, publish / draft / archive or delete several classes. */
    public function bulk(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $request->validate(['action' => 'required|in:move,publish,draft,archive,delete', 'ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $classes = LibraryClass::where('exam_id', $exam->id)->whereIn('id', $request->ids)->get();
        $ids = $classes->pluck('id');

        switch ($request->action) {
            case 'move':
                $target = $this->folder($exam, $request->validate(['folder_id' => 'required|integer'])['folder_id']);
                LibraryClass::whereIn('id', $ids)->update(['folder_id' => $target->id]);
                $msg = $ids->count() . ' class(es) moved to "' . $target->name . '".';
                break;
            case 'delete':
                $classes->each(fn ($c) => $c->delete());
                $msg = $ids->count() . ' class(es) deleted.';
                break;
            default:
                $status = ['publish' => 'published', 'draft' => 'draft', 'archive' => 'archived'][$request->action];
                LibraryClass::whereIn('id', $ids)->update(['status' => $status]);
                $msg = $ids->count() . " class(es) set to {$status}.";
        }

        return back()->with('success', $msg);
    }

    public function syncLegacy(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        abort_unless($exam->slug === 'kpsc' && $request->user()->type === 'Admin', 403, 'Only an Admin can do this, for KPSC.');
        @set_time_limit(300);
        $s = (new LegacyClassSync())->syncAll();

        return back()->with('success', "Old class links synced: {$s['created']} new, {$s['updated']} updated.");
    }

    // ============================================================ cloud storage

    /** Browse the storage disk (S3 ...) folder by folder and pick files or whole folders to become classes. */
    public function storage(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $browser = new StorageBrowser();
        $path = $browser->clean($request->path);

        $listing = ['dirs' => [], 'files' => [], 'truncated' => false];
        $error = null;
        if ($browser->configured()) {
            try {
                $listing = $browser->browse($path);
            } catch (\Throwable $e) {
                $error = 'The storage could not be read: ' . \Illuminate\Support\Str::limit($e->getMessage(), 200);
            }
        }
        $known = LibraryClass::where('exam_id', $exam->id)->where('storage_disk', $browser->disk())
            ->whereIn('storage_path', array_column($listing['files'], 'full'))->pluck('storage_path')->all();

        return view('admin.class-library.storage', $this->data($exam, [
            'browser' => $browser, 'path' => $path, 'listing' => $listing, 'error' => $error, 'known' => $known,
            'tree' => ClassFolder::flatTree($exam->id),
            'crumbs' => $path === '' ? [] : array_map(null, explode('/', $path), array_map(fn ($i) => implode('/', array_slice(explode('/', $path), 0, $i + 1)), array_keys(explode('/', $path)))),
        ]));
    }

    /** Make classes from the ticked files / folders. Optionally the folder structure is created in the library too. */
    public function importFromStorage(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $browser = new StorageBrowser();
        abort_unless($browser->configured(), 422, 'No storage is configured.');

        $request->validate([
            'folder_id' => 'required|integer', 'path' => 'nullable|string', 'files' => 'nullable|array', 'files.*' => 'string',
            'dirs' => 'nullable|array', 'dirs.*' => 'string', 'mirror' => 'nullable|boolean', 'status' => 'required|in:draft,published',
        ], ['folder_id.required' => 'Choose the library folder the classes go into.']);

        $target = $this->folder($exam, $request->folder_id);
        $base = $browser->clean($request->path);
        $fs = Storage::disk($browser->disk());
        $extensions = (array) config('class_library.storage.extensions');
        $limit = 2000;

        // every file to import: the ticked files, plus everything inside the ticked folders
        $paths = [];
        foreach ((array) $request->input('files', []) as $f) {
            $paths[] = $browser->clean($f);
        }
        foreach ((array) $request->input('dirs', []) as $d) {
            $dir = $browser->clean($d);
            foreach ($fs->allFiles($browser->full($dir)) as $full) {
                if (in_array(strtolower(pathinfo($full, PATHINFO_EXTENSION)), $extensions, true)) {
                    $rel = $browser->full('') === '' ? $full : ltrim(substr($full, strlen($browser->full(''))), '/');
                    $paths[] = $rel;
                }
            }
        }
        $paths = array_slice(array_values(array_unique($paths)), 0, $limit);
        if (!$paths) {
            return back()->withErrors(['files' => 'Tick at least one file or folder.']);
        }

        $made = 0;
        $skipped = 0;
        $folderCache = [];
        DB::transaction(function () use ($paths, $browser, $exam, $target, $base, $request, &$made, &$skipped, &$folderCache) {
            foreach ($paths as $rel) {
                $full = $browser->full($rel);
                if (LibraryClass::where('exam_id', $exam->id)->where('storage_disk', $browser->disk())->where('storage_path', $full)->exists()) {
                    $skipped++;
                    continue;
                }

                $folderId = $target->id;
                if ($request->boolean('mirror')) {
                    $inside = $base !== '' && str_starts_with($rel, $base . '/') ? substr($rel, strlen($base) + 1) : $rel;
                    $dirs = array_filter(explode('/', dirname($inside)), fn ($p) => $p !== '.' && $p !== '');
                    foreach ($dirs as $name) {
                        $key = $folderId . '>' . $name;
                        $folderId = $folderCache[$key] ??= ClassFolder::firstOrCreate(
                            ['exam_id' => $exam->id, 'parent_id' => $folderId, 'name' => mb_substr($name, 0, 150)], ['position' => 0]
                        )->id;
                    }
                }

                LibraryClass::create([
                    'exam_id' => $exam->id, 'folder_id' => $folderId, 'title' => mb_substr(StorageBrowser::titleFromFile(basename($rel)), 0, 250),
                    'source' => 's3', 'storage_disk' => $browser->disk(), 'storage_path' => $full, 'status' => $request->status,
                    'created_by' => $request->user()->id,
                ]);
                $made++;
            }
        });

        return redirect($this->data($exam)['base'] . '?folder=' . $target->id . ($request->boolean('mirror') ? '&deep=1' : ''))
            ->with('success', "{$made} class(es) added from storage" . ($skipped ? ", {$skipped} were already in the library." : '.') . ' Open them to add the details.');
    }

    // ----------------------------------------------------------------- helpers

    private function validated(Request $request, CompetitiveExam $exam, ?LibraryClass $existing = null): array
    {
        $request->validate([
            'folder_id' => 'required|integer',
            'title' => 'required|string|max:250',
            'url' => 'nullable|string|max:2000',
            'storage_path' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'details' => 'nullable|string|max:50000',
            'instructor' => 'nullable|string|max:150',
            'language' => 'nullable|string|max:5',
            'duration' => 'nullable|string|max:12',
            'class_date' => 'nullable|date',
            'tags' => 'nullable|string|max:500',
            'notes_url' => 'nullable|url|max:500',
            'thumbnail' => 'nullable|image|max:3072',
            'status' => 'required|in:draft,published,archived',
            'max_watch_minutes' => 'nullable|integer|min:1|max:100000',
            'max_views' => 'nullable|integer|min:1|max:10000',
            'access_days' => 'nullable|integer|min:1|max:3650',
            'complete_percent' => 'nullable|integer|min:1|max:100',
        ], ['notes_url.url' => 'The notes address must be a full link starting with http:// or https://.']);

        $this->folder($exam, $request->folder_id);
        $errors = [];
        $url = trim((string) $request->url);
        $storagePath = trim((string) $request->storage_path, " /");
        $browser = new StorageBrowser();

        $source = 'link';
        $disk = null;
        $path = null;
        if ($storagePath !== '') {
            if (!$browser->configured()) {
                $errors['storage_path'] = 'No cloud storage is set up (CLASS_STORAGE_DISK in the .env file).';
            } elseif (str_contains($storagePath, '..')) {
                $errors['storage_path'] = 'Invalid file path.';
            }
            $source = 's3';
            $disk = $browser->disk();
            $path = $storagePath;
        } elseif ($url !== '') {
            if (!preg_match('#^https?://[^\s]+$#i', $url)) {
                $errors['url'] = 'The class link must start with http:// or https://.';
            } else {
                $source = ClassSource::detect($url)['source'];
                $url = ClassSource::detect($url)['url'];
            }
        } else {
            $errors['url'] = 'Give the class link (YouTube / CDN) or choose a file from the cloud storage.';
        }

        $duration = null;
        if (trim((string) $request->duration) !== '') {
            $duration = ClassSource::parseDuration($request->duration);
            if ($duration === null) {
                $errors['duration'] = 'Write the length as 1:25:30 (hours:minutes:seconds), 45:00 (minutes:seconds) or just the minutes.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $tags = collect(preg_split('/[,\n]+/u', (string) $request->tags))->map(fn ($t) => trim($t))->filter()->unique()->take(15)->values()->all();

        return [
            'folder_id' => (int) $request->folder_id, 'title' => trim($request->title), 'description' => $request->description,
            'details' => SafeHtml::clean($request->details) ?: null, 'source' => $source,
            'url' => $source === 's3' ? ($url ?: null) : $url, 'storage_disk' => $disk, 'storage_path' => $path,
            'instructor' => $request->instructor, 'language' => $request->language ?: null, 'duration_seconds' => $duration,
            'class_date' => $request->class_date ?: null, 'tags' => $tags ?: null, 'notes_url' => $request->notes_url,
            'status' => $request->status,
            'max_watch_minutes' => $request->max_watch_minutes ?: null, 'max_views' => $request->max_views ?: null,
            'access_days' => $request->access_days ?: null, 'complete_percent' => (int) ($request->complete_percent ?: 90),
            'allow_speed' => $request->boolean('allow_speed'), 'allow_seek' => $request->boolean('allow_seek'),
            'allow_download' => $request->boolean('allow_download'), 'resume_playback' => $request->boolean('resume_playback'),
            'watermark_user' => $request->boolean('watermark_user'),
        ];
    }

    private function saveThumbnail(Request $request, LibraryClass $class): void
    {
        if ($request->hasFile('thumbnail')) {
            $this->forgetThumbnail($class);
            $class->update(['thumbnail' => $request->file('thumbnail')->store('class-library/thumbnails', 'public')]);
        } elseif ($request->boolean('remove_thumbnail')) {
            $this->forgetThumbnail($class);
            $class->update(['thumbnail' => null]);
        }
    }

    private function forgetThumbnail(LibraryClass $class): void
    {
        if ($class->thumbnail && !preg_match('#^https?://#', $class->thumbnail)) {
            Storage::disk('public')->delete($class->thumbnail);
        }
    }
}
