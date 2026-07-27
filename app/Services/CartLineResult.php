<?php

namespace App\Services;

class CartLineResult
{
    public function __construct(
        public readonly float $grossAmount,
        public readonly float $discountAmount,
        public readonly float $taxableAmount,
        public readonly float $taxAmount,
        public readonly float $lineTotal,
    ) {
    }
}
