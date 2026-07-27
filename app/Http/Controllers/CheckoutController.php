<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\CartCalculator;
use App\Services\Payments\PaymentGateway;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('checkout');
    }

    public function store(Request $request, StockService $stockService, PaymentGateway $gateway): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,other'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Prices and tax rates are always read fresh from the database here —
        // never trust client-supplied amounts for anything that affects money.
        $products = Product::query()
            ->whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $lines = [];
        foreach ($data['items'] as $item) {
            $product = $products->get($item['product_id']);

            if (! $product || ! $product->is_active) {
                return response()->json([
                    'message' => 'One of the items in your cart is no longer available. Please review your cart.',
                ], 422);
            }

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => (float) $product->price,
                'quantity' => $item['quantity'],
                'discount_amount' => $item['discount_amount'] ?? 0,
                'tax_rate' => $product->effectiveTaxRate(),
            ];
        }

        $calculation = CartCalculator::calculate($lines, (float) ($data['order_discount'] ?? 0));

        $amountTendered = null;
        $changeDue = null;

        if ($data['payment_method'] === 'cash') {
            $amountTendered = (float) ($data['amount_tendered'] ?? 0);

            if ($amountTendered < $calculation->total) {
                return response()->json([
                    'message' => 'Amount tendered is less than the total due.',
                ], 422);
            }

            $changeDue = round($amountTendered - $calculation->total, 2);
        } else {
            $result = $gateway->charge($calculation->total, ['method' => $data['payment_method']]);

            if (! $result->approved) {
                return response()->json([
                    'message' => $result->message ?? 'Payment was declined.',
                ], 422);
            }
        }

        try {
            $transaction = DB::transaction(function () use ($data, $lines, $calculation, $amountTendered, $changeDue, $stockService) {
                $stockService->decrementForSale(array_map(
                    fn ($line) => ['product_id' => $line['product_id'], 'quantity' => $line['quantity']],
                    $lines,
                ));

                $transaction = Transaction::query()->create([
                    'user_id' => auth()->id(),
                    'subtotal' => $calculation->subtotal,
                    'discount_amount' => $calculation->discountAmount,
                    'tax_amount' => $calculation->taxAmount,
                    'total' => $calculation->total,
                    'payment_method' => $data['payment_method'],
                    'amount_tendered' => $amountTendered,
                    'change_due' => $changeDue,
                    'status' => 'completed',
                ]);

                foreach ($lines as $index => $line) {
                    $lineResult = $calculation->lines[$index];

                    $transaction->items()->create([
                        'product_id' => $line['product_id'],
                        'product_name' => $line['product_name'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'discount_amount' => $lineResult->discountAmount,
                        'tax_amount' => $lineResult->taxAmount,
                        'line_total' => $lineResult->lineTotal,
                    ]);
                }

                return $transaction;
            });
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'redirect' => route('receipts.show', $transaction),
        ]);
    }
}
