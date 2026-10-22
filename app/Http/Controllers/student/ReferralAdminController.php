<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** admin/students/referrals: the refer & earn programme - settings (coins), who joined through whom, rewards, manual bonus coins. */
class ReferralAdminController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
    }

    private function allow(Request $request): void
    {
        abort_unless($request->user() && $request->user()->can('Students Tab'), 403);
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $this->referrals->settle();   // pays the ones whose friend has finished a first test

        $base = fn () => DB::table('referrals as r')->join('users as a', 'a.id', '=', 'r.referrer_id')->join('users as b', 'b.id', '=', 'r.referred_id');
        $list = $base()
            ->when(in_array($request->status, ['joined', 'rewarded', 'rejected'], true), fn ($q) => $q->where('r.status', $request->status))
            ->when(in_array($request->platform, ['web', 'android', 'ios'], true), fn ($q) => $q->where('r.platform', $request->platform))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('r.created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('r.created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('a.name', 'like', "%{$request->q}%")->orWhere('b.name', 'like', "%{$request->q}%")
                ->orWhere('a.mobile', 'like', "%{$request->q}%")->orWhere('b.mobile', 'like', "%{$request->q}%")->orWhere('r.code', 'like', "%{$request->q}%")))
            ->orderByDesc('r.id')
            ->select('r.*', 'a.name as referrer_name', 'a.mobile as referrer_mobile', 'b.name as referred_name', 'b.mobile as referred_mobile')
            ->paginate(30)->withQueryString();

        $tot = DB::table('referrals')->selectRaw("count(*) c, sum(status='rewarded') rewarded, sum(status='joined') pending, coalesce(sum(referrer_coins + referred_coins),0) coins")->first();
        $byPlatform = DB::table('referrals')->selectRaw('platform, count(*) c')->groupBy('platform')->pluck('c', 'platform');
        $top = DB::table('referrals as r')->join('users as a', 'a.id', '=', 'r.referrer_id')->selectRaw('a.id, a.name, a.mobile, count(*) joined, sum(r.status="rewarded") rewarded, coalesce(sum(r.referrer_coins),0) coins')
            ->groupBy('a.id', 'a.name', 'a.mobile')->orderByDesc('joined')->limit(8)->get();

        return view('admin.referral.index', ['list' => $list, 'tot' => $tot, 'byPlatform' => $byPlatform, 'top' => $top, 'settings' => $this->referrals->settings()]);
    }

    public function settings(Request $request)
    {
        $this->allow($request);

        return view('admin.referral.settings', ['s' => $this->referrals->settings()]);
    }

    public function saveSettings(Request $request)
    {
        $this->allow($request);
        $data = $request->validate([
            'referrer_coins' => 'required|integer|min:0|max:100000', 'referred_coins' => 'required|integer|min:0|max:100000',
            'qualify' => 'required|in:signup,first_test', 'max_per_user' => 'nullable|integer|min:0|max:100000',
        ]);
        $data['enabled'] = $request->boolean('enabled');
        $this->referrals->saveSettings($data);

        return redirect(url('admin/students/referrals/settings'))->with('message', 'Referral settings saved. New values apply to every reward from now on.');
    }

    public function reward(Request $request, $id)
    {
        $this->allow($request);
        $ref = DB::table('referrals')->find($id);
        abort_unless($ref, 404);
        $ok = $this->referrals->reward($ref, $request->user()->id, 'Rewarded by ' . $request->user()->name);

        return back()->with('message', $ok ? 'Coins given to both students.' : 'This referral was already handled.');
    }

    public function reject(Request $request, $id)
    {
        $this->allow($request);
        DB::table('referrals')->where('id', $id)->where('status', 'joined')->update(['status' => 'rejected', 'note' => 'Rejected by ' . $request->user()->name, 'updated_at' => now()]);

        return back()->with('message', 'Referral rejected - no coins will be given.');
    }

    /** Give (or take back) coins for one student from their profile page. */
    public function coins(Request $request, $id)
    {
        $this->allow($request);
        $user = User::findOrFail($id);
        $data = $request->validate(['coins' => 'required|integer|not_in:0|min:-100000|max:100000', 'note' => 'nullable|string|max:150']);
        DB::table('coin_ledger')->insert(['user_id' => $user->id, 'coins' => (int) $data['coins'], 'type' => $data['coins'] > 0 ? 'bonus' : 'adjustment',
            'note' => $data['note'] ?: ($data['coins'] > 0 ? 'Bonus from Pachavellam' : 'Coins corrected'), 'created_by' => $request->user()->id, 'created_at' => now()]);
        Cache::forget('student_progress_board_v3');

        return back()->with('message', abs($data['coins']) . ' coins ' . ($data['coins'] > 0 ? 'added to ' : 'taken from ') . ($user->name ?: 'the student'));
    }
}
