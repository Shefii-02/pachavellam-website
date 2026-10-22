<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ClassChatController;
use Illuminate\Http\Request;

/** The chat beside a free class / video for the apps: reading is open to everyone, writing needs the student's token. */
class ClassChatApiController extends ClassChatController
{
    public function messages(Request $request, $uid)
    {
        auth()->shouldUse('api');

        return parent::messages($request, $uid);
    }

    public function send(Request $request, $uid)
    {
        auth()->shouldUse('api');

        return parent::send($request, $uid);
    }
}
