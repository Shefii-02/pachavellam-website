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
}
