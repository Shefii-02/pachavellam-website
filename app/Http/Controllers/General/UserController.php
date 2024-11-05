<?php

namespace App\Http\Controllers\General;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institution;
use App\Models\InstitutionService;
use App\Models\Subject;
use App\Models\Pages;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

use Hash;


use DB;

class UserController extends Controller
{
    //
    public function users(){
        $list = User::leftJoin('model_has_roles','model_has_roles.model_id','users.id')
                      ->leftJoin('roles','model_has_roles.role_id','roles.id')
                      ->where('type','Admin Staff')
                      ->select('users.*','roles.name as role_name')
                      ->get();
        return view('admin.general.users',compact('list'));
    }
   
    public function users_store(Request $request){
        $newUser        = new User;
        $newUser->email = $request->email_id;
        $newUser->name  = $request->full_name;
        $newUser->password = Hash::make($request->Password);
        $newUser->mobile = $request->mobile_no;
        $newUser->type   = "Admin Staff";
        $newUser->filled_column = 2;
        $newUser->save();
        $newUser->assignRole($request->role);
        
        return redirect()->back()->with('message','Data added Successfully');
        
       
    }
    public function users_delete($id){

        User::where('id',$id)->delete();
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function users_updating(Request $request){
        $newUser        = User::where('id',$request->id)->first();
        $newUser->email = $request->email_id;
        $newUser->name  = $request->full_name;
        $newUser->mobile = $request->mobile_no;
        $newUser->save();
        $newUser->syncRoles($request->role);

        if($request->Password != null){
            $newUser->password = Hash::make($request->Password);
            $newUser->save();
        }
        

        
        return redirect()->back()->with('message','Data added Successfully');
    }


    public function sever_cache_clear(){
        \Artisan::call('config:clear');
        \Artisan::call('optimize:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        \Artisan::call('migrate');
        return redirect()->back()->with('message','Server cache cleared');

    }
    
    public function pages(){
        
        $pages = Pages::orderby('position')->get();
        
        return view('admin.general.pages',compact('pages'));
    }
    
    public function pages_store(Request $request){
        $add_data = new Pages;
        $add_data->title = $request->title;
        $add_data->content = $request->content;
        $add_data->position = 1;
        $add_data->post_date = $request->date;
        $add_data->status =1;
        $add_data->save();
        
        return redirect()->back()->with('message','Page Created Successfully');
    }
    
    public function update_pages(Request $request){
        $pages = Pages::where('id',$request->id)->first(); 
        $pages->title = $request->title;
        $pages->content = $request->content;
        $pages->post_date = $request->date;
        $pages->position =$request->position;
        $pages->status = $request->status;
        $pages->save();
        
        
        return redirect()->back()->with('message','Page Updated  Successfully');
    }
    
    public function pages_delete ($id){
        Pages::where('id',$id)->delete();
        
         return redirect()->back()->with('message','Page Deleted  Successfully');
    }

    
    
}
