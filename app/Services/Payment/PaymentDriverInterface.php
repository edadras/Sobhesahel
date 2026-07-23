<?php

namespace App\Services\Payment;

use App\Models\Payment;

/**
 * Contract every payment driver must implement.
 *
 * درگاه‌های پرداخت به صورت پلاگین: the site never talks to a gateway
 * directly — callers go through PaymentManager, which resolves the driver
 * configured in config/payments.php. Adding a real gateway later (e.g.
 * Zarinpal) means writing ONE class implementing this interface and mapping
 * it in PaymentManager::$drivers; no controller or Filament code changes.
 *
 * Lifecycle:
 *
 * 1. create(Payment): called right after the Payment row (status=pending) is
 *    stored. The driver starts the transaction and returns HOW the customer
 *    should proceed:
 *
 *      [
 *        'type'         => 'redirect' | 'manual',
 *        // type=redirect → gateway payment URL to send the customer to:
 *        'url'          => 'https://gateway.example/pay/...',
 *        // type=manual → key/value instructions rendered to the customer
 *        // (e.g. bank name, card number, IBAN, holder, note):
 *        'instructions' => ['بانک' => '...', 'شماره کارت' => '...'],
 *      ]
 *
 *    A gateway driver would typically also store its authority/token on the
 *    Payment (ref_code) before returning the redirect URL.
 *
 * 2. verify(Payment, array $data): called when the customer returns from the
 *    gateway callback (redirect drivers — $data is the callback request
 *    input) or submits the manual form (offline driver — $data holds the
 *    customer-entered tracking code). Returns:
 *
 *      [
 *        'success'  => bool,        // transaction finalized as paid
 *        'status'   => Payment::STATUS_*, // resulting payment status
 *        'ref_code' => ?string,     // gateway ref id / tracking code
 *        'message'  => string,      // Persian message shown to the customer
 *      ]
 *
 *    The driver MUST persist the resulting status/ref_code on the Payment
 *    before returning. Offline/manual drivers finalize as
 *    STATUS_MANUAL_REVIEW (an admin later confirms and marks paid).
 */
interface PaymentDriverInterface
{
    /** Machine name of the driver, e.g. "offline", "zarinpal". */
    public function name(): string;

    /**
     * Start the payment. See class docblock for the return shape.
     *
     * @return array{type: string, url?: string, instructions?: array}
     */
    public function create(Payment $payment): array;

    /**
     * Verify / finalize the payment. See class docblock for the return shape.
     *
     * @return array{success: bool, status: string, ref_code: ?string, message: string}
     */
    public function verify(Payment $payment, array $data = []): array;
}
