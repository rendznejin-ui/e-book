<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Config;

/**
 * Mock QR gateway for development / demos.
 *
 * It issues an HMAC-signed payload so a forged QR cannot be used to mark an
 * order paid: only payloads signed with the app key verify. There is no real
 * money movement — approval is triggered by the sandbox "simulate" action.
 */
class SandboxGateway implements PaymentGateway
{
    public function createQrPayload(Payment $payment): array
    {
        $signature = $this->sign($payment->transaction_ref, $payment->amount_cents);

        $payload = json_encode([
            'gateway' => 'sandbox_qr',
            'ref' => $payment->transaction_ref,
            'amount' => $payment->amount_cents,
            'currency' => Config::get('store.currency'),
            'sig' => $signature,
        ], JSON_THROW_ON_ERROR);

        return ['payload' => $payload, 'signature' => $signature];
    }

    public function verify(string $transactionRef, int $amountCents, string $signature): bool
    {
        return hash_equals($this->sign($transactionRef, $amountCents), $signature);
    }

    /** Deterministic HMAC over the canonical "ref|amount" string. */
    private function sign(string $transactionRef, int $amountCents): string
    {
        return hash_hmac('sha256', $transactionRef.'|'.$amountCents, $this->secret());
    }

    private function secret(): string
    {
        // The app key is unique per deployment; good enough as a signing secret.
        return (string) Config::get('app.key');
    }
}
