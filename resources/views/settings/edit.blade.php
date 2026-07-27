<x-layout title="Settings">
    <x-admin-tabs />

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Store name</label>
            <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Currency code</label>
                <input type="text" name="currency_code" value="{{ old('currency_code', $settings->currency_code) }}" required maxlength="3"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Currency symbol</label>
                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Default tax rate (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="default_tax_rate" value="{{ old('default_tax_rate', $settings->default_tax_rate) }}" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-slate-500">Applied to products that don't have their own tax rate override.</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Receipt footer text</label>
            <textarea name="receipt_footer_text" rows="2"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('receipt_footer_text', $settings->receipt_footer_text) }}</textarea>
        </div>

        <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-medium text-white">
            Save settings
        </button>
    </form>

    <a href="{{ route('users.index') }}" class="mt-6 block w-full rounded-lg border border-slate-300 py-2.5 text-center text-sm font-medium text-slate-700">
        Manage users
    </a>
</x-layout>
