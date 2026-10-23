<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TrashPurger;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** Trash (admin/trash): what was deleted from the libraries, course exams and Roaring Study. Restore it, or remove it for good. */
class TrashController extends Controller
{
    private function model(string $type)
    {
        abort_unless(isset(TrashPurger::TYPES[$type]), 404);

        return TrashPurger::TYPES[$type][0];
    }

    /** Staff limited to some exam types only see the trash of those exam types. */
    private function scoped($query, string $type, Request $request)
    {
        $ids = StaffAccess::examIds($request->user());
        if ($ids === null) {
            return $query;
        }

        return $type === 'roaring'
            ? $query->whereHas('exams', fn ($q) => $q->whereIn('competitive_exams.id', $ids ?: [0]))
            : $query->whereIn('exam_id', $ids ?: [0]);
    }

    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'Trash manage');
        $type = $request->input('type', 'questions');
        $class = $this->model($type);

        $list = $this->scoped($class::onlyTrashed(), $type, $request)
            ->when($request->filled('q') && $type !== 'questions', fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('deleted_at')->paginate(per_page(25))->withQueryString();

        $counts = [];
        foreach (TrashPurger::TYPES as $k => [$c]) {
            $counts[$k] = $this->scoped($c::onlyTrashed(), $k, $request)->count();
        }
        $who = User::whereIn('id', $list->pluck('deleted_by')->filter())->pluck('name', 'id');

        return view('admin.staff.trash', ['type' => $type, 'list' => $list, 'counts' => $counts, 'types' => TrashPurger::TYPES, 'who' => $who]);
    }

    public function restore(Request $request, $type, $id)
    {
        StaffAccess::authorize($request, 'Trash manage');
        $item = $this->scoped($this->model($type)::onlyTrashed(), $type, $request)->findOrFail($id);
        $item->restore();

        return back()->with('success', 'Restored.');
    }

    public function destroy(Request $request, $type, $id)
    {
        StaffAccess::authorize($request, 'Trash manage');
        $item = $this->scoped($this->model($type)::onlyTrashed(), $type, $request)->findOrFail($id);
        TrashPurger::purge($item);

        return back()->with('success', 'Removed for good.');
    }

    /** Remove everything in this tab that has been in Trash for more than 30 days. */
    public function purgeOld(Request $request, $type)
    {
        StaffAccess::authorize($request, 'Trash manage');
        $n = 0;
        foreach ($this->scoped($this->model($type)::onlyTrashed()->where('deleted_at', '<', now()->subDays(30)), $type, $request)->get() as $item) {
            TrashPurger::purge($item);
            $n++;
        }

        return back()->with('success', $n . ' old item(s) removed for good.');
    }
}
