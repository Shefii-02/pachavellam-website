<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Categories and sub categories of an exam type: admin/exam/{exam}/category
 * Every exam type has its own tree (KPSC: uniformed / assistant / typist ...; KTET, UGC NET: subjects ...).
 * Any child can itself be a parent.
 */
class CategoryController extends Controller
{
    use ResolvesExam;

    private function baseUrl(CompetitiveExam $exam, string $path = ''): string
    {
        return $exam->examUrl('category' . ($path !== '' ? '/' . $path : ''));
    }

    private function viewData(CompetitiveExam $exam, array $extra = []): array
    {
        return array_merge([
            'exam' => $exam,                       // the generic exam sidebar needs it
            'layout' => $exam->layout(),
            'moduleLabel' => $this->examLabel($exam),
            'baseUrl' => $this->baseUrl($exam),
        ], $extra);
    }

    private function find(CompetitiveExam $exam, $id): ExamCategory
    {
        return ExamCategory::where('exam_id', $exam->id)->findOrFail($id);
    }

    /** Children of ?parent=ID (or the roots), with breadcrumb. */
    public function index(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $parent = $request->filled('parent') ? $this->find($exam, $request->parent) : null;

        $list = ExamCategory::where('exam_id', $exam->id)
            ->where('parent_id', $parent?->id)
            ->withCount('children')
            ->orderBy('position')->orderBy('id')
            ->get();

        return view('admin.exam-category.index', $this->viewData($exam, [
            'list' => $list,
            'parent' => $parent,
            'breadcrumb' => $parent ? $parent->ancestors()->push($parent) : collect(),
        ]));
    }

    public function create(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);

        return view('admin.exam-category.form', $this->viewData($exam, [
            'category' => null,
            'parentOptions' => ExamCategory::optionsFor($exam->id),
            'selectedParent' => $request->parent,
        ]));
    }

    public function store(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $data = $this->validated($request, $exam);

        $data['exam_id'] = $exam->id;
        $data['slug'] = $this->uniqueSlug($exam, $data['name']);
        $data['depth'] = $this->depthFor($exam, $data['parent_id'] ?? null);
        $data['created_by'] = auth()->id();
        $data['image'] = $request->hasFile('image') ? $request->file('image')->store('exam-categories/' . $exam->slug, 'public') : null;

        $category = ExamCategory::create($data);

        return redirect($this->baseUrl($exam) . ($category->parent_id ? '?parent=' . $category->parent_id : ''))
            ->with('message', 'Category created successfully');
    }

    public function edit($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $category = $this->find($exam, $id);

        return view('admin.exam-category.form', $this->viewData($exam, [
            'category' => $category,
            'parentOptions' => ExamCategory::optionsFor($exam->id, array_merge([$category->id], $category->descendantIds())),
            'selectedParent' => $category->parent_id,
        ]));
    }

    public function update(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $category = $this->find($exam, $id);
        $data = $this->validated($request, $exam);

        // a category can not be moved under itself or one of its own children
        if (!empty($data['parent_id']) &&
            ($data['parent_id'] == $category->id || in_array($data['parent_id'], $category->descendantIds()))) {
            return back()->withInput()->with('error', 'A category cannot be moved under itself or its own child.');
        }

        $data['depth'] = $this->depthFor($exam, $data['parent_id'] ?? null);
        $parentChanged = $category->parent_id != ($data['parent_id'] ?? null);

        if ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($exam, $data['name'], $category->id);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $data['image'] = $request->file('image')->store('exam-categories/' . $exam->slug, 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($category->image);
            $data['image'] = null;
        }

        $category->update($data);

        if ($parentChanged) {
            $this->refreshDepths($category);
        }

        return redirect($this->baseUrl($exam) . ($category->parent_id ? '?parent=' . $category->parent_id : ''))
            ->with('message', 'Category updated successfully');
    }

    /** Deletes the category and (via foreign key cascade) all of its children. */
    public function destroy($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $category = $this->find($exam, $id);
        $parentId = $category->parent_id;

        ExamCategory::whereIn('id', $category->descendantIds())->pluck('image')->push($category->image)
            ->each(fn ($path) => $this->deleteImage($path));

        $category->delete();

        return redirect($this->baseUrl($exam) . ($parentId ? '?parent=' . $parentId : ''))
            ->with('message', 'Category deleted successfully');
    }

    private function validated(Request $request, CompetitiveExam $exam): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // the parent has to be a category of the same exam type
            'parent_id' => ['nullable', 'integer', Rule::exists('exam_categories', 'id')->where('exam_id', $exam->id)],
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $data['parent_id'] = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;   // "None" arrives as an empty string
        unset($data['image']);                                                                // handled separately (file upload)
        $data['description'] = $data['description'] ?? null;
        $data['meta_title'] = $data['meta_title'] ?? null;
        $data['meta_description'] = $data['meta_description'] ?? null;
        $data['position'] = $data['position'] ?? 0;
        $data['status'] = $request->boolean('status');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }

    private function uniqueSlug(CompetitiveExam $exam, string $name, $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;

        for ($i = 2; ExamCategory::where('exam_id', $exam->id)->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    private function depthFor(CompetitiveExam $exam, $parentId): int
    {
        return $parentId ? ExamCategory::where('exam_id', $exam->id)->find($parentId)->depth + 1 : 0;
    }

    private function refreshDepths(ExamCategory $category): void
    {
        foreach ($category->children as $child) {
            $child->update(['depth' => $category->depth + 1]);
            $this->refreshDepths($child);
        }
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
