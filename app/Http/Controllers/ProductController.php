<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function lookup(string $barcode): JsonResponse
    {
        $product = Product::query()
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json([
                'found' => false,
                'barcode' => $barcode,
            ], 404);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'barcode' => $product->barcode,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'tax_rate' => $product->effectiveTaxRate(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ]);
    }
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->search($request->string('q')->toString())
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $request->string('q')->toString(),
            'selectedCategoryId' => $request->integer('category_id') ?: null,
        ]);
    }

    public function create(Request $request): View
    {
        $product = new Product(['barcode' => $request->string('barcode')->toString() ?: null]);

        return view('products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'returnTo' => $request->string('return_to')->toString() ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        $product = Product::query()->create($data);

        if ($request->input('return_to') === 'scan') {
            return redirect()
                ->route('scan', ['added' => $product->barcode])
                ->with('status', "Product \"{$product->name}\" created and ready to scan.");
        }

        return redirect()
            ->route('products.edit', $product)
            ->with('status', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'returnTo' => null,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);

        $product->update($data);

        return redirect()
            ->route('products.edit', $product)
            ->with('status', "Product \"{$product->name}\" updated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'barcode' => ['nullable', 'string', 'max:64', 'unique:products,barcode,'.($product?->id).',id'],
            'sku' => ['nullable', 'string', 'max:64', 'unique:products,sku,'.($product?->id).',id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
