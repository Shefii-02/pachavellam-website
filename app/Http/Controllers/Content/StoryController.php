<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CompetitiveExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stories (admin/stories): the picture slider on the app's home screen.
 * Each story is shown for the exam types ticked on it.
 */
class StoryController extends Controller
{
    public function index(Request $request)
    {
        $this->allow($request, 'Banner Slider list');

        $list = Banner::with('exams:id,short_name,name,color')
            ->when($request->filled('exam'), fn ($q) => $q->forExam($request->exam))
            ->when($request->filled('status') && in_array($request->status, ['0', '1'], true), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('position')->orderByDesc('id')->paginate(24)->withQueryString();

        // stories synced from the Instagram highlights live here too
        $achievements = \App\Models\ContentItem::with('exams:id,short_name,name,color')->ofKind('achievement')
            ->when($request->filled('exam'), fn ($q) => $q->where(fn ($w) => $w->forExam($request->exam)->orDoesntHave('exams')))
            ->when($request->filled('status') && in_array($request->status, ['0', '1'], true), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('position')->orderByDesc('id')->get();

        return view('admin.content.story.index', ['list' => $list, 'achievements' => $achievements, 'exams' => CompetitiveExam::orderBy('position')->get()]);
    }

    public function create(Request $request)
    {
        $this->allow($request, 'Banner Slider Add');

        return view('admin.content.story.form', $this->formData(null, $request->exam));
    }

    public function store(Request $request)
    {
        $this->allow($request, 'Banner Slider Add');
        $data = $this->validated($request, null);

        $story = new Banner();
        $story->image = $this->saveImage($request->file('image'));
        $story->redirection = $data['redirection'];
        [$story->target_type, $story->target_value] = \App\Support\Landing::fromRequest($request);
        $story->status = $request->boolean('status') ? 1 : 0;
        $story->author_id = $request->user()->id;
        $story->save();

        $story->position = $data['position'] ?? $story->id;
        $story->save();
        $story->exams()->sync($data['exam_ids']);

        return redirect(url('admin/stories'))->with('message', 'Story added');
    }

    public function edit(Request $request, $id)
    {
        $this->allow($request, 'Banner Slider Edit');

        return view('admin.content.story.form', $this->formData(Banner::findOrFail($id), null));
    }

    public function update(Request $request, $id)
    {
        $this->allow($request, 'Banner Slider Edit');
        $story = Banner::findOrFail($id);
        $data = $this->validated($request, $story);

        if ($request->hasFile('image')) {
            $this->deleteImage($story->image);
            $story->image = $this->saveImage($request->file('image'));
        }
        $story->redirection = $data['redirection'];
        [$story->target_type, $story->target_value] = \App\Support\Landing::fromRequest($request);
        $story->position = $data['position'] ?? $story->position;
        $story->status = $request->boolean('status') ? 1 : 0;
        $story->save();
        $story->exams()->sync($data['exam_ids']);

        return redirect(url('admin/stories'))->with('message', 'Story updated');
    }

    public function destroy(Request $request, $id)
    {
        $this->allow($request, 'Banner Slider Delete');
        $story = Banner::findOrFail($id);

        $this->deleteImage($story->image);
        $story->exams()->detach();
        $story->delete();

        return redirect(url('admin/stories'))->with('message', 'Story deleted');
    }

    public function toggle(Request $request, $id)
    {
        $this->allow($request, 'Banner Slider Edit');
        $story = Banner::findOrFail($id);
        $story->status = $story->status ? 0 : 1;
        $story->save();

        return back()->with('message', $story->status ? 'Story is now shown' : 'Story is now hidden');
    }

    // ------------------------------------------------------------- helpers

    private function allow(Request $request, string $permission): void
    {
        abort_unless($request->user() && $request->user()->can($permission), 403, 'You do not have permission for this.');
    }

    private function formData(?Banner $story, $preselect): array
    {
        $exams = CompetitiveExam::orderBy('position')->get();
        $chosen = $story ? $story->exams()->pluck('competitive_exams.id')->all()
            : $exams->filter(fn ($e) => $e->slug === $preselect)->pluck('id')->all();

        return ['story' => $story, 'exams' => $exams, 'chosen' => $chosen];
    }

    private function validated(Request $request, ?Banner $story): array
    {
        $data = $request->validate([
            'image' => [$story ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'redirection' => 'nullable|string|max:500',
            'position' => 'nullable|integer|min:0',
            'exam_ids' => 'required|array|min:1',
            'exam_ids.*' => 'integer|exists:competitive_exams,id',
        ], ['exam_ids.required' => 'Tick at least one exam type this story is for.', 'exam_ids.min' => 'Tick at least one exam type this story is for.']);

        $data['exam_ids'] = array_values(array_unique(array_map('intval', $data['exam_ids'])));

        return $data;
    }

    /** Same place and name style the app already reads: public/files/banner/<name>, stored as "banner/<name>". */
    private function saveImage($file): string
    {
        $name = 'Banner-' . Str::random(40) . '.' . $file->extension();
        $file->storeAs('files/banner', $name, 'public');

        return 'banner/' . $name;
    }

    private function deleteImage(?string $image): void
    {
        if ($image) {
            Storage::disk('public')->delete('files/' . $image);
        }
    }
}
