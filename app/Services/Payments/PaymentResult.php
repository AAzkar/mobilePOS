<?php

namespace App\Services\Payments;

class PaymentResult
{
    public function __construct(
        public readonly bool $approved,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
    ) {
    }
}
