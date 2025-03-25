<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institution;
use App\Models\InstitutionService;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Hash;

// use App\Models\Role;

use DB;

class UserController extends Controller
{
    //
    public function index(){
        return view('');
    }
    public function register(){
        $ret =  User::check_filledform(auth()->user()->id);
      
        if($ret != 'dashboard'){
            return view($ret);
        }
        else{
           
            if(\Auth::user()->type == 'Teacher'){
                return redirect()->intended('/teacher/dashboard');
            }
            else if(\Auth::user()->type == 'Student'){
                return redirect()->intended('/student/dashboard');
            }
            else if(\Auth::user()->type == 'Institution'){
                return redirect()->intended('/institution/dashboard');
            }
            else if((\Auth::user()->type == 'Admin') || (\Auth::user()->type == 'Admin Staff')){
                return redirect()->intended('/admin/dashboard');
            }
            else{

                dd('invalid attempt');
            }

        }
        dd($ret);
    }

    
    public function stage1_store(Request $request){
    
        $this->validate($request, [
            'fullname' => 'required',
            'mobile' => 'required',
            'password' => 'required|same:confirm-password',
            'type' => 'required'
        ]);
       
        $user   = User::where('id',auth()->id())->first();
        $user->type = $request->type;
        $user->name = $request->fullname;
        $user->gender = $request->gender;
        $user->dob = $request->year.'-'.$request->month.'-'.$request->day;
        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->pincode = $request->pincode;
        $user->password = Hash::make($request->password);
        $user->reffer_type = $request->heard_about;
        $user->reffered_to = $request->reffer_code ?? null;
        $user->filled_column = 1;
        $user->save();

       
        $user->assignRole($request->type);

        
        return redirect()->intended('register');
    }
    
    public function stage2_store(Request $request){
        if(\Auth::user()->type == 'Teacher'){
            $teacher = new Teacher;
            $teacher->user_id	 = auth()->id();
            $teacher->exp_online	 = $request->exp_online;
            $teacher->exp_offline	 =  $request->exp_offline;
            $teacher->without_payment	 = $request->without_payment;
            $teacher->home_tution = $request->home_tution;
            $teacher->preferable_working = $request->Preferable_working;
            $teacher->save();
            
            
            if($request->has('subjects'))
            {
                $sub = explode(',',$request->subjects);
                foreach($sub as $subjects){
                    
                    $subject_teacher = new Subject;
                    $subject_teacher->user_id = auth()->id();
                    $subject_teacher->subject = $subjects;
                    $subject_teacher->save();

                }
                
            }

            // $request->facility;
            
        }
        if(\Auth::user()->type == 'Student'){
            $student = new Student;
            $student->user_id = auth()->id();	
            $student->grade	 = $request->grade;
            $student->college = $request->college;	
            $student->university = $request->university;
            
            $student->save();
        }
        if(\Auth::user()->type == 'Institution'){
            $institution = new Institution;
            $institution->user_id = auth()->id();
            $institution->website = $request->webiste;
            $institution->mobile_app = $request->mobile_app;
            $institution->save();
            if($request->has('service'))
            {
                $ar = explode(',',$request->service);
                foreach($ar as $service){
                    
                    $institution_service = new InstitutionService;
                    $institution_service->user_id = auth()->id();
                    $institution_service->service = $service;
                    $institution_service->save();

                }
                
            }

            

        }
        $user   = User::where('id',auth()->id())->first();
        $user->filled_column = 2;
        $user->save();
        
        return redirect()->intended('register');

    }
    
    public function account_settings(Request $request){

        if(auth()->check())
        {
            if(auth()->user()->filled_column == 0){
                return view('student.account-settings');
            }
            else{
                return redirect('/');
            }
        }
    }
    
    public function account_settings_submit(Request $request){
         $validator = Validator::make($request->all(), [
                'phone' => 'bail|required|numeric|digits:10',
                'name'      => 'bail|required',
                'email'     => 'bail|required|email',  
            ]);
        
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user   = User::where('id',auth()->id())->first();
           if($user){ 
                try{
                    $mobile = $request->phone;
                    $user->mobile = $mobile;
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->save();
                    return redirect('/');
                }
                catch(Exception $e){
                       dd('invalid attempt');
                }
           }
           else
           {
               dd('invalid attempt');
           }
        
    }
    
    
    





    
}
