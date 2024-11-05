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

class FacebookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }
          
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleFacebookCallback()
    {
        
         date_default_timezone_set('Asia/Kolkata');

        try {
            $user = Socialite::driver('facebook')->stateless()->user();
           
            $finduser = User::where('facebook_id', $user->id)->orwhere('email',$user->email)->first();
        
            if($finduser){
         
                Auth::login($finduser);
        
                // $ret =  User::check_filledform(auth()->user()->id);

                   return redirect()->intended('/kpsc/daily-exam');
            //   return redirect()->intended($ret);
         
            }else{
                $contents = file_get_contents($user->avatar);
                $name = Str::random(40).'png';
                Storage::put("/public/users/".$name, $contents);

                $newUser = new User;
                $newUser->email = $user->email;
                $newUser->name  = $user->name;
                $newUser->email_verified_at = Carbon::now();
                $newUser->google_id= $user->id;
                $newUser->password = Hash::make('123456dummy');
                $newUser->type  = "Student";
                $newUser->reffer_type  = "Telegram";
                $newUser->reffered_to  = "ASIFT";
                $newUser->image = $name;
                $newUser->save();
                
        
                Auth::login($newUser);
        
                // $ret =  User::check_filledform(auth()->user()->id);

                   return redirect()->intended('/kpsc/register');
                // return redirect()->intended($ret);
                // return redirect()->back();
            }
        
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
