<?php

namespace App\Http\Controllers\General;;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    //
    public function permission_index(){
        $list = Permission::get();
        $exams = \App\Models\CompetitiveExam::orderBy('position')->get();

        // permission name => names of the roles that have it
        $holders = \Illuminate\Support\Facades\DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->select('permissions.name as permission', 'roles.name as role')->get()
            ->groupBy('permission')->map(fn ($rows) => $rows->pluck('role')->sort()->values()->all())->all();

        return view('admin.general.permission', [
            'list' => $list, 'exams' => $exams, 'holders' => $holders,
            'catalogs' => $exams->mapWithKeys(fn ($e) => [$e->id => \App\Support\ExamFeatures::catalog($e)])->all(),
            'categories' => array_values(array_unique(array_merge(['General', 'Common', 'UG-PG'], $exams->map(fn ($e) => $e->short_name ?: $e->name)->all(), $list->pluck('category')->filter()->all()))),
        ]);
    }

    public function permission_store(Request $request){
        $add_new = new Permission;
        $add_new->name = $request->permission_name;
        $add_new->category =  $request->category;
        $add_new->guard_name = 'web';
        $add_new->save();

        return redirect()->back()->with('message','Data added Successfully');
      
    }
    public function permission_updating(Request $request){
        $add_new = Permission::where('id',$request->id)->first();
        $add_new->name =$request->permission_name;
        $add_new->category =  $request->category;
        $add_new->save();
        
        return redirect()->back()->with('message','Data updated Successfully');
    }
    public function permission_delete($id){
        Permission::where('id',$id)->delete();
        
        return redirect()->back()->with('message','Data deleted Successfully');
    }  
    
}
