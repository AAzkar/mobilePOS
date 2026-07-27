<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock): Product
    {
        $category = Category::query()->firstOrCreate(['slug' => 'test'], ['name' => 'Test']);

        return Product::query()->create([
            'name' => 'Widget',
            'price' => 10,
            'stock_quantity' => $stock,
            'low_stock_threshold' => 5,
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_decrements_stock_by_the_requested_quantity(): void
    {
        $product = $this->makeProduct(20);

        (new StockService)->decrementForSale([
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertSame(15, $product->fresh()->stock_quantity);
    }

    public function test_throws_when_requested_quantity_exceeds_stock(): void
    {
        $product = $this->makeProduct(3);

        $this->expectException(InsufficientStockException::class);

        (new StockService)->decrementForSale([
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    public function test_a_failed_item_rolls_back_earlier_decrements_in_the_same_sale(): void
    {
        $inStock = $this->makeProduct(10);
        $outOfStock = $this->makeProduct(1);

        try {
            DB::transaction(function () use ($inStock, $outOfStock) {
                (new StockService)->decrementForSale([
                    ['product_id' => $inStock->id, 'quantity' => 4],
                    ['product_id' => $outOfStock->id, 'quantity' => 5],
                ]);
            });
        } catch (InsufficientStockException) {
            // expected
        }

        // The first item's decrement must not survive since the whole sale
        // failed — this is exactly why checkout wraps StockService in
        // DB::transaction() rather than calling it decrement-by-decrement.
        $this->assertSame(10, $inStock->fresh()->stock_quantity);
        $this->assertSame(1, $outOfStock->fresh()->stock_quantity);
    }

    public function test_exact_stock_match_is_allowed(): void
    {
        $product = $this->makeProduct(5);

        (new StockService)->decrementForSale([
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertSame(0, $product->fresh()->stock_quantity);
    }
}
