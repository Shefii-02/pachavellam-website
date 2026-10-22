<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use App\Services\ClientInfo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records one row per request (api + website) for the Dev Tools screens, and keeps
 * user_platform_activity (last active / app version / device per user and platform) up to date.
 * The writes happen in terminate(), i.e. after the response was sent, so they add no latency.
 * Request bodies, headers (other than the app version headers) and query strings are never stored.
 */
class LogRequest
{
    /** Seconds between two last-active updates for the same user, platform and app version. */
    private const ACTIVITY_THROTTLE = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('log_started_at', microtime(true));

        $response = $next($request);

        $request->attributes->set('log_user_id', $this->userId($request));

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $path = '/' . ltrim($request->path(), '/');

            // skip noise: static assets, health check, and the dev tool screens themselves
            if ($path === '/up' || str_starts_with($path, '/admin/general/dev-tools')
                || preg_match('/\.(css|js|map|png|jpe?g|gif|svg|ico|woff2?|ttf)$/i', $path)) {
                return;
            }

            $started = $request->attributes->get('log_started_at', microtime(true));
            $status = $response->getStatusCode();
            $userId = $request->attributes->get('log_user_id');
            $client = ClientInfo::fromRequest($request);

            RequestLog::create([
                'is_api' => $request->is('api/*'),
                'platform' => $client['platform'],
                'app_version' => $client['app_version'],
                'app_build' => $client['app_build'],
                'method' => $request->method(),
                'path' => mb_substr($path, 0, 255),
                'route_name' => mb_substr((string) optional($request->route())->getName(), 0, 150) ?: null,
                'status' => $status,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'bytes_in' => (int) $request->headers->get('Content-Length', 0),
                'bytes_out' => $this->responseSize($response),
                'user_id' => $userId,
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255) ?: null,
                'error' => $status >= 500 ? $this->errorMessage($response) : null,
            ]);

            // only real, successful use counts as "active" (not a 401 for an expired token)
            if ($userId && $status < 400) {
                $this->touchActivity($request, $userId, $client);
            }
        } catch (\Throwable $e) {
            // logging must never break a request
        }
    }

    private function touchActivity(Request $request, int $userId, array $client): void
    {
        $platform = $client['platform'];

        // older app builds do not say ios/android: use the platform the push token was registered with
        if ($platform === 'app') {
            $registered = DB::table('device_tokens')->where('user_id', $userId)->orderByDesc('last_seen_at')->value('platform');
            if (in_array($registered, ['ios', 'android'], true)) {
                $platform = $registered;
            }
        }

        // one write per user / platform / version every few minutes, not one per request
        if (!Cache::add("upa:$userId:$platform:" . ($client['app_version'] ?? '-'), 1, self::ACTIVITY_THROTTLE)) {
            return;
        }

        $now = now();

        DB::table('user_platform_activity')->upsert([[
            'user_id' => $userId,
            'platform' => $platform,
            'app_version' => $client['app_version'],
            'app_build' => $client['app_build'],
            'os_version' => $client['os_version'],
            'device_model' => $client['device_model'],
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255) ?: null,
            'last_ip' => $request->ip(),
            'first_seen_at' => $now,
            'last_active_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]], ['user_id', 'platform'], [
            // an older build that sends no headers must not wipe a known version
            'app_version' => DB::raw('COALESCE(VALUES(app_version), app_version)'),
            'app_build' => DB::raw('COALESCE(VALUES(app_build), app_build)'),
            'os_version' => DB::raw('COALESCE(VALUES(os_version), os_version)'),
            'device_model' => DB::raw('COALESCE(VALUES(device_model), device_model)'),
            'user_agent', 'last_ip', 'last_active_at', 'updated_at',
        ]);
    }

    private function userId(Request $request)
    {
        try {
            return $request->is('api/*') ? auth('api')->id() : optional($request->user())->id;
        } catch (\Throwable $e) {
            return null; // expired / invalid token
        }
    }

    private function responseSize(Response $response): int
    {
        $length = $response->headers->get('Content-Length');
        if ($length !== null) {
            return (int) $length;
        }

        // streamed / file downloads have no in-memory content
        $content = $response->getContent();

        return $content === false ? 0 : strlen($content);
    }

    private function errorMessage(Response $response): ?string
    {
        $json = json_decode((string) $response->getContent(), true);

        return mb_substr((string) (is_array($json) ? ($json['message'] ?? '') : ''), 0, 500) ?: null;
    }
}
