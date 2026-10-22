<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** Staff Access (admin/staff-access): roles, the new permissions and the exam types each staff member may work in. */
class StaffAccessController extends Controller
{
    private function authorizeManage(Request $request): void
    {
        StaffAccess::authorize($request, 'Staff access manage');
    }

    public function index(Request $request)
    {
        $this->authorizeManage($request);

        $staff = User::where('type', 'Admin Staff')->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')))
            ->with('roles:id,name')->orderBy('name')->get();
        $exams = CompetitiveExam::orderBy('position')->get(['id', 'short_name', 'name']);
        $access = DB::table('staff_exam_access')->whereIn('user_id', $staff->pluck('id'))->get()->groupBy('user_id');
        $last = DB::table('activity_logs')->whereIn('user_id', $staff->pluck('id'))->where('action', 'login')->select('user_id', DB::raw('max(created_at) as at'))->groupBy('user_id')->pluck('at', 'user_id');

        return view('admin.staff.access-index', [
            'staff' => $staff, 'exams' => $exams, 'access' => $access, 'lastLogin' => $last,
            'admins' => User::where('type', 'Admin')->get(['id', 'name', 'email']),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorizeManage($request);
        $user = User::where('type', 'Admin Staff')->findOrFail($id);

        $viaRole = $user->getPermissionsViaRoles()->pluck('name')->all();
        $direct = $user->getDirectPermissions()->pluck('name')->all();

        return view('admin.staff.access-edit', [
            'user' => $user, 'roles' => Role::whereNotIn('name', ['Student', 'Admin'])->orderBy('name')->get(),
            'userRoles' => $user->roles->pluck('name')->all(), 'groups' => config('staff_access.groups'),
            'viaRole' => $viaRole, 'direct' => $direct,
            'exams' => CompetitiveExam::orderBy('position')->get(['id', 'short_name', 'name']),
            'examIds' => DB::table('staff_exam_access')->where('user_id', $user->id)->pluck('exam_id')->map(fn ($v) => (int) $v)->all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManage($request);
        $user = User::where('type', 'Admin Staff')->findOrFail($id);
        $request->validate([
            'roles' => 'nullable|array', 'roles.*' => 'string', 'permissions' => 'nullable|array', 'permissions.*' => 'string',
            'exam_ids' => 'nullable|array', 'exam_ids.*' => 'integer|exists:competitive_exams,id',
        ]);

        $before = ['roles' => $user->roles->pluck('name')->sort()->values()->all(), 'permissions' => $user->getDirectPermissions()->pluck('name')->intersect(StaffAccess::permissionNames())->sort()->values()->all(),
            'exam types' => DB::table('staff_exam_access')->where('user_id', $user->id)->pluck('exam_id')->sort()->values()->all()];

        DB::transaction(function () use ($request, $user) {
            $roles = Role::whereIn('name', (array) $request->roles)->whereNotIn('name', ['Admin', 'Student'])->pluck('name')->all();
            $user->syncRoles($roles);

            // only the permissions of this page are managed here; other direct permissions are left alone
            $managed = StaffAccess::permissionNames();
            $chosen = array_values(array_intersect($managed, (array) $request->permissions));
            $keep = $user->getDirectPermissions()->pluck('name')->diff($managed)->all();
            $user->syncPermissions(array_merge($keep, $chosen));

            DB::table('staff_exam_access')->where('user_id', $user->id)->delete();
            foreach (array_unique(array_map('intval', (array) $request->exam_ids)) as $eid) {
                DB::table('staff_exam_access')->insert(['user_id' => $user->id, 'exam_id' => $eid]);
            }
        });
        StaffAccess::forget($user->id);

        $after = ['roles' => $user->fresh()->roles->pluck('name')->sort()->values()->all(), 'permissions' => $user->fresh()->getDirectPermissions()->pluck('name')->intersect(StaffAccess::permissionNames())->sort()->values()->all(),
            'exam types' => DB::table('staff_exam_access')->where('user_id', $user->id)->pluck('exam_id')->sort()->values()->all()];
        $changes = [];
        foreach ($before as $k => $v) {
            if ($v !== $after[$k]) {
                $changes[$k] = [implode(', ', $v), implode(', ', $after[$k])];
            }
        }
        ActivityLogger::record('updated', 'Staff access', 'Access changed for ' . $user->name, $changes ?: null);

        return redirect(url('admin/staff-access'))->with('success', 'Access saved for ' . $user->name . '.');
    }
}
