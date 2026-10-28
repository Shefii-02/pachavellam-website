<?php

namespace App\Listeners;

use App\Services\DeviceSessions;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/** Website sign in / sign out -> the device list. Over the limit: the sign in is undone and the person is asked which device to remove. */
class WebDeviceSession
{
    public function __construct(private DeviceSessions $devices)
    {
    }

    public function login(Login $event): void
    {
        if ($event->guard !== 'web' || !request()->hasSession()) {
            return;
        }
        $user = $event->user;
        $request = request();
        // staff, and staff impersonating a student, are never limited or counted
        if ($this->devices->exempt($user) || $request->session()->has('impersonator_id') || $request->session()->has('impersonating')) {
            return;
        }
        if ($this->devices->overLimit($user)) {
            $ticket = $this->devices->ticket($user);
            $remember = $request->hasCookie(\Illuminate\Support\Facades\Auth::guard('web')->getRecallerName());
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            $request->session()->flush();
            $request->session()->put('device_limit_ticket', $ticket);
            $request->session()->put('device_limit_remember', $remember);

            return;
        }
        $request->session()->put('device_sid', $this->devices->open($user, $request, 'web')->sid);
    }

    public function logout(Logout $event): void
    {
        if ($event->guard === 'web' && request()->hasSession()) {
            $this->devices->revokeSid(request()->session()->get('device_sid'), 'logout');
        }
    }
}
