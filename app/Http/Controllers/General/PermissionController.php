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
        return view('admin.general.permission',compact('list'));
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
