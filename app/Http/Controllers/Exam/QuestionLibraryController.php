<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\CourseExam;
use App\Models\ExamCategory;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use App\Services\CourseExamBuilder;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Question library of an exam type: admin/exam/{exam}/question-library
 * Folders and sub folders (any depth) hold questions; questions are added by hand here or imported (QuestionImportController).
 */
class QuestionLibraryController extends Controller
{
    use ResolvesExam;

    private function folder(CompetitiveExam $exam, $id): QuestionFolder
    {
        return QuestionFolder::where('exam_id', $exam->id)->findOrFail($id);
    }

    private function question(CompetitiveExam $exam, $id): LibraryQuestion
    {
        return LibraryQuestion::where('exam_id', $exam->id)->findOrFail($id);
    }

    private function data(CompetitiveExam $exam, array $extra = []): array
    {
        return array_merge([
            'exam' => $exam,
            'layout' => $exam->layout(),
            'base' => $exam->examUrl('question-library'),
        ], $extra);
    }

    // ================================================================ browse

    public function index(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $current = $request->filled('folder') ? $this->folder($exam, $request->folder) : null;

        $flat = QuestionFolder::flatTree($exam->id);
        $direct = LibraryQuestion::where('exam_id', $exam->id)->select('folder_id', DB::raw('count(*) as c'))->groupBy('folder_id')->pluck('c', 'folder_id');

        // questions in a folder including everything below it
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

        $questions = LibraryQuestion::where('exam_id', $exam->id)
            ->when($folderIds, fn ($q) => $q->whereIn('folder_id', $folderIds))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('exam_categories.id', $request->category)))
            ->when($request->filled('lang'), fn ($q) => $q->whereHas('texts', fn ($t) => $t->where('lang', $request->lang)))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('texts', fn ($t) => $t->where('question', 'like', '%' . $request->q . '%')))
            ->when($request->input('images') === '1', fn ($q) => $q->where(fn ($w) => $w->whereNotNull('question_image')->orWhereNotNull('option_images')->orWhereNotNull('explanation_image')))
            ->with(['texts', 'categories:id,name', 'folder:id,name'])
            ->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.question-library.index', $this->data($exam, [
            'current' => $current,
            'breadcrumb' => $current ? $current->ancestors()->push($current) : collect(),
            'tree' => $flat,
            'direct' => $direct,
            'total' => $total,
            'subfolders' => collect($flat)->where('parent_id', $current?->id)->values(),
            'questions' => $questions,
            'deep' => $deep,
            'categories' => ExamCategory::optionsFor($exam->id),
            'languages' => config('question_library.languages'),
            'unfiled' => 0,
        ]));
    }

    /** Copies the old daily / CA daily / model exam questions into the library (KPSC; they are also copied as they are saved). */
    public function syncDailyExams(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        abort_unless($exam->slug === 'kpsc' && $request->user()->type === 'Admin', 403, 'Only an Admin can do this, for KPSC.');

        @set_time_limit(600);
        $stats = (new \App\Services\LegacyQuestionSync())->syncAll();

        return back()->with('success', "Daily exam questions synced: {$stats['created']} new, {$stats['updated']} updated"
            . ($stats['no_answer'] ? ", {$stats['no_answer']} left out (the saved answer matches none of the options)." : '.'));
    }

    // ================================================================ folders

    public function storeFolder(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ]);

        $parent = !empty($data['parent_id']) ? $this->folder($exam, $data['parent_id']) : null;
        $this->abortIfNameTaken($exam, $parent?->id, $data['name']);

        $folder = QuestionFolder::create([
            'exam_id' => $exam->id, 'parent_id' => $parent?->id, 'name' => trim($data['name']),
            'description' => $data['description'] ?? null, 'created_by' => $request->user()->id,
            'position' => (int) QuestionFolder::where('exam_id', $exam->id)->where('parent_id', $parent?->id)->max('position') + 1,
        ]);

        return redirect($this->data($exam)['base'] . '?folder=' . $folder->id)->with('success', 'Folder created.');
    }

    public function updateFolder(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $folder = $this->folder($exam, $id);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer',
        ]);

        $newParent = !empty($data['parent_id']) ? $this->folder($exam, $data['parent_id']) : null;
        if ($newParent && ($newParent->id === $folder->id || in_array($newParent->id, $folder->descendantIds(), true))) {
            return back()->withErrors(['parent_id' => 'A folder cannot be moved inside itself or one of its own sub folders.']);
        }
        $this->abortIfNameTaken($exam, $newParent?->id, $data['name'], $folder->id);

        $folder->update(['name' => trim($data['name']), 'description' => $data['description'] ?? null, 'parent_id' => $newParent?->id]);

        return redirect($this->data($exam)['base'] . '?folder=' . $folder->id)->with('success', 'Folder saved.');
    }

    public function destroyFolder(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $folder = $this->folder($exam, $id);
        $ids = array_merge([$folder->id], $folder->descendantIds());

        $count = LibraryQuestion::withTrashed()->whereIn('folder_id', $ids)->count();   // items in Trash still sit in the folder
        if ($count > 0) {
            return back()->withErrors(['folder' => "This folder (with its sub folders) still has {$count} question(s). Move or delete them first."]);
        }

        $parentId = $folder->parent_id;
        $folder->delete();   // sub folders go with it (cascade)

        return redirect($this->data($exam)['base'] . ($parentId ? '?folder=' . $parentId : ''))->with('success', 'Folder deleted.');
    }

    private function abortIfNameTaken(CompetitiveExam $exam, ?int $parentId, string $name, ?int $ignore = null): void
    {
        $taken = QuestionFolder::where('exam_id', $exam->id)->where('parent_id', $parentId)
            ->where('name', trim($name))->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->exists();

        if ($taken) {
            throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'A folder with this name already exists here.']);
        }
    }

    // =============================================================== questions

    public function createQuestion(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);

        return view('admin.question-library.question-form', $this->formData($exam, null, $request->folder, $request));
    }

    public function editQuestion(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $question = $this->question($exam, $id)->load('texts', 'categories');

        return view('admin.question-library.question-form', $this->formData($exam, $question, $question->folder_id, $request));
    }

    private function formData(CompetitiveExam $exam, ?LibraryQuestion $question, $folderId, Request $request): array
    {
        // adding from a course exam: the question is placed in that exam's section and we return there afterwards
        $attach = $request->filled('attach') ? CourseExam::where('exam_id', $exam->id)->find($request->attach) : null;

        return $this->data($exam, [
            'question' => $question,
            'folderId' => $folderId,
            'tree' => QuestionFolder::flatTree($exam->id),
            'categories' => ExamCategory::optionsFor($exam->id),
            'languages' => config('question_library.languages'),
            'attach' => $attach,
            'sectionId' => $request->section,
            'back' => $this->safeBack($request->input('back')),
        ]);
    }

    /** Only addresses inside the admin may be used as "go back to". */
    private function safeBack(?string $url): ?string
    {
        return ($url && str_starts_with($url, url('admin') . '/')) ? $url : null;
    }

    public function storeQuestion(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $attach = $request->filled('attach') ? CourseExam::where('exam_id', $exam->id)->findOrFail($request->attach) : null;
        $data = $this->validated($request, $exam, (bool) $attach);

        $question = DB::transaction(function () use ($request, $exam, $data, $attach) {
            $folderId = $data['folder_id'] ?? CourseExamBuilder::autoFolder($attach)->id;
            $q = LibraryQuestion::create([
                'exam_id' => $exam->id, 'folder_id' => $folderId, 'correct_option' => $data['correct_option'],
                'question_type' => $data['question_type'], 'correct_options' => $data['correct_options'], 'is_rich' => true,
                'difficulty' => $data['difficulty'] ?? null, 'source' => 'manual', 'created_by' => $request->user()->id,
                'question_hash' => $data['hash'],
            ]);
            $this->saveTexts($q, $data['texts']);
            $q->categories()->sync($data['category_ids']);
            $this->saveImages($request, $q, $exam);
            if ($attach) {
                CourseExamBuilder::attach($attach, $request->input('section'), [$q->id]);
            }

            return $q;
        });

        if ($back = $this->safeBack($request->input('back'))) {
            return redirect($back)->with('success', 'Question added' . ($attach ? ' to the exam and saved in the question library.' : '.'));
        }

        return redirect($this->data($exam)['base'] . '?folder=' . $question->folder_id)->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $question = $this->question($exam, $id);
        $data = $this->validated($request, $exam);

        DB::transaction(function () use ($request, $exam, $question, $data) {
            $question->update([
                'folder_id' => $data['folder_id'], 'correct_option' => $data['correct_option'],
                'question_type' => $data['question_type'], 'correct_options' => $data['correct_options'], 'is_rich' => true,
                'difficulty' => $data['difficulty'] ?? null, 'question_hash' => $data['hash'],
            ]);
            $this->saveTexts($question, $data['texts']);
            $question->categories()->sync($data['category_ids']);
            $this->saveImages($request, $question, $exam);
        });

        if ($back = $this->safeBack($request->input('back'))) {
            return redirect($back)->with('success', 'Question saved.');
        }

        return redirect($this->data($exam)['base'] . '?folder=' . $question->folder_id)->with('success', 'Question saved.');
    }

    public function destroyQuestion(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $question = $this->question($exam, $id);
        $folderId = $question->folder_id;

        $this->deleteQuestions(collect([$question]));

        return redirect($this->data($exam)['base'] . '?folder=' . $folderId)->with('success', 'Question deleted.');
    }

    /** Move or delete several questions at once. */
    public function bulk(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $request->validate(['action' => 'required|in:move,delete', 'ids' => 'required|array|min:1', 'ids.*' => 'integer']);

        $questions = LibraryQuestion::where('exam_id', $exam->id)->whereIn('id', $request->ids)->get();

        if ($request->action === 'move') {
            $target = $this->folder($exam, $request->validate(['folder_id' => 'required|integer'])['folder_id']);
            LibraryQuestion::whereIn('id', $questions->pluck('id'))->update(['folder_id' => $target->id]);
            $message = $questions->count() . ' question(s) moved to "' . $target->name . '".';
        } else {
            $this->deleteQuestions($questions);
            $message = $questions->count() . ' question(s) deleted.';
        }

        return back()->with('success', $message);
    }

    public static function deleteQuestions($questions): void
    {
        foreach ($questions as $q) {
            $q->delete();   // goes to Trash with its pictures; Trash removes them for good
        }
    }

    // ---------------------------------------------------------------- helpers

    private function validated(Request $request, CompetitiveExam $exam, bool $folderOptional = false): array
    {
        $max = LibraryQuestion::MAX_OPTIONS;
        $request->validate([
            'folder_id' => ($folderOptional ? 'nullable' : 'required') . '|integer',
            'question_type' => 'required|in:' . implode(',', array_keys(LibraryQuestion::TYPES)),
            'option_count' => "nullable|integer|between:2,$max",
            'correct_option' => "nullable|integer|between:1,$max",
            'correct_options' => 'nullable|array',
            'correct_options.*' => "integer|between:1,$max",
            'difficulty' => 'nullable|in:easy,medium,hard',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer',
            'question_image' => 'nullable|image|max:3072',
            'explanation_image' => 'nullable|image|max:3072',
            'option_images.*' => 'nullable|image|max:3072',
            'texts' => 'nullable|array',
        ], ['question_image.image' => 'The question picture must be an image (jpg, png, gif, webp) up to 3 MB.']);

        $type = $request->question_type;
        $count = $type === 'truefalse' ? 2 : max(2, min($max, (int) $request->input('option_count', 4)));
        $folder = $request->filled('folder_id') ? $this->folder($exam, $request->folder_id) : null;
        $categoryIds = ExamCategory::where('exam_id', $exam->id)->whereIn('id', $request->input('category_ids', []))->pluck('id')->all();

        $texts = [];
        foreach (array_keys(config('question_library.languages')) as $lang) {
            $t = $request->input("texts.$lang", []);
            $options = [];
            for ($n = 1; $n <= $max; $n++) {
                $options[$n] = $n <= $count ? trim((string) ($t["option_$n"] ?? '')) : '';
            }
            $row = [
                'question' => SafeHtml::clean($t['question'] ?? ''),
                'options' => $options,
                'explanation' => SafeHtml::clean($t['explanation'] ?? ''),
            ];
            $filled = SafeHtml::text($row['question']) !== '' || SafeHtml::text($row['explanation']) !== '' || array_filter($options);
            if ($filled) {
                if ($type === 'truefalse') {
                    $row['options'] = [1 => $lang === 'ml' ? 'ശരി' : 'True', 2 => $lang === 'ml' ? 'തെറ്റ്' : 'False', 3 => '', 4 => '', 5 => '', 6 => ''];
                }
                $texts[$lang] = $row;
            }
        }

        $errors = [];
        $questionPicture = $request->hasFile('question_image') || $this->existing($request, 'question_image');
        if (!$texts && !$questionPicture) {
            $errors['texts'] = 'Write the question in at least one language.';
        }
        $main = $texts ? reset($texts) : null;
        $present = fn (int $n) => ($main['options'][$n] ?? '') !== '' || $request->hasFile("option_images.$n") || $this->existing($request, "option_$n");

        if ($type !== 'truefalse' && count(array_filter(range(1, $count), $present)) < 2) {
            $errors['texts'] = 'A question needs at least two options (text or picture).';
        }

        // the right answer(s)
        $right = null;
        if ($type === 'multiple') {
            $right = collect($request->input('correct_options', []))->map(fn ($v) => (int) $v)->unique()->filter(fn ($n) => $n >= 1 && $n <= $count)->sort()->values()->all();
            if (count($right) < 2) {
                $errors['correct_options'] = 'Tick two or more right options (for one right answer choose "one right answer").';
            }
            $correct = $right[0] ?? 1;
        } else {
            $correct = (int) $request->input('correct_option');
            if ($correct < 1 || $correct > $count) {
                $errors['correct_option'] = 'Choose the right answer.';
            }
        }
        if (!$errors && $type !== 'truefalse') {
            foreach ($right ?? [$correct] as $n) {
                if (!$present($n)) {
                    $errors['correct_option'] = 'The right answer ' . chr(64 + $n) . ' is an empty option.';
                }
            }
        }
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $first = $main ? SafeHtml::text($main['question']) : '';

        return [
            'folder_id' => $folder?->id, 'question_type' => $type, 'correct_option' => $correct, 'correct_options' => $right,
            'difficulty' => $request->difficulty, 'category_ids' => $categoryIds, 'texts' => $texts,
            'hash' => $first !== '' ? LibraryQuestion::hashOf($first) : null,
        ];
    }

    /** On the edit form: a picture that is kept (not removed and not replaced). */
    private function existing(Request $request, string $slot): bool
    {
        $id = $request->route('id');
        if (!$id) {
            return false;
        }
        $q = LibraryQuestion::find($id);
        if (!$q || $request->boolean("remove.$slot")) {
            return false;
        }

        return match (true) {
            $slot === 'question_image' => (bool) $q->question_image,
            $slot === 'explanation_image' => (bool) $q->explanation_image,
            default => (bool) (($q->option_images ?? [])[(int) substr($slot, -1)] ?? false),
        };
    }

    private function saveTexts(LibraryQuestion $q, array $texts): void
    {
        $q->texts()->whereNotIn('lang', array_keys($texts))->delete();
        foreach ($texts as $lang => $t) {
            $q->texts()->updateOrCreate(['lang' => $lang], [
                'question' => $t['question'] !== '' ? $t['question'] : null,
                'option_1' => $t['options'][1] ?: null, 'option_2' => $t['options'][2] ?: null,
                'option_3' => $t['options'][3] ?: null, 'option_4' => $t['options'][4] ?: null,
                'option_5' => $t['options'][5] ?: null, 'option_6' => $t['options'][6] ?: null,
                'explanation' => $t['explanation'] !== '' ? $t['explanation'] : null,
            ]);
        }
    }

    /** Replaces / removes the pictures chosen on the form. */
    private function saveImages(Request $request, LibraryQuestion $q, CompetitiveExam $exam): void
    {
        $dir = 'question-library/' . $exam->slug . '/manual';
        $options = $q->option_images ?? [];
        $slots = ['question_image' => $q->question_image, 'explanation_image' => $q->explanation_image];

        foreach (['question_image', 'explanation_image'] as $col) {
            if ($request->hasFile($col)) {
                $this->forget($slots[$col]);
                $slots[$col] = $request->file($col)->store($dir, 'public');
            } elseif ($request->boolean("remove.$col")) {
                $this->forget($slots[$col]);
                $slots[$col] = null;
            }
        }
        for ($n = 1; $n <= LibraryQuestion::MAX_OPTIONS; $n++) {
            if ($request->hasFile("option_images.$n")) {
                $this->forget($options[$n] ?? null);
                $options[$n] = $request->file("option_images.$n")->store($dir, 'public');
            } elseif ($request->boolean("remove.option_$n")) {
                $this->forget($options[$n] ?? null);
                unset($options[$n]);
            }
        }

        $q->update($slots + ['option_images' => $options ?: null]);
    }

    private function forget(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
