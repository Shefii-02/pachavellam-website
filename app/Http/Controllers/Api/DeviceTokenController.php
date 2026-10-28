<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceTokenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'required|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:120',
            'app_version' => 'nullable|string|max:30',
        ]);

        // push turned off in the app: keep no token for this person
        if (auth('api')->check() && !DB::table('users')->where('id', auth('api')->id())->value('push_enabled')) {
            return response()->json(['message' => 'Push notifications are turned off', 'enabled' => false], 200);
        }

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => auth('api')->id(),
                'platform' => $request->platform,
                'device_name' => $request->device_name,
                'app_version' => $request->app_version,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['message' => 'Device token registered'], 200);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:255',
        ]);

        DeviceToken::where('token', $request->token)
            ->where('user_id', auth('api')->id())
            ->delete();

        return response()->json(['message' => 'Device token removed'], 200);
    }

    /** GET api/auth/push-preference -> { enabled } */
    public function preference()
    {
        return response()->json(['status' => 200, 'enabled' => (bool) DB::table('users')->where('id', auth('api')->id())->value('push_enabled')]);
    }

    /** POST api/auth/push-preference {enabled, token?}: off = no push at all (the device token is dropped too). */
    public function setPreference(Request $request)
    {
        $data = $request->validate(['enabled' => 'required|boolean', 'token' => 'nullable|string|max:255']);
        $on = (bool) $data['enabled'];
        DB::table('users')->where('id', auth('api')->id())->update(['push_enabled' => $on]);
        if (!$on) {
            DeviceToken::where('user_id', auth('api')->id())->when(!empty($data['token']), fn ($q) => $q->where('token', $data['token']))->delete();
        }

        return response()->json(['status' => 200, 'enabled' => $on]);
    }
}
