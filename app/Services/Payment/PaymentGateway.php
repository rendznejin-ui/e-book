<?php

namespace App\Services\Payment;

use App\Models\Payment;

/**
 * Contract for a payment gateway. Swapping the sandbox for a real provider
 * (Stripe, PayPal, a bank QR API, …) means writing one new implementation and
 * rebinding it — no checkout/controller changes required.
 */
interface PaymentGateway
{
    /**
     * Build the data needed to present a QR payment to the customer.
     *
     * @return array{payload: string, signature: string}
     *         `payload` is the (signed) string encoded into the QR image.
     */
    public function createQrPayload(Payment $payment): array;

    /**
     * Verify that a returned payment reference + amount carries a valid,
     * untampered signature issued by this gateway.
     */
    public function verify(string $transactionRef, int $amountCents, string $signature): bool;
}
