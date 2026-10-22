<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCourse;
use App\Models\LibraryClass;
use App\Models\RoaringItem;
use App\Models\RoaringSection;
use App\Models\RoaringStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Support\StaffAccess;

/**
 * Roaring Study (admin/roaring-study): timed study packs of sections of notes, PDFs, voice notes, links and recorded classes.
 * A pack is tagged to one or more exam types (the exam type sidebars link here filtered by their type) and is either free for
 * everyone ("global") or limited to students of chosen courses.
 */
class RoaringStudyController extends Controller
{
    /** admin/exam/{exam}/roaring-study when opened from an exam type, else admin/roaring-study. */
    private function base(): string
    {
        $exam = request()->attributes->get('rs_exam');

        return $exam ? $exam->examUrl('roaring-study') : url('admin/roaring-study');
    }

    // ================================================================ packs

    /** A pack, if the person may work in at least one of its exam types. */
    private function study($id, array $with = []): RoaringStudy
    {
        $study = RoaringStudy::with($with)->findOrFail($id);
        $allowed = StaffAccess::examIds(request()->user());
        if ($allowed !== null) {
            abort_unless($study->exams()->whereIn('competitive_exams.id', $allowed ?: [0])->exists(), 403, 'You do not have access to this exam type.');
        }

        return $study;
    }

    public function index(Request $request)
    {
        $allowed = StaffAccess::examIds($request->user());
        $list = RoaringStudy::with('exams:id,short_name,name', 'sections.items.libraryClass:id,title')->withCount('sections', 'courses')
            ->when($allowed !== null, fn ($q) => $q->whereHas('exams', fn ($e) => $e->whereIn('competitive_exams.id', $allowed ?: [0])))
            ->when($request->filled('exam'), fn ($q) => $q->forExam($request->exam))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('access'), fn ($q) => $q->where('access_type', $request->access))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('publish_at')->orderByDesc('id')->paginate(20)->withQueryString();

        $itemCounts = RoaringItem::join('roaring_sections', 'roaring_sections.id', '=', 'roaring_items.section_id')
            ->whereIn('roaring_sections.roaring_id', $list->pluck('id'))
            ->select('roaring_sections.roaring_id', DB::raw('count(*) as c'))->groupBy('roaring_sections.roaring_id')->pluck('c', 'roaring_id');

