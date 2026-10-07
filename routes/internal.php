<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal Routes
|--------------------------------------------------------------------------
|
| Server-to-server only — called by the Node chat-service, never by a
| browser or the mobile app. Guarded by the `internal.secret` middleware
| (a shared secret header), not JWT.
|
*/

Route::post('/push/chat-message', 'Internal\ChatPushController@chatMessage');
