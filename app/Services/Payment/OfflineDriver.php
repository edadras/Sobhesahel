<?php

namespace App\Services\Payment;

use App\Models\Payment;

/**
 * پرداخت آفلاین (کارت به کارت / واریز بانکی).
 *
 * create() returns manual instructions (bank/card info from
 * config/payments.php); verify() records the customer-entered tracking code
 * and moves the payment to manual_review, where an admin confirms it from
 * the Filament panel.
 */
class OfflineDriver implements PaymentDriverInterface
{
    public function name(): string
    {
        return 'offline';
    }

    public function create(Payment $payment): array
    {
        $config = (array) config('payments.offline', []);

        $instructions = array_filter([
            'بانک' => $config['bank_name'] ?? null,
            'شماره کارت' => $config['card_number'] ?? null,
            'شماره شبا' => $config['iban'] ?? null,
            'به نام' => $config['holder_name'] ?? null,
            'مبلغ قابل پرداخت' => $payment->formattedAmount(),
        ], fn ($value) => filled($value));

        return [
            'type' => 'manual',
            'instructions' => $instructions,
            'note' => (string) ($config['note'] ?? ''),
        ];
    }

    public function verify(Payment $payment, array $data = []): array
    {
        $ref_code = trim((string) ($data['ref_code'] ?? ''));

        if ($ref_code === '') {
            return [
                'success' => false,
                'status' => $payment->status,
                'ref_code' => null,
                'message' => 'کد رهگیری واریز را وارد کنید',
            ];
        }

        // Already confirmed by an admin — do not regress the status.
        if ($payment->isPaid()) {
            return [
                'success' => true,
                'status' => Payment::STATUS_PAID,
                'ref_code' => $payment->ref_code,
                'message' => 'این پرداخت قبلا تایید شده است',
            ];
        }

        $payment->forceFill([
            'status' => Payment::STATUS_MANUAL_REVIEW,
            'ref_code' => $ref_code,
        ])->save();

        return [
            'success' => false, // not final yet — an admin must confirm
            'status' => Payment::STATUS_MANUAL_REVIEW,
            'ref_code' => $ref_code,
            'message' => 'کد رهگیری شما ثبت شد؛ پرداخت پس از بررسی همکاران ما تایید و اطلاع‌رسانی می‌شود',
        ];
    }
}
