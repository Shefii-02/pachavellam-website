<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Capsule;
use App\Models\CapsuleComment;
use App\Models\CapsuleLike;
use App\Models\CompetitiveExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Capsule (admin/capsule): picture or text cards shown in the app feed.
 * Each capsule is shown for the exam types ticked on it.
 */
class CapsuleController extends Controller
{
    public function index(Request $request)
    {
        $this->allow($request, 'Capsule list');

        $list = Capsule::with(['exams:id,short_name,name,color', 'author:id,name'])
            ->withCount(['likes', 'comments'])
            ->when($request->filled('exam'), fn ($q) => $q->forExam($request->exam))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status') && in_array($request->status, ['0', '1'], true), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('content', 'like', '%' . $request->q . '%')->orWhere('tag', 'like', '%' . $request->q . '%')))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.content.capsule.index', ['list' => $list, 'exams' => CompetitiveExam::orderBy('position')->get()]);
    }

    public function create(Request $request)
    {
        $this->allow($request, 'Capsule add');

        return view('admin.content.capsule.form', $this->formData(null, $request->exam));
    }

    public function store(Request $request)
    {
        $this->allow($request, 'Capsule add');
        $data = $this->validated($request, null);

        $capsule = new Capsule();
        $capsule->type = $data['type'];
        $capsule->tag = $data['tag'] ?? null;
        $capsule->author_id = $request->user()->id;
        $capsule->status = $request->boolean('status') ? 1 : 0;
        $capsule->content = $data['type'] === 'Content' ? $data['content'] : null;
        if ($data['type'] === 'Image') {
            $capsule->image = $this->saveImage($request->file('image'));
        }
        $capsule->save();

        $capsule->position = $data['position'] ?? $capsule->id;
        $capsule->save();
        $capsule->exams()->sync($data['exam_ids']);

        return redirect(url('admin/capsule'))->with('message', 'Capsule added');
    }

    public function edit(Request $request, $id)
    {
        $this->allow($request, 'Capsule edit');

        return view('admin.content.capsule.form', $this->formData(Capsule::findOrFail($id), null));
    }

    public function update(Request $request, $id)
    {
        $this->allow($request, 'Capsule edit');
        $capsule = Capsule::findOrFail($id);
        $data = $this->validated($request, $capsule);

        if ($capsule->type === 'Image' && $request->hasFile('image')) {
            $this->deleteImage($capsule->image);
            $capsule->image = $this->saveImage($request->file('image'));
        }
        if ($capsule->type === 'Content') {
            $capsule->content = $data['content'];
        }
        $capsule->tag = $data['tag'] ?? null;
        $capsule->position = $data['position'] ?? $capsule->position;
        $capsule->status = $request->boolean('status') ? 1 : 0;
        $capsule->save();
        $capsule->exams()->sync($data['exam_ids']);

        return redirect(url('admin/capsule'))->with('message', 'Capsule updated');
    }

    public function destroy(Request $request, $id)
    {
        $this->allow($request, 'Capsule delete');
        $capsule = Capsule::findOrFail($id);

        $this->deleteImage($capsule->image);
        CapsuleComment::where('cap_id', $capsule->id)->delete();
        CapsuleLike::where('cap_id', $capsule->id)->delete();
        $capsule->exams()->detach();
        $capsule->delete();

        return redirect(url('admin/capsule'))->with('message', 'Capsule deleted');
    }

    public function toggle(Request $request, $id)
    {
        $this->allow($request, 'Capsule edit');
        $capsule = Capsule::findOrFail($id);
        $capsule->status = $capsule->status ? 0 : 1;
        $capsule->save();

        return back()->with('message', $capsule->status ? 'Capsule is now shown' : 'Capsule is now hidden');
    }

    // ------------------------------------------------------------- helpers

    private function allow(Request $request, string $permission): void
    {
        abort_unless($request->user() && $request->user()->can($permission), 403, 'You do not have permission for this.');
    }

    private function formData(?Capsule $capsule, $preselect): array
    {
        $exams = CompetitiveExam::orderBy('position')->get();
        $chosen = $capsule ? $capsule->exams()->pluck('competitive_exams.id')->all()
            : $exams->filter(fn ($e) => $e->slug === $preselect)->pluck('id')->all();

        return ['capsule' => $capsule, 'exams' => $exams, 'chosen' => $chosen];
    }

    private function validated(Request $request, ?Capsule $capsule): array
    {
        $type = $capsule ? $capsule->type : $request->input('type');

        $data = $request->validate([
            'type' => $capsule ? 'nullable' : ['required', Rule::in(['Image', 'Content'])],
            'image' => [$type === 'Image' && !$capsule ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'content' => [$type === 'Content' ? 'required' : 'nullable', 'string'],
            'tag' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'exam_ids' => 'required|array|min:1',
            'exam_ids.*' => 'integer|exists:competitive_exams,id',
        ], ['exam_ids.required' => 'Tick at least one exam type this capsule is for.', 'exam_ids.min' => 'Tick at least one exam type this capsule is for.']);

        $data['type'] = $type;
        $data['exam_ids'] = array_values(array_unique(array_map('intval', $data['exam_ids'])));

        return $data;
    }

    /** Same place and name style the app already reads: public/files/capsule/<name>, stored as "capsule/<name>". */
    private function saveImage($file): string
    {
        $name = 'Capsule-' . Str::random(40) . '.' . $file->extension();
        $file->storeAs('files/capsule', $name, 'public');

        return 'capsule/' . $name;
    }

    private function deleteImage(?string $image): void
    {
        if ($image) {
            Storage::disk('public')->delete('files/' . $image);
        }
    }
}
