<?php

namespace App\Services;

class CartCalculationResult
{
    /**
     * @param  list<CartLineResult>  $lines  Same order as the input lines.
     */
    public function __construct(
        public readonly array $lines,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $taxAmount,
        public readonly float $total,
    ) {
    }
}
