<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Chats page (admin/chat). The chat itself runs in the Node.js chat-server: this page only
 * gives the logged in staff member a token for it (the same JWT kind the apps use) and shows the screen.
 */
class ChatController extends Controller
{
    private function authorizeChat(Request $request): void
    {
        StaffAccess::authorize($request, 'Staff chat use');
    }

    public function index(Request $request)
    {
        $this->authorizeChat($request);

        return view('admin.chat.index', [
            'chatServer' => config('chat.server_url'),
            'token' => JWTAuth::fromUser($request->user()),
            'me' => ['id' => $request->user()->id, 'name' => $request->user()->name, 'is_admin' => StaffAccess::isAdmin($request->user())],
            'viewingAs' => session('impersonator_id') ? true : false,
        ]);
    }

    /** A fresh token (they last an hour; the page asks again before that). */
    public function token(Request $request)
    {
        $this->authorizeChat($request);

        return response()->json(['token' => JWTAuth::fromUser($request->user())]);
    }
}
