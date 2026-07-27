<?php

namespace App\Services;

class CartCalculator
{
    /**
     * Computes line and cart totals for a checkout.
     *
     * Each line item needs: unit_price, quantity, discount_amount (flat, per
     * line), tax_rate (percent). The order-level discount is applied as a
     * flat deduction from the already-taxed items total (i.e. a straight
     * "coupon off the total") rather than being redistributed across lines
     * or recomputed against the tax base — the simplest model that's still
     * easy to audit on a receipt.
     *
     * @param  list<array{unit_price: float, quantity: int, discount_amount: float, tax_rate: float}>  $lines
     */
    public static function calculate(array $lines, float $orderDiscount = 0): CartCalculationResult
    {
        $results = [];
        $subtotal = 0.0;
        $itemDiscountTotal = 0.0;
        $taxTotal = 0.0;
        $itemsTotal = 0.0;

        foreach ($lines as $line) {
            $gross = self::round($line['unit_price'] * $line['quantity']);
            $discount = min(self::round($line['discount_amount']), $gross);
            $taxable = self::round($gross - $discount);
            $tax = self::round($taxable * ($line['tax_rate'] / 100));
            $lineTotal = self::round($taxable + $tax);

            $results[] = new CartLineResult($gross, $discount, $taxable, $tax, $lineTotal);

            $subtotal = self::round($subtotal + $gross);
            $itemDiscountTotal = self::round($itemDiscountTotal + $discount);
            $taxTotal = self::round($taxTotal + $tax);
            $itemsTotal = self::round($itemsTotal + $lineTotal);
        }

        $orderDiscount = max(0.0, min(self::round($orderDiscount), $itemsTotal));
        $total = self::round($itemsTotal - $orderDiscount);
        $totalDiscount = self::round($itemDiscountTotal + $orderDiscount);

        return new CartCalculationResult($results, $subtotal, $totalDiscount, $taxTotal, $total);
    }

    private static function round(float $amount): float
    {
        return round($amount + PHP_FLOAT_EPSILON, 2);
    }
}
