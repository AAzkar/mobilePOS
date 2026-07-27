<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = fn (string $name) => Category::query()
            ->where('slug', Str::slug($name))
            ->value('id');

        $products = [
            ['barcode' => '8901234560013', 'sku' => 'BEV-001', 'name' => 'Coca-Cola 400ml Bottle', 'price' => 180.00, 'cost' => 120.00, 'category' => 'Beverages', 'stock' => 60],
            ['barcode' => '8901234560020', 'sku' => 'BEV-002', 'name' => 'Bottled Water 500ml', 'price' => 80.00, 'cost' => 45.00, 'category' => 'Beverages', 'stock' => 100],
            ['barcode' => '8901234560037', 'sku' => 'BEV-003', 'name' => 'Ceylon Tea Bags (100pk)', 'price' => 450.00, 'cost' => 300.00, 'category' => 'Beverages', 'stock' => 40],
            ['barcode' => '8901234560044', 'sku' => 'SNK-001', 'name' => 'Potato Chips 150g', 'price' => 320.00, 'cost' => 220.00, 'category' => 'Snacks', 'stock' => 50],
            ['barcode' => '8901234560051', 'sku' => 'SNK-002', 'name' => 'Chocolate Biscuits 200g', 'price' => 280.00, 'cost' => 190.00, 'category' => 'Snacks', 'stock' => 45],
            ['barcode' => '8901234560068', 'sku' => 'SNK-003', 'name' => 'Roasted Peanuts 100g', 'price' => 150.00, 'cost' => 90.00, 'category' => 'Snacks', 'stock' => 70],
            ['barcode' => '8901234560075', 'sku' => 'DRY-001', 'name' => 'Fresh Milk 1L', 'price' => 420.00, 'cost' => 340.00, 'category' => 'Dairy & Eggs', 'stock' => 30],
            ['barcode' => '8901234560082', 'sku' => 'DRY-002', 'name' => 'Eggs (Tray of 10)', 'price' => 350.00, 'cost' => 270.00, 'category' => 'Dairy & Eggs', 'stock' => 25],
            ['barcode' => '8901234560099', 'sku' => 'DRY-003', 'name' => 'Cheddar Cheese Block 200g', 'price' => 690.00, 'cost' => 520.00, 'category' => 'Dairy & Eggs', 'stock' => 15],
            ['barcode' => '8901234560105', 'sku' => 'BKY-001', 'name' => 'White Bread Loaf', 'price' => 160.00, 'cost' => 100.00, 'category' => 'Bakery', 'stock' => 35, 'threshold' => 8],
            ['barcode' => '8901234560112', 'sku' => 'BKY-002', 'name' => 'Butter Croissant', 'price' => 120.00, 'cost' => 70.00, 'category' => 'Bakery', 'stock' => 20],
            ['barcode' => '8901234560129', 'sku' => 'HSH-001', 'name' => 'Dish Washing Liquid 500ml', 'price' => 390.00, 'cost' => 260.00, 'category' => 'Household', 'stock' => 28],
            ['barcode' => '8901234560136', 'sku' => 'HSH-002', 'name' => 'Laundry Detergent 1kg', 'price' => 890.00, 'cost' => 650.00, 'category' => 'Household', 'stock' => 18],
            ['barcode' => '8901234560143', 'sku' => 'PC-001', 'name' => 'Bath Soap Bar 100g', 'price' => 140.00, 'cost' => 85.00, 'category' => 'Personal Care', 'stock' => 55],
            ['barcode' => '8901234560150', 'sku' => 'PC-002', 'name' => 'Toothpaste 150g', 'price' => 380.00, 'cost' => 260.00, 'category' => 'Personal Care', 'stock' => 32],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['barcode' => $product['barcode']],
                [
                    'sku' => $product['sku'],
                    'name' => $product['name'],
                    'description' => null,
                    'price' => $product['price'],
                    'cost' => $product['cost'],
                    'tax_rate' => null,
                    'category_id' => $categoryId($product['category']),
                    'stock_quantity' => $product['stock'],
                    'low_stock_threshold' => $product['threshold'] ?? 5,
                    'is_active' => true,
                ],
            );
        }
    }
}
