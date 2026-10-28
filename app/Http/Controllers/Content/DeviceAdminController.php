<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\DeviceSession;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\DeviceSessions;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/devices: how many devices are signed in (website / app), who they belong to, remove a device, and the device limit. Admin only. */
class DeviceAdminController extends Controller
{
    public function __construct(private DeviceSessions $devices)
    {
    }

    private function allow(Request $r): void
    {
        abort_unless(StaffAccess::isAdmin($r->user()), 403, 'Only an administrator can manage devices.');
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $stale = now()->subDays($this->devices->staleDays());
        $live = fn () => DeviceSession::live()->where(fn ($q) => $q->where('last_active_at', '>=', $stale)->orWhereNull('last_active_at'));

        $byPlatform = $live()->select('channel', 'platform', DB::raw('count(*) as devices'), DB::raw('count(distinct user_id) as people'))->groupBy('channel', 'platform')->get();
        $stats = [
            'devices' => $live()->count(), 'people' => $live()->distinct('user_id')->count('user_id'),
            'web' => $live()->where('channel', 'web')->count(), 'app' => $live()->where('channel', 'app')->count(),
            'online' => $live()->where('last_active_at', '>=', now()->subMinutes(10))->count(),
            'blocked' => DeviceSession::whereNotNull('revoked_at')->where('revoked_at', '>=', now()->subDay())->count(),
        ];

        $user = $request->filled('user') ? User::find($request->user) : null;
        $q = $live()->with('user:id,name,email,mobile,device_limit')->when($user, fn ($w) => $w->where('user_id', $user->id))
            ->when($request->filled('channel'), fn ($w) => $w->where('channel', $request->channel))
            ->when($request->filled('q') && !$user, fn ($w) => $w->whereIn('user_id', User::where('name', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')->orWhere('mobile', 'like', '%' . $request->q . '%')->select('id')))
            ->orderByDesc('last_active_at');

        return view('admin.content.devices', [
            'stats' => $stats, 'byPlatform' => $byPlatform, 'rows' => $q->paginate(30)->withQueryString(), 'user' => $user,
            'limit' => (int) SiteSetting::get('device_limit', '2'), 'stale' => $this->devices->staleDays(),
            'userLimit' => $user ? $this->devices->limit($user) : null,
        ]);
    }

    public function remove(Request $request, $id)
    {
        $this->allow($request);
        $row = DeviceSession::findOrFail($id);
        $this->devices->revoke($row->user, [$row->id], 'admin');

        return back()->with('message', 'Device signed out - it stops working at once');
    }

    public function removeAll(Request $request, $userId)
    {
        $this->allow($request);
        $n = $this->devices->revoke(User::findOrFail($userId), null, 'admin');

        return back()->with('message', $n . ' device(s) signed out');
    }

    public function settings(Request $request)
    {
        $this->allow($request);
        $d = $request->validate(['device_limit' => 'required|integer|min:0|max:20', 'device_stale_days' => 'required|integer|min:1|max:365']);
        SiteSetting::put('device_limit', (string) $d['device_limit']);
        SiteSetting::put('device_stale_days', (string) $d['device_stale_days']);

        return back()->with('message', 'Device settings saved');
    }

    public function userLimit(Request $request, $userId)
    {
        $this->allow($request);
        $d = $request->validate(['device_limit' => 'nullable|integer|min:0|max:20']);
        User::whereKey($userId)->update(['device_limit' => $d['device_limit'] ?? null]);

        return back()->with('message', 'Limit for this person saved');
    }
}
