<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * New joins (admin/new-joins): students who recently signed up, for the sales team to call.
 * Each can be marked (contacted, with an outcome and a note) or unmarked; filters show who is still waiting for a call.
 */
class NewJoinController extends Controller
{
    public const OUTCOMES = ['contacted' => 'Contacted', 'interested' => 'Interested', 'not_interested' => 'Not interested', 'no_answer' => 'No answer', 'admitted' => 'Admitted'];

    private function query(Request $request)
    {
        $user = $request->user();
        $days = $request->input('period', '7');
        $q = User::query()->where('users.type', 'Student')
            ->leftJoin('lead_marks as lm', 'lm.user_id', '=', 'users.id')
            ->select('users.*', 'lm.marked_by', 'lm.marked_at', 'lm.note as mark_note', 'lm.outcome as mark_outcome')
            ->when(ctype_digit((string) $days) && $days !== '0', fn ($w) => $w->where('users.created_at', '>=', now()->subDays((int) $days)->startOfDay()))
            ->when($days === 'today', fn ($w) => $w->whereDate('users.created_at', today()))
            ->when($request->input('mark') === 'marked', fn ($w) => $w->whereNotNull('lm.user_id'))
            ->when($request->input('mark') === 'unmarked', fn ($w) => $w->whereNull('lm.user_id'))
            ->when($request->filled('outcome'), fn ($w) => $w->where('lm.outcome', $request->outcome))
            ->when($request->input('mobile') === 'yes', fn ($w) => $w->whereNotNull('users.mobile')->where('users.mobile', '!=', ''))
            ->when($request->input('mobile') === 'no', fn ($w) => $w->where(fn ($x) => $x->whereNull('users.mobile')->orWhere('users.mobile', '')))
            ->when($request->input('admitted') === 'yes', fn ($w) => $w->whereExists(fn ($s) => $s->select(DB::raw(1))->from('course_admissions as a')->whereColumn('a.user_id', 'users.id')->where('a.status', 'active')->whereNull('a.deleted_at')))
            ->when($request->input('admitted') === 'no', fn ($w) => $w->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('course_admissions as a')->whereColumn('a.user_id', 'users.id')->where('a.status', 'active')->whereNull('a.deleted_at')))
            ->when($request->filled('q'), fn ($w) => $w->where(fn ($x) => $x->where('users.name', 'like', '%' . $request->q . '%')->orWhere('users.mobile', 'like', '%' . $request->q . '%')->orWhere('users.email', 'like', '%' . $request->q . '%')));

        // exam type: the ones the student chose in the app. Staff limited to some exam types only see students of those.
        $allowed = StaffAccess::examIds($user);
        $examFilter = $request->filled('exam') ? [(int) $request->exam] : null;
        if ($examFilter !== null && $allowed !== null) {
            $examFilter = array_values(array_intersect($examFilter, $allowed)) ?: [0];
        }
        $ids = $examFilter ?? $allowed;
        if ($ids !== null) {
            $q->whereExists(fn ($s) => $s->select(DB::raw(1))->from('student_exam_interests as i')->whereColumn('i.user_id', 'users.id')->whereIn('i.exam_id', $ids ?: [0]));
        }

