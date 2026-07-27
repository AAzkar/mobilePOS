<x-layout title="Cart">
    <div x-data="cartPage()" class="space-y-4">

        <template x-if="cart.isEmpty">
            <div class="flex h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-slate-500">
                <p class="font-medium">Your cart is empty</p>
                <a href="{{ route('scan') }}" class="mt-2 text-sm text-teal-700">Go scan something &rarr;</a>
            </div>
        </template>

        <template x-if="!cart.isEmpty">
            <div class="space-y-4">
                <div class="space-y-2">
                    <template x-for="item in cart.items" :key="item.id">
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-medium" x-text="item.name"></p>
                                    <p class="text-xs text-slate-500">
                                        {{ $storeSettings->currency_symbol }} <span x-text="item.price.toFixed(2)"></span> each
                                    </p>
                                </div>
                                <button @click="cart.remove(item.id)" type="button" class="shrink-0 text-xs text-red-600">
                                    Remove
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="cart.increment(item.id, -1)"
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-lg"
                                    >&minus;</button>
                                    <input
                                        type="number"
                                        min="1"
                                        x-model.number="item.quantity"
                                        @change="cart.setQuantity(item.id, item.quantity)"
                                        class="w-12 rounded-lg border border-slate-300 px-1 py-1 text-center text-sm"
                                    >
                                    <button
                                        @click="cart.increment(item.id, 1)"
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-lg"
                                    >+</button>
                                </div>

                                <div class="flex items-center gap-1 text-xs text-slate-500">
                                    <span>Discount</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        x-model.number="item.discountAmount"
                                        @change="cart.setDiscount(item.id, item.discountAmount)"
                                        class="w-16 rounded-lg border border-slate-300 px-1 py-1 text-right text-sm"
                                    >
                                </div>

                                <p class="font-semibold" x-text="'{{ $storeSettings->currency_symbol }} ' + cart.lineFor(item).total.toFixed(2)"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-500">Subtotal</span>
                        <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.totals.subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-500">Discount</span>
                        <span x-text="'- {{ $storeSettings->currency_symbol }} ' + cart.totals.discount.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-500">Tax</span>
                        <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.totals.tax.toFixed(2)"></span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-slate-200 pt-1 text-base font-semibold">
                        <span>Total</span>
                        <span x-text="'{{ $storeSettings->currency_symbol }} ' + cart.totals.total.toFixed(2)"></span>
                    </div>
                </div>

                <a
                    href="{{ route('checkout') }}"
                    class="block w-full rounded-lg bg-teal-700 py-2.5 text-center text-sm font-medium text-white"
                >
                    Proceed to checkout
                </a>

                <div>
                    <button
                        x-show="!confirmingClear"
                        @click="confirmingClear = true"
                        type="button"
                        class="w-full py-2 text-center text-sm text-red-600"
                    >
                        Clear cart
                    </button>
                    <div x-show="confirmingClear" x-cloak class="flex items-center justify-center gap-3 text-sm">
                        <span class="text-slate-600">Clear the whole cart?</span>
                        <button @click="clearCart()" type="button" class="font-medium text-red-600">Yes, clear</button>
                        <button @click="confirmingClear = false" type="button" class="text-slate-500">Cancel</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-layout>
