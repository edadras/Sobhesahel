<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * نقطه ورود پرداخت‌ها — resolves the configured driver and owns the Payment
 * row lifecycle so callers (رپورتاژ، اشتراک، ...) stay gateway-agnostic.
 *
 * Usage:
 *
 *   $manager = app(PaymentManager::class);
 *   [$payment, $init] = $manager->start($order, $order->price, $name, $mobile);
 *   // $init['type'] === 'redirect' → redirect($init['url'])
 *   // $init['type'] === 'manual'   → render $init['instructions'] + tracking form
 *   ...
 *   $result = $manager->verify($payment, $request->all());
 *
 * To add a real gateway (e.g. Zarinpal): implement PaymentDriverInterface,
 * add 'zarinpal' => ZarinpalDriver::class below, and set
 * PAYMENT_DRIVER=zarinpal — nothing else changes.
 */
class PaymentManager
{
    /**
     * Driver registry: machine name => implementation class.
     *
     * @var array<string, class-string<PaymentDriverInterface>>
     */
    protected array $drivers = [
        'offline' => OfflineDriver::class,
        // 'zarinpal' => ZarinpalDriver::class, // future online gateway
    ];

    /**
     * Resolve a driver by name (defaults to config('payments.driver')).
     */
    public function driver(?string $name = null): PaymentDriverInterface
    {
        $name = $name ?: (string) config('payments.driver', 'offline');

        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Unknown payment driver [{$name}]");
        }

        return app($this->drivers[$name]);
    }

    /**
     * Register an additional driver at runtime (e.g. from a service provider).
     */
    public function extend(string $name, string $class): void
    {
        $this->drivers[$name] = $class;
    }

    /**
     * Create a pending Payment for the payable and initialize it on the
     * configured driver.
     *
     * @param  Model  $payable  ReportageOrder, Subscription, ...
     * @param  int  $amount  amount in تومان
     * @return array{0: Payment, 1: array} [$payment, $driverInitResult]
     */
    public function start(Model $payable, int $amount, ?string $payer_name = null, ?string $payer_mobile = null): array
    {
        $driver = $this->driver();

        $payment = new Payment([
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'driver' => $driver->name(),
            'payer_name' => $payer_name,
            'payer_mobile' => $payer_mobile,
        ]);

        $payment->payable()->associate($payable);
        $payment->save();

        return [$payment, $driver->create($payment)];
    }

    /**
     * Re-run create() on an existing pending payment to (re)display the
     * gateway URL / manual instructions without duplicating Payment rows.
     */
    public function instructions(Payment $payment): array
    {
        return $this->driver($payment->driver)->create($payment);
    }

    /**
     * Verify a payment through the driver that created it.
     *
     * @return array{success: bool, status: string, ref_code: ?string, message: string}
     */
    public function verify(Payment $payment, array $data = []): array
    {
        return $this->driver($payment->driver)->verify($payment, $data);
    }
}
