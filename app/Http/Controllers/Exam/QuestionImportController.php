<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCategory;
use App\Models\LibraryImportBatch;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use App\Services\QuestionImport\ImportException;
use App\Services\QuestionImport\QuestionImporter;
use App\Services\QuestionImport\QuestionParser;
use App\Services\QuestionImport\SampleFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Importing questions into the library: admin/exam/{exam}/question-library/import
 * folder (and sub folders) -> categories -> Word or CSV/Excel -> kind of file -> upload.
 */
class QuestionImportController extends Controller
{
    use ResolvesExam;

    private function data(CompetitiveExam $exam, array $extra = []): array
    {
        return array_merge([
            'exam' => $exam,
            'layout' => $exam->layout(),
            'base' => $exam->examUrl('question-library'),
        ], $extra);
    }

    public function create(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);

        return view('admin.question-library.import', $this->data($exam, [
            'tree' => QuestionFolder::flatTree($exam->id),
            'categoryTree' => $this->categoryTree($exam),
            'types' => config('question_library.types'),
            'languages' => config('question_library.languages'),
            'selectedFolder' => $request->folder,
            'limits' => [
                'file_kb' => config('question_library.max_file_kb'),
                'zip_kb' => config('question_library.max_zip_kb'),
                'php_upload' => ini_get('upload_max_filesize'),
                'php_post' => ini_get('post_max_size'),
            ],
            'sampleKeys' => SampleFiles::KEYS,
        ]));
    }

    /** [id, name, parent_id, depth] of the categories, for the checkbox tree. */
    private function categoryTree(CompetitiveExam $exam): array
    {
        $all = ExamCategory::where('exam_id', $exam->id)->orderBy('position')->orderBy('id')->get(['id', 'name', 'parent_id'])->groupBy('parent_id');
        $out = [];
        $walk = function ($parentId, $depth) use (&$walk, $all, &$out) {
            foreach ($all->get($parentId, collect()) as $c) {
                $out[] = ['id' => $c->id, 'name' => $c->name, 'parent_id' => $c->parent_id, 'depth' => $depth];
                $walk($c->id, $depth + 1);
            }
        };
        $walk(null, 0);

        return $out;
    }

    public function store(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $types = array_keys(config('question_library.types'));

        $request->validate([
            'folder_id' => 'required|integer',
            'new_folder_name' => 'nullable|string|max:150',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer',
            'format' => 'required|in:word,sheet',
            'file_type' => 'required|in:' . implode(',', $types),
            'language' => 'nullable|string|max:5',
            'file' => 'required|file|max:' . config('question_library.max_file_kb'),
            'images_zip' => 'nullable|file|max:' . config('question_library.max_zip_kb'),
        ], [
            'folder_id.required' => 'Choose the folder the questions go into.',
            'file.required' => 'Choose the file to upload.',
            'file.max' => 'The file is bigger than ' . (int) (config('question_library.max_file_kb') / 1024) . ' MB.',
            'images_zip.max' => 'The pictures zip is bigger than ' . (int) (config('question_library.max_zip_kb') / 1024) . ' MB.',
        ]);

        $type = $request->file_type;
        $upload = $request->file('file');
        $ext = strtolower($upload->getClientOriginalExtension());

        $format = null;
        if ($request->format === 'word') {
            $format = $ext === 'docx' ? 'docx' : null;
            $error = 'Choose a Word file saved as .docx (old .doc files are not supported).';
        } else {
            $format = match ($ext) { 'csv', 'txt' => 'csv', 'xlsx', 'xls' => 'xlsx', default => null };
            $error = 'Choose a .csv, .xlsx or .xls file.';
        }
        if (!$format) {
            return back()->withInput()->withErrors(['file' => $error]);
        }

        $language = null;
        if (config("question_library.types.$type.single", true)) {
            $language = config("question_library.types.$type.language") ?: $request->language;
            if (!$language || !config("question_library.languages.$language")) {
                return back()->withInput()->withErrors(['language' => 'Choose the language of the questions in this file.']);
            }
        }

        // the folder: the chosen one, or a new sub folder created inside it
        $folder = QuestionFolder::where('exam_id', $exam->id)->findOrFail($request->folder_id);
        if (trim((string) $request->new_folder_name) !== '') {
            $name = trim($request->new_folder_name);
            $folder = QuestionFolder::firstOrCreate(
                ['exam_id' => $exam->id, 'parent_id' => $folder->id, 'name' => $name],
                ['created_by' => $request->user()->id, 'position' => (int) QuestionFolder::where('parent_id', $folder->id)->max('position') + 1]
            );
        }

        @set_time_limit(300);
        try {
            $parsed = (new QuestionParser())->parse($upload->getRealPath(), $format, $type);
            if ($request->hasFile('images_zip')) {
                if ($format === 'docx') {
                    $parsed->problem('-', 'The pictures zip was ignored: a Word file carries its own pictures.');
                } else {
                    $parsed->attachImageZip($request->file('images_zip')->getRealPath());
                }
            }

            $batch = \App\Services\ActivityLogger::quietly(fn () => (new QuestionImporter())->import($parsed, [
                'exam' => $exam, 'folder_id' => $folder->id, 'category_ids' => $request->input('category_ids', []),
                'type' => $type, 'language' => $language, 'format' => $format, 'file_name' => $upload->getClientOriginalName(),
                'skip_duplicates' => $request->boolean('skip_duplicates'), 'user_id' => $request->user()->id,
            ]));
            \App\Services\ActivityLogger::record('created', 'Question import', "Imported {$batch->imported} question(s), skipped {$batch->skipped}, from {$batch->file_name}", null, $exam->id);
        } catch (ImportException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect($this->data($exam)['base'] . '/imports/' . $batch->id)
            ->with($batch->imported ? 'success' : 'error', $batch->imported
                ? "{$batch->imported} question(s) imported" . ($batch->skipped ? ", {$batch->skipped} skipped." : '.')
                : 'No question was imported. See the problems below.');
    }

    // ================================================================ history

    public function history(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);

        return view('admin.question-library.imports', $this->data($exam, [
            'batches' => LibraryImportBatch::where('exam_id', $exam->id)->with(['folder:id,name', 'user:id,name'])->orderByDesc('id')->paginate(per_page(20))->withQueryString(),
        ]));
    }

    public function show($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $batch = LibraryImportBatch::where('exam_id', $exam->id)->with(['folder', 'user:id,name'])->findOrFail($id);

        return view('admin.question-library.import-show', $this->data($exam, [
            'batch' => $batch,
            'folderPath' => $batch->folder?->path(),
            'categoryNames' => ExamCategory::whereIn('id', $batch->category_ids ?? [])->pluck('name'),
            'liveCount' => LibraryQuestion::where('import_batch_id', $batch->id)->count(),
        ]));
    }

    /** Deletes the questions (and pictures) this import created. */
    public function undo($examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $batch = LibraryImportBatch::where('exam_id', $exam->id)->findOrFail($id);

        if ($batch->status === 'undone') {
            return back()->withErrors(['batch' => 'This import was already undone.']);
        }

        $count = DB::transaction(function () use ($batch) {
            $questions = LibraryQuestion::where('import_batch_id', $batch->id)->get();
            \App\Services\ActivityLogger::quietly(fn () => QuestionLibraryController::deleteQuestions($questions));
            \App\Services\ActivityLogger::record('deleted', 'Question import', "Undid import #{$batch->id}: {$questions->count()} question(s) moved to Trash", null, $exam->id);
            $batch->update(['status' => 'undone', 'undone_at' => now()]);

            return $questions->count();
        });

        return back()->with('success', "Import undone: {$count} question(s) removed.");
    }

    // ================================================================ samples

    public function sample($examSlug, $ext, $key)
    {
        abort_unless(SampleFiles::exists($ext, $key), 404);

        $path = SampleFiles::make($ext, $key);
        $name = $ext === 'zip' ? 'sample-pictures.zip' : "sample-questions-{$key}.{$ext}";

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
