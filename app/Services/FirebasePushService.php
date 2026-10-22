<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;

class FirebasePushService
{
    protected ?Messaging $messaging = null;

    /**
     * Lazily builds the Messaging client. Returns null (rather than
     * throwing) when the service-account file isn't present yet, so the
     * rest of the app keeps working before that credential is supplied.
     */
    protected function messaging(): ?Messaging
    {
        if ($this->messaging) {
            return $this->messaging;
        }

        $credentials = config('firebase.credentials');
        if (!$credentials || !file_exists($credentials)) {
            Log::warning('FirebasePushService: service-account credentials not found, push disabled', [
                'path' => $credentials,
            ]);
            return null;
        }

        try {
            $this->messaging = (new Factory())->withServiceAccount($credentials)->createMessaging();
            return $this->messaging;
        } catch (\Throwable $e) {
            Log::error('FirebasePushService: failed to initialize Messaging client', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sends the same notification + data payload to every token given.
     *
     * Returns null if sending was never attempted (credentials missing, or
     * the SDK call itself failed) — distinct from an empty array, which
     * means it *was* attempted and every token was valid. Callers must not
     * report a send as successful when this returns null.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): ?array
    {
        $messaging = $this->messaging();
        if (!$messaging || empty($tokens)) {
            return null;
        }

        $message = CloudMessage::new()
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData(array_map('strval', $data));

        try {
            /** @var MulticastSendReport $report */
            $report = $messaging->sendMulticast($message, $tokens);
            return array_map(
                fn ($item) => $item->target()->value(),
                [...$report->invalidTokens(), ...$report->unknownTokens()]
            );
        } catch (\Throwable $e) {
            Log::error('FirebasePushService: sendMulticast failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Rich send for Android, iOS and web at once (up to 500 tokens per call).
     * Returns ['delivered' => n, 'failed' => n, 'invalid' => [tokens to forget]] or null when nothing could be attempted.
     */
    public function sendRich(array $tokens, string $title, string $body, array $data = [], ?string $image = null, ?string $webLink = null): ?array
    {
        $messaging = $this->messaging();
        if (!$messaging || empty($tokens)) {
            return null;
        }

        $notification = array_filter(['title' => $title, 'body' => $body, 'image' => $image]);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData(array_map('strval', $data))
            ->withAndroidConfig(['priority' => 'high', 'notification' => ['channel_id' => 'pachavellam_default', 'sound' => 'default']])
            ->withApnsConfig(['headers' => ['apns-priority' => '10'], 'payload' => ['aps' => ['sound' => 'default', 'mutable-content' => 1]]])
            ->withWebPushConfig(array_filter(['headers' => ['Urgency' => 'high'], 'fcm_options' => $webLink ? ['link' => $webLink] : null]));

        $out = ['delivered' => 0, 'failed' => 0, 'invalid' => []];
        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                $report = $messaging->sendMulticast($message, $chunk);
                $out['delivered'] += $report->successes()->count();
                $out['failed'] += $report->failures()->count();
                foreach ([...$report->invalidTokens(), ...$report->unknownTokens()] as $item) {
                    $out['invalid'][] = $item->target()->value();
                }
            } catch (\Throwable $e) {
                Log::error('FirebasePushService: sendRich failed', ['error' => $e->getMessage()]);
                $out['failed'] += count($chunk);
            }
        }

        return $out;
    }
}
