<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReferralService;
use App\Services\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/** Refer & earn for students: the shared link (/r/CODE), the web "apply a code" form and the API of the apps. */
class ReferralController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
    }

    /** Landing page of a shared link. Remembers the code (30 days) so the sign up that follows - on web or in the app - counts. */
    public function landing(Request $request, string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $referrer = $code !== '' ? User::where('referral_code', $code)->first() : null;
        $platform = ReferralService::detectPlatform($request);
        $s = $this->referrals->settings();

        // a signed in student opening their own or any link just goes on
        if (!$referrer || !$s['enabled']) {
            return redirect(url('/'));
        }
        $first = trim(explode(' ', trim((string) $referrer->name))[0] ?? '') ?: 'A friend';

        return response()->view('kpsc.referral-landing', [
            'title' => $first . ' invited you', 'code' => $code, 'inviter' => $first, 'platform' => $platform, 'coins' => $s['referred_coins'],
            'android' => config('app_links.android'), 'ios' => config('app_links.ios'),
            // Play Store passes this to the app after install (Install Referrer), so the app can apply the code on first sign up
            'androidLink' => config('app_links.android') ? config('app_links.android') . (str_contains(config('app_links.android'), '?') ? '&' : '?') . 'referrer=' . urlencode('ref_code=' . $code) : null,
            'signUp' => url('sign-in'),
        ])->withCookie(cookie(ReferralService::COOKIE, $code . '|' . $platform, 60 * 24 * 30, '/', null, null, false));
    }

    /** Web: "I have a friend's code" on My account. */
    public function apply(Request $request)
    {
        $r = $this->tryApply($request->user(), (string) $request->input('code'), ReferralService::detectPlatform($request));

        return redirect(url('my-account?section=referral'))->with('hub_msg', $r['message']);
    }

    // ------------------------------------------------------------------ API (apps)

    /** GET api/auth/referral - code, link, share text, numbers, the friends invited and the rank lists. */
    public function show(Request $request)
    {
        $u = $request->user();
        $d = $this->referrals->forStudent($u);
        $progress = app(StudentProgress::class);
        $mine = $progress->summary($u->id);

        return response()->json(['status' => 200, 'data' => [
            'enabled' => $d['enabled'], 'code' => $d['code'], 'link' => $d['link'], 'share_text' => $d['text'],
            'reward' => ['you_get' => $d['settings']['referrer_coins'], 'friend_gets' => $d['settings']['referred_coins'], 'when' => $d['settings']['qualify'] === 'signup' ? 'friend joins' : 'friend finishes first test'],
            'joined' => $d['joined'], 'rewarded' => $d['rewarded'], 'pending' => $d['pending'], 'coins_earned' => $d['earned'],
            'friends' => $d['rows']->map(fn ($r) => ['name' => $r->name, 'status' => $r->status, 'platform' => $r->platform, 'coins' => $r->coins, 'joined_at' => $r->at])->values(),
            'invited_by' => $d['invitedBy'], 'can_apply_code' => $d['canApply'],
            'coins' => $mine['coins'], 'rank' => $mine['rank'], 'ranked' => $mine['ranked'],
            'leaderboard' => $progress->publicBoard(null, 10)->map(fn ($b) => ['position' => $b->pos, 'name' => $b->name, 'coins' => $b->coins, 'me' => $b->uid === $u->id])->values(),
        ]]);
    }

    /** POST api/auth/referral/apply {code} - the app got the code from the Play Store install referrer or the student typed it. */
    public function applyApi(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);
        $r = $this->tryApply($request->user(), (string) $request->input('code'), ReferralService::detectPlatform($request));

        return response()->json(['status' => $r['ok'] ? 200 : 422, 'message' => $r['message']], $r['ok'] ? 200 : 422);
    }

    private function tryApply(User $u, string $code, string $platform): array
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $s = $this->referrals->settings();
        if (!$s['enabled']) {
            return ['ok' => false, 'message' => 'The referral programme is not running now.'];
        }
        if (DB::table('referrals')->where('referred_id', $u->id)->exists()) {
            return ['ok' => false, 'message' => 'You have already joined with a friend\'s code.'];
        }
        if (!$u->created_at || $u->created_at->lt(now()->subDays(ReferralService::WINDOW_DAYS))) {
            return ['ok' => false, 'message' => 'A code can only be added in the first ' . ReferralService::WINDOW_DAYS . ' days after joining.'];
        }
        $owner = $code !== '' ? User::where('referral_code', $code)->first() : null;
        if (!$owner) {
            return ['ok' => false, 'message' => 'This code was not found. Check it and try again.'];
        }
        if ($owner->id === $u->id) {
            return ['ok' => false, 'message' => 'You cannot use your own code.'];
        }
        $ref = $this->referrals->attach($u, $code, $platform, true);
        if (!$ref) {
            return ['ok' => false, 'message' => 'This code cannot be used right now.'];
        }
        $this->referrals->settle($owner->id);

        return ['ok' => true, 'message' => $s['referred_coins'] > 0 && $s['qualify'] === 'signup'
            ? 'Code added - ' . $s['referred_coins'] . ' coins are in your account.'
            : 'Code added.' . ($s['referred_coins'] > 0 ? ' You get ' . $s['referred_coins'] . ' coins after your first test.' : '')];
    }
}
