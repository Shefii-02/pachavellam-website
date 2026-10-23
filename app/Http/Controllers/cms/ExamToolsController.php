<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\CourseExam;
use App\Models\DailyExam;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Models\KpscSubject;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use App\Services\DailyExamLinker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Settings shared by the old KPSC exam series - Daily Exam, OMR (model) Exam and CA Daily Exam:
 *   admin/kpsc/exam-tools/{id}            free / premium, subjects, exam types, categories, courses
 *   admin/kpsc/exam-tools/{id}/library    add questions from the question library (daily and CA exams)
 */
class ExamToolsController extends Controller
{
    private const SECTIONS = [
        'Daily Exam' => ['label' => 'Daily Exam', 'back' => 'daily-exam', 'edit' => 'daily-exam/edit/', 'questions' => true],
        'Model Exam' => ['label' => 'OMR Exam', 'back' => 'model-exam', 'edit' => 'model-exam/view', 'questions' => false],
        'CA-Daily-Exam' => ['label' => 'CA Exam Series', 'back' => 'ca-daily-exam', 'edit' => 'ca-daily-exam/edit/', 'questions' => true],
    ];

    private function exam($id): DailyExam
    {
        return DailyExam::whereIn('section', array_keys(self::SECTIONS))->findOrFail($id);
    }

    private function backUrl(DailyExam $exam): string
    {
        $cfg = self::SECTIONS[$exam->section];

        return kpsc_cms($cfg['edit'] . ($cfg['questions'] ? $exam->id : ''));
    }

    // ============================================================ settings

    public function show($id)
    {
        $exam = $this->exam($id);
        $cfg = self::SECTIONS[$exam->section];
        $feature = ExamFeature::where('slug', DailyExamLinker::featureSlug($exam))->first();

        $chosenSubjects = DB::table('kpsc_daily_exam_subjects')->where('exam_id', $exam->id)->pluck('subject_id')->all();
        if (!$chosenSubjects && $exam->subject) {
            $chosenSubjects = [(int) $exam->subject];   // the single subject the old module saved
        }
        $kpsc = CompetitiveExam::where('slug', 'kpsc')->first();
        $chosenTypes = DB::table('kpsc_daily_exam_types')->where('exam_id', $exam->id)->pluck('exam_type_id')->all() ?: [$kpsc->id];

        $types = CompetitiveExam::orderBy('position')->get();
        $categoryTrees = [];
        foreach ($types as $t) {
            $categoryTrees[$t->id] = $this->categoryTree($t->id);
        }

        $courses = ExamCourse::whereHas('features', fn ($q) => $q->where('exam_features.id', $feature?->id))->orderBy('title')->get(['id', 'title', 'exam_id']);
        $linked = DB::table('kpsc_daily_exam_courses as l')->leftJoin('course_exams as ce', 'ce.id', '=', 'l.course_exam_id')
            ->where('l.exam_id', $exam->id)->get(['l.course_id', 'l.course_exam_id', 'ce.status', 'ce.feature_id'])->keyBy('course_id');

        return view('cms.exam-tools.settings', [
            'exam' => $exam, 'cfg' => $cfg, 'feature' => $feature,
            'subjects' => KpscSubject::where('status', 'show')->orderBy('position')->get(),
            'chosenSubjects' => $chosenSubjects,
            'types' => $types, 'chosenTypes' => $chosenTypes,
            'categoryTrees' => $categoryTrees,
            'chosenCategories' => DB::table('kpsc_daily_exam_categories')->where('exam_id', $exam->id)->pluck('category_id')->all(),
            'courses' => $courses, 'linked' => $linked,
            'questionCount' => $exam->exam_details()->count(),
            'back' => $this->backUrl($exam),
        ]);
    }

