<?php

namespace App\Services\Payments;

/**
 * Contract for non-cash payment methods (card, wallet, etc). Cash is handled
 * directly by CheckoutController since it only needs a tendered/change
 * calculation, not an external authorization step.
 *
 * Bind a real implementation (Stripe, PayPal, a regional gateway) in
 * AppServiceProvider without touching CheckoutController or any views.
 */
interface PaymentGateway
{
    /**
     * @param  float  $amount  Amount to charge, in the store's currency units.
     * @param  array<string, mixed>  $meta  Free-form context (transaction reference, method label, etc).
     */
    public function charge(float $amount, array $meta = []): PaymentResult;
}
