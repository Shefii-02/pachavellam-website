<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mobile-number OTP: generation, delivery, verification and rate limiting.
 * Shared by the web (session) and API (JWT) sign-in flows.
 *
 * Rules: 1 initial send + 2 resends per number. Once the 3rd OTP is sent the
 * number is locked for LOCK_MINUTES before another OTP can be requested.
 */
class OtpService
{
    const MAX_SENDS = 3;          // initial + 2 resends
    const LOCK_MINUTES = 15;
    const OTP_TTL_MINUTES = 5;
    const MAX_VERIFY_ATTEMPTS = 5;

    /** Normalize to E.164 style "+<cc><number>"; null when invalid. */
    public function normalize(?string $countryCode, ?string $mobile): ?string
    {
        $cc = preg_replace('/\D/', '', (string) $countryCode);
        $number = preg_replace('/\D/', '', (string) $mobile);

        if ($cc === '' || strlen($cc) > 4 || $number === '' || strlen($number) < 6 || strlen($number) > 12) {
            return null;
        }
        if ($cc === '91' && !preg_match('/^[6-9]\d{9}$/', $number)) {
            return null;
        }

        return '+' . $cc . $number;
    }

    /**
     * @return array{ok:bool,message:string,status:int,resends_left?:int,retry_after?:int}
     */
    public function send(string $phone): array
    {
        $lockedFor = $this->lockRemaining($phone);
        if ($lockedFor > 0) {
            return $this->locked($lockedFor);
        }

        $sends = (int) Cache::get($this->key('sends', $phone), 0);
        if ($sends >= self::MAX_SENDS) {
            // Counter outlived the lock somehow; start a fresh lock.
            $this->lock($phone);
            return $this->locked(self::LOCK_MINUTES * 60);
        }

        $otp = $this->providerOwnsOtp() ? null : (string) random_int(100000, 999999);

        try {
            $otp === null ? $this->teamzoSend($phone) : $this->deliver($phone, $otp);
        } catch (\Throwable $e) {
            Log::error('OTP delivery failed: ' . $e->getMessage());
            return ['ok' => false, 'status' => 502, 'message' => 'Could not send OTP. Please try again.'];
        }

        // With Teamzo the code lives at the provider; we only track wrong attempts.
        Cache::put($this->key('code', $phone), [
            'hash' => $otp === null ? null : Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        $sends++;
        Cache::put($this->key('sends', $phone), $sends, now()->addMinutes(self::LOCK_MINUTES));

        if ($sends >= self::MAX_SENDS) {
            $this->lock($phone);
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'OTP sent successfully.',
            'resends_left' => max(0, self::MAX_SENDS - $sends),
            'retry_after' => $sends >= self::MAX_SENDS ? self::LOCK_MINUTES * 60 : 0,
        ];
    }

    /** @return array{ok:bool,message:string,status:int} */
    public function verify(string $phone, string $otp): array
    {
        $entry = Cache::get($this->key('code', $phone));
        if (!$entry) {
            return ['ok' => false, 'status' => 422, 'message' => 'OTP expired. Please request a new one.'];
        }

        if ($entry['attempts'] >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($this->key('code', $phone));
            return ['ok' => false, 'status' => 429, 'message' => 'Too many wrong attempts. Please request a new OTP.'];
        }

        $valid = $entry['hash'] === null
            ? $this->teamzoVerify($phone, $otp)
            : Hash::check($otp, $entry['hash']);

        if (!$valid) {
            $entry['attempts']++;
            Cache::put($this->key('code', $phone), $entry, now()->addMinutes(self::OTP_TTL_MINUTES));
            return ['ok' => false, 'status' => 422, 'message' => 'Invalid OTP.'];
        }

        Cache::forget($this->key('code', $phone));
        Cache::forget($this->key('sends', $phone));
        Cache::forget($this->key('lock', $phone));

        return ['ok' => true, 'status' => 200, 'message' => 'OTP verified.'];
    }

    /**
     * Find the user for a verified number, or create one.
     *
     * @return array{0:User,1:bool} [user, isNew]
     */
    public function findOrCreateUser(string $phone, string $countryCode): array
    {
        $national = substr($phone, strlen('+' . preg_replace('/\D/', '', $countryCode)));
        $variants = [$phone, ltrim($phone, '+'), $national];

        $user = User::whereIn('mobile', $variants)->first();
        if ($user) {
            if (!$user->mobile_verified_at) {
                $user->mobile_verified_at = Carbon::now();
                $user->save();
            }
            return [$user, false];
        }

        $user = new User();
        $user->email = $phone; // email is required+unique; matches the existing phone sign-in behaviour
        $user->password = Hash::make(bin2hex(random_bytes(16)));
        $user->type = 'Student';
        $user->reffer_type = 'Telegram';
        $user->reffered_to = 'ASIFT';
        $user->mobile = $phone;
        $user->mobile_verified_at = Carbon::now();
        $user->filled_column = 0;
        $user->save();

        return [$user, true];
    }

    protected function providerOwnsOtp(): bool
    {
        return config('services.sms.driver') === 'teamzo';
    }

    protected function teamzoRequest()
    {
        $cfg = config('services.teamzo');
        $base = rtrim($cfg['base_url'], '/') . '/api/v1/' . $cfg['chat_token'];

        return [Http::timeout(15)->acceptJson()->withToken($cfg['api_token']), $base];
    }

    /** Teamzo (WhatsApp) generates and delivers the OTP itself. */
    protected function teamzoSend(string $phone): void
    {
        [$http, $base] = $this->teamzoRequest();
        $response = $http->post($base . '/otp/send', [
            'phone' => ltrim($phone, '+'),
            'service' => config('services.teamzo.service'),
            'reference_id' => (string) \Illuminate\Support\Str::uuid(),
            'variables' => ['text' => config('services.teamzo.variable_text')],
        ]);

        if ($response->failed() || $response->json('success') === false) {
            throw new \RuntimeException('Teamzo send error: ' . $response->body());
        }
    }

    protected function teamzoVerify(string $phone, string $otp): bool
    {
        try {
            [$http, $base] = $this->teamzoRequest();
            $response = $http->post($base . '/otp/verify', ['phone' => ltrim($phone, '+'), 'otp' => $otp]);
        } catch (\Throwable $e) {
            Log::error('Teamzo verify failed: ' . $e->getMessage());
            return false;
        }

        return $response->successful() && $response->json('success') !== false;
    }

    protected function deliver(string $phone, string $otp): void
    {
        $driver = config('services.sms.driver', 'log');

        if ($driver === 'smsgatewayhub') {
            $cfg = config('services.smsgatewayhub');
            $response = Http::timeout(15)->get('https://www.smsgatewayhub.com/api/mt/SendSMS', [
                'APIKey' => $cfg['key'],
                'senderid' => $cfg['senderid'],
                'channel' => 2,
                'DCS' => 0,
                'flashsms' => 0,
                'number' => ltrim($phone, '+'),
                'text' => str_replace(['{otp}', '{minutes}'], [$otp, self::OTP_TTL_MINUTES], $cfg['message']),
                'route' => 54,
                'EntityId' => $cfg['entity_id'],
                'dlttemplateid' => $cfg['template_id'],
            ]);
            // The gateway answers 200 even on failure; ErrorCode "000" means accepted.
            if ($response->failed() || (string) $response->json('ErrorCode') !== '000') {
                throw new \RuntimeException('SMSGatewayHub error: ' . $response->body());
            }
            return;
        }

        if ($driver === 'msg91') {
            $cfg = config('services.sms.msg91');
            $response = Http::withHeaders(['authkey' => $cfg['auth_key']])
                ->acceptJson()
                ->post('https://control.msg91.com/api/v5/otp', [
                    'template_id' => $cfg['template_id'],
                    'mobile' => ltrim($phone, '+'),
                    'otp' => $otp,
                    'otp_expiry' => self::OTP_TTL_MINUTES,
                ]);
            if ($response->failed() || ($response->json('type') === 'error')) {
                throw new \RuntimeException('MSG91 error: ' . $response->body());
            }
            return;
        }

        if (app()->environment('production')) {
            throw new \RuntimeException('SMS_DRIVER is not configured for production.');
        }
        Log::info("OTP for {$phone}: {$otp}");
    }

    protected function lock(string $phone): void
    {
        Cache::put($this->key('lock', $phone), now()->addMinutes(self::LOCK_MINUTES)->timestamp, now()->addMinutes(self::LOCK_MINUTES));
    }

    protected function lockRemaining(string $phone): int
    {
        $until = Cache::get($this->key('lock', $phone));
        return $until ? max(0, $until - now()->timestamp) : 0;
    }

    protected function locked(int $seconds): array
    {
        return [
            'ok' => false,
            'status' => 429,
            'message' => 'Maximum OTP resend limit reached. Please try again after ' . (int) ceil($seconds / 60) . ' minutes.',
            'resends_left' => 0,
            'retry_after' => $seconds,
        ];
    }

    protected function key(string $type, string $phone): string
    {
        return "otp:{$type}:" . $phone;
    }
}