    public function save(Request $request, $id)
    {
        $exam = $this->exam($id);
        $request->validate([
            'access_type' => 'required|in:free,premium',
            'subject_ids' => 'nullable|array', 'subject_ids.*' => 'integer',
            'exam_type_ids' => 'required|array|min:1', 'exam_type_ids.*' => 'integer|exists:competitive_exams,id',
            'category_ids' => 'nullable|array', 'category_ids.*' => 'integer',
            'course_ids' => 'nullable|array', 'course_ids.*' => 'integer',
        ], ['exam_type_ids.required' => 'Tick at least one exam type.']);

        $typeIds = array_map('intval', $request->exam_type_ids);
        $subjectIds = KpscSubject::whereIn('id', (array) $request->subject_ids)->pluck('id')->all();
        $categoryIds = ExamCategory::whereIn('exam_id', $typeIds)->whereIn('id', (array) $request->category_ids)->pluck('id')->all();

        $feature = ExamFeature::where('slug', DailyExamLinker::featureSlug($exam))->first();
        $courseIds = ExamCourse::whereIn('exam_id', $typeIds)->whereIn('id', (array) $request->course_ids)
            ->whereHas('features', fn ($q) => $q->where('exam_features.id', $feature?->id))->get()
            ->filter(fn ($c) => $c->canBeEditedBy($request->user()))->pluck('id')->all();

        DB::transaction(function () use ($exam, $request, $typeIds, $subjectIds, $categoryIds) {
            $exam->access_type = $request->access_type;
            if ($subjectIds) {
                $exam->subject = $subjectIds[0];   // the old column the app reads keeps the first subject
            }
            $exam->save();
            $this->syncPivot('kpsc_daily_exam_subjects', $exam->id, 'subject_id', $subjectIds);
            $this->syncPivot('kpsc_daily_exam_types', $exam->id, 'exam_type_id', $typeIds);
            $this->syncPivot('kpsc_daily_exam_categories', $exam->id, 'category_id', $categoryIds);
        });

        // courses: make / refresh a course exam in each ticked course; an unticked course is only unlinked (its course exam stays)
        $made = $courseIds ? (new DailyExamLinker())->toCourses($exam->fresh(), $courseIds, $request->user()->id) : [];
        DB::table('kpsc_daily_exam_courses')->where('exam_id', $exam->id)->whereNotIn('course_id', $courseIds ?: [0])->delete();

        return redirect(url('admin/kpsc/exam-tools/' . $exam->id))
            ->with('success', 'Saved.' . ($made ? ' Course exams (draft) are ready in ' . count($made) . ' course(s); open a course to publish them.' : ''));
    }

    private function syncPivot(string $table, int $examId, string $column, array $ids): void
    {
        DB::table($table)->where('exam_id', $examId)->delete();
        foreach (array_unique($ids) as $v) {
            DB::table($table)->insert(['exam_id' => $examId, $column => $v]);
        }
    }

    /** [id, name, parent_id, depth] of one exam type's categories, in tree order. */
    private function categoryTree(int $examId): array
    {
        $all = ExamCategory::where('exam_id', $examId)->orderBy('position')->orderBy('id')->get(['id', 'name', 'parent_id'])->groupBy('parent_id');
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

    // ======================================================= library picker

    public function library(Request $request, $id)
    {
        $exam = $this->exam($id);
        abort_unless(self::SECTIONS[$exam->section]['questions'], 404, 'OMR exams use a question paper file, not typed questions.');
        $kpsc = CompetitiveExam::where('slug', 'kpsc')->value('id');

        $folderIds = null;
        if ($request->filled('folder')) {
            $folder = QuestionFolder::where('exam_id', $kpsc)->findOrFail($request->folder);
            $folderIds = array_merge([$folder->id], $folder->descendantIds());
        }
        $have = $exam->exam_details()->whereNotNull('library_question_id')->pluck('library_question_id')->all();

        $questions = LibraryQuestion::where('exam_id', $kpsc)->where('question_type', 'single')
            ->when($folderIds, fn ($q) => $q->whereIn('folder_id', $folderIds))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('exam_categories.id', $request->category)))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('texts', fn ($t) => $t->where('question', 'like', '%' . $request->q . '%')))
            ->when($request->input('hide_used', '1') === '1', fn ($q) => $q->whereNotIn('id', $have))
            ->with(['texts', 'folder:id,name'])->orderByDesc('id')->paginate(per_page(30))->withQueryString();

        return view('cms.exam-tools.library', [
            'exam' => $exam, 'cfg' => self::SECTIONS[$exam->section], 'questions' => $questions, 'have' => $have,
            'tree' => QuestionFolder::flatTree($kpsc), 'categories' => ExamCategory::optionsFor($kpsc), 'back' => $this->backUrl($exam),
        ]);
    }

    public function addFromLibrary(Request $request, $id)
    {
        $exam = $this->exam($id);
        abort_unless(self::SECTIONS[$exam->section]['questions'], 404);
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer'], ['ids.required' => 'Tick at least one question.']);

        $result = (new DailyExamLinker())->copyFromLibrary($exam, $request->ids);

        return redirect($this->backUrl($exam))
            ->with('message', $result['added'] . ' question(s) added from the question library.')
            ->with('skipped', $result['skipped']);
    }
}
