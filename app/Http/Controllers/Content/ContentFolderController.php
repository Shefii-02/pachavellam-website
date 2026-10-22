<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ContentFolder;
use App\Models\ContentItem;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** Parent and child folders of Syllabus, Study Materials and Results (admin/content/{section}/folders), per exam type. */
class ContentFolderController extends Controller
{
    private function kind(string $section): string
    {
        $m = ContentItem::KINDS[$section] ?? null;
        abort_unless($m && !empty($m['folders']), 404);

        return $m['kind'];
    }

    private function allow(Request $request, CompetitiveExam $exam): void
    {
        StaffAccess::authorize($request, 'Content manage');
        $allowed = StaffAccess::examIds($request->user());
        abort_unless($allowed === null || in_array($exam->id, $allowed, true), 403, 'You do not have access to this exam type.');
    }

    public function store(Request $request, string $section)
    {
        $kind = $this->kind($section);
        $data = $request->validate(['exam' => 'required|string', 'title' => 'required|string|max:150', 'parent_id' => 'nullable|integer']);
        $exam = CompetitiveExam::where('slug', $data['exam'])->firstOrFail();
        $this->allow($request, $exam);

        $parent = null;
        if (!empty($data['parent_id'])) {
            $parent = ContentFolder::where('exam_id', $exam->id)->where('kind', $kind)->findOrFail($data['parent_id']);
            abort_if(count($parent->trail()) >= 6, 422, 'Folders can go up to 6 levels deep.');
        }
        $name = trim($data['title']);
        if (ContentFolder::where('exam_id', $exam->id)->where('kind', $kind)->where('parent_id', $parent?->id)->where('title', $name)->exists()) {
            return back()->withErrors(['title' => 'A folder with this name already exists here.']);
        }
        $f = ContentFolder::create(['exam_id' => $exam->id, 'kind' => $kind, 'parent_id' => $parent?->id, 'title' => $name, 'created_by' => $request->user()->id]);
        $f->update(['position' => $f->id]);

        return redirect(url('admin/content/' . $section . '?exam=' . $exam->slug . ($parent ? '&folder=' . $parent->id : '')))->with('message', 'Folder added');
    }

    public function update(Request $request, string $section, $id)
    {
        $kind = $this->kind($section);
        $f = ContentFolder::where('kind', $kind)->findOrFail($id);
        $this->allow($request, $f->exam);
        $data = $request->validate(['title' => 'required|string|max:150', 'parent_id' => 'nullable|integer']);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = ContentFolder::where('exam_id', $f->exam_id)->where('kind', $kind)->findOrFail($parentId);
            abort_if(in_array($parent->id, $f->descendantIds(), true), 422, 'A folder cannot be moved inside itself.');
        }
        $f->update(['title' => trim($data['title']), 'parent_id' => $parentId]);

        return redirect(url('admin/content/' . $section . '?exam=' . $f->exam->slug . ($parentId ? '&folder=' . $parentId : '')))->with('message', 'Folder saved');
    }

    /** Deleting a folder keeps what is inside: its items and sub folders move up one level. */
    public function destroy(Request $request, string $section, $id)
    {
        $kind = $this->kind($section);
        $f = ContentFolder::where('kind', $kind)->findOrFail($id);
        $this->allow($request, $f->exam);

        ContentItem::where('folder_id', $f->id)->update(['folder_id' => $f->parent_id]);
        ContentFolder::where('parent_id', $f->id)->update(['parent_id' => $f->parent_id]);
        $slug = $f->exam->slug;
        $up = $f->parent_id;
        $f->delete();

        return redirect(url('admin/content/' . $section . '?exam=' . $slug . ($up ? '&folder=' . $up : '')))->with('message', 'Folder deleted - its content moved up one level');
    }
}
