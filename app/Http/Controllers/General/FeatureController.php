<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Course features, common to every exam type (General > Course Features). Admin only.
 * A feature is offered on all exam types, or only on the ones chosen here.
 */
class FeatureController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user() && $request->user()->type === 'Admin', 403, 'Only an Admin can manage course features.');

            return $next($request);
        });
    }

    public function index()
    {
        $features = ExamFeature::with('exams:id,short_name,name')->orderBy('position')->orderBy('id')->get();
        $usage = DB::table('exam_course_features')->select('feature_id', DB::raw('COUNT(*) as c'))->groupBy('feature_id')->pluck('c', 'feature_id');

        return view('admin.general.features.index', compact('features', 'usage'));
    }

    public function create()
    {
        return view('admin.general.features.form', ['feature' => null, 'exams' => CompetitiveExam::orderBy('position')->get(), 'selectedExams' => []]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $examIds = $data['exam_ids'];
        unset($data['exam_ids']);

        $data['image'] = $request->hasFile('image') ? $request->file('image')->store('exam-features', 'public') : null;
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? null) ?: $data['name']);
        $data['is_system'] = false;

        $feature = ExamFeature::create($data);
        $feature->exams()->sync($examIds);

        return redirect(admin_general('features'))->with('message', 'Feature added. It can now be ticked on courses.');
    }

    public function edit($id)
    {
        $feature = ExamFeature::findOrFail($id);

        return view('admin.general.features.form', [
            'feature' => $feature,
            'exams' => CompetitiveExam::orderBy('position')->get(),
            'selectedExams' => $feature->exams()->pluck('competitive_exams.id')->all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $feature = ExamFeature::findOrFail($id);
        $data = $this->validated($request, $feature);
        $examIds = $data['exam_ids'];
        unset($data['slug'], $data['exam_ids']);   // the slug is used in urls and rules, so it never changes

        if ($request->hasFile('image')) {
            $this->deleteImage($feature->image);
            $data['image'] = $request->file('image')->store('exam-features', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($feature->image);
            $data['image'] = null;
        }

        // changing the kind of a feature that courses already use would orphan their content
        if ($feature->content_kind !== $data['content_kind'] && DB::table('exam_course_features')->where('feature_id', $feature->id)->exists()) {
            return back()->withInput()->withErrors(['content_kind' => 'Courses already use this feature, so its type cannot change.']);
        }

        $feature->update($data);
        $feature->exams()->sync($examIds);

        return redirect(admin_general('features'))->with('message', 'Feature updated');
    }

    public function destroy($id)
    {
        $feature = ExamFeature::findOrFail($id);

        if ($feature->is_system) {
            return back()->with('error', 'Built-in features cannot be deleted. Hide it instead.');
        }
        if (DB::table('exam_course_features')->where('feature_id', $feature->id)->exists()) {
            return back()->with('error', 'Courses use this feature. Remove it from them, or hide it.');
        }

        $this->deleteImage($feature->image);
        $feature->delete();

        return redirect(admin_general('features'))->with('message', 'Feature deleted');
    }

    private function validated(Request $request, ?ExamFeature $feature = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'regex:/^[a-z0-9\-]+$/', 'max:60', Rule::unique('exam_features', 'slug')],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'content_kind' => ['required', Rule::in(array_keys(ExamFeature::KINDS))],
            'exam_scope' => ['required', Rule::in(['all', 'selected'])],
            'exam_ids' => [$request->exam_scope === 'selected' ? 'required' : 'nullable', 'array'],
            'exam_ids.*' => 'integer|exists:competitive_exams,id',
            'position' => 'nullable|integer|min:0',
            'list_limit' => 'nullable|integer|min:0|max:20',
            'season_limit' => 'nullable|integer|min:0|max:20',
            'season_items_limit' => 'nullable|integer|min:0|max:20',
        ], [
            'image.max' => 'The picture can be at most 1 MB.',
            'exam_ids.required' => 'Tick at least one exam type, or choose "All exam types".',
        ]);

        unset($data['image']);   // the upload is handled separately
        $data['description'] = $data['description'] ?? null;
        $data['position'] = $data['position'] ?? 0;
        $data['list_limit'] = $data['list_limit'] ?? 3;
        $data['season_limit'] = $data['season_limit'] ?? 3;
        $data['season_items_limit'] = $data['season_items_limit'] ?? 6;
        // category / subject based exams are not offered; the supports_subjects column stays parked (always off)
        $data['supports_subjects'] = false;
        $data['status'] = $request->boolean('status');
        $data['exam_ids'] = $data['exam_scope'] === 'selected' ? array_values(array_unique($data['exam_ids'] ?? [])) : [];

        return $data;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'feature';
        $try = $slug;
        for ($i = 2; ExamFeature::where('slug', $try)->exists(); $i++) {
            $try = $slug . '-' . $i;
        }

        return $try;
    }
}
