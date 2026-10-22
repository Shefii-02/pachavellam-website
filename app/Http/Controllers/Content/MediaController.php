<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Services\MediaService;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Media library (admin/media): every picture, video, voice recording and document in folders - like WordPress.
 * The same JSON endpoints feed the "choose from the library / upload" window that opens from every file field.
 */
class MediaController extends Controller
{
    public function __construct(private MediaService $media)
    {
    }

    private function allow(Request $request): void
    {
        StaffAccess::authorize($request, 'Content manage');
    }

    public function index(Request $request)
    {
        $this->allow($request);

        return view('admin.media.index', ['specs' => config('media.specs'), 'accept' => implode(',', array_map(fn ($e) => '.' . $e, MediaService::allowedExtensions())), 'maxMb' => config('media.max_mb')]);
    }

    /** Folders under $folder and the files in it (or search / type filter across the library), 48 per page. */
    public function list(Request $request)
    {
        $this->allow($request);
        $folderId = $request->filled('folder') ? (int) $request->folder : null;
        $type = in_array($request->type, ['image', 'video', 'audio', 'document', 'other'], true) ? $request->type : null;
        $q = trim((string) $request->query('q'));
        $searching = $q !== '' || $request->boolean('all');

        $media = Media::query()
            ->when($type, fn ($w) => $w->where('type', $type))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('title', 'like', "%$q%")->orWhere('path', 'like', "%$q%")))
            ->when(!$searching, fn ($w) => $w->where('folder_id', $folderId))
            ->orderByDesc('id');
        $page = $media->paginate(48);

        $folders = MediaFolder::where('parent_id', $searching ? -1 : $folderId)->orderBy('name')->get(['id', 'name', 'parent_id']);
        $counts = Media::selectRaw('folder_id, count(*) c')->whereIn('folder_id', $folders->pluck('id'))->groupBy('folder_id')->pluck('c', 'folder_id');

        return response()->json([
            'folders' => $folders->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'count' => (int) ($counts[$f->id] ?? 0), 'children' => MediaFolder::where('parent_id', $f->id)->count()]),
            'trail' => $this->media->folderPath($folderId),
            'items' => $page->getCollection()->map->toTile()->values(),
            'page' => $page->currentPage(), 'last' => $page->lastPage(), 'total' => $page->total(),
            'tree' => $request->boolean('tree') ? $this->tree() : null,
            'trash' => Media::onlyTrashed()->count() + MediaFolder::onlyTrashed()->count(),
        ]);
    }

    private function tree(): array
    {
        $all = MediaFolder::orderBy('name')->get(['id', 'name', 'parent_id']);
        $build = function ($parent) use (&$build, $all) {
            return $all->where('parent_id', $parent)->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'children' => $build($f->id)])->values()->all();
        };

        return $build(null);
    }

    /** Upload one or more files (AJAX). Answers with the new tiles, and the problems of the files that were refused. */
    public function upload(Request $request)
    {
        $this->allow($request);
        $request->validate(['files' => 'required|array|max:30', 'files.*' => 'file', 'folder_id' => 'nullable|integer|exists:media_folders,id']);
        $ok = [];
        $errors = [];
        foreach ($request->file('files') as $file) {
            try {
                $ok[] = $this->media->store($file, $request->filled('folder_id') ? (int) $request->folder_id : null, $request->user()->id)->toTile();
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            } catch (\Throwable $e) {
                report($e);
                $errors[] = '"' . $file->getClientOriginalName() . '" could not be saved.';
            }
        }

        return response()->json(['items' => $ok, 'errors' => $errors], $ok || !$errors ? 200 : 422);
    }

    public function folderStore(Request $request)
    {
        $this->allow($request);
        $data = $request->validate(['name' => 'required|string|max:120', 'parent_id' => 'nullable|integer|exists:media_folders,id']);
        $name = trim($data['name']);
        $parent = $data['parent_id'] ?? null;
        if (MediaFolder::where('parent_id', $parent)->where('name', $name)->exists()) {
            return response()->json(['message' => 'A folder with this name already exists here.'], 422);
        }
        $f = MediaFolder::create(['name' => $name, 'parent_id' => $parent, 'created_by' => $request->user()->id]);

        return response()->json(['id' => $f->id, 'name' => $f->name]);
    }

    /** Rename a folder and / or move it (parent_id = the folder it goes into, null = the top of the library). */
    public function folderUpdate(Request $request, $id)
    {
        $this->allow($request);
        $f = MediaFolder::findOrFail($id);
        $data = $request->validate(['name' => 'nullable|string|max:120', 'parent_id' => 'nullable|integer|exists:media_folders,id']);
        $name = trim((string) ($data['name'] ?? '')) ?: $f->name;
        $parent = $request->has('parent_id') ? ($data['parent_id'] ?? null) : $f->parent_id;
        if ($parent && in_array((int) $parent, $f->descendantIds(), true)) {
            return response()->json(['message' => 'A folder cannot be moved inside itself.'], 422);
        }
        if (MediaFolder::where('parent_id', $parent)->where('name', $name)->where('id', '!=', $f->id)->exists()) {
            return response()->json(['message' => 'A folder named "' . $name . '" already exists there.'], 422);
        }
        $f->update(['name' => $name, 'parent_id' => $parent]);

        return response()->json(['ok' => true, 'path' => $this->pathText($f->id)]);
    }

    /** "Media library / Website content / content" */
    private function pathText(?int $id): string
    {
        return implode(' / ', array_merge(['Media library'], array_map(fn ($x) => $x['name'], $this->media->folderPath($id))));
    }

    private function uniqueFolderName(?int $parent, string $name): string
    {
        $n = $name;
        for ($i = 2; MediaFolder::where('parent_id', $parent)->where('name', $n)->exists(); $i++) {
            $n = $name . ' (copy' . ($i > 2 ? ' ' . ($i - 1) : '') . ')';
        }

        return $n;
    }

    /** A real copy of a file: a new file on the disk and a new library row, so changing or deleting one never touches the other. */
    private function copyFile(Media $m, ?int $folderId, ?string $name, ?int $userId): Media
    {
        $disk = Storage::disk(config('media.disk'));
        $dir = trim(config('media.root'), '/') . '/' . now()->format('Y/m');
        $new = $dir . '/' . \Illuminate\Support\Str::random(40) . '.' . $m->ext;
        $disk->copy($m->path, $new);
        $c = $m->replicate();
        $c->path = $new;
        $c->folder_id = $folderId;
        $c->name = $name ?: $m->name;
        $c->uploaded_by = $userId;
        $c->source = 'upload';
        $c->created_at = now();
        $c->updated_at = now();
        $c->save();

        return $c;
    }

    private function copyFolderTree(MediaFolder $src, ?int $parent, ?string $name, ?int $userId, array &$count): MediaFolder
    {
        $copy = MediaFolder::create(['parent_id' => $parent, 'name' => $name ?: $this->uniqueFolderName($parent, $src->name), 'created_by' => $userId]);
        $count['folders']++;
        foreach (Media::where('folder_id', $src->id)->get() as $m) {
            if (Storage::disk(config('media.disk'))->exists($m->path)) {
                $this->copyFile($m, $copy->id, null, $userId);
                $count['files']++;
            }
        }
        foreach (MediaFolder::where('parent_id', $src->id)->get() as $child) {
            $this->copyFolderTree($child, $copy->id, null, $userId, $count);
        }

        return $copy;
    }

    /** Copy a folder with everything in it to another place. */
    public function folderCopy(Request $request, $id)
    {
        $this->allow($request);
        $src = MediaFolder::findOrFail($id);
        $data = $request->validate(['parent_id' => 'nullable|integer|exists:media_folders,id', 'name' => 'nullable|string|max:120']);
        $parent = $data['parent_id'] ?? null;
        if ($parent && in_array((int) $parent, $src->descendantIds(), true)) {
            return response()->json(['message' => 'A folder cannot be copied inside itself.'], 422);
        }
        $name = trim((string) ($data['name'] ?? ''));
        if ($name !== '' && MediaFolder::where('parent_id', $parent)->where('name', $name)->exists()) {
            return response()->json(['message' => 'A folder named "' . $name . '" already exists there.'], 422);
        }
        $count = ['folders' => 0, 'files' => 0];
        $copy = DB::transaction(function () use ($src, $parent, $name, $request, &$count) {
            return $this->copyFolderTree($src, $parent, $name ?: null, $request->user()->id, $count);
        });

        return response()->json(['ok' => true, 'id' => $copy->id, 'folders' => $count['folders'], 'files' => $count['files'], 'path' => $this->pathText($copy->id)]);
    }

    /**
     * Delete a folder. mode=keep (default): the files and sub folders move up one level.
     * mode=all: the folder, every sub folder and every file in them are deleted (asks first if a file is on the website).
     */
    public function folderDestroy(Request $request, $id)
    {
        $this->allow($request);
        $request->validate(['confirm' => 'required|in:DELETE'], ['confirm.in' => 'Type DELETE to confirm.', 'confirm.required' => 'Type DELETE to confirm.']);
        $f = MediaFolder::findOrFail($id);
        if ($request->input('mode') === 'all') {
            $permanent = $request->boolean('permanent');
            if ($permanent) {
                $this->mustBeAdmin($request);
            }
            $ids = $f->descendantIds();
            $files = Media::whereIn('folder_id', $ids)->get();
            $used = $files->filter(fn ($m) => $this->usage($m) > 0);
            if ($used->count() && !$request->boolean('force')) {
                return response()->json(['message' => $used->count() . ' file(s) in this folder are used on the website (' . $used->pluck('name')->take(3)->implode(', ') . '). Delete everything anyway?', 'needs_confirm' => true], 409);
            }
            DB::transaction(function () use ($ids, $files, $permanent) {
                if ($permanent) {
                    $this->purgeFiles($files);
                    MediaFolder::whereIn('id', $ids)->get()->each->forceDelete();
                } else {
                    $files->each->delete();
                    MediaFolder::whereIn('id', $ids)->get()->each->delete();
                }
            });

            return response()->json(['ok' => true, 'deleted_files' => $files->count(), 'deleted_folders' => count($ids), 'permanent' => $permanent]);
        }
        Media::where('folder_id', $f->id)->update(['folder_id' => $f->parent_id]);
        MediaFolder::where('parent_id', $f->id)->update(['parent_id' => $f->parent_id]);
        $f->delete();

        return response()->json(['ok' => true]);
    }

    public function treeJson(Request $request)
    {
        $this->allow($request);

        return response()->json(['tree' => $this->tree()]);
    }

    public function update(Request $request, $id)
    {
        $this->allow($request);
        $m = Media::findOrFail($id);
        $data = $request->validate(['title' => 'nullable|string|max:255', 'alt' => 'nullable|string|max:255', 'name' => 'nullable|string|max:240']);
        $upd = ['title' => $data['title'] ?? null, 'alt' => $data['alt'] ?? null];
        if (!empty($data['name'])) {
            // the file name shown (and used when downloading); the stored address of the file never changes, so nothing on the website breaks
            $nm = trim($data['name']);
            $upd['name'] = $m->ext && !str_ends_with(strtolower($nm), '.' . $m->ext) ? $nm . '.' . $m->ext : $nm;
        }
        $m->update($upd);

        return response()->json($m->toTile());
    }

    /** Move files to another folder (folder_id null = top of the library). With one file, name renames it on the way. */
    public function move(Request $request)
    {
        $this->allow($request);
        $data = $request->validate(['ids' => 'required|array|max:200', 'ids.*' => 'integer', 'folder_id' => 'nullable|integer|exists:media_folders,id', 'name' => 'nullable|string|max:240']);
        $q = Media::whereIn('id', $data['ids']);
        $n = (clone $q)->count();
        $q->update(['folder_id' => $data['folder_id'] ?? null]);
        if (!empty($data['name']) && $n === 1) {
            $m = Media::find($data['ids'][0]);
            $nm = trim($data['name']);
            $m->update(['name' => $m->ext && !str_ends_with(strtolower($nm), '.' . $m->ext) ? $nm . '.' . $m->ext : $nm]);
        }

        return response()->json(['ok' => true, 'moved' => $n, 'path' => $this->pathText($data['folder_id'] ?? null)]);
    }

    /** Copy files to another folder. With one file, name gives the copy another name. */
    public function copy(Request $request)
    {
        $this->allow($request);
        $data = $request->validate(['ids' => 'required|array|max:100', 'ids.*' => 'integer', 'folder_id' => 'nullable|integer|exists:media_folders,id', 'name' => 'nullable|string|max:240']);
        $items = Media::whereIn('id', $data['ids'])->get();
        $made = 0;
        foreach ($items as $m) {
            if (!Storage::disk(config('media.disk'))->exists($m->path)) {
                continue;
            }
            $nm = $items->count() === 1 && !empty($data['name']) ? trim($data['name']) : null;
            if ($nm && $m->ext && !str_ends_with(strtolower($nm), '.' . $m->ext)) {
                $nm .= '.' . $m->ext;
            }
            $this->copyFile($m, $data['folder_id'] ?? null, $nm, $request->user()->id);
            $made++;
        }

        return response()->json(['ok' => true, 'copied' => $made, 'path' => $this->pathText($data['folder_id'] ?? null)]);
    }

    /** Where a file is used (content items), so nobody removes a picture that is on a live page by mistake. */
    private function usage(Media $m): int
    {
        if ($m->fieldValue() === null) {
            return 0;
        }

        return (int) DB::table('content_items')->whereNull('deleted_at')->where(fn ($q) => $q->where('image', $m->fieldValue())->orWhere('attachment', $m->fieldValue())->orWhere('media_path', $m->fieldValue()))->count();
    }

    /** Removes files from the disk and the library for good (the row and the stored file; a file another row still points at stays). */
    private function purgeFiles($files): void
    {
        $disk = Storage::disk(config('media.disk'));
        foreach ($files as $m) {
            if (Media::withTrashed()->where('path', $m->path)->where('id', '!=', $m->id)->doesntExist()) {
                $disk->delete($m->path);
            }
            $m->forceDelete();
        }
    }

    private function mustBeAdmin(Request $request): void
    {
        abort_unless(StaffAccess::isAdmin($request->user()), 403, 'Only an administrator can delete files for good.');
    }

    /**
     * Delete files. Needs confirm=DELETE (typed in the window). Default: they go to the Trash and can be restored.
     * permanent=1 (administrators only): removed for good, skipping the Trash.
     */
    public function destroy(Request $request)
    {
        $this->allow($request);
        $data = $request->validate(['ids' => 'required|array|max:200', 'ids.*' => 'integer', 'force' => 'nullable|boolean', 'permanent' => 'nullable|boolean', 'confirm' => 'required|in:DELETE'],
            ['confirm.in' => 'Type DELETE to confirm.', 'confirm.required' => 'Type DELETE to confirm.']);
        if ($request->boolean('permanent')) {
            $this->mustBeAdmin($request);
        }
        $items = Media::whereIn('id', $data['ids'])->get();
        $used = $items->filter(fn ($m) => $this->usage($m) > 0);
        if ($used->count() && !$request->boolean('force')) {
            return response()->json(['message' => $used->count() . ' of the files are used on the website (' . $used->pluck('name')->take(3)->implode(', ') . '). Delete anyway?', 'needs_confirm' => true], 409);
        }
        if ($request->boolean('permanent')) {
            $this->purgeFiles($items);
        } else {
            $items->each->delete();   // soft delete: the row goes to the Trash with its folder remembered; the file stays on the disk
        }

        return response()->json(['deleted' => $items->count(), 'permanent' => $request->boolean('permanent')]);
    }

    // ------------------------------------------------------------------ trash

    /** "Media library / A / B" even when some of those folders are in the trash too. */
    private function pathTextAny(?int $id): string
    {
        $names = [];
        for ($i = 0; $id && $i < 12; $i++) {
            $f = MediaFolder::withTrashed()->find($id);
            if (!$f) {
                return 'Media library (the old folder no longer exists - it will be restored to the top)';
            }
            array_unshift($names, $f->name . ($f->trashed() ? ' (deleted)' : ''));
            $id = $f->parent_id;
        }

        return implode(' / ', array_merge(['Media library'], $names));
    }

    /** Everything in the trash: files (with the folder they were in) and deleted folders. */
    public function trash(Request $request)
    {
        $this->allow($request);
        $page = Media::onlyTrashed()->orderByDesc('deleted_at')->paginate(48);
        $items = $page->getCollection()->map(function ($m) {
            $t = $m->toTile();
            $t['deleted'] = optional($m->deleted_at)->format('d M Y, h:i a');
            $t['was'] = $this->pathTextAny($m->folder_id);

            return $t;
        })->values();
        $folders = $page->currentPage() === 1 ? MediaFolder::onlyTrashed()->orderByDesc('deleted_at')->get()->map(fn ($f) => [
            'id' => $f->id, 'name' => $f->name, 'deleted' => optional($f->deleted_at)->format('d M Y, h:i a'), 'was' => $this->pathTextAny($f->parent_id),
            'files' => Media::onlyTrashed()->whereIn('folder_id', MediaFolder::withTrashed()->find($f->id)->descendantIds())->count(),
        ])->values() : [];

        return response()->json(['items' => $items, 'folders' => $folders, 'page' => $page->currentPage(), 'last' => $page->lastPage(), 'total' => $page->total() + count($folders), 'admin' => StaffAccess::isAdmin($request->user())]);
    }

    /** Put a trashed folder (and the folders above it that are also trashed) back. */
    private function restoreFolderChain(?int $id): ?int
    {
        if (!$id) {
            return null;
        }
        $f = MediaFolder::withTrashed()->find($id);
        if (!$f) {
            return null;                       // gone for good: the file goes to the top of the library
        }
        if ($f->parent_id) {
            $this->restoreFolderChain($f->parent_id);
        }
        if ($f->trashed()) {
            $f->restore();
        }

        return $f->id;
    }

    /** Restore files and / or folders to where they were. A restored folder brings back the folders and files that were deleted inside it. */
    public function restore(Request $request)
    {
        $this->allow($request);
        $data = $request->validate(['ids' => 'nullable|array|max:300', 'ids.*' => 'integer', 'folder_ids' => 'nullable|array|max:100', 'folder_ids.*' => 'integer']);
        $files = 0;
        $folders = 0;
        DB::transaction(function () use ($data, &$files, &$folders) {
            foreach (MediaFolder::onlyTrashed()->whereIn('id', $data['folder_ids'] ?? [])->get() as $f) {
                $this->restoreFolderChain($f->id);
                $folders++;
                $ids = MediaFolder::withTrashed()->find($f->id)->descendantIds();
                $folders += MediaFolder::onlyTrashed()->whereIn('id', $ids)->count();
                MediaFolder::onlyTrashed()->whereIn('id', $ids)->restore();
                $files += Media::onlyTrashed()->whereIn('folder_id', $ids)->count();
                Media::onlyTrashed()->whereIn('folder_id', $ids)->restore();
            }
            foreach (Media::onlyTrashed()->whereIn('id', $data['ids'] ?? [])->get() as $m) {
                $m->folder_id = $this->restoreFolderChain($m->folder_id);
                $m->restore();
                $m->save();
                $files++;
            }
        });

        return response()->json(['ok' => true, 'files' => $files, 'folders' => $folders]);
    }

    /** Delete for good (admin only): the file leaves the disk. Asks for confirmation again if a page still uses a file. */
    public function purge(Request $request)
    {
        $this->allow($request);
        $this->mustBeAdmin($request);
        $data = $request->validate(['confirm' => 'required|in:DELETE', 'ids' => 'nullable|array|max:500', 'ids.*' => 'integer', 'folder_ids' => 'nullable|array|max:200', 'folder_ids.*' => 'integer', 'all' => 'nullable|boolean', 'force' => 'nullable|boolean']);

        $folderIds = [];
        $fileQ = Media::onlyTrashed();
        if ($request->boolean('all')) {
            $folderIds = MediaFolder::onlyTrashed()->pluck('id')->all();
        } else {
            foreach (MediaFolder::onlyTrashed()->whereIn('id', $data['folder_ids'] ?? [])->get() as $f) {
                $folderIds = array_merge($folderIds, MediaFolder::withTrashed()->find($f->id)->descendantIds());
            }
            $fileQ = $fileQ->where(fn ($w) => $w->whereIn('id', $data['ids'] ?? [])->orWhereIn('folder_id', $folderIds ?: [0]));
        }
        $files = $fileQ->get();
        $used = $files->filter(fn ($m) => $this->usage($m) > 0);
        if ($used->count() && !$request->boolean('force')) {
            return response()->json(['message' => $used->count() . ' file(s) are still used on the website (' . $used->pluck('name')->take(3)->implode(', ') . '). Delete them for good anyway? Those pages will lose the picture.', 'needs_confirm' => true], 409);
        }
        $this->purgeFiles($files);
        MediaFolder::onlyTrashed()->whereIn('id', $request->boolean('all') ? $folderIds : array_unique($folderIds))->get()->each->forceDelete();

        return response()->json(['ok' => true, 'files' => $files->count()]);
    }
}
