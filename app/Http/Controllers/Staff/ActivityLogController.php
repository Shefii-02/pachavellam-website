<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompetitiveExam;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Staff activity log (admin/activity-logs): every event, filterable by person, action, exam type, item and date. */
class ActivityLogController extends Controller
{
    private function query(Request $request)
    {
        $user = $request->user();
        $q = ActivityLog::query()
            ->when($request->filled('staff'), fn ($w) => $w->where('user_id', $request->staff))
            ->when($request->filled('action'), fn ($w) => $w->where('action', $request->action))
            ->when($request->filled('type'), fn ($w) => $w->where('subject_type', $request->type))
            ->when($request->filled('exam'), fn ($w) => $w->where('exam_id', $request->exam))
            ->when($request->filled('from'), fn ($w) => $w->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($w) => $w->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($w) => $w->where(fn ($x) => $x->where('subject_label', 'like', '%' . $request->q . '%')->orWhere('description', 'like', '%' . $request->q . '%')));

        // someone limited to some exam types sees the events of those exam types (and the general ones: sign-ins)
        $ids = StaffAccess::examIds($user);
        if ($ids !== null) {
            $q->where(fn ($w) => $w->whereNull('exam_id')->orWhereIn('exam_id', $ids ?: [0]));
        }

        return $q;
    }

    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'Activity log view');

        $list = $this->query($request)->orderByDesc('id')->paginate(40)->withQueryString();

        return view('admin.staff.activity-logs', [
            'list' => $list, 'staff' => User::where('type', '!=', 'Student')->orderBy('name')->get(['id', 'name']),
            'exams' => CompetitiveExam::orderBy('position')->get(['id', 'short_name', 'name']),
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'types' => ActivityLog::query()->whereNotNull('subject_type')->select('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
        ]);
    }

    public function export(Request $request)
    {
        StaffAccess::authorize($request, 'Activity log view');
        $rows = $this->query($request)->with('exam:id,short_name,name')->orderByDesc('id')->limit(50000)->get();
        ActivityLogger::record('exported', 'Activity log', 'Exported ' . $rows->count() . ' log rows');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['When', 'Staff', 'Action', 'Item type', 'Item', 'Exam type', 'Details', 'Changes', 'IP', 'Where']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->created_at->format('Y-m-d H:i:s'), $r->user_name, $r->action, $r->subject_type, $r->subject_label, $r->exam?->short_name ?: $r->exam?->name, $r->description,
                    collect($r->changes ?? [])->map(fn ($c, $f) => $f . ': ' . ($c[0] ?? '') . ' → ' . ($c[1] ?? ''))->implode('; '), $r->ip, $r->url]);
            }
            fclose($out);
        }, 'activity-log-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
