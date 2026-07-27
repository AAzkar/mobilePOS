<x-layout title="Products">
    <x-admin-tabs />

    <div class="mb-4 flex items-center justify-between gap-2">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-1 gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search name, barcode, SKU…"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
            <select
                name="category_id"
                onchange="this.form.submit()"
                class="rounded-lg border border-slate-300 px-2 py-2 text-sm"
            >
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategoryId === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-teal-700 px-3 py-2 text-sm font-medium text-white">Go</button>
        </form>
    </div>

    <a
        href="{{ route('products.create') }}"
        class="mb-4 flex w-full items-center justify-center rounded-lg border-2 border-dashed border-teal-600 py-2 text-sm font-medium text-teal-700"
    >
        + Add product
    </a>

    <div class="space-y-2">
        @forelse ($products as $product)
            <a href="{{ route('products.edit', $product) }}" class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $product->name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $product->category?->name ?? 'Uncategorized' }}
                            @if ($product->barcode)
                                &middot; {{ $product->barcode }}
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="font-semibold">{{ $storeSettings->currency_symbol }} {{ number_format($product->price, 2) }}</p>
                        <p class="text-xs {{ $product->isLowStock() ? 'font-medium text-red-600' : 'text-slate-500' }}">
                            Stock: {{ $product->stock_quantity }}
                        </p>
                    </div>
                </div>
                @if (! $product->is_active)
                    <span class="mt-2 inline-block rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Inactive</span>
                @endif
            </a>
        @empty
            <p class="py-10 text-center text-sm text-slate-500">No products found.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</x-layout>
