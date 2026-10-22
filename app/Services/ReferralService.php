<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Refer & earn. A student shares a link (https://site/r/CODE); whoever joins through it - on the website, the Android app
 * or the iOS app - is recorded in `referrals`. The coins (set in admin/referrals/settings) go to both students into
 * `coin_ledger` once the friend qualifies: right after joining, or after the first finished test.
 */
class ReferralService
{
    const COOKIE = 'pv_ref';
    const WINDOW_DAYS = 7;   // a code can still be applied this many days after joining

    // ------------------------------------------------------------------ settings

    public function settings(): array
    {
        return Cache::remember('portal_settings_referral', 300, function () {
            $rows = Schema::hasTable('portal_settings') ? DB::table('portal_settings')->where('key', 'like', 'referral.%')->pluck('value', 'key') : collect();

            return [
                'enabled' => (bool) ($rows['referral.enabled'] ?? 1),
                'referrer_coins' => (int) ($rows['referral.referrer_coins'] ?? 50),
                'referred_coins' => (int) ($rows['referral.referred_coins'] ?? 20),
                'qualify' => ($rows['referral.qualify'] ?? 'first_test') === 'signup' ? 'signup' : 'first_test',
                'max_per_user' => (int) ($rows['referral.max_per_user'] ?? 0),
            ];
        });
    }

    public function saveSettings(array $in): void
    {
        $vals = [
            'referral.enabled' => !empty($in['enabled']) ? '1' : '0',
            'referral.referrer_coins' => (string) max(0, (int) ($in['referrer_coins'] ?? 0)),
            'referral.referred_coins' => (string) max(0, (int) ($in['referred_coins'] ?? 0)),
            'referral.qualify' => ($in['qualify'] ?? '') === 'signup' ? 'signup' : 'first_test',
            'referral.max_per_user' => (string) max(0, (int) ($in['max_per_user'] ?? 0)),
        ];
        foreach ($vals as $k => $v) {
            DB::table('portal_settings')->updateOrInsert(['key' => $k], ['value' => $v, 'updated_at' => now()]);
        }
        Cache::forget('portal_settings_referral');
    }

    // ------------------------------------------------------------------ codes and links

    public function codeFor(User $u): string
    {
        if ($u->referral_code) {
            return $u->referral_code;
        }
        do {
            $code = strtoupper(Str::random(7));
            $code = strtr($code, ['O' => 'X', '0' => '7', 'I' => 'K', '1' => '9', 'L' => 'M']);   // no look-alike characters
        } while (DB::table('users')->where('referral_code', $code)->exists());
        DB::table('users')->where('id', $u->id)->update(['referral_code' => $code]);
        $u->referral_code = $code;

        return $code;
    }

    public function linkFor(User $u): string
    {
        return url('r/' . $this->codeFor($u));
    }

    public function shareText(User $u): string
    {
        $s = $this->settings();

        return 'Join me on Pachavellam for exam preparation' . ($s['referred_coins'] ? ' and get ' . $s['referred_coins'] . ' coins' : '') . ': ' . $this->linkFor($u);
    }

    /** Store the code from a shared link (cookie is set by the caller), so the sign up that follows can use it. */
    public static function detectPlatform(Request $r): string
    {
        $p = strtolower((string) ($r->input('platform') ?: $r->header('X-Platform')));
        if (in_array($p, ['android', 'ios', 'web'], true)) {
            return $p;
        }
        $ua = strtolower((string) $r->userAgent());

        return str_contains($ua, 'android') ? 'android' : (preg_match('/iphone|ipad|ipod/', $ua) ? 'ios' : 'web');
    }

    // ------------------------------------------------------------------ joining

    /** The code that came with this request: form field / header (apps), or the cookie of a shared link (website). */
    public function codeFromRequest(?Request $r = null): array
    {
        $r = $r ?: request();
        $code = (string) ($r->input('ref_code') ?: $r->input('referral_code') ?: $r->header('X-Referral-Code'));
        $platform = null;
        if ($code === '' && ($c = (string) $r->cookie(self::COOKIE)) !== '') {
            [$code, $platform] = array_pad(explode('|', $c, 2), 2, null);
        }

        return [strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code)), $platform ?: self::detectPlatform($r)];
    }

    /** Called for every new student (User::created) and by the "apply a code" API. Returns the referral or null. */
    public function attach(User $joined, ?string $code = null, ?string $platform = null, bool $strict = false): ?object
    {
        $s = $this->settings();
        if (!$s['enabled'] || !Schema::hasTable('referrals')) {
            return null;
        }
        if ($code === null) {
            [$code, $platform] = $this->codeFromRequest();
        }
        if ($code === '' || $code === null || DB::table('referrals')->where('referred_id', $joined->id)->exists()) {
            return null;
        }
        $referrer = User::where('referral_code', $code)->where('id', '!=', $joined->id)->first();
        if (!$referrer) {
            return null;
        }
        if ($s['max_per_user'] > 0 && DB::table('referrals')->where('referrer_id', $referrer->id)->count() >= $s['max_per_user']) {
            return null;
        }
        $id = DB::table('referrals')->insertGetId([
            'referrer_id' => $referrer->id, 'referred_id' => $joined->id, 'code' => $code, 'platform' => $platform ?: 'web',
            'status' => 'joined', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $ref = DB::table('referrals')->find($id);
        if ($s['qualify'] === 'signup') {
            $this->reward($ref);
            $ref = DB::table('referrals')->find($id);
        }

        return $ref;
    }

    // ------------------------------------------------------------------ rewards

    public function hasFinishedTest(int $userId): bool
    {
        foreach ([['kpsc_daily_exams_attempt', 'attend_ended_at'], ['kpsc_ca_daily_exam_attempts', 'attend_ended_at'], ['kpsc_model_exam_attempt', 'created_at']] as [$t, $col]) {
            if (Schema::hasTable($t) && DB::table($t)->where('user_id', $userId)->whereNotNull($col)->exists()) {
                return true;
            }
        }

        return false;
    }

    /** Give the coins of one referral to both students (once). */
    public function reward(object $ref, ?int $by = null, ?string $note = null): bool
    {
        $s = $this->settings();

        return DB::transaction(function () use ($ref, $s, $by, $note) {
            $row = DB::table('referrals')->where('id', $ref->id)->lockForUpdate()->first();
            if (!$row || $row->status !== 'joined') {
                return false;
            }
            $a = (int) $s['referrer_coins'];
            $b = (int) $s['referred_coins'];
            if ($a > 0) {
                DB::table('coin_ledger')->insert(['user_id' => $row->referrer_id, 'coins' => $a, 'type' => 'referral_referrer', 'ref_id' => $row->id, 'note' => 'A friend joined with your link', 'created_by' => $by, 'created_at' => now()]);
            }
            if ($b > 0) {
                DB::table('coin_ledger')->insert(['user_id' => $row->referred_id, 'coins' => $b, 'type' => 'referral_welcome', 'ref_id' => $row->id, 'note' => 'Welcome bonus for joining with a friend\'s link', 'created_by' => $by, 'created_at' => now()]);
            }
            DB::table('referrals')->where('id', $row->id)->update(['status' => 'rewarded', 'referrer_coins' => $a, 'referred_coins' => $b, 'rewarded_at' => now(), 'note' => $note, 'updated_at' => now()]);
            Cache::forget('student_progress_board_v3');

            return true;
        });
    }

    /** Pay the referrals whose friend has now finished a first test (mode "first_test"). $referrerId limits it to one student. */
    public function settle(?int $referrerId = null): int
    {
        if (!Schema::hasTable('referrals') || $this->settings()['qualify'] !== 'first_test') {
            return 0;
        }
        $n = 0;
        DB::table('referrals')->where('status', 'joined')->when($referrerId, fn ($q) => $q->where('referrer_id', $referrerId))->orderBy('id')->limit(300)->get()
            ->each(function ($r) use (&$n) {
                if ($this->hasFinishedTest($r->referred_id) && $this->reward($r)) {
                    $n++;
                }
            });

        return $n;
    }

    // ------------------------------------------------------------------ what a student / the admin sees

    public function forStudent(User $u): array
    {
        $this->settle($u->id);
        $s = $this->settings();
        $rows = DB::table('referrals as r')->join('users as f', 'f.id', '=', 'r.referred_id')->where('r.referrer_id', $u->id)->orderByDesc('r.id')->limit(100)
            ->get(['r.id', 'r.status', 'r.platform', 'r.referrer_coins', 'r.created_at', 'r.rewarded_at', 'f.name']);
        $by = DB::table('referrals as r')->join('users as p', 'p.id', '=', 'r.referrer_id')->where('r.referred_id', $u->id)->first(['p.name', 'r.status', 'r.referred_coins', 'r.created_at']);

        return [
            'enabled' => $s['enabled'], 'settings' => $s, 'code' => $this->codeFor($u), 'link' => $this->linkFor($u), 'text' => $this->shareText($u),
            'joined' => $rows->count(), 'rewarded' => $rows->where('status', 'rewarded')->count(), 'pending' => $rows->where('status', 'joined')->count(),
            'earned' => (int) $rows->where('status', 'rewarded')->sum('referrer_coins'),
            'rows' => $rows->map(fn ($r) => (object) [
                'name' => $this->mask($r->name), 'status' => $r->status, 'platform' => $r->platform, 'coins' => (int) $r->referrer_coins, 'at' => $r->created_at,
            ]),
            'invitedBy' => $by ? (object) ['name' => $this->mask($by->name), 'coins' => (int) $by->referred_coins, 'status' => $by->status] : null,
            'canApply' => !$by && $u->created_at && $u->created_at->gte(now()->subDays(self::WINDOW_DAYS)),
        ];
    }

    /** Names of other students are shown as "Anu K." */
    private function mask(?string $name): string
    {
        $p = preg_split('/\s+/', trim((string) $name)) ?: [];
        if (!$p || $p[0] === '') {
            return 'Student';
        }

        return count($p) > 1 ? $p[0] . ' ' . mb_substr(end($p), 0, 1) . '.' : $p[0];
    }

    /** Admin student page: who invited this student and every student they invited, with what those students did and spent. */
    public function adminDetail(User $u): array
    {
        $invitedBy = DB::table('referrals as r')->join('users as p', 'p.id', '=', 'r.referrer_id')->where('r.referred_id', $u->id)
            ->first(['p.id as uid', 'p.name', 'p.mobile', 'r.status', 'r.platform', 'r.referred_coins', 'r.created_at']);

        $friends = DB::table('referrals as r')->join('users as f', 'f.id', '=', 'r.referred_id')->where('r.referrer_id', $u->id)->orderByDesc('r.id')
            ->get(['r.id', 'r.status', 'r.platform', 'r.referrer_coins', 'r.created_at', 'r.rewarded_at', 'f.id as uid', 'f.name', 'f.mobile', 'f.email']);
        $ids = $friends->pluck('uid')->all();

        $tests = [];
        $last = [];
        foreach ([['kpsc_daily_exams_attempt', 'attend_ended_at'], ['kpsc_ca_daily_exam_attempts', 'attend_ended_at'], ['kpsc_model_exam_attempt', 'created_at']] as [$t, $col]) {
            if ($ids && Schema::hasTable($t)) {
                foreach (DB::table($t)->whereIn('user_id', $ids)->whereNotNull($col)->selectRaw("user_id, count(*) c, max($col) m")->groupBy('user_id')->get() as $r) {
                    $tests[$r->user_id] = ($tests[$r->user_id] ?? 0) + $r->c;
                    $last[$r->user_id] = max($last[$r->user_id] ?? '', $r->m);
                }
            }
        }
        $spend = $ids ? DB::table('course_admissions')->whereIn('user_id', $ids)->whereNull('deleted_at')->whereNotIn('status', ['pending', 'cancelled'])
            ->selectRaw('user_id, count(*) c, sum(amount_paid - refunded_amount) s')->groupBy('user_id')->get()->keyBy('user_id') : collect();
        $coins = $ids ? DB::table('coin_ledger')->whereIn('user_id', $ids)->selectRaw('user_id, sum(coins) s')->groupBy('user_id')->pluck('s', 'user_id') : collect();

        $friends = $friends->map(function ($f) use ($tests, $last, $spend, $coins) {
            $f->tests = (int) ($tests[$f->uid] ?? 0);
            $f->last_active = $last[$f->uid] ?? null;
            $f->courses = (int) ($spend[$f->uid]->c ?? 0);
            $f->spent = (float) ($spend[$f->uid]->s ?? 0);
            $f->coins_total = (int) ($coins[$f->uid] ?? 0);

            return $f;
        });

        return [
            'invitedBy' => $invitedBy, 'friends' => $friends, 'code' => $u->referral_code,
            'earned' => (int) $friends->where('status', 'rewarded')->sum('referrer_coins'),
            'spent' => (float) $friends->sum('spent'), 'tests' => (int) $friends->sum('tests'),
            'ledger' => DB::table('coin_ledger')->where('user_id', $u->id)->orderByDesc('id')->limit(30)->get(),
            'bonus' => (int) DB::table('coin_ledger')->where('user_id', $u->id)->sum('coins'),
        ];
    }
}
