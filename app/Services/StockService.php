<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;

class StockService
{
    /**
     * Decrements stock for a completed sale. Must run inside a DB
     * transaction — callers should wrap this (and the transaction/
     * transaction_items inserts) in a single DB::transaction() so a failed
     * sale can never partially decrement stock.
     *
     * Row-locks each product first so two concurrent checkouts can't both
     * read the same stock_quantity and oversell it.
     *
     * @param  list<array{product_id: int, quantity: int}>  $items
     *
     * @throws InsufficientStockException
     */
    public function decrementForSale(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::query()
                ->whereKey($item['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock_quantity < $item['quantity']) {
                throw new InsufficientStockException($product->name, $product->stock_quantity, $item['quantity']);
            }

            $product->decrement('stock_quantity', $item['quantity']);
        }
    }
}
