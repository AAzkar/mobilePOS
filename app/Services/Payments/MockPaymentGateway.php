<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;

/**
 * Always-approves stand-in used until a real gateway (Stripe, PayPal, a
 * regional processor) is bound in AppServiceProvider for production.
 */
class MockPaymentGateway implements PaymentGateway
{
    public function charge(float $amount, array $meta = []): PaymentResult
    {
        return new PaymentResult(
            approved: true,
            reference: 'MOCK-'.Str::upper(Str::random(10)),
            message: 'Approved (mock gateway)',
        );
    }
}
