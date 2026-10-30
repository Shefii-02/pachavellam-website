<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CourseExam;
use App\Models\CourseExamQuestion;
use App\Models\CourseExamSection;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use App\Services\CourseExamBuilder;
use App\Services\CourseWorkspace;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Exams inside a course (daily, OMR and special exams):
 *   admin/exam/{exam}/courses/{course}/exams/{feature}                  list
 *   .../exams/{feature}/create | {id}/edit                              exam settings and sections
 *   .../exams/{feature}/{id}                                            questions of the exam (builder)
 *   .../exams/{feature}/{id}/library                                    pick questions from the question library
 * Questions always live in the question library; an exam only places them in its sections.
 */
class CourseExamController extends Controller
{
    use ResolvesExam;

    // ----------------------------------------------------------------- list

    public function index(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, false);

        $exams = CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('season'), fn ($q) => $request->season === 'none' ? $q->whereNull('season_id') : $q->where('season_id', (int) $request->season))
            ->withCount('links', 'sections')
            ->orderBy('position')->orderByDesc('id')->get();

        return view('admin.exam.course-exams.index', $this->data($request, $course, $feature) + ['exams' => $exams, 'seasons' => $this->seasonsFor($course, $feature), 'season' => $request->filled('season') && $request->season !== 'none' ? \App\Models\ExamSeason::find($request->season) : null]);
    }

    // ------------------------------------------------------- create / update

    public function create(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);

        return view('admin.exam.course-exams.form', $this->data($request, $course, $feature) + ['item' => null, 'sections' => collect([new CourseExamSection()]), 'seasons' => $this->seasonsFor($course, $feature), 'preSeason' => $request->season]);
    }

    public function store(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        [$data, $sections] = $this->validated($request, null, $this->isCa($feature));

        $exam = DB::transaction(function () use ($request, $course, $feature, $data, $sections) {
            $exam = CourseExam::create($data + [
                'exam_id' => $course->exam_id, 'course_id' => $course->id, 'feature_id' => $feature->id,
                'status' => 'draft', 'created_by' => $request->user()->id,
                'position' => (int) CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->max('position') + 1,
            ]);
            $this->saveSections($request, $exam, $sections);
            $this->saveExamPdf($request, $exam);

            return $exam;
        });

        $copies = $this->copyToCourses($request, $exam, $course, $feature);

        return redirect($this->examUrl($course, $feature, $exam->id))->with('success', ($this->isCa($feature) ? 'Entry created.' : 'Exam and sections created. Step 2: add questions to each section, then publish.') . ($copies ? " A copy was added to {$copies} other course(s)." : ''));
    }

    public function edit(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);

        return view('admin.exam.course-exams.form', $this->data($request, $course, $feature) + ['item' => $item, 'sections' => $item->sections()->withCount('links')->get(), 'seasons' => $this->seasonsFor($course, $feature), 'preSeason' => null]);
    }

    public function update(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        [$data, $sections] = $this->validated($request, $item, $this->isCa($feature));

        DB::transaction(function () use ($request, $item, $data, $sections) {
            $item->update($data);
            $this->saveSections($request, $item, $sections);
            $this->saveExamPdf($request, $item);
        });

        return redirect($this->examUrl($course, $feature, $item->id))->with('success', 'Exam saved.');
    }

    public function destroy(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);

        $item->delete();   // goes to Trash with its sections, files and question links; the questions stay in the library

        $back = (string) $request->input('back');

        return redirect(str_starts_with($back, url('admin/')) ? $back : CourseWorkspace::featureUrl($course, $feature))->with('success', 'Exam moved to Trash. Its questions are still in the question library.');
    }

    /** draft / published / archived */
    public function status(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        $to = $request->validate(['status' => 'required|in:draft,published,archived'])['status'];

        if ($to === 'published') {
            $problems = $item->publishProblems();
            if ($problems) {
                return back()->withErrors(['status' => 'The exam cannot be published yet: ' . implode(' ', $problems)]);
            }
        }
        $item->update(['status' => $to]);

        return back()->with('success', 'Exam is now ' . strtolower(CourseExam::STATUSES[$to][0]) . '.');
    }

    /** Copies the exam (settings, sections, questions) into other courses of the same exam type, as drafts. */
    public function copy(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        $request->validate(['course_ids' => 'required|array|min:1', 'course_ids.*' => 'integer'], ['course_ids.required' => 'Tick at least one course.']);

        $n = $this->copyToCourses($request, $item, $course, $feature);

        return back()->with('success', $n ? "Copied to {$n} course(s) as a draft. Each copy can be edited on its own." : 'Nothing was copied (those courses do not include this feature, or you cannot edit them).');
    }

    private function copyToCourses(Request $request, CourseExam $exam, ExamCourse $course, $feature): int
    {
        $n = 0;
        foreach ($this->otherCourses($request, $course, $feature)->whereIn('id', $request->input('course_ids', [])) as $target) {
            CourseExamBuilder::copyTo($exam, $target, $request->user()->id);
            $n++;
        }

        return $n;
    }

    /** Other courses of this exam type that include the same feature and that the user may edit. */
    private function otherCourses(Request $request, ExamCourse $course, $feature)
    {
        return ExamCourse::forExam($course->exam_id)->where('id', '!=', $course->id)
            ->whereHas('features', fn ($q) => $q->where('exam_features.id', $feature->id))
            ->orderBy('title')->get()
            ->filter(fn ($c) => $c->canBeEditedBy($request->user()))->values();
    }

    /** Seasons of the series this exam list belongs to (only the internal series course has them). */
    private function seasonsFor(ExamCourse $course, $feature)
    {
        $kind = array_search($feature->slug, array_column(\App\Http\Controllers\Exam\ExamSeriesController::KINDS, 0), true);
        $kinds = array_keys(\App\Http\Controllers\Exam\ExamSeriesController::KINDS);

        return $kind === false || $course->slug !== 'series-' . ($course->exam->slug ?? '') ? collect()
            : \App\Models\ExamSeason::where('exam_id', $course->exam_id)->where('kind', $kinds[$kind])->orderByDesc('starts_on')->orderByDesc('id')->get();
    }

    private function seasonId(Request $request): ?int
    {
        $id = (int) $request->input('season_id');

        return $id && \App\Models\ExamSeason::whereKey($id)->exists() ? $id : null;
    }

    private function isCa($feature): bool
    {
        return $feature->slug === 'premium-daily-ca';
    }

    // --------------------------------------------------------------- builder

    public function show(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, false);
        $item = $this->find($course, $feature, $id);

        $sections = $item->sections()->with(['links.question.texts'])->get();

        return view('admin.exam.course-exams.show', $this->data($request, $course, $feature) + [
            'item' => $item,
            'sections' => $sections,
            'problems' => $item->publishProblems(),
        ]);
    }

    /** The question library, to pick questions into a section. */
    public function library(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        $section = CourseExamBuilder::section($item, $request->section);

        $folderIds = null;
        if ($request->filled('folder')) {
            $folder = QuestionFolder::where('exam_id', $course->exam_id)->findOrFail($request->folder);
            $folderIds = array_merge([$folder->id], $folder->descendantIds());
        }
        $inExam = CourseExamQuestion::where('course_exam_id', $item->id)->pluck('question_id')->all();

        $questions = LibraryQuestion::where('exam_id', $course->exam_id)
            ->when($folderIds, fn ($q) => $q->whereIn('folder_id', $folderIds))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('exam_categories.id', $request->category)))
            ->when($request->filled('type'), fn ($q) => $q->where('question_type', $request->type))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('texts', fn ($t) => $t->where('question', 'like', '%' . $request->q . '%')))
            ->when($request->input('hide_used', '1') === '1', fn ($q) => $q->whereNotIn('id', $inExam))
            ->with(['texts', 'folder:id,name'])
            ->orderByDesc('id')->paginate(per_page(30))->withQueryString();

        return view('admin.exam.course-exams.library', $this->data($request, $course, $feature) + [
            'item' => $item, 'section' => $section, 'sections' => $item->sections()->get(),
            'questions' => $questions, 'inExam' => $inExam,
            'tree' => QuestionFolder::flatTree($course->exam_id),
            'categories' => ExamCategory::optionsFor($course->exam_id),
        ]);
    }

    public function addQuestions(Request $request, $examSlug, $courseId, $featureSlug, $id)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer', 'section' => 'nullable|integer'], ['ids.required' => 'Tick at least one question.']);

        $added = CourseExamBuilder::attach($item, $request->section, $request->ids);

        return redirect($this->examUrl($course, $feature, $item->id))
            ->with('success', $added . ' question' . ($added === 1 ? '' : 's') . ' added' . ($added < count($request->ids) ? ' (the rest were already in this exam)' : '') . '.');
    }

    public function removeQuestion(Request $request, $examSlug, $courseId, $featureSlug, $id, $linkId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);

        CourseExamQuestion::where('course_exam_id', $item->id)->where('id', $linkId)->delete();   // stays in the library

        return back()->with('success', 'Question removed from the exam (it is still in the question library).');
    }

    /** Move a question one place up or down inside its section. */
    public function moveQuestion(Request $request, $examSlug, $courseId, $featureSlug, $id, $linkId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $item = $this->find($course, $feature, $id);
        $dir = $request->validate(['dir' => 'required|in:up,down'])['dir'];

        $link = CourseExamQuestion::where('course_exam_id', $item->id)->findOrFail($linkId);
        $ids = CourseExamQuestion::where('section_id', $link->section_id)->orderBy('position')->orderBy('id')->pluck('id')->all();
        $i = array_search($link->id, $ids, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;

        if (isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $pos => $linkId2) {
                CourseExamQuestion::where('id', $linkId2)->update(['position' => $pos + 1]);
            }
        }

        return back();
    }

    // ---------------------------------------------------------------- helpers

    private function resolve(Request $request, $examSlug, $courseId, $featureSlug, bool $needsEdit): array
    {
        $exam = $this->exam($examSlug);
        $course = ExamCourse::forExam($exam->id)->findOrFail($courseId);
        $course->setRelation('exam', $exam);

        if ($needsEdit) {
            abort_unless($course->canBeEditedBy($request->user()), 403, 'You can view this course but not edit it.');
        } else {
            abort_unless($course->canBeViewedBy($request->user()), 403, 'You do not have access to this course.');
        }

        $feature = $course->features()->where('exam_features.slug', $featureSlug)->first() ?? abort(404, 'This course does not include that feature.');
        abort_unless($feature->content_kind === 'exam', 404);

        return [$course, $feature];
    }

    private function find(ExamCourse $course, $feature, $id): CourseExam
    {
        return CourseExam::where('course_id', $course->id)->where('feature_id', $feature->id)->findOrFail($id);
    }

    private function examUrl(ExamCourse $course, $feature, $id = null): string
    {
        return CourseWorkspace::featureUrl($course, $feature) . ($id ? '/' . $id : '');
    }

    private function data(Request $request, ExamCourse $course, $feature): array
    {
        return CourseWorkspace::data($course, $request->user()) + [
            'feature' => $feature,
            'manageUrl' => CourseWorkspace::featureUrl($course, $feature),
            'libraryUrl' => $course->exam->examUrl('question-library'),
            'isCa' => $this->isCa($feature),
            'otherCourses' => $this->otherCourses($request, $course, $feature),
        ];
    }

    /**
     * @return array{0: array, 1: array} exam fields and the cleaned section rows
     */
    private function validated(Request $request, ?CourseExam $item, bool $ca = false): array
    {
        if ($ca && !$request->has('sections')) {
            $keep = $item?->sections()->first();   // an entry has one section; keep the one it has
            $request->merge(['sections' => [$keep ? ['id' => $keep->id, 'duration_minutes' => $keep->duration_minutes, 'right_mark' => $keep->right_mark, 'negative_mark' => $keep->negative_mark] : []], 'right_mark' => $request->input('right_mark', 1), 'mode' => $request->input('mode', 'moderate'), 'access' => $request->input('access', 'free')]);
        }
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'entry_date' => ($ca ? 'required' : 'nullable') . '|date',
            'body' => 'nullable|string|max:300000',
            'mode' => 'required|in:' . implode(',', array_keys(CourseExam::MODES)),
            'duration_minutes' => 'nullable|integer|min:1|max:1440',
            'avg_seconds_per_question' => 'nullable|integer|min:1|max:3600',
            'right_mark' => 'required|numeric|min:0|max:1000',
            'negative_mark' => 'nullable|numeric|min:0|max:1000',
            'total_mark' => 'nullable|numeric|min:0|max:100000',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'unlock_at' => 'nullable|date',
            'max_attempts' => 'nullable|integer|min:0|max:1000',
            'position' => 'nullable|integer|min:0',
            'access' => 'required|in:free,paid',
            'instruction' => 'nullable|string|max:20000',
            'instruction_type' => 'nullable|in:none,text,pdf',
            'instruction_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'sections.*.instruction_type' => 'nullable|in:none,text,pdf',
            'sections' => 'required|array|min:1|max:12',
            'sections.*.id' => 'nullable|integer',
            'sections.*.title' => 'nullable|string|max:150',
            'sections.*.duration_minutes' => 'nullable|integer|min:1|max:1440',
            'sections.*.right_mark' => 'nullable|numeric|min:0|max:1000',
            'sections.*.negative_mark' => 'nullable|numeric|min:0|max:1000',
            'sections.*.instruction' => 'nullable|string|max:20000',
            'section_pdf.*' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'end_at.after_or_equal' => 'The end date must be on or after the start date.',
            'instruction_pdf.mimes' => 'The instruction file must be a PDF.',
            'section_pdf.*.mimes' => 'Section instruction files must be PDFs.',
        ]);

        $sections = array_values($request->input('sections', []));
        $multi = count($sections) > 1;
        $errors = [];

        foreach ($sections as $i => $s) {
            if ($multi && trim((string) ($s['title'] ?? '')) === '') {
                $errors["sections.$i.title"] = 'Give section ' . ($i + 1) . ' a title.';
            }
        }
        if (!$ca && !$request->filled('duration_minutes') && !collect($sections)->every(fn ($s) => !empty($s['duration_minutes']))) {
            $errors['duration_minutes'] = $multi
                ? 'Set the total duration, or a time limit for every section.'
                : 'Set the exam duration in minutes.';
        }
        // instructions are either text or a PDF: a chosen PDF must exist
        if ($request->input('instruction_type') === 'pdf' && !$request->hasFile('instruction_pdf') && !$item?->instruction_pdf) {
            $errors['instruction_pdf'] = 'Upload the instructions PDF, or choose text / none.';
        }
        foreach ($sections as $i => $s) {
            if (($s['instruction_type'] ?? null) === 'pdf' && !$request->hasFile("section_pdf.$i")) {
                $existing = !empty($s['id']) ? CourseExamSection::where('course_exam_id', $item?->id)->find($s['id']) : null;
                if (!$existing?->instruction_pdf) {
                    $errors["sections.$i.instruction_type"] = 'Upload the PDF for section ' . ($i + 1) . ', or choose text / none.';
                }
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $instrType = $request->input('instruction_type');
        $data = [
            'title' => trim($request->title),
            'entry_date' => $request->entry_date ?: null,
            'body' => SafeHtml::clean($request->body) ?: null,
            'description' => $request->description,
            'mode' => $request->mode,
            'duration_minutes' => $request->duration_minutes ?: null,
            'avg_seconds_per_question' => $request->avg_seconds_per_question ?: null,
            'right_mark' => $request->right_mark,
            'negative_mark' => $request->negative_mark ?: 0,
            'total_mark' => $request->filled('total_mark') ? $request->total_mark : null,
            'start_at' => $request->start_at ?: null,
            'end_at' => $request->end_at ?: null,
            'unlock_at' => $request->unlock_at ?: null,
            'max_attempts' => (int) $request->input('max_attempts', 0),
            'position' => (int) $request->input('position', $item->position ?? 0),
            'season_id' => $this->seasonId($request),
            'is_paid' => $request->access === 'paid',
            'free_access' => $request->boolean('free_access'),
            'sort_no' => $request->filled('sort_no') ? max(0, (int) $request->sort_no) : null,
            'instruction' => $instrType === 'text' || $instrType === null ? (SafeHtml::clean($request->instruction) ?: null) : null,
        ];

        return [$data, $sections];
    }

    private function saveSections(Request $request, CourseExam $exam, array $rows): void
    {
        $existing = $exam->sections()->withCount('links')->get()->keyBy('id');
        $keep = [];

        foreach ($rows as $i => $row) {
            $values = [
                'title' => count($rows) > 1 ? trim((string) ($row['title'] ?? '')) : null,
                'position' => $i,
                'duration_minutes' => !empty($row['duration_minutes']) ? (int) $row['duration_minutes'] : null,
                'right_mark' => ($row['right_mark'] ?? '') !== '' ? $row['right_mark'] : null,
                'negative_mark' => ($row['negative_mark'] ?? '') !== '' ? $row['negative_mark'] : null,
                'instruction' => in_array($row['instruction_type'] ?? 'text', ['text'], true) ? (SafeHtml::clean($row['instruction'] ?? '') ?: null) : null,
            ];
            $section = !empty($row['id']) ? $existing->get((int) $row['id']) : null;

            if ($section) {
                $section->update($values);
            } else {
                $section = CourseExamSection::create($values + ['course_exam_id' => $exam->id]);
            }
            $keep[] = $section->id;

            $type = $row['instruction_type'] ?? null;
            if ($type === 'pdf' && $request->hasFile("section_pdf.$i")) {
                $this->forget($section->instruction_pdf);
                $section->update(['instruction_pdf' => $request->file("section_pdf.$i")->store('course-exams/instructions', 'public')]);
            } elseif (in_array($type, ['text', 'none'], true) || !empty($row['remove_pdf'])) {
                $this->forget($section->instruction_pdf);
                $section->update(['instruction_pdf' => null]);
            }
        }

        // sections taken out of the form: their questions move to the first section so nothing is lost
        $removed = $existing->keys()->diff($keep);
        if ($removed->isNotEmpty()) {
            $target = $keep[0];
            $position = (int) CourseExamQuestion::where('section_id', $target)->max('position');
            foreach (CourseExamQuestion::whereIn('section_id', $removed)->orderBy('section_id')->orderBy('position')->get() as $link) {
                $link->update(['section_id' => $target, 'position' => ++$position]);
            }
            foreach ($removed as $sid) {
                $this->forget($existing[$sid]->instruction_pdf);
            }
            CourseExamSection::whereIn('id', $removed)->delete();
        }
    }

    private function saveExamPdf(Request $request, CourseExam $exam): void
    {
        $type = $request->input('instruction_type');
        if ($type === 'pdf' && $request->hasFile('instruction_pdf')) {
            $this->forget($exam->instruction_pdf);
            $exam->update(['instruction_pdf' => $request->file('instruction_pdf')->store('course-exams/instructions', 'public')]);
        } elseif (in_array($type, ['text', 'none'], true) || $request->boolean('remove_instruction_pdf')) {
            $this->forget($exam->instruction_pdf);
            $exam->update(['instruction_pdf' => null]);
        }
    }

    private function forget(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
