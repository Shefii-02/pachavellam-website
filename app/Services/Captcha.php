<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Our own CAPTCHA (no third party): a distorted code drawn as an SVG, used once, valid for 5 minutes.
 * Managed in admin/security: on / off, the forms it guards, and whether it is always asked or only after repeated failures from one IP.
 */
class Captcha
{
    /** form key => label (the forms the switch list in admin/security shows) */
    public const FORMS = [
        'otp' => 'Mobile OTP sign in (website and app)',
        'login' => 'Email / password sign in (app)',
        'signup' => 'Sign up (app)',
        'counselling' => 'Free counselling call back form',
        'feedback' => 'Support / feedback forms',
    ];

    /** 5 x 7 dot-matrix shapes of the characters used (no I, O, 0, 1: easy to mix up). The picture is drawn from shapes, not text, so every viewer shows it the same. */
    private const GLYPHS = [
        'A' => '01110,10001,10001,11111,10001,10001,10001', 'B' => '11110,10001,10001,11110,10001,10001,11110', 'C' => '01110,10001,10000,10000,10000,10001,01110',
        'D' => '11110,10001,10001,10001,10001,10001,11110', 'E' => '11111,10000,10000,11110,10000,10000,11111', 'F' => '11111,10000,10000,11110,10000,10000,10000',
        'G' => '01110,10001,10000,10111,10001,10001,01111', 'H' => '10001,10001,10001,11111,10001,10001,10001', 'J' => '00111,00010,00010,00010,00010,10010,01100',
        'K' => '10001,10010,10100,11000,10100,10010,10001', 'L' => '10000,10000,10000,10000,10000,10000,11111', 'M' => '10001,11011,10101,10101,10001,10001,10001',
        'N' => '10001,11001,10101,10011,10001,10001,10001', 'P' => '11110,10001,10001,11110,10000,10000,10000', 'Q' => '01110,10001,10001,10001,10101,10010,01101',
        'R' => '11110,10001,10001,11110,10100,10010,10001', 'S' => '01111,10000,10000,01110,00001,00001,11110', 'T' => '11111,00100,00100,00100,00100,00100,00100',
        'U' => '10001,10001,10001,10001,10001,10001,01110', 'V' => '10001,10001,10001,10001,10001,01010,00100', 'W' => '10001,10001,10001,10101,10101,11011,10001',
        'X' => '10001,10001,01010,00100,01010,10001,10001', 'Y' => '10001,10001,01010,00100,00100,00100,00100', 'Z' => '11111,00001,00010,00100,01000,10000,11111',
        '2' => '01110,10001,00001,00010,00100,01000,11111', '3' => '11110,00001,00001,01110,00001,00001,11110', '4' => '00010,00110,01010,10010,11111,00010,00010',
        '5' => '11111,10000,11110,00001,00001,10001,01110', '6' => '00110,01000,10000,11110,10001,10001,01110', '7' => '11111,00001,00010,00100,01000,01000,01000',
        '8' => '01110,10001,10001,01110,10001,10001,01110', '9' => '01110,10001,10001,01111,00001,00010,01100',
    ];

    public static function enabled(): bool
    {
        return SiteSetting::get('captcha_on', '0') === '1';
    }

    public static function forms(): array
    {
        return array_filter(explode(',', (string) SiteSetting::get('captcha_forms', 'otp,counselling')));
    }

    public static function failureKey(Request $r): string
    {
        return 'captcha:fail:' . $r->ip();
    }

    /** Does this form need a captcha answer from this visitor right now? */
    public static function required(string $form, Request $r): bool
    {
        if (!self::enabled() || !in_array($form, self::forms(), true)) {
            return false;
        }
        if (SiteSetting::get('captcha_mode', 'always') === 'always') {
            return true;
        }

        return (int) Cache::get(self::failureKey($r), 0) >= max(1, (int) SiteSetting::get('captcha_failures', '3'));
    }

    public static function fail(Request $r): void
    {
        Cache::add(self::failureKey($r), 0, 900);
        Cache::increment(self::failureKey($r));
    }

    /** @return array{id:string,image:string} a new challenge */
    public static function challenge(): array
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';   // no 0 O 1 I
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $id = Str::random(24);
        Cache::put('captcha:' . $id, $code, 300);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="56" viewBox="0 0 160 56"><rect width="160" height="56" rx="8" fill="#eef3f1"/>';
        for ($i = 0; $i < 3; $i++) {
            $svg .= sprintf('<path d="M%d %d Q %d %d %d %d" stroke="#%s" stroke-width="1.2" fill="none" opacity=".35"/>', random_int(0, 30), random_int(5, 50), random_int(50, 110), random_int(0, 56), random_int(120, 160), random_int(5, 50), substr(md5((string) random_int(0, 99999)), 0, 6));
        }
        foreach (str_split($code) as $k => $ch) {
            $cx = 24 + $k * 28;
            $svg .= sprintf('<g transform="rotate(%d %d 28)" fill="#%02x%02x%02x">', random_int(-14, 14), $cx, random_int(10, 120), random_int(10, 120), random_int(10, 120));
            foreach (explode(',', self::GLYPHS[$ch]) as $row => $bits) {
                foreach (str_split($bits) as $col => $on) {
                    if ($on === '1') {
                        $svg .= sprintf('<rect x="%.1f" y="%.1f" width="4.6" height="4.6" rx="1"/>', $cx - 12 + $col * 5 + random_int(-4, 4) / 10, 9 + $row * 5 + random_int(-4, 4) / 10 + ($k % 2 ? 2 : 0));
                    }
                }
            }
            $svg .= '</g>';
        }
        for ($i = 0; $i < 25; $i++) {
            $svg .= sprintf('<circle cx="%d" cy="%d" r="1" fill="#%s"/>', random_int(0, 160), random_int(0, 56), substr(md5((string) random_int(0, 99999)), 0, 6));
        }
        $svg .= '</svg>';

        return ['id' => $id, 'image' => 'data:image/svg+xml;base64,' . base64_encode($svg)];
    }

    /** Checks the answer and uses the challenge up (right or wrong). */
    public static function verify(?string $id, ?string $answer): bool
    {
        if (!$id || !$answer) {
            return false;
        }
        $code = Cache::pull('captcha:' . $id);

        return $code !== null && hash_equals(strtoupper((string) $code), strtoupper(trim($answer)));
    }
}
