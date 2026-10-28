<?php

namespace App\Http\Middleware;

use App\Services\DeviceSessions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A device that was removed (by the person, "log out other devices", or the panel) stops working at once:
 *  - app: the token carries the device id (sid); a removed one gets 401 {code: session_revoked}
 *  - website: the session keeps the device id; a removed one is signed out and sent to the sign in page
 * If the sign in just hit the device limit (set by the Login listener), the website goes to the "choose a device to remove" page.
 */
class EnforceDeviceSession
{
    public function __construct(private DeviceSessions $devices)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $sid = null;
            if ($request->bearerToken()) {
                try {
                    $sid = JWTAuth::setToken($request->bearerToken())->getPayload()->get('sid');
                } catch (\Throwable $e) {
                    $sid = null;   // expired / invalid tokens are dealt with by the auth guard
                }
            }
            if ($sid && !$this->devices->valid($sid)) {
                return response()->json(['code' => 'session_revoked', 'message' => 'You were signed out because this account was signed in on another device or removed from the device list.'], 401);
            }
            if ($sid) {
                $this->devices->touch($sid, $request);
            }

            return $next($request);
        }

        $sid = $request->hasSession() ? $request->session()->get('device_sid') : null;
        if ($sid && Auth::guard('web')->check() && !$this->devices->valid($sid)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('sign-in')->with('error', 'You were signed out because this device was removed from your device list.');
        }
        if ($sid) {
            $this->devices->touch($sid, $request);
        }

        $response = $next($request);

        // the sign in that just happened was over the device limit
        if ($request->hasSession() && $request->session()->has('device_limit_ticket') && !$request->is('devices/limit*')) {
            return redirect('devices/limit');
        }

        return $response;
    }
}
