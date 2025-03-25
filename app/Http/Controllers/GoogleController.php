<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;

use Exception;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

use Storage;
use Hash;
use Carbon\Carbon;

use Illuminate\Support\Str;

class GoogleController extends Controller

{

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function redirectToGoogle()

    {

        return Socialite::driver('google')->redirect();

    }

          

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function handleGoogleCallback()

    {

        date_default_timezone_set('Asia/Kolkata');
        
        try {
 
            $user = Socialite::driver('google')->stateless()->user();
            $finduser = User::where('google_id', $user->id)->orwhere('email',$user->email)->first();
            if($finduser){
                Auth::login($finduser,true);

                // $ret =  User::where('id',auth()->user()->id)->first();
            
                if($finduser->mobile == ''){
                      return redirect()->route('account-settings');
                }
                else
                {
                    return redirect()->intended('/');
                    //   return redirect('/');
                }

            //   return redirect()->back();
         

            }else{

                $contents = file_get_contents($user->avatar);
                $name = Str::random(40).'.png';
                
                Storage::put("/public/users/".$name, $contents);

                $newUser = new User;
                $newUser->email = $user->email;
                $newUser->name = $user->name;
                $newUser->google_id= $user->id;
                $newUser->password = Hash::make('123456dummy');
                $newUser->image = $name;
                $newUser->type  = "Student";
                $newUser->reffer_type  = "Telegram";
                $newUser->reffered_to  = "ASIFT";
                $newUser->email_verified_at = Carbon::now();
                $newUser->save();

                Auth::login($newUser,true);

                 return redirect()->intended('/kpsc/register');

            }

        

        } catch (Exception $e) {

            dd($e->getMessage());

        }

    }

}