        return view('admin.roaring-study.index', ['list' => $list, 'itemCounts' => $itemCounts, 'exams' => StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get()]);
    }

    public function create(Request $request)
    {
        return view('admin.roaring-study.form', $this->formData(null, $request->exam));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $study = DB::transaction(function () use ($request, $data) {
            $study = RoaringStudy::create($data['fields'] + ['created_by' => $request->user()->id, 'status' => 'draft']);
            $study->exams()->sync($data['exam_ids']);
            $study->courses()->sync($data['course_ids']);
            if ($request->hasFile('cover_image')) {
                $study->update(['cover_image' => $request->file('cover_image')->store('roaring-study/covers', 'public')]);
            }
            RoaringSection::create(['roaring_id' => $study->id, 'title' => 'Section 1', 'position' => 1]);

            return $study;
        });

        return redirect($this->base() . '/' . $study->id)->with('success', 'Roaring study created. Add its sections and content, then publish.');
    }

    public function edit($id)
    {
        return view('admin.roaring-study.form', $this->formData($this->study($id), null));
    }

    public function update(Request $request, $id)
    {
        $study = $this->study($id);
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $study, $data) {
            $study->update($data['fields']);
            $study->exams()->sync($data['exam_ids']);
            $study->courses()->sync($data['course_ids']);
            if ($request->hasFile('cover_image')) {
                $this->forget($study->cover_image);
                $study->update(['cover_image' => $request->file('cover_image')->store('roaring-study/covers', 'public')]);
            } elseif ($request->boolean('remove_cover')) {
                $this->forget($study->cover_image);
                $study->update(['cover_image' => null]);
            }
        });

        return redirect($this->base() . '/' . $study->id)->with('success', 'Saved.');
    }

    public function destroy($id)
    {
        $study = $this->study($id);
        $study->delete();   // goes to Trash with its sections, items and files

        return redirect($this->base())->with('success', 'Roaring study moved to Trash.');
    }

    public function status(Request $request, $id)
    {
        $study = $this->study($id);
        $to = $request->validate(['status' => 'required|in:draft,published,archived'])['status'];

        if ($to === 'published') {
            $problems = $this->publishProblems($study);
            if ($problems) {
                return back()->withErrors(['status' => 'It cannot be published yet: ' . implode(' ', $problems)]);
            }
        }
        $study->update(['status' => $to]);

        return back()->with('success', 'Roaring study is now ' . strtolower(RoaringStudy::STATUSES[$to][0]) . '.');
    }

    // ============================================================ builder

    public function show($id)
    {
        $study = $this->study($id, ['exams:id,short_name,name', 'courses:id,title', 'sections.items.libraryClass:id,title']);

        return view('admin.roaring-study.show', ['study' => $study, 'problems' => $this->publishProblems($study)]);
    }

    public function storeSection(Request $request, $id)
    {
        $study = $this->study($id);
        $data = $request->validate(['title' => 'required|string|max:200', 'release_at' => 'nullable|date']);

        RoaringSection::create([
            'roaring_id' => $study->id, 'title' => trim($data['title']), 'release_at' => $data['release_at'] ?? null,
            'position' => (int) $study->sections()->max('position') + 1,
        ]);

        return back()->with('success', 'Section added.');
    }

    public function updateSection(Request $request, $id, $sectionId)
    {
        $section = RoaringSection::where('roaring_id', $id)->findOrFail($sectionId);
        $data = $request->validate(['title' => 'required|string|max:200', 'release_at' => 'nullable|date']);
        $section->update(['title' => trim($data['title']), 'release_at' => $data['release_at'] ?? null]);

        return back()->with('success', 'Section saved.');
    }

    public function destroySection($id, $sectionId)
    {
        $study = $this->study($id);
        $section = RoaringSection::where('roaring_id', $study->id)->findOrFail($sectionId);

        if ($study->sections()->count() === 1) {
            return back()->withErrors(['section' => 'A study needs at least one section.']);
        }
        foreach ($section->items as $item) {
            $this->forget($item->file_path);
        }
        $section->delete();

        return back()->with('success', 'Section deleted.');
    }

    public function storeItem(Request $request, $id, $sectionId)
    {
        $study = $this->study($id, ['exams:id']);
        $section = RoaringSection::where('roaring_id', $study->id)->findOrFail($sectionId);

        $type = $request->validate(['type' => 'required|in:' . implode(',', array_keys(RoaringStudy::TYPES))])['type'];
        $rules = ['title' => 'nullable|string|max:250'];
        $rules += match ($type) {
            'text' => ['content' => 'required|string|max:50000'],
            'link' => ['content' => ['required', 'string', 'max:2000', 'regex:#^https?://[^\s]+$#i']],
            'pdf' => ['file' => 'required|file|mimes:pdf|max:20480'],
            'voice' => ['file' => 'required|file|extensions:mp3,m4a,aac,wav,ogg|max:30720'],
            'class' => ['class_id' => 'required|integer'],
        };
        $data = $request->validate($rules, [
            'content.regex' => 'The link must start with http:// or https://.', 'file.required' => 'Choose the file to upload.',
            'class_id.required' => 'Search and choose a class.',
        ]);

        $values = ['section_id' => $section->id, 'type' => $type, 'position' => (int) $section->items()->max('position') + 1, 'status' => true];
        $title = trim((string) ($data['title'] ?? ''));

        if ($type === 'class') {
            $class = LibraryClass::published()->whereIn('exam_id', $study->exams->pluck('id'))->find($data['class_id']);
            if (!$class) {
                throw ValidationException::withMessages(['class_id' => 'That class is not available (it must be published and belong to one of the exam types of this study).']);
            }
            $values['class_id'] = $class->id;
            $title = $title !== '' ? $title : $class->title;
        } elseif (in_array($type, ['pdf', 'voice'], true)) {
            $file = $request->file('file');
            $values['file_path'] = $file->store('roaring-study', 'public');
            $title = $title !== '' ? $title : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        } else {
            $values['content'] = $type === 'text' ? \App\Support\SafeHtml::clean($data['content']) : $data['content'];
            $title = $title !== '' ? $title : ($type === 'link' ? 'Link' : 'Note');
        }
        $values['title'] = mb_substr($title, 0, 250);

        RoaringItem::create($values);

        return back()->with('success', 'Content added.');
    }

    public function toggleItem($id, $itemId)
    {
        $item = $this->item($id, $itemId);
        $item->update(['status' => !$item->status]);

        return back()->with('success', $item->status ? 'Shown to students.' : 'Hidden from students.');
    }

    public function destroyItem($id, $itemId)
    {
        $item = $this->item($id, $itemId);
        $this->forget($item->file_path);
        $item->delete();

        return back()->with('success', 'Content removed.');
    }

    public function moveItem(Request $request, $id, $itemId)
    {
        $item = $this->item($id, $itemId);
        $dir = $request->validate(['dir' => 'required|in:up,down'])['dir'];

        $ids = RoaringItem::where('section_id', $item->section_id)->orderBy('position')->orderBy('id')->pluck('id')->all();
        $i = array_search($item->id, $ids, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $pos => $itemId2) {
                RoaringItem::where('id', $itemId2)->update(['position' => $pos + 1]);
            }
        }

        return back();
    }

    /** JSON for the class picker: published classes of the study's exam types matching the search. */
    public function classSearch(Request $request, $id)
    {
        $study = $this->study($id, ['exams:id']);
        $q = trim((string) $request->q);

        $rows = LibraryClass::published()->whereIn('exam_id', $study->exams->pluck('id'))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', "%{$q}%")->orWhere('instructor', 'like', "%{$q}%")))
            ->with('folder:id,name')->orderByDesc('id')->limit(15)->get()
            ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'meta' => trim(($c->folder?->name ?? '') . ' · ' . $c->durationLabel() . ($c->instructor ? ' · ' . $c->instructor : ''), ' ·')]);

        return response()->json($rows);
    }

    // ---------------------------------------------------------------- helpers

    private function item($studyId, $itemId): RoaringItem
    {
        return RoaringItem::whereIn('section_id', RoaringSection::where('roaring_id', $studyId)->pluck('id'))->findOrFail($itemId);
    }

    private function formData(?RoaringStudy $study, $preselect): array
    {
        $exams = StaffAccess::scope(CompetitiveExam::orderBy('position'), request()->user(), 'id')->get();
        $chosen = $study ? $study->exams()->pluck('competitive_exams.id')->all()
            : ($preselect ? $exams->where('slug', $preselect)->pluck('id')->all() : []);

        return [
            'study' => $study, 'exams' => $exams, 'chosenExams' => $chosen,
            'courses' => StaffAccess::scope(ExamCourse::orderBy('title'), request()->user())->get(['id', 'title', 'exam_id']),
            'chosenCourses' => $study ? $study->courses()->pluck('exam_courses.id')->all() : [],
        ];
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:200', 'description' => 'nullable|string|max:2000', 'cover_image' => 'nullable|image|max:3072',
            'exam_ids' => 'required|array|min:1', 'exam_ids.*' => 'integer|exists:competitive_exams,id',
            'access_type' => 'required|in:global,course', 'course_ids' => 'nullable|array', 'course_ids.*' => 'integer',
            'publish_at' => 'nullable|date', 'end_at' => 'nullable|date|after_or_equal:publish_at', 'position' => 'nullable|integer|min:0',
        ], ['exam_ids.required' => 'Tick at least one exam type.', 'end_at.after_or_equal' => 'The end time must be after the start time.']);

        $allowedExams = StaffAccess::examIds($request->user());
        if ($allowedExams !== null && array_diff(array_map('intval', $request->exam_ids), $allowedExams)) {
            throw ValidationException::withMessages(['exam_ids' => 'You can only choose the exam types you have access to.']);
        }
        $courseIds = [];
        if ($request->access_type === 'course') {
            $courseIds = ExamCourse::whereIn('exam_id', $request->exam_ids)->whereIn('id', (array) $request->course_ids)->pluck('id')->all();
            if (!$courseIds) {
                throw ValidationException::withMessages(['course_ids' => 'Choose at least one course (of the ticked exam types), or make it free for everyone.']);
            }
        }

        return [
            'fields' => [
                'title' => trim($request->title), 'description' => $request->description, 'access_type' => $request->access_type,
                'publish_at' => $request->publish_at ?: null, 'end_at' => $request->end_at ?: null, 'position' => (int) $request->input('position', 0),
            ],
            'exam_ids' => array_map('intval', $request->exam_ids), 'course_ids' => $courseIds,
        ];
    }

    private function publishProblems(RoaringStudy $study): array
    {
        $problems = [];
        $items = RoaringItem::whereIn('section_id', $study->sections()->pluck('id'))->where('status', true)->count();
        if ($items === 0) {
            $problems[] = 'Add some content.';
        }
        if ($study->access_type === 'course' && !$study->courses()->exists()) {
            $problems[] = 'Choose the courses that can open it.';
        }
        if (!$study->exams()->exists()) {
            $problems[] = 'Tick an exam type.';
        }

        return $problems;
    }

    private function forget(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
