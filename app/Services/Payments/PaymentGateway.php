<?php

namespace App\Services\Payments;

use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    /** Are the keys for this gateway set? */
    public function configured(): bool;

    /**
     * Creates the order at the gateway. Returns what the app / website needs to open the payment screen;
     * sets gateway_order_id on the transaction (the caller saves it).
     *
     * @return array<string, mixed>
     * @throws PaymentException
     */
    public function createOrder(PaymentTransaction $tx, User $user, ExamCourse $course): array;

    /**
     * Asks the gateway what happened to a payment (never trust the app's word).
     *
     * @return array{status: string, payment_id: ?string, method: ?string, amount: ?float, reason: ?string, raw: array}  status: success|failed|pending
     * @throws PaymentException
     */
    public function fetchStatus(PaymentTransaction $tx): array;

    /**
     * Reads a webhook / callback request. Returns null when its signature is wrong.
     *
     * @return array{merchant_ref: ?string, order_id: ?string, status: string, payment_id: ?string, method: ?string, amount: ?float, reason: ?string, raw: array}|null
     */
    public function parseWebhook(Request $request): ?array;

    /**
     * Refunds part or all of a payment at the gateway.
     *
     * @return array{id: ?string, raw: array}
     * @throws PaymentException
     */
    public function refund(PaymentTransaction $payment, float $amount, string $reason): array;
}
