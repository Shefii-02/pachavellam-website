<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Carbon\Carbon;
// use Kreait\Firebase\Auth;
// use Kreait\Firebase\Exception\Auth\InvalidIdToken;

class MobileNumberController extends Controller
{
    // protected $firebaseAuth;

    // public function __construct(Auth $firebaseAuth)
    // {
    //     $this->firebaseAuth = $firebaseAuth;
    // }

    // public function sendOTP(Request $request)
    // {
    //     // Implement code to send OTP to the user's phone number or email
    //     // This will involve using the Firebase Authentication SDK
        
    //     // For example, you might want to use Firebase phone authentication:
    //     $phoneNumber = $request->input('phone_number');

    //     $verificationId = $this->firebaseAuth->sendSignInLinkToEmail($phoneNumber, null);

    //     return response()->json(['verificationId' => $verificationId]);
    // }

    // public function verifyOTP(Request $request)
    // {
    //     try {
    //         $verificationId = $request->input('verificationId');
    //         $code = $request->input('code');

    //         $firebaseAuth = $this->firebaseAuth->signInWithPhoneNumber($verificationId, $code);

    //         // Implement code to handle successful OTP verification (e.g., create user in Laravel)
            
    //         return response()->json(['message' => 'OTP verified successfully']);
    //     } catch (InvalidIdToken $e) {
    //         return response()->json(['error' => 'Invalid OTP'], 401);
    //     }
    // }
    
    public function mobileNoVerification(Request $request){
         $userData = $request->json('user', []);
        $phoneNumber = isset($userData['phoneNumber']) ? $userData['phoneNumber'] : null;
    
        if($phoneNumber){
            
            $cleanedPhoneNumber = str_replace('+91', '', $phoneNumber);

           $existingUser = User::where('mobile', $phoneNumber)
                    ->orWhere('mobile', $cleanedPhoneNumber)
                    ->first();

            if ($existingUser) {
            
                auth()->login($existingUser, true);
        
                if ($existingUser->name && $existingUser->email) {
                    return response()->json(['status' => 'success', 'message' => 'Logged in Successfully.','redirect' => '/']);
                } else {
                   
                    return response()->json(['status' => 'success', 'message' => 'Logged in Successfully but additional information needed.','redirect' => 'account-settings']);
                }
            } else {
                    $newUser = new User();
                    $newUser->email =$phoneNumber;;
                    $newUser->password = Hash::make('123456dummy');
                    $newUser->type  = "Student";
                    $newUser->reffer_type  = "Telegram";
                    $newUser->reffered_to  = "ASIFT";
                    $newUser->mobile_verified_at = Carbon::now();
                    $newUser->mobile = $phoneNumber;
                    $newUser->filled_column = 0;
                    $newUser->save();
                                auth()->login($newUser, true);
                    return response()->json(['status' => 'success', 'message' => 'Logged in Successfully but additional information needed.','redirect' => 'account-settings']);
            }
        }
        else{
                return response()->json(['status' => 'error', 'message' => 'Invalid Attempt','redirect' => '/']);
        }
    }
    
    
    
    
}
