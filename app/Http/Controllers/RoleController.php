<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use App\Models\CompetitiveExam;
use App\Support\ExamFeatures;
use App\Support\StaffAccess;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //  $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
        //  $this->middleware('permission:role-create', ['only' => ['create','store']]);
        //  $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
        //  $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('id', 'asc')->paginate(per_page(5))->withQueryString();
        return view('admin.roles.index', compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.roles.create', $this->formData(null));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            // 'permission' => 'required',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));
        $this->saveExams($role->id, $request);

        return redirect()->route('admingeneralrole.index')
            ->with('success', 'Role created successfully');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();

        return view('admin.roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        return view('admin.roles.edit', $this->formData($role) + ['role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required|array', // Ensure it's an array
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->save();

        // Convert permission IDs to Names
        $permissions = Permission::whereIn('id', $request->input('permission'))->pluck('name')->toArray();

        // Sync permissions using names, not IDs
        $role->syncPermissions($permissions);
        $this->saveExams($role->id, $request);
        StaffAccess::forget();

        return redirect()->route('admingeneralrole.index')
            ->with('success', 'Role updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id', $id)->delete();
        return redirect()->route('admingeneralrole.index')
            ->with('success', 'Role deleted successfully');
    }

    /** Everything the role form needs: permissions by section, the exam types and what each exam type shows. */
    private function formData(?Role $role): array
    {
        $permission = Permission::get();
        $groups = config('staff_access.groups');
        $inGroups = StaffAccess::permissionNames();
        $exams = CompetitiveExam::orderBy('position')->get();

        return [
            'permission' => $permission,
            'groups' => $groups,
            'other' => $permission->reject(fn ($p) => in_array($p->name, $inGroups, true)),
            'rolePermissions' => $role ? DB::table('role_has_permissions')->where('role_id', $role->id)->pluck('permission_id', 'permission_id')->all() : [],
            'exams' => $exams,
            'roleExams' => $role ? DB::table('role_exam_access')->where('role_id', $role->id)->pluck('exam_id')->map(fn ($v) => (int) $v)->all() : [],
            'examFeatures' => $exams->mapWithKeys(fn ($e) => [$e->id => ExamFeatures::catalog($e)])->all(),
            'enabled' => $exams->mapWithKeys(fn ($e) => [$e->id => collect(ExamFeatures::keys($e))->filter(fn ($k) => ExamFeatures::enabled($e, $k))->values()->all()])->all(),
        ];
    }

    private function saveExams(int $roleId, Request $request): void
    {
        DB::table('role_exam_access')->where('role_id', $roleId)->delete();
        foreach (array_unique(array_map('intval', (array) $request->input('exam_ids', []))) as $eid) {
            if (CompetitiveExam::whereKey($eid)->exists()) {
                DB::table('role_exam_access')->insert(['role_id' => $roleId, 'exam_id' => $eid]);
            }
        }
    }
}
