<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->search($request->string('q')->toString())
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.index', [
            'products' => $products,
            'search' => $request->string('q')->toString(),
            'lowStockOnly' => $request->boolean('low_stock'),
        ]);
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'in:restock,damage,correction'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newQuantity = $product->stock_quantity + $data['quantity_change'];

        if ($newQuantity < 0) {
            return back()->withErrors(['quantity_change' => 'That adjustment would take stock below zero.']);
        }

        DB::transaction(function () use ($product, $data, $newQuantity) {
            $product->update(['stock_quantity' => $newQuantity]);

            $product->stockAdjustments()->create([
                'user_id' => auth()->id(),
                'quantity_change' => $data['quantity_change'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('status', "Stock for \"{$product->name}\" adjusted to {$newQuantity}.");
    }
}
