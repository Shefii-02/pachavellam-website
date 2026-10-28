<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * First line of defence against floods (DoS) and scraping, tuned in admin/security:
 *  - blocked IPs get 403; allowed IPs skip every check
 *  - requests per minute per IP, with their own limits for the website, the api and sign in / OTP / captcha / forms
 *  - an IP that keeps hitting the limit is blocked for a while by itself
 * The admin panel, payment callbacks and internal calls are never counted.
 */
class SecurityGate
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();
        if ($request->is('up', 'admin/*', 'api/payments/*', 'internal/*') || preg_match('/\.(css|js|map|png|jpe?g|gif|svg|ico|woff2?|ttf|webp)$/i', $path)) {
            return $next($request);
        }
        $ip = (string) $request->ip();
        [$block, $allow] = $this->lists();
        if (isset($allow[$ip])) {
            return $next($request);
        }
        if (isset($block[$ip]) && (!$block[$ip] || $block[$ip] > time())) {
            return $this->deny($request, 403, 'Your network has been blocked because of too many requests. Try again later.');
        }
        if (SiteSetting::get('rl_on', '1') !== '1') {
            return $next($request);
        }

        $auth = $request->isMethod('POST') && $request->is('sign-in', 'sign-up', 'send-otp', 'verify-otp', 'counselling', 'api/auth/*', 'api/captcha*', 'captcha*', 'devices/limit/*');
        [$bucket, $max] = $auth ? ['auth', (int) SiteSetting::get('rl_auth', '20')] : ($request->is('api/*') ? ['api', (int) SiteSetting::get('rl_api', '300')] : ['web', (int) SiteSetting::get('rl_web', '240')]);
        $key = 'rl:' . $bucket . ':' . $ip;
        if ($max > 0 && RateLimiter::tooManyAttempts($key, $max)) {
            $this->strike($ip);

            return $this->deny($request, 429, 'Too many requests. Please slow down.', RateLimiter::availableIn($key));
        }
        RateLimiter::hit($key, 60);

        return $next($request);
    }

    /** @return array{0: array<string,int>, 1: array<string,int>}  [ip => until timestamp (0 = for good)] */
    private function lists(): array
    {
        return Cache::remember('security:ips', 60, function () {
            $rows = BlockedIp::where(fn ($q) => $q->whereNull('until')->orWhere('until', '>', now()))->get();

            return [$rows->where('kind', 'block')->mapWithKeys(fn ($r) => [$r->ip => $r->until ? $r->until->timestamp : 0])->all(), $rows->where('kind', 'allow')->mapWithKeys(fn ($r) => [$r->ip => 0])->all()];
        });
    }

    private function strike(string $ip): void
    {
        $after = (int) SiteSetting::get('rl_autoblock_after', '5');
        if ($after <= 0) {
            return;
        }
        Cache::add('security:s:' . $ip, 0, 600);
        if (Cache::increment('security:s:' . $ip) >= $after) {
            BlockedIp::updateOrCreate(['ip' => $ip], ['kind' => 'block', 'reason' => 'Automatic: kept exceeding the request limit', 'until' => now()->addMinutes(max(1, (int) SiteSetting::get('rl_autoblock_minutes', '30')))]);
            Cache::forget('security:ips');
            Cache::forget('security:s:' . $ip);
        }
    }

    private function deny(Request $request, int $status, string $message, int $retry = 0)
    {
        $r = $request->expectsJson() || $request->is('api/*') ? response()->json(['message' => $message], $status) : response($message, $status);

        return $retry > 0 ? $r->header('Retry-After', (string) $retry) : $r;
    }
}
