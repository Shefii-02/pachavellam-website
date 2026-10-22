<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Works out who is calling: the iOS app, the Android app, the website, or an older app build.
 *
 * The mobile app sends  X-App-Platform (ios|android), X-App-Version, X-App-Build,
 * X-OS-Version and X-Device-Model  with every API request. Older builds do not send them,
 * so their requests are recorded as platform "app" (platform and version unknown).
 */
class ClientInfo
{
    public const PLATFORM_LABELS = [
        'ios' => 'iOS',
        'android' => 'Android',
        'web' => 'Web',
        'app' => 'App (older build)',
    ];

    public static function fromRequest(Request $request): array
    {
        $isApi = $request->is('api/*');
        $declared = strtolower((string) $request->header('X-App-Platform'));
        $ua = (string) $request->userAgent();

        $platform = in_array($declared, ['ios', 'android', 'web'], true) ? $declared : ($isApi ? 'app' : 'web');

        $info = [
            'platform' => $platform,
            'app_version' => self::clean($request->header('X-App-Version'), 30),
            'app_build' => self::clean($request->header('X-App-Build'), 30),
            'os_version' => self::clean($request->header('X-OS-Version'), 50),
            'device_model' => self::clean($request->header('X-Device-Model'), 100),
        ];

        // browsers: describe them from the user agent ("Chrome on Android")
        if ($platform === 'web' && !$info['device_model']) {
            $info['device_model'] = self::browser($ua);
        }

        return $info;
    }

    private static function clean($value, int $max): ?string
    {
        $value = trim(preg_replace('/[^\x20-\x7E]/', '', (string) $value));

        return $value === '' ? null : substr($value, 0, $max);
    }

    public static function browser(string $ua): ?string
    {
        if ($ua === '') {
            return null;
        }

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') || str_contains($ua, 'CriOS') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Other browser',
        };

        $os = match (true) {
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS X') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => null,
        };

        return $os ? "$browser on $os" : $browser;
    }

    public static function label(?string $platform): string
    {
        return self::PLATFORM_LABELS[$platform] ?? ucfirst((string) $platform);
    }
}
