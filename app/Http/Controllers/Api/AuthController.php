<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\{User, ModelExamAttempt, DailyExamattempt, CapsuleComment, CapsuleLike};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Resources\UserResources;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller

{
    public function SignIn(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');

        $user = User::whereEmail($request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials'
            ], 422);
        }

        $token = '1234567890zyxwvutsrqponmlkjihgfedcba';

        return response([
            'user' => new UserResources($user),
            'token' => $token
        ], 200);
    }

    public function SignUp(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        try {

            $user = $request->all();
            $token = '1234567890zyxwvutsrqponmlkjihgfedcba';

            if ($user) {
                $finduser = User::where('email', $user['email'])->first();
                if ($finduser) {
                    return response()->json(['message' => 'Email Address Already Exist'], 401);
                }

                $newUser = new User;
                $newUser->email = $user['email'];
                $newUser->name = $user['fullName'];
                $newUser->google_id = null;
                $newUser->password = Hash::make($user['password']);
                $newUser->image = null;
                $newUser->type  = "Student";
                $newUser->reffer_type  = "Telegram";
                $newUser->reffered_to  = "ASIFT";
                $newUser->email_verified_at = null;
                $newUser->save();

                return response()->json(['message' => 'Signup in successfully', 'user' => new UserResources($newUser), 'token' => $token], 200);
            } else {
                Log::info(" data error");
                return response()->json(['message' => 'data is null'], 401);
            }
        } catch (Exception $e) {

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function googleSignIn(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');

        try {

            $user = $request->all();
            $token = '1234567890zyxwvutsrqponmlkjihgfedcba';

            if ($user) {

                $finduser = User::where('google_id', $user['uid'])->orwhere('email', $user['email'])->first();
                if ($finduser) {
                    $finduser->email = $user['email'];
                    $finduser->google_id = $user['uid'];
                    if ($finduser->image == null || $finduser->image == '') {
                        $contents = file_get_contents($user['image']);
                        $name = Str::random(40) . '.png';
                        Storage::put("/public/users/" . $name, $contents);
                        $finduser->image = $name;
                    }
                    $finduser->save();

                    Log::info('user existed');
                    return response()->json(['message' => 'User signed in successfully', 'user' => new UserResources($finduser), 'token' => $token], 200);
                } else {

                    Log::info('user new');

                    $contents = file_get_contents($user['image']);
                    $name = Str::random(40) . '.png';

                    Storage::put("/public/users/" . $name, $contents);

                    $newUser = new User;
                    $newUser->email = $user['email'];
                    $newUser->name = $user['name'];
                    $newUser->google_id = $user['uid'];
                    $newUser->password = Hash::make('123456dummy');
                    $newUser->image = $name;
                    $newUser->type  = "Student";
                    $newUser->reffer_type  = "Telegram";
                    $newUser->reffered_to  = "ASIFT";
                    $newUser->email_verified_at = Carbon::now();
                    $newUser->save();

                    return response()->json(['message' => 'User signed in successfully', 'user' => new UserResources($newUser), 'token' => $token], 200);
                }
            } else {
                Log::info(" data error");
                return response()->json(['message' => 'data is null'], 401);
            }
        } catch (Exception $e) {

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }


    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            Log::info('Update Profile Request:', $request->all());

            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                Log::info('Uploading file: ' . $image->getClientOriginalName() . ' (' . $image->getSize() . ' bytes)');
            
                // Delete old image if exists
                if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
                    Storage::disk('public')->delete('users/' . $user->image);
                }
            
                // Create a custom file name
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
                // Read the file content
                $content = file_get_contents($image->getRealPath());
            
                // Put the file in the 'users' directory on the public disk
                $path = Storage::disk('public')->put('users/' . $filename, $content);
            
                if (!$path) {
                    Log::error('Image storage failed for file: ' . $image->getClientOriginalName());
                } else {
                    Log::info('New image stored at: users/' . $filename);
                    $user->image = $filename;
                }
            }   
            

            $user->name = $request->full_name;
            $user->mobile = $request->mobile;
            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResources($user)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function forgetPassword(Request $request) {}



    public function deleteMyaccount(Request $request)
    {
        $finduser = User::where('id', $request->user_id)->first();

        try {
            if ($finduser) {
                ModelExamAttempt::where('user_id', $request->user_id)->delete();
                DailyExamattempt::where('user_id', $request->user_id)->delete();
                CapsuleComment::where('user_id', $request->user_id)->delete();
                CapsuleLike::where('user_id', $request->user_id)->delete();
                User::where('id', $request->user_id)->delete();
                return response()->json(['message' => 'Your Account Deleted'], 200);
            } else {
                return response()->json(['message' => 'Data Not Found, Contact Our Team'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
