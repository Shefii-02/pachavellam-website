<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\ExamSubject;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Subjects of an exam type (admin/exam/{exam}/subjects): groups / folders that the free YouTube classes are filed under. */
class ExamSubjectController extends Controller
{
    use ResolvesExam;

    private function scope(Request $request, $examSlug)
    {
        StaffAccess::authorize($request, 'Content manage');
        $exam = $this->exam($examSlug);
        $allowed = StaffAccess::examIds($request->user());
        abort_unless($allowed === null || in_array($exam->id, $allowed, true), 403, 'You do not have access to this exam type.');

        return $exam;
    }

    public function index(Request $request, $examSlug)
    {
        $exam = $this->scope($request, $examSlug);
        $subjects = ExamSubject::where('exam_id', $exam->id)->orderBy('position')->orderBy('id')->get();
        $classCounts = ContentItem::ofKind('promo_class')->whereIn('subject_id', $subjects->pluck('id'))->selectRaw('subject_id, count(*) c')->groupBy('subject_id')->pluck('c', 'subject_id');

        return view('admin.exam.subjects', ['exam' => $exam, 'layout' => $exam->layout(), 'base' => $exam->examUrl('subjects'), 'subjects' => $subjects, 'classCounts' => $classCounts]);
    }

    public function store(Request $request, $examSlug)
    {
        $exam = $this->scope($request, $examSlug);
        $data = $this->validated($request, $exam);
        $s = ExamSubject::create($data + ['exam_id' => $exam->id, 'created_by' => $request->user()->id, 'status' => true]);
        $s->update(['position' => $data['position'] ?? $s->id]);
        if ($request->hasFile('image')) {
            $s->update(['image' => $request->file('image')->store('exam-subjects', 'public')]);
        }

        return redirect($exam->examUrl('subjects'))->with('success', 'Subject added.');
    }

    public function update(Request $request, $examSlug, $id)
    {
        $exam = $this->scope($request, $examSlug);
        $s = ExamSubject::where('exam_id', $exam->id)->findOrFail($id);
        $data = $this->validated($request, $exam, $s->id);
        $s->update($data + ['status' => $request->boolean('status')]);
        if ($request->hasFile('image')) {
            if ($s->image) {
                Storage::disk('public')->delete($s->image);
            }
            $s->update(['image' => $request->file('image')->store('exam-subjects', 'public')]);
        }

        return redirect($exam->examUrl('subjects'))->with('success', 'Subject saved.');
    }

    public function destroy(Request $request, $examSlug, $id)
    {
        $exam = $this->scope($request, $examSlug);
        $s = ExamSubject::where('exam_id', $exam->id)->findOrFail($id);
        ContentItem::where('subject_id', $s->id)->update(['subject_id' => null]);   // classes stay, just without a folder
        ExamSubject::where('parent_id', $s->id)->update(['parent_id' => null]);
        $s->delete();

        return redirect($exam->examUrl('subjects'))->with('success', 'Subject deleted. Its classes are kept without a folder.');
    }

    private function validated(Request $request, $exam, ?int $selfId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:150',
            'parent_id' => ['nullable', 'integer', function ($a, $v, $fail) use ($exam, $selfId) {
                if ($v && ($v == $selfId || !ExamSubject::where('exam_id', $exam->id)->where('id', $v)->exists())) {
                    $fail('Choose another parent subject.');
                }
            }],
            'color' => 'nullable|string|max:20', 'position' => 'nullable|integer|min:0', 'image' => 'nullable|image|max:3072',
        ]);
    }
}
