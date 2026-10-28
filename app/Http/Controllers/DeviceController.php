<?php

namespace App\Http\Controllers;

use App\Models\DeviceSession;
use App\Services\DeviceSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Website pages for signed in devices.
 *   devices/limit        sign in stopped (too many devices): choose which to remove, then continue
 *   devices              my devices: remove one, or sign out everywhere else
 */
class DeviceController extends Controller
{
    public function __construct(private DeviceSessions $devices)
    {
    }

    private function pending(Request $request)
    {
        return $this->devices->userFromTicket($request->session()->get('device_limit_ticket'));
    }

    public function limit(Request $request)
    {
        $user = $this->pending($request);
        if (!$user) {
            $request->session()->forget(['device_limit_ticket', 'device_limit_remember']);

            return redirect('sign-in')->with('error', 'Please sign in again.');
        }

        return view('kpsc.devices-limit', ['title' => 'Too many devices', 'limit' => $this->devices->limit($user), 'rows' => $this->devices->live($user), 'name' => $user->name]);
    }

    public function release(Request $request)
    {
        $user = $this->pending($request);
        if (!$user) {
            return redirect('sign-in');
        }
        $this->devices->revoke($user, $request->boolean('all') ? null : (array) $request->input('ids', []), 'user');
        if ($this->devices->overLimit($user)) {
            return back()->with('error', 'Choose at least one device to sign out.');
        }
        $remember = (bool) $request->session()->pull('device_limit_remember', false);
        $request->session()->forget('device_limit_ticket');
        Auth::guard('web')->login($user, $remember);   // opens this device (the Login listener)

        return redirect()->intended('/');
    }

    public function index(Request $request)
    {
        return view('kpsc.devices', ['title' => 'My devices', 'limit' => $this->devices->limit($request->user()), 'rows' => $this->devices->live($request->user()), 'sid' => $request->session()->get('device_sid')]);
    }

    public function remove(Request $request, $id)
    {
        $row = DeviceSession::where('user_id', $request->user()->id)->findOrFail($id);
        $this->devices->revoke($request->user(), [$row->id], 'user');

        return back()->with('message', 'Device signed out');
    }

    public function logoutOthers(Request $request)
    {
        $n = $this->devices->revoke($request->user(), null, 'other-devices', $request->session()->get('device_sid'));

        return back()->with('message', $n . ' device(s) signed out');
    }
}
