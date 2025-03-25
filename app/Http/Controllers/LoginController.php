<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use Hash;


class LoginController extends Controller
{
    //
    public function index(){
      return view('web.sign-in');
    }

    public function create(){
        return  view('web.sign-up');
    }

    public function sign_out(){
            Auth::logout();
            return redirect('/');
        
    }
    
    
    

    public function check(Request $request){
        $request->validate(['email'	=> 'bail|required|email', 'password' => 'bail|required']);
		
            $credentials = [
                'email' => $request['email'],
                'password' => $request['password'],
            ];
        
            $remember_me = $request->has('remember_me') ? true : false; 
 
        
            if (Auth::attempt($credentials,$remember_me)) {
                return redirect()->intended('/register');
            }
            else{
                return redirect()->back()->withMessage('Invalid login attempt');
            }
        
    }
    
    
    public function register_new(Request $request){
        if (Auth::check()){
            
        $user_val = User::where('id', auth()->user()->id)
                            ->where(function ($query) {
                                $query->whereNull('mobile')->orWhereNull('email');
                            })
                            ->first();
            
            if($user_val){
                  return view('kpsc.register'); 
            }
            else{
              return redirect()->intended('/kpsc');
            }
        }
    }
    
    public function register_submit(Request $request){
      
        
        $validator = Validator::make($request->all(), [
                            'mobile_no' => 'required|min:10',
                            'password'  => 'required|min:6',
                            'confirmed' => 'required_with:password|same:password'
                        ]);
                        
        if($validator->fails()){
            
            return redirect()->back()->withErrors($validator);
                    
        } else{
        
            $user_val =  User::where('id',auth()->user()->id)->first();
            $user_val->name = $request->name;
            $user_val->email = $request->email;
            $user_val->mobile = $request->mobile_no;
            $user_val->password = Hash::make($request->password);
            
            try{
                
                $user_val->save();
                
                return redirect()->intended('/kpsc');
            }
            catch(\Exception $e){
                return redirect()->back();
            }
        }
    }
    
}
