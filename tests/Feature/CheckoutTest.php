<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeCashier(): User
    {
        return User::query()->create([
            'name' => 'Test Cashier',
            'pin_hash' => Hash::make('1234'),
            'role' => 'cashier',
            'is_active' => true,
        ]);
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::query()->firstOrCreate(['slug' => 'test'], ['name' => 'Test']);

        return Product::query()->create(array_merge([
            'name' => 'Widget',
            'price' => 100,
            'tax_rate' => 10,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'category_id' => $category->id,
            'is_active' => true,
        ], $overrides));
    }

    public function test_completed_cash_sale_creates_a_transaction_and_decrements_stock(): void
    {
        $cashier = $this->makeCashier();
        $product = $this->makeProduct();

        $response = $this->actingAs($cashier)->postJson('/checkout', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'discount_amount' => 0],
            ],
            'order_discount' => 0,
            'payment_method' => 'cash',
            'amount_tendered' => 300,
        ]);

        $response->assertOk();

        $transaction = Transaction::query()->first();
        $this->assertNotNull($transaction);
        $this->assertSame($cashier->id, $transaction->user_id);
        $this->assertEquals(200.00, $transaction->subtotal);
        $this->assertEquals(20.00, $transaction->tax_amount);
        $this->assertEquals(220.00, $transaction->total);
        $this->assertEquals(80.00, $transaction->change_due);
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_cash_payment_below_total_is_rejected_and_stock_is_untouched(): void
    {
        $cashier = $this->makeCashier();
        $product = $this->makeProduct();

        $response = $this->actingAs($cashier)->postJson('/checkout', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'discount_amount' => 0],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, Transaction::query()->count());
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_insufficient_stock_rejects_the_sale_without_creating_a_transaction(): void
    {
        $cashier = $this->makeCashier();
        $product = $this->makeProduct(['stock_quantity' => 1]);

        $response = $this->actingAs($cashier)->postJson('/checkout', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'discount_amount' => 0],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 1000,
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, Transaction::query()->count());
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_guests_cannot_checkout(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/checkout', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'discount_amount' => 0],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 1000,
        ]);

        $response->assertUnauthorized();
    }
}
