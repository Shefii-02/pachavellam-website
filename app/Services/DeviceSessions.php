<?php

namespace App\Services;

use App\Models\DeviceSession;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Signed in devices of each person.
 *  - every sign in (website or app) opens a session; its id (sid) travels inside the app token / the website session
 *  - a person may have at most `limit()` live devices; over the limit the sign in stops and the list is shown (ticket) so one can be removed
 *  - removing a device (by the person, by "log out other devices" or from the panel) makes its next request fail at once
 * Staff (Admin / Admin Staff) are never limited. A device unused for `staleDays()` days no longer counts.
 */
class DeviceSessions
{
    public const EXEMPT_TYPES = ['Admin', 'Admin Staff'];

    public function exempt(User $user): bool
    {
        return in_array($user->type, self::EXEMPT_TYPES, true);
    }

    /** Devices allowed at once for this person (0 = no limit). */
    public function limit(User $user): int
    {
        if ($this->exempt($user)) {
            return 0;
        }

        return $user->device_limit !== null ? (int) $user->device_limit : (int) SiteSetting::get('device_limit', '2');
    }

    public function staleDays(): int
    {
        return max(1, (int) SiteSetting::get('device_stale_days', '30'));
    }

    /** Live devices that still count (not removed, used within the stale period). */
    public function live(User $user)
    {
        return DeviceSession::live()->where('user_id', $user->id)->where(fn ($q) => $q->where('last_active_at', '>=', now()->subDays($this->staleDays()))->orWhereNull('last_active_at'))
            ->orderByDesc('last_active_at')->get();
    }

    public function overLimit(User $user): bool
    {
        $limit = $this->limit($user);

        return $limit > 0 && $this->live($user)->count() >= $limit;
    }

    public function open(User $user, Request $request, string $channel): DeviceSession
    {
        $info = ClientInfo::fromRequest($request);
        $s = DeviceSession::create([
            'user_id' => $user->id, 'sid' => Str::lower(Str::random(32)), 'channel' => $channel, 'platform' => $info['platform'],
            'device_name' => $info['device_model'], 'os_version' => $info['os_version'], 'app_version' => $info['app_version'],
            'ip' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 255) ?: null, 'last_active_at' => now(),
        ]);
        Cache::forget('ds:' . $s->sid);

        return $s;
    }

    /** @param iterable<int>|null $ids  session row ids (of this person); null = every live device */
    public function revoke(User $user, ?iterable $ids, string $by, ?string $exceptSid = null): int
    {
        $q = DeviceSession::live()->where('user_id', $user->id)->when($ids !== null, fn ($w) => $w->whereIn('id', collect($ids)->map(fn ($i) => (int) $i)->all()))
            ->when($exceptSid, fn ($w) => $w->where('sid', '!=', $exceptSid));
        $rows = $q->get(['id', 'sid']);
        foreach ($rows as $r) {
            Cache::put('ds:' . $r->sid, 'revoked', now()->addDays(60));
        }
        DeviceSession::whereIn('id', $rows->pluck('id'))->update(['revoked_at' => now(), 'revoked_by' => $by]);

        return $rows->count();
    }

    public function revokeSid(?string $sid, string $by): void
    {
        if (!$sid) {
            return;
        }
        Cache::put('ds:' . $sid, 'revoked', now()->addDays(60));
        DeviceSession::live()->where('sid', $sid)->update(['revoked_at' => now(), 'revoked_by' => $by]);
    }

    /** Is this device still signed in? Unknown ids (older tokens) count as signed in. Cached, so it is cheap on every request. */
    public function valid(string $sid): bool
    {
        $state = Cache::remember('ds:' . $sid, 120, fn () => DeviceSession::where('sid', $sid)->whereNull('revoked_at')->exists() ? 'ok' : (DeviceSession::where('sid', $sid)->exists() ? 'revoked' : 'unknown'));

        return $state !== 'revoked';
    }

    /** Mark the device as used (at most once in 5 minutes). */
    public function touch(string $sid, Request $request): void
    {
        if (!Cache::add('ds:t:' . $sid, 1, 300)) {
            return;
        }
        DeviceSession::where('sid', $sid)->whereNull('revoked_at')->update(['last_active_at' => now(), 'ip' => $request->ip()]);
    }

    // ------------------------------------------------------------ the "too many devices" ticket (lets a person who is not signed in pick a device to remove)

    public function ticket(User $user): string
    {
        return Crypt::encryptString(json_encode(['u' => $user->id, 'x' => now()->addMinutes(15)->timestamp]));
    }

    public function userFromTicket(?string $ticket): ?User
    {
        try {
            $d = json_decode(Crypt::decryptString((string) $ticket), true);
        } catch (\Throwable $e) {
            return null;
        }

        return is_array($d) && ($d['x'] ?? 0) >= now()->timestamp ? User::find($d['u'] ?? 0) : null;
    }

    /** Devices as the apps and pages list them. */
    public function describe($rows, ?string $currentSid = null): array
    {
        return collect($rows)->map(fn ($d) => [
            'id' => $d->id, 'name' => $d->device_name ?: ($d->channel === 'web' ? 'Web browser' : 'Mobile app'), 'platform' => $d->platform, 'channel' => $d->channel,
            'app_version' => $d->app_version, 'ip' => $d->ip, 'signed_in' => optional($d->created_at)->toIso8601String(), 'last_active' => optional($d->last_active_at)->toIso8601String(),
            'current' => $currentSid !== null && $d->sid === $currentSid,
        ])->values()->all();
    }
}
