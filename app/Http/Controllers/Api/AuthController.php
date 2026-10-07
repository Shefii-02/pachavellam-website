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
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller

{
    /**
     * Build the standard JWT token payload returned to clients.
     */
    protected function respondWithToken(string $token, User $user, int $statusCode = 200, ?string $message = null)
    {
        $payload = [
            'user' => new UserResources($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];

        if ($message) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $statusCode);
    }

    public function SignIn(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');

        $user = User::whereEmail($request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials'
            ], 422);
        }

        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user);
    }

    public function sendOtp(Request $request, \App\Services\OtpService $otp)
    {
        $data = $request->validate([
            'country_code' => 'required|string|max:5',
            'mobile' => 'required|string|max:12',
        ]);

        $phone = $otp->normalize($data['country_code'], $data['mobile']);
        if (!$phone) {
            return response()->json(['message' => 'Please enter a valid mobile number.'], 422);
        }

        $result = $otp->send($phone);

        return response()->json([
            'message' => $result['message'],
            'resends_left' => $result['resends_left'] ?? null,
            'retry_after' => $result['retry_after'] ?? 0,
        ], $result['status']);
    }

    public function verifyOtp(Request $request, \App\Services\OtpService $otp)
    {
        $data = $request->validate([
            'country_code' => 'required|string|max:5',
            'mobile' => 'required|string|max:12',
            'otp' => 'required|digits:6',
        ]);

        $phone = $otp->normalize($data['country_code'], $data['mobile']);
        if (!$phone) {
            return response()->json(['message' => 'Please enter a valid mobile number.'], 422);
        }

        $result = $otp->verify($phone, $data['otp']);
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], $result['status']);
        }

        [$user] = $otp->findOrCreateUser($phone, $data['country_code']);
        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user, 200, 'Signed in successfully');
    }

    public function SignUp(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        try {

            $user = $request->all();

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

                $token = auth('api')->login($newUser);

                return $this->respondWithToken($token, $newUser, 200, 'Signup in successfully');
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
                    $token = auth('api')->login($finduser);

                    return $this->respondWithToken($token, $finduser, 200, 'User signed in successfully');
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

                    $token = auth('api')->login($newUser);

                    return $this->respondWithToken($token, $newUser, 200, 'User signed in successfully');
                }
            } else {
                Log::info(" data error");
                return response()->json(['message' => 'data is null'], 401);
            }
        } catch (Exception $e) {

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Exchange a still-valid refresh token for a new access token.
     */
    public function refresh(Request $request)
    {
        try {
            $token = auth('api')->refresh();
            $user = auth('api')->user();

            return $this->respondWithToken($token, $user);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Refresh token expired, please sign in again'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not refresh token'], 401);
        }
    }

    /**
     * Invalidate the current access token (logout).
     */
    public function logout(Request $request)
    {
        try {
            auth('api')->logout();
            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not log out'], 500);
        }
    }

    /**
     * Return the currently authenticated user for the given access token.
     */
    public function me(Request $request)
    {
        return response()->json(['user' => new UserResources(auth('api')->user())], 200);
    }


    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = auth('api')->user();

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
                
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $storedPath = Storage::disk('public')->putFileAs('users', $image, $filename);
                
                if (!$storedPath) {
                    Log::error('Storage::putFileAs() failed for file: ' . $filename);
                } else {
                    Log::info('New image stored at: ' . $storedPath);
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
        $userId = auth('api')->id();
        $finduser = User::find($userId);

        try {
            if ($finduser) {
                ModelExamAttempt::where('user_id', $userId)->delete();
                DailyExamattempt::where('user_id', $userId)->delete();
                CapsuleComment::where('user_id', $userId)->delete();
                CapsuleLike::where('user_id', $userId)->delete();
                auth('api')->logout();
                $finduser->delete();
                return response()->json(['message' => 'Your Account Deleted'], 200);
            } else {
                return response()->json(['message' => 'Data Not Found, Contact Our Team'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
