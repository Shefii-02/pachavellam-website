<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ExamContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD for the content sections (banners, whats-new, announcements, ...) of an exam module.
 * The section definitions live in config/exam_sections.php.
 * The using controller defines: $moduleSlug, $moduleLabel, $moduleFolder. Category fields use the categories of that exam type.
 */
trait ManagesExamSections
{
    private function sectionConfig(string $section): array
    {
        abort_unless(array_key_exists($section, config('exam_sections')), 404);

        return config('exam_sections.' . $section);
    }

    private function sectionModel(string $section): ExamContent
    {
        return ExamContent::forTable($this->moduleFolder . '_' . str_replace('-', '_', $section));
    }

    private function sectionUrl(string $section, string $path = ''): string
    {
        return url('admin/' . $this->moduleSlug . '/' . $section . ($path ? '/' . $path : ''));
    }

    private function sectionViewData(string $section, array $extra = []): array
    {
        return array_merge([
            'layout' => 'admin.' . $this->moduleFolder . '.section',
            'moduleLabel' => $this->moduleLabel,
            'section' => $section,
            'config' => $this->sectionConfig($section),
            'baseUrl' => $this->sectionUrl($section),
        ], $extra);
    }

    public function index(Request $request, $section)
    {
        $config = $this->sectionConfig($section);
        $query = $this->sectionModel($section)->newQuery();

        $textField = collect($config['fields'])->firstWhere('type', 'text')['name'] ?? 'id';
        if ($request->filled('q')) {
            $query->where($textField, 'like', '%' . $request->q . '%');
        }

        $categoryField = collect($config['fields'])->firstWhere('type', 'category')['name'] ?? null;
        if ($categoryField && $request->filled('category')) {
            $query->where($categoryField, $request->category);
        }

        $list = $query->orderBy('position')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.exam-content.index', $this->sectionViewData($section, [
            'list' => $list,
            'categoryField' => $categoryField,
            'categoryOptions' => $categoryField ? $this->sectionCategoryOptions() : [],
        ]));
    }

    public function create($section)
    {
        return view('admin.exam-content.form', $this->sectionViewData($section, [
            'row' => null,
            'categoryOptions' => $this->sectionCategoryOptions(),
        ]));
    }

    public function store(Request $request, $section)
    {
        $config = $this->sectionConfig($section);
        $data = $request->validate($this->sectionRules($config));
        $data = $this->sectionPrepare($request, $config, $section, $data);
        $data['created_by'] = auth()->id();

        $this->sectionModel($section)->newQuery()->create($data);

        return redirect($this->sectionUrl($section))->with('message', $config['label'] . ' added successfully');
    }

    public function edit($section, $id)
    {
        $row = $this->sectionModel($section)->newQuery()->findOrFail($id);

        return view('admin.exam-content.form', $this->sectionViewData($section, [
            'row' => $row,
            'categoryOptions' => $this->sectionCategoryOptions(),
        ]));
    }

    public function update(Request $request, $section, $id)
    {
        $config = $this->sectionConfig($section);
        $row = $this->sectionModel($section)->newQuery()->findOrFail($id);

        $data = $request->validate($this->sectionRules($config, $row));
        $data = $this->sectionPrepare($request, $config, $section, $data, $row);

        $row->update($data);

        return redirect($this->sectionUrl($section))->with('message', $config['label'] . ' updated successfully');
    }

    public function destroy($section, $id)
    {
        $config = $this->sectionConfig($section);
        $row = $this->sectionModel($section)->newQuery()->findOrFail($id);

        foreach ($config['fields'] as $f) {
            if (in_array($f['type'], ['image', 'file'])) {
                $this->sectionDeleteFile($row->{$f['name']});
            }
        }
        $row->delete();

        return redirect($this->sectionUrl($section))->with('message', 'Deleted successfully');
    }

    private function sectionRules(array $config, $row = null): array
    {
        $rules = [
            'position' => 'nullable|integer|min:0',
        ];

        foreach ($config['fields'] as $f) {
            $name = $f['name'];
            $required = !empty($f['required']);

            // an existing file/image satisfies "required" when editing
            if ($required && in_array($f['type'], ['image', 'file']) && $row && $row->{$name}) {
                $required = false;
            }

            $rule = match ($f['type']) {
                'text' => 'string|max:255',
                'url' => 'url|max:500',
                'textarea' => 'string',
                'number' => 'integer|min:0',
                'date' => 'date',
                'select' => 'in:' . implode(',', array_keys($f['options'])),
                'image' => 'image|mimes:jpg,jpeg,png,webp,svg|max:4096',
                'file' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png|max:20480',
                'category' => ['integer', \Illuminate\Validation\Rule::exists('exam_categories', 'id')->where('exam_id', $this->sectionExamId())],
                'checkbox' => 'boolean',
                default => 'string',
            };

            // string rules ("a|b") and rule objects can be mixed, so build an array
            $rules[$name] = array_merge([$required ? 'required' : 'nullable'], is_array($rule) ? $rule : explode('|', $rule));
        }

        return $rules;
    }

    /** Handles uploads, checkboxes, status and position. */
    private function sectionPrepare(Request $request, array $config, string $section, array $data, $row = null): array
    {
        foreach ($config['fields'] as $f) {
            $name = $f['name'];

            if (in_array($f['type'], ['image', 'file'])) {
                unset($data[$name]);

                if ($request->hasFile($name)) {
                    if ($row) {
                        $this->sectionDeleteFile($row->{$name});
                    }
                    $data[$name] = $request->file($name)->store(
                        'exam-content/' . $this->moduleFolder . '/' . str_replace('-', '_', $section),
                        'public'
                    );
                } elseif ($row && $request->boolean('remove_' . $name)) {
                    $this->sectionDeleteFile($row->{$name});
                    $data[$name] = null;
                }
            } elseif ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            }
        }

        $data['position'] = $data['position'] ?? 0;
        $data['status'] = $request->boolean('status');

        return $data;
    }

    private function sectionDeleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function sectionExamId(): int
    {
        return (int) \App\Models\CompetitiveExam::where('slug', $this->moduleSlug)->value('id');
    }

    /** Flat, indented category list of this exam type for the category dropdown / filter. */
    private function sectionCategoryOptions(): array
    {
        return \App\Models\ExamCategory::optionsFor($this->sectionExamId());
    }
}
