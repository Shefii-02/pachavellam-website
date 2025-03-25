<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Auth;
use App\Helper;
use Storage;

class HomeController extends Controller
{
    //
    public function dashboard(){

        return view('admin.student.dashboard');
    }


    public function students_list(){
        $list = User::orderby('created_at')->get();
        return view('admin.student.student-list',compact('list'));
    }
    
    public function student_delete($slug){
        
        if(auth()->user()->type == 'Admin'){
            User::where('id',$slug)->delete();
            
            return redirect()->back()->with('message','Deleted Successfully');
        }
        else{
            abort(404);
        }
    }
}
