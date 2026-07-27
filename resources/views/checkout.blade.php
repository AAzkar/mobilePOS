<x-layout title="Checkout">
    <div x-data="checkoutPage()" class="space-y-4">

        <template x-if="cart.isEmpty">
            <div class="flex h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-slate-500">
                <p class="font-medium">Your cart is empty</p>
                <a href="{{ route('scan') }}" class="mt-2 text-sm text-teal-700">Go scan something &rarr;</a>
            </div>
        </template>

        <template x-if="!cart.isEmpty">
            <div class="space-y-4">
                {{-- Order summary --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
                    <p class="mb-2 font-medium text-slate-700">Order summary</p>
                    <template x-for="item in cart.items" :key="item.id">
                        <div class="flex justify-between py-0.5 text-slate-600">
                            <span x-text="item.quantity + ' × ' + item.name"></span>
                            <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.lineFor(item).total.toFixed(2)"></span>
                        </div>
                    </template>
                    <div class="mt-2 flex justify-between border-t border-slate-200 pt-2">
                        <span class="text-slate-500">Subtotal</span>
                        <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.totals.subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Item discounts</span>
                        <span x-text="'- {{ $storeSettings->currency_symbol }} ' + cart.totals.discount.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tax</span>
                        <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.totals.tax.toFixed(2)"></span>
                    </div>
                </div>

                {{-- Order-level discount --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Order discount / coupon ({{ $storeSettings->currency_symbol }})</label>
                    <input
                        type="number" min="0" step="0.01"
                        x-model.number="orderDiscount"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>

                {{-- Grand total --}}
                <div class="rounded-xl bg-slate-900 p-4 text-center text-white">
                    <p class="text-xs uppercase tracking-wide text-slate-300">Total due</p>
                    <p class="text-2xl font-semibold" x-text="'{{ $storeSettings->currency_symbol }} ' + displayTotal.toFixed(2)"></p>
                </div>

                {{-- Payment method --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="mb-2 text-sm font-medium text-slate-700">Payment method</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button" @click="paymentMethod = 'cash'"
                            class="rounded-lg border py-2 text-sm"
                            :class="paymentMethod === 'cash' ? 'border-teal-700 bg-teal-50 text-teal-700 font-medium' : 'border-slate-300 text-slate-600'"
                        >Cash</button>
                        <button
                            type="button" @click="paymentMethod = 'card'"
                            class="rounded-lg border py-2 text-sm"
                            :class="paymentMethod === 'card' ? 'border-teal-700 bg-teal-50 text-teal-700 font-medium' : 'border-slate-300 text-slate-600'"
                        >Card</button>
                        <button
                            type="button" @click="paymentMethod = 'other'"
                            class="rounded-lg border py-2 text-sm"
                            :class="paymentMethod === 'other' ? 'border-teal-700 bg-teal-50 text-teal-700 font-medium' : 'border-slate-300 text-slate-600'"
                        >Other</button>
                    </div>

                    <div x-show="paymentMethod === 'cash'" x-cloak class="mt-3">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Amount tendered</label>
                        <input
                            type="number" min="0" step="0.01"
                            x-model.number="amountTendered"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            placeholder="0.00"
                        >
                        <p x-show="changeDue !== null" class="mt-2 text-sm text-slate-600">
                            Change due: <span class="font-semibold" x-text="'{{ $storeSettings->currency_symbol }} ' + (changeDue ?? 0).toFixed(2)"></span>
                        </p>
                    </div>

                    <p x-show="paymentMethod === 'card'" x-cloak class="mt-3 text-xs text-slate-500">
                        Card payment will be authorized via the store's payment gateway.
                    </p>
                </div>

                <div x-show="errorMessage" x-cloak class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" x-text="errorMessage"></div>

                <button
                    @click="submit()"
                    type="button"
                    :disabled="!canSubmit"
                    class="w-full rounded-lg bg-teal-700 py-3 text-sm font-medium text-white disabled:opacity-40"
                >
                    <span x-show="!submitting">Complete sale</span>
                    <span x-show="submitting" x-cloak>Processing…</span>
                </button>
            </div>
        </template>
    </div>
</x-layout>
