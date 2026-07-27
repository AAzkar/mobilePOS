<x-layout title="Inventory">
    <x-admin-tabs />

    <form method="GET" action="{{ route('inventory.index') }}" class="mb-4 flex gap-2">
        <input
            type="search" name="q" value="{{ $search }}"
            placeholder="Search name, barcode, SKU…"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
        >
        <label class="flex shrink-0 items-center gap-1 text-xs text-slate-600">
            <input type="checkbox" name="low_stock" value="1" @checked($lowStockOnly) onchange="this.form.submit()">
            Low stock
        </label>
        <button type="submit" class="rounded-lg bg-teal-700 px-3 py-2 text-sm font-medium text-white">Go</button>
    </form>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="space-y-2">
        @forelse ($products as $product)
            <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white p-3">
                <button @click="open = !open" type="button" class="flex w-full items-center justify-between gap-2 text-left">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $product->name }}</p>
                        <p class="text-xs text-slate-500">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="font-semibold {{ $product->isLowStock() ? 'text-red-600' : '' }}">
                            {{ $product->stock_quantity }} in stock
                        </p>
                        @if ($product->isLowStock())
                            <span class="text-xs text-red-600">Low stock (&le; {{ $product->low_stock_threshold }})</span>
                        @endif
                    </div>
                </button>

                <div x-show="open" x-cloak class="mt-3 border-t border-slate-100 pt-3">
                    <form method="POST" action="{{ route('inventory.adjust', $product) }}" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input
                                type="number" name="quantity_change" placeholder="e.g. 10 or -2" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            >
                            <select name="reason" required class="rounded-lg border border-slate-300 px-2 py-2 text-sm">
                                <option value="restock">Restock</option>
                                <option value="damage">Damage</option>
                                <option value="correction">Correction</option>
                            </select>
                        </div>
                        <input
                            type="text" name="notes" placeholder="Notes (optional)"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                        <button type="submit" class="w-full rounded-lg bg-teal-700 py-2 text-sm font-medium text-white">
                            Apply adjustment
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="py-10 text-center text-sm text-slate-500">No products found.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-layout>
