<?php

namespace Tests\Unit;

use App\Services\CartCalculator;
use PHPUnit\Framework\TestCase;

class CartCalculatorTest extends TestCase
{
    public function test_calculates_subtotal_and_total_with_no_discount_or_tax(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 100.00, 'quantity' => 2, 'discount_amount' => 0, 'tax_rate' => 0],
        ]);

        $this->assertSame(200.0, $result->subtotal);
        $this->assertSame(0.0, $result->discountAmount);
        $this->assertSame(0.0, $result->taxAmount);
        $this->assertSame(200.0, $result->total);
    }

    public function test_applies_per_line_discount_before_tax(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 100.00, 'quantity' => 1, 'discount_amount' => 20, 'tax_rate' => 10],
        ]);

        // (100 - 20) taxed at 10% = 80 + 8 = 88
        $this->assertSame(100.0, $result->subtotal);
        $this->assertSame(20.0, $result->discountAmount);
        $this->assertSame(8.0, $result->taxAmount);
        $this->assertSame(88.0, $result->total);
    }

    public function test_line_discount_cannot_exceed_line_gross(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 50.00, 'quantity' => 1, 'discount_amount' => 999, 'tax_rate' => 0],
        ]);

        $this->assertSame(50.0, $result->discountAmount);
        $this->assertSame(0.0, $result->total);
    }

    public function test_order_level_discount_is_deducted_from_items_total(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 100.00, 'quantity' => 1, 'discount_amount' => 0, 'tax_rate' => 0],
            ['unit_price' => 50.00, 'quantity' => 2, 'discount_amount' => 0, 'tax_rate' => 0],
        ], orderDiscount: 30);

        $this->assertSame(200.0, $result->subtotal);
        $this->assertSame(30.0, $result->discountAmount);
        $this->assertSame(170.0, $result->total);
    }

    public function test_order_level_discount_is_capped_at_items_total(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 20.00, 'quantity' => 1, 'discount_amount' => 0, 'tax_rate' => 0],
        ], orderDiscount: 500);

        $this->assertSame(20.0, $result->discountAmount);
        $this->assertSame(0.0, $result->total);
    }

    public function test_multiple_lines_with_mixed_tax_rates_sum_correctly(): void
    {
        $result = CartCalculator::calculate([
            ['unit_price' => 100.00, 'quantity' => 1, 'discount_amount' => 0, 'tax_rate' => 10],
            ['unit_price' => 50.00, 'quantity' => 3, 'discount_amount' => 10, 'tax_rate' => 0],
        ]);

        // Line 1: 100 taxed at 10% = 110
        // Line 2: 150 - 10 discount = 140, no tax
        $this->assertSame(250.0, $result->subtotal);
        $this->assertSame(10.0, $result->discountAmount);
        $this->assertSame(10.0, $result->taxAmount);
        $this->assertSame(250.0, $result->total);
        $this->assertCount(2, $result->lines);
    }
}