        return $q->orderByDesc('users.id');
    }

    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'New joins view');
        $list = $this->query($request)->paginate(per_page(30))->withQueryString();

        $ids = $list->pluck('id');
        $interests = DB::table('student_exam_interests as i')->join('competitive_exams as e', 'e.id', '=', 'i.exam_id')->whereIn('i.user_id', $ids)->get(['i.user_id', 'e.short_name', 'e.name'])->groupBy('user_id');
        $admissions = DB::table('course_admissions as a')->join('exam_courses as c', 'c.id', '=', 'a.course_id')->whereIn('a.user_id', $ids)->where('a.status', 'active')->whereNull('a.deleted_at')->get(['a.user_id', 'c.title'])->groupBy('user_id');
        $markers = User::whereIn('id', $list->pluck('marked_by')->filter())->pluck('name', 'id');

        $total = $this->query($request);
        $stats = [
            'shown' => $list->total(),
            'unmarked' => (clone $total)->whereNull('lm.user_id')->count(),
            'marked' => (clone $total)->whereNotNull('lm.user_id')->count(),
        ];

        return view('admin.staff.new-joins', [
            'list' => $list, 'interests' => $interests, 'admissions' => $admissions, 'markers' => $markers, 'stats' => $stats, 'outcomes' => self::OUTCOMES,
            'exams' => CompetitiveExam::orderBy('position')->get(['id', 'short_name', 'name']), 'canMark' => StaffAccess::can($request->user(), 'New joins mark'),
        ]);
    }

    public function mark(Request $request, $id)
    {
        StaffAccess::authorize($request, 'New joins mark');
        $student = User::where('type', 'Student')->findOrFail($id);
        $data = $request->validate(['outcome' => 'nullable|in:' . implode(',', array_keys(self::OUTCOMES)), 'note' => 'nullable|string|max:490']);

        $this->markOne($student, $data['outcome'] ?? 'contacted', $data['note'] ?? null, $request->user());

        return back()->with('success', $student->name . ' marked.');
    }

    public function unmark(Request $request, $id)
    {
        StaffAccess::authorize($request, 'New joins mark');
        $student = User::where('type', 'Student')->findOrFail($id);
        DB::table('lead_marks')->where('user_id', $student->id)->delete();
        ActivityLogger::record('unmarked', 'New join', 'Unmarked ' . $student->name, null);

        return back()->with('success', $student->name . ' is unmarked again.');
    }

    public function bulk(Request $request)
    {
        StaffAccess::authorize($request, 'New joins mark');
        $data = $request->validate([
            'ids' => 'required|array|min:1', 'ids.*' => 'integer', 'action' => 'required|in:mark,unmark',
            'outcome' => 'nullable|in:' . implode(',', array_keys(self::OUTCOMES)), 'note' => 'nullable|string|max:490',
        ], ['ids.required' => 'Tick at least one student.']);

        $students = User::where('type', 'Student')->whereIn('id', $data['ids'])->get();
        foreach ($students as $s) {
            if ($data['action'] === 'mark') {
                $this->markOne($s, $data['outcome'] ?? 'contacted', $data['note'] ?? null, $request->user(), true);
            } else {
                DB::table('lead_marks')->where('user_id', $s->id)->delete();
            }
        }
        ActivityLogger::record($data['action'] === 'mark' ? 'marked' : 'unmarked', 'New join', $students->count() . ' student(s) ' . ($data['action'] === 'mark' ? 'marked' : 'unmarked'));

        return back()->with('success', $students->count() . ' student(s) ' . ($data['action'] === 'mark' ? 'marked.' : 'unmarked.'));
    }

    private function markOne(User $student, string $outcome, ?string $note, User $by, bool $quiet = false): void
    {
        DB::table('lead_marks')->updateOrInsert(['user_id' => $student->id], ['marked_by' => $by->id, 'marked_at' => now(), 'note' => $note, 'outcome' => $outcome, 'updated_at' => now(), 'created_at' => now()]);
        if (!$quiet) {
            ActivityLogger::record('marked', 'New join', 'Marked ' . $student->name . ' as ' . (self::OUTCOMES[$outcome] ?? $outcome) . ($note ? ': ' . $note : ''));
        }
    }

    public function export(Request $request)
    {
        StaffAccess::authorize($request, 'New joins view');
        $rows = $this->query($request)->limit(20000)->get();
        $ids = $rows->pluck('id');
        $interests = DB::table('student_exam_interests as i')->join('competitive_exams as e', 'e.id', '=', 'i.exam_id')->whereIn('i.user_id', $ids)->get(['i.user_id', 'e.short_name', 'e.name'])->groupBy('user_id');
        ActivityLogger::record('exported', 'New joins', 'Exported ' . $rows->count() . ' new joins');

        return response()->streamDownload(function () use ($rows, $interests) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Mobile', 'Email', 'Joined', 'Exam types', 'Marked', 'Outcome', 'Note']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->name, $r->mobile, $r->email, $r->created_at?->format('Y-m-d H:i'), collect($interests[$r->id] ?? [])->map(fn ($e) => $e->short_name ?: $e->name)->implode(', '),
                    $r->marked_at ? 'Marked ' . $r->marked_at : 'Not marked', self::OUTCOMES[$r->mark_outcome] ?? '', $r->mark_note]);
            }
            fclose($out);
        }, 'new-joins-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
