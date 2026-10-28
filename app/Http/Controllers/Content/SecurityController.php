<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\SiteSetting;
use App\Services\Captcha;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** admin/security: CAPTCHA on / off and where, request limits (DoS protection), blocked and always-allowed IPs. Admin only. */
class SecurityController extends Controller
{
    private function allow(Request $r): void
    {
        abort_unless(StaffAccess::isAdmin($r->user()), 403, 'Only an administrator can change security settings.');
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $g = fn ($k, $d) => SiteSetting::get($k, $d);

        return view('admin.content.security', [
            'forms' => Captcha::FORMS, 'on' => Captcha::enabled(), 'chosen' => Captcha::forms(), 'mode' => $g('captcha_mode', 'always'), 'failures' => $g('captcha_failures', '3'),
            'rl' => ['on' => $g('rl_on', '1'), 'web' => $g('rl_web', '240'), 'api' => $g('rl_api', '300'), 'auth' => $g('rl_auth', '20'), 'after' => $g('rl_autoblock_after', '5'), 'minutes' => $g('rl_autoblock_minutes', '30')],
            'ips' => BlockedIp::orderByDesc('id')->limit(200)->get(), 'myIp' => $request->ip(),
        ]);
    }

    public function captcha(Request $request)
    {
        $this->allow($request);
        $d = $request->validate(['mode' => 'required|in:always,after_failures', 'failures' => 'required|integer|min:1|max:20', 'forms' => 'nullable|array', 'forms.*' => 'in:' . implode(',', array_keys(Captcha::FORMS))]);
        SiteSetting::put('captcha_on', $request->boolean('on') ? '1' : '0');
        SiteSetting::put('captcha_mode', $d['mode']);
        SiteSetting::put('captcha_failures', (string) $d['failures']);
        SiteSetting::put('captcha_forms', implode(',', $d['forms'] ?? []));

        return back()->with('message', 'CAPTCHA settings saved');
    }

    public function limits(Request $request)
    {
        $this->allow($request);
        $d = $request->validate(['web' => 'required|integer|min:0|max:100000', 'api' => 'required|integer|min:0|max:100000', 'auth' => 'required|integer|min:0|max:10000', 'after' => 'required|integer|min:0|max:100', 'minutes' => 'required|integer|min:1|max:10080']);
        SiteSetting::put('rl_on', $request->boolean('on') ? '1' : '0');
        foreach (['web', 'api', 'auth'] as $k) {
            SiteSetting::put('rl_' . $k, (string) $d[$k]);
        }
        SiteSetting::put('rl_autoblock_after', (string) $d['after']);
        SiteSetting::put('rl_autoblock_minutes', (string) $d['minutes']);

        return back()->with('message', 'Request limits saved');
    }

    public function addIp(Request $request)
    {
        $this->allow($request);
        $d = $request->validate(['ip' => 'required|ip', 'kind' => 'required|in:block,allow', 'reason' => 'nullable|string|max:150', 'hours' => 'nullable|integer|min:1|max:8760']);
        abort_if($d['kind'] === 'block' && $d['ip'] === $request->ip(), 422, 'That is your own IP - you would lock yourself out.');
        BlockedIp::updateOrCreate(['ip' => $d['ip']], ['kind' => $d['kind'], 'reason' => $d['reason'] ?? null, 'until' => !empty($d['hours']) ? now()->addHours($d['hours']) : null, 'created_by' => $request->user()->id]);
        Cache::forget('security:ips');

        return back()->with('message', 'IP list updated');
    }

    public function removeIp(Request $request, $id)
    {
        $this->allow($request);
        BlockedIp::whereKey($id)->delete();
        Cache::forget('security:ips');

        return back()->with('message', 'IP removed from the list');
    }
}
