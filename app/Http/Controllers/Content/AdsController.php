<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\AdUnit;
use App\Models\SiteSetting;
use App\Support\AdPages;
use App\Support\Ads;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** admin/ads: Google ads on / off, which kinds, where, and how often - for the apps (AdMob) and the website (AdSense). Admin only. */
class AdsController extends Controller
{
    private function allow(Request $r): void
    {
        abort_unless(StaffAccess::isAdmin($r->user()), 403, 'Only an administrator can manage ads.');
    }

    public function index(Request $request)
    {
        $this->allow($request);

        return view('admin.content.ads', [
            'app' => AdUnit::where('platform', 'app')->orderBy('id')->get(), 'web' => AdUnit::where('platform', 'web')->orderBy('id')->get(), 'pages' => AdPages::ALL, 'lists' => AdPages::LISTS,
            'master' => SiteSetting::get('ads_master', '0') === '1', 'appOn' => SiteSetting::get('ads_app', '0') === '1', 'webOn' => SiteSetting::get('ads_web', '0') === '1', 'client' => SiteSetting::get('adsense_client'), 'quiet' => SiteSetting::get('ads_quiet_minutes', '3'), 'sessionMax' => SiteSetting::get('ads_session_max', '3'),
        ]);
    }

    public function save(Request $request)
    {
        $this->allow($request);
        $request->validate([
            'adsense_client' => ['nullable', 'regex:/^ca-pub-\d{8,20}$/'],
            'u' => 'nullable|array', 'u.*.unit_android' => 'nullable|string|max:80', 'u.*.unit_ios' => 'nullable|string|max:80', 'u.*.slot' => ['nullable', 'regex:/^\d{4,20}$/'],
            'u.*.pages' => 'nullable|array', 'u.*.pages.*' => 'in:' . implode(',', array_keys(AdPages::ALL)),
            'u.*.every_n' => ['nullable', 'integer', 'max:100', fn ($a, $v, $fail) => ($v !== null && $v !== '' && (int) $v !== 0 && (int) $v < 4) ? $fail('Show an ad after every 4 items or more (0 = none) so ads never crowd the list.') : null],
            'quiet' => 'nullable|integer|min:0|max:60', 'session_max' => 'nullable|integer|min:0|max:20', 'u.*.max_per_page' => 'nullable|integer|min:1|max:20', 'u.*.start_page' => 'nullable|integer|min:1|max:50', 'u.*.cap_minutes' => 'nullable|integer|min:0|max:1440',
        ], ['adsense_client.regex' => 'The AdSense publisher id looks like ca-pub-1234567890123456.', 'u.*.slot.regex' => 'An AdSense ad slot id is a number.']);

        SiteSetting::put('ads_master', $request->boolean('master') ? '1' : '0');
        SiteSetting::put('ads_app', $request->boolean('app_on') ? '1' : '0');
        SiteSetting::put('ads_web', $request->boolean('web_on') ? '1' : '0');
        SiteSetting::put('ads_quiet_minutes', (string) (int) $request->input('quiet', 3));
        SiteSetting::put('ads_session_max', (string) (int) $request->input('session_max', 3));
        SiteSetting::put('adsense_client', trim((string) $request->input('adsense_client')) ?: null);

        foreach (AdUnit::all() as $unit) {
            $in = $request->input('u.' . $unit->id);
            if (!is_array($in)) {
                continue;
            }
            $unit->update([
                'enabled' => !empty($in['enabled']), 'hide_for_paid' => !empty($in['hide_for_paid']),
                'unit_android' => trim((string) ($in['unit_android'] ?? '')) ?: $unit->unit_android, 'unit_ios' => trim((string) ($in['unit_ios'] ?? '')) ?: $unit->unit_ios,
                'slot' => trim((string) ($in['slot'] ?? '')) ?: null, 'pages' => array_values($in['pages'] ?? []),
                'every_n' => (int) ($in['every_n'] ?? 0), 'max_per_page' => max(1, (int) ($in['max_per_page'] ?? 2)), 'start_page' => max(1, (int) ($in['start_page'] ?? 1)), 'cap_minutes' => (int) ($in['cap_minutes'] ?? 3),
            ]);
        }
        Ads::forget();

        return back()->with('message', 'Ad settings saved');
    }
}
