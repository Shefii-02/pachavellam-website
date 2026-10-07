<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\FirebasePushService;
use Illuminate\Http\Request;

class ChatPushController extends Controller
{
    public function __construct(protected FirebasePushService $push)
    {
    }

    public function chatMessage(Request $request)
    {
        $request->validate([
            'recipient_user_ids' => 'required|array|min:1',
            'recipient_user_ids.*' => 'integer',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'array',
        ]);

        $tokens = DeviceToken::whereIn('user_id', $request->recipient_user_ids)->pluck('token')->all();
        if (empty($tokens)) {
            return response()->json(['message' => 'No registered devices for these recipients', 'sent' => false], 200);
        }

        $invalidTokens = $this->push->sendToTokens(
            $tokens,
            $request->title,
            $request->body,
            $request->input('data', [])
        );

        // null means sending was never attempted (credentials missing, or
        // the SDK call failed) — report that honestly rather than as a
        // successful dispatch to zero/some recipients.
        if ($invalidTokens === null) {
            return response()->json(['message' => 'Push not sent (Firebase credentials not configured)', 'sent' => false], 200);
        }

        if (!empty($invalidTokens)) {
            DeviceToken::whereIn('token', $invalidTokens)->delete();
        }

        return response()->json([
            'message' => 'Push dispatched',
            'sent' => true,
            'sent_to' => count($tokens) - count($invalidTokens),
        ], 200);
    }
}
