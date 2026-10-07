<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'required|string|in:android,ios,web',
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => auth('api')->id(),
                'platform' => $request->platform,
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
}
