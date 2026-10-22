<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\DB;

/** Finds a gateway by name and knows which one is switched on. */
class PaymentManager
{
    public const GATEWAYS = ['razorpay' => RazorpayGateway::class, 'phonepe' => PhonePeGateway::class];

    public function gateway(string $name): PaymentGateway
    {
        $class = self::GATEWAYS[$name] ?? throw new PaymentException("Unknown payment gateway \"{$name}\".");

        return app($class);
    }

    /** The gateway new payments go through: the Admin's switch, else the .env default. */
    public function activeName(): string
    {
        $saved = DB::table('payment_settings')->where('key', 'active_gateway')->value('value');

        return isset(self::GATEWAYS[$saved]) ? $saved : (isset(self::GATEWAYS[config('payments.default_gateway')]) ? config('payments.default_gateway') : 'razorpay');
    }

    public function active(): PaymentGateway
    {
        return $this->gateway($this->activeName());
    }

    public function switchTo(string $name, ?int $userId = null): void
    {
        if (!isset(self::GATEWAYS[$name])) {
            throw new PaymentException('Unknown payment gateway.');
        }
        DB::table('payment_settings')->updateOrInsert(['key' => 'active_gateway'], ['value' => $name, 'updated_by' => $userId, 'updated_at' => now()]);
    }

    /** @return array<string, bool> gateway => keys are set */
    public function status(): array
    {
        return collect(self::GATEWAYS)->map(fn ($class, $name) => $this->gateway($name)->configured())->all();
    }
}
