<x-layout title="Scan">
    <div x-data="scanPage()" class="space-y-4">

        {{-- Camera preview --}}
        <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-black">
            <div id="camera-scanner" class="aspect-[4/3] w-full"></div>

            {{-- Flash confirmation overlay --}}
            <div
                x-show="flash"
                x-transition.opacity.duration.300ms
                class="pointer-events-none absolute inset-0 bg-emerald-400/50"
            ></div>

            <div x-show="cameraStatus === 'starting'" class="absolute inset-0 flex items-center justify-center bg-black/60 text-sm text-white">
                Starting camera…
            </div>

            <div x-show="cameraStatus === 'denied'" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/85 px-4 text-center text-sm text-white">
                <p class="font-medium">Camera access is blocked</p>
                <p class="text-slate-300">
                    Enable camera access for this site in your browser's site settings (usually the
                    padlock icon next to the address bar &rarr; Site settings &rarr; Camera &rarr; Allow),
                    then reload.
                </p>
                <button @click="retryCamera()" type="button" class="mt-1 rounded-lg bg-white px-3 py-1.5 text-slate-900">
                    Try again
                </button>
            </div>

            <div x-show="cameraStatus === 'error'" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/85 px-4 text-center text-sm text-white">
                <p class="font-medium">Camera couldn't start</p>
                <p class="text-slate-300" x-text="errorMessage"></p>
                <p class="text-slate-300">You can still use manual entry or a wired/Bluetooth scanner below.</p>
            </div>
        </div>

        {{-- Last scanned confirmation --}}
        <div x-show="lastScan" x-cloak x-transition class="rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            Added <span class="font-medium" x-text="lastScan?.name"></span>
            ({{ $storeSettings->currency_symbol }} <span x-text="lastScan?.price?.toFixed(2)"></span>)
        </div>

        {{-- Manual entry fallback --}}
        <form @submit.prevent="submitManual()" class="flex gap-2">
            <input
                type="text"
                x-model="manualCode"
                inputmode="numeric"
                placeholder="Enter barcode or SKU manually"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
            <button type="submit" class="shrink-0 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white">
                Add
            </button>
        </form>

        <p class="text-center text-xs text-slate-400">
            A Bluetooth/USB barcode scanner also works here automatically — just scan while this page is open.
        </p>

        {{-- Cart summary bar --}}
        <template x-if="!Alpine.store('cart').isEmpty">
            <a
                href="{{ route('cart') }}"
                class="flex items-center justify-between rounded-lg bg-slate-900 px-4 py-3 text-sm font-medium text-white"
            >
                <span x-text="Alpine.store('cart').itemCount + ' item(s) in cart'"></span>
                <span x-text="'{{ $storeSettings->currency_symbol }} ' + Alpine.store('cart').totals.total.toFixed(2)"></span>
            </a>
        </template>

        {{-- Unknown barcode modal --}}
        <div x-show="unknownBarcode" x-cloak class="fixed inset-0 z-30 flex items-end justify-center bg-black/50 sm:items-center" @click.self="dismissUnknown()">
            <div class="w-full max-w-lg rounded-t-xl bg-white p-4 sm:rounded-xl">
                <p class="mb-1 text-sm font-medium text-slate-900">Unknown barcode</p>
                <p class="mb-4 text-sm text-slate-500">
                    "<span x-text="unknownBarcode"></span>" isn't in your product catalog yet.
                </p>
                <div class="space-y-2">
                    <a
                        :href="'{{ route('products.create') }}?barcode=' + encodeURIComponent(unknownBarcode) + '&return_to=scan'"
                        class="block w-full rounded-lg bg-teal-700 py-2.5 text-center text-sm font-medium text-white"
                    >
                        + Add as new product
                    </a>
                    <a
                        :href="'{{ route('products.index') }}?q=' + encodeURIComponent(unknownBarcode)"
                        class="block w-full rounded-lg border border-slate-300 py-2.5 text-center text-sm font-medium text-slate-700"
                    >
                        Search manually
                    </a>
                    <button @click="dismissUnknown()" type="button" class="block w-full py-2 text-center text-sm text-slate-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
