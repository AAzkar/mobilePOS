@php $isEdit = $product->exists; @endphp
<x-layout :title="$isEdit ? 'Edit Product' : 'Add Product'">
    <a href="{{ ($returnTo ?? null) === 'scan' ? route('scan') : route('products.index') }}" class="mb-3 inline-block text-sm text-teal-700">
        &larr; {{ ($returnTo ?? null) === 'scan' ? 'Back to scan' : 'Back to products' }}
    </a>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}"
        class="space-y-4"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        @if (! empty($returnTo))
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
        @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Barcode</label>
                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="2"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Price ({{ $storeSettings->currency_symbol }})
                </label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Cost</label>
                <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', $product->cost) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Tax rate % (blank = store default {{ $storeSettings->default_tax_rate }}%)
                </label>
                <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">None</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Stock quantity</label>
                <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Low stock threshold</label>
                <input type="number" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
            Active (visible for sale)
        </label>

        <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-medium text-white">
            {{ $isEdit ? 'Save changes' : 'Create product' }}
        </button>
    </form>
</x-layout>
