<?php

namespace App\Services\Payments;

use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * PhonePe Payment Gateway (pay page, X-VERIFY checksum with the salt key).
 *   1. createOrder   -> the app opens redirect_url (PhonePe pay page)
 *   2. PhonePe sends the student back to return_url and calls callback_url (server to server)
 *   3. fetchStatus() asks PhonePe directly, so the result never depends on what the app says
 */
class PhonePeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'phonepe';
    }

    private function cfg(string $key)
    {
        return config("payments.phonepe.$key");
    }

    public function configured(): bool
    {
        return filled($this->cfg('merchant_id')) && filled($this->cfg('salt_key'));
    }

    private function base(): string
    {
        return rtrim((string) config('payments.phonepe.base_urls.' . ($this->cfg('env') === 'production' ? 'production' : 'sandbox')), '/');
    }

    /** X-VERIFY = SHA256(body-or-empty + path + salt key) . "###" . salt index */
    public function checksum(string $payload, string $path): string
    {
        return hash('sha256', $payload . $path . $this->cfg('salt_key')) . '###' . $this->cfg('salt_index');
    }

    public function createOrder(PaymentTransaction $tx, User $user, ExamCourse $course): array
    {
        $this->assertConfigured();

        $payload = base64_encode(json_encode([
            'merchantId' => $this->cfg('merchant_id'),
            'merchantTransactionId' => $tx->merchant_ref,
            'merchantUserId' => 'U' . $user->id,
            'amount' => (int) round($tx->amount * 100),
            'redirectUrl' => url('api/payments/phonepe/return/' . $tx->merchant_ref),
            'redirectMode' => 'POST',
            'callbackUrl' => url('api/payments/phonepe/callback'),
            'mobileNumber' => preg_replace('/\D/', '', (string) $user->mobile) ?: null,
            'paymentInstrument' => ['type' => 'PAY_PAGE'],
        ]));

        $response = Http::withHeaders(['X-VERIFY' => $this->checksum($payload, '/pg/v1/pay'), 'Accept' => 'application/json'])
            ->timeout(25)->post($this->base() . '/pg/v1/pay', ['request' => $payload]);

        $url = $response->json('data.instrumentResponse.redirectInfo.url');
        if (!$response->successful() || !$url) {
            throw new PaymentException('PhonePe could not start the payment: ' . ($response->json('message') ?: 'HTTP ' . $response->status()));
        }

        $tx->gateway_order_id = $tx->merchant_ref;   // PhonePe knows the order by our own reference

        return [
            'redirect_url' => $url,
            'merchant_ref' => $tx->merchant_ref,
            'amount' => (int) round($tx->amount * 100),
            'currency' => $tx->currency,
            'environment' => $this->cfg('env'),
        ];
    }

    public function fetchStatus(PaymentTransaction $tx): array
    {
        $this->assertConfigured();
        $path = '/pg/v1/status/' . $this->cfg('merchant_id') . '/' . $tx->merchant_ref;

        $response = Http::withHeaders(['X-VERIFY' => $this->checksum('', $path), 'X-MERCHANT-ID' => $this->cfg('merchant_id'), 'Accept' => 'application/json'])
            ->timeout(25)->get($this->base() . $path);

        if (!$response->successful() && !$response->json('code')) {
            throw new PaymentException('PhonePe status check failed: HTTP ' . $response->status());
        }

        return $this->fromResponse($response->json() ?? []);
    }

    /** @param array $body  the decoded PhonePe response: {code, message, data: {merchantTransactionId, transactionId, amount, paymentInstrument}} */
    private function fromResponse(array $body): array
    {
        $code = (string) ($body['code'] ?? '');
        $status = match (true) {
            $code === 'PAYMENT_SUCCESS' => 'success',
            in_array($code, ['PAYMENT_PENDING', 'INTERNAL_SERVER_ERROR'], true) => 'pending',
            $code === '' => 'pending',
            default => 'failed',
        };
        $data = $body['data'] ?? [];

        return [
            'status' => $status, 'payment_id' => $data['transactionId'] ?? null, 'method' => strtolower((string) ($data['paymentInstrument']['type'] ?? '')) ?: null,
            'amount' => isset($data['amount']) ? round($data['amount'] / 100, 2) : null,
            'reason' => $status === 'failed' ? ($body['message'] ?? $code) : null, 'raw' => $body,
        ];
    }

    public function parseWebhook(Request $request): ?array
    {
        $encoded = $request->input('response');
        if (!is_string($encoded) || $encoded === '') {
            return null;
        }
        $expected = $this->checksum($encoded, '');
        if (!hash_equals($expected, (string) $request->header('X-VERIFY'))) {
            return null;
        }

        $body = json_decode((string) base64_decode($encoded, true), true);
        if (!is_array($body)) {
            return null;
        }

        return ['merchant_ref' => $body['data']['merchantTransactionId'] ?? null, 'order_id' => null] + $this->fromResponse($body);
    }

    public function refund(PaymentTransaction $payment, float $amount, string $reason): array
    {
        $this->assertConfigured();
        $refundRef = 'RF' . substr($payment->merchant_ref, 0, 24) . random_int(100, 999);

        $payload = base64_encode(json_encode([
            'merchantId' => $this->cfg('merchant_id'),
            'merchantUserId' => 'U' . $payment->user_id,
            'originalTransactionId' => $payment->merchant_ref,
            'merchantTransactionId' => $refundRef,
            'amount' => (int) round($amount * 100),
            'callbackUrl' => url('api/payments/phonepe/callback'),
        ]));

        $response = Http::withHeaders(['X-VERIFY' => $this->checksum($payload, '/pg/v1/refund'), 'Accept' => 'application/json'])
            ->timeout(25)->post($this->base() . '/pg/v1/refund', ['request' => $payload]);

        if (!$response->successful() || !in_array($response->json('code'), ['PAYMENT_SUCCESS', 'PAYMENT_PENDING', 'SUCCESS'], true)) {
            throw new PaymentException('PhonePe refused the refund: ' . ($response->json('message') ?: 'HTTP ' . $response->status()));
        }

        return ['id' => $refundRef, 'raw' => $response->json() ?? []];
    }

    private function assertConfigured(): void
    {
        if (!$this->configured()) {
            throw new PaymentException('PhonePe is not set up (PHONEPE_MERCHANT_ID / PHONEPE_SALT_KEY in the .env file).');
        }
    }
}
