<?php

namespace App\Services\Payments;

use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Razorpay (Orders API + Checkout).
 *   1. createOrder   -> the app opens Razorpay Checkout with key_id + order_id
 *   2. the app sends razorpay_order_id / payment_id / signature back -> verifySignature()
 *   3. the webhook (payment.captured / payment.failed / refund.processed) confirms it again, even if the app never reports back
 */
class RazorpayGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'razorpay';
    }

    private function cfg(string $key)
    {
        return config("payments.razorpay.$key");
    }

    public function configured(): bool
    {
        return filled($this->cfg('key_id')) && filled($this->cfg('key_secret'));
    }

    private function http()
    {
        return Http::withBasicAuth((string) $this->cfg('key_id'), (string) $this->cfg('key_secret'))->acceptJson()->timeout(25)->baseUrl($this->cfg('base_url'));
    }

    public function createOrder(PaymentTransaction $tx, User $user, ExamCourse $course): array
    {
        $this->assertConfigured();

        $response = $this->http()->post('/orders', [
            'amount' => (int) round($tx->amount * 100),
            'currency' => $tx->currency,
            'receipt' => $tx->merchant_ref,
            'notes' => ['course_id' => (string) $course->id, 'user_id' => (string) $user->id, 'admission_id' => (string) $tx->admission_id],
        ]);
        if (!$response->successful()) {
            throw new PaymentException('Razorpay could not create the order: ' . ($response->json('error.description') ?: 'HTTP ' . $response->status()));
        }

        $tx->gateway_order_id = $response->json('id');

        return [
            'key_id' => $this->cfg('key_id'),
            'order_id' => $tx->gateway_order_id,
            'amount' => (int) $response->json('amount'),        // paise
            'currency' => $tx->currency,
            'name' => 'Pachavellam',
            'description' => mb_substr($course->title, 0, 120),
            'prefill' => ['name' => $user->name, 'email' => $user->email, 'contact' => $user->mobile],
        ];
    }

    /** The signature the app sends back after paying: HMAC-SHA256 of "order_id|payment_id" with the key secret. */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, (string) $this->cfg('key_secret'));

        return hash_equals($expected, $signature);
    }

    public function fetchStatus(PaymentTransaction $tx): array
    {
        $this->assertConfigured();
        if (!$tx->gateway_order_id) {
            throw new PaymentException('This payment has no Razorpay order.');
        }

        $response = $this->http()->get("/orders/{$tx->gateway_order_id}/payments");
        if (!$response->successful()) {
            throw new PaymentException('Razorpay status check failed: ' . ($response->json('error.description') ?: 'HTTP ' . $response->status()));
        }

        $payments = collect($response->json('items') ?? []);
        $paid = $payments->first(fn ($p) => in_array($p['status'] ?? '', ['captured'], true));
        if ($paid) {
            return $this->result('success', $paid);
        }
        $last = $payments->sortByDesc('created_at')->first();
        if ($last && ($last['status'] ?? '') === 'failed') {
            return $this->result('failed', $last);
        }

        return ['status' => 'pending', 'payment_id' => null, 'method' => null, 'amount' => null, 'reason' => null, 'raw' => $response->json() ?? []];
    }

    private function result(string $status, array $p): array
    {
        return [
            'status' => $status, 'payment_id' => $p['id'] ?? null, 'method' => $p['method'] ?? null,
            'amount' => isset($p['amount']) ? round($p['amount'] / 100, 2) : null,
            'reason' => $status === 'failed' ? ($p['error_description'] ?? 'Payment failed') : null, 'raw' => $p,
        ];
    }

    public function parseWebhook(Request $request): ?array
    {
        $secret = (string) $this->cfg('webhook_secret');
        $signature = (string) $request->header('X-Razorpay-Signature');
        $body = $request->getContent();

        if ($secret === '' || $signature === '' || !hash_equals(hash_hmac('sha256', $body, $secret), $signature)) {
            return null;
        }

        $event = json_decode($body, true) ?: [];
        $payment = $event['payload']['payment']['entity'] ?? null;
        $refund = $event['payload']['refund']['entity'] ?? null;
        $type = $event['event'] ?? '';

        if ($payment && in_array($type, ['payment.captured', 'order.paid'], true)) {
            return ['merchant_ref' => null, 'order_id' => $payment['order_id'] ?? null] + $this->result('success', $payment);
        }
        if ($payment && $type === 'payment.failed') {
            return ['merchant_ref' => null, 'order_id' => $payment['order_id'] ?? null] + $this->result('failed', $payment);
        }
        if ($refund && $type === 'refund.processed') {
            return ['merchant_ref' => null, 'order_id' => null, 'status' => 'refunded', 'payment_id' => $refund['payment_id'] ?? null, 'method' => null,
                'amount' => isset($refund['amount']) ? round($refund['amount'] / 100, 2) : null, 'reason' => null, 'raw' => $refund];
        }

        return ['merchant_ref' => null, 'order_id' => null, 'status' => 'ignored', 'payment_id' => null, 'method' => null, 'amount' => null, 'reason' => null, 'raw' => $event];
    }

    public function refund(PaymentTransaction $payment, float $amount, string $reason): array
    {
        $this->assertConfigured();
        if (!$payment->gateway_payment_id) {
            throw new PaymentException('The Razorpay payment id is missing, so it cannot be refunded online.');
        }

        $response = $this->http()->post("/payments/{$payment->gateway_payment_id}/refund", [
            'amount' => (int) round($amount * 100),
            'notes' => ['reason' => mb_substr($reason, 0, 200)],
        ]);
        if (!$response->successful()) {
            throw new PaymentException('Razorpay refused the refund: ' . ($response->json('error.description') ?: 'HTTP ' . $response->status()));
        }

        return ['id' => $response->json('id'), 'raw' => $response->json() ?? []];
    }

    private function assertConfigured(): void
    {
        if (!$this->configured()) {
            throw new PaymentException('Razorpay is not set up (RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET in the .env file).');
        }
    }
}
