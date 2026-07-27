@php
    $currency = $storeSettings->currency_symbol;
@endphp
<x-layout title="Receipt">
    <div x-data="{
        share() {
            const text = document.getElementById('receipt-text').innerText;
            if (navigator.share) {
                navigator.share({ title: 'Receipt #{{ $transaction->id }}', text }).catch(() => {});
            } else {
                navigator.clipboard?.writeText(text);
                alert('Web Share isn\'t available on this browser — receipt text copied instead.');
            }
        }
    }">
        <div class="mb-4 flex gap-2 print:hidden">
            <button @click="window.print()" type="button" class="flex-1 rounded-lg bg-teal-700 py-2 text-sm font-medium text-white">
                Print
            </button>
            <a href="{{ route('receipts.pdf', $transaction) }}" class="flex-1 rounded-lg border border-slate-300 py-2 text-center text-sm font-medium text-slate-700">
                Download PDF
            </a>
            <button @click="share()" type="button" class="flex-1 rounded-lg border border-slate-300 py-2 text-sm font-medium text-slate-700">
                Share
            </button>
        </div>

        <div id="receipt-text" class="mx-auto max-w-xs rounded-xl border border-slate-200 bg-white p-4 font-mono text-xs leading-relaxed text-slate-800 print:border-0 print:shadow-none">
            <div class="text-center">
                <p class="text-sm font-bold">{{ $storeSettings->store_name }}</p>
                <p>Receipt #{{ $transaction->id }}</p>
                <p>{{ $transaction->created_at->format('Y-m-d H:i') }}</p>
                <p>Cashier: {{ $transaction->user->name }}</p>
            </div>

            <div class="my-2 border-t border-dashed border-slate-400"></div>

            @foreach ($transaction->items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->quantity }} x {{ $item->product_name }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>&nbsp;&nbsp;@ {{ $currency }} {{ number_format($item->unit_price, 2) }}</span>
                    <span>{{ $currency }} {{ number_format($item->line_total, 2) }}</span>
                </div>
            @endforeach

            <div class="my-2 border-t border-dashed border-slate-400"></div>

            <div class="flex justify-between"><span>Subtotal</span><span>{{ $currency }} {{ number_format($transaction->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span>Discount</span><span>-{{ $currency }} {{ number_format($transaction->discount_amount, 2) }}</span></div>
            <div class="flex justify-between"><span>Tax</span><span>{{ $currency }} {{ number_format($transaction->tax_amount, 2) }}</span></div>
            <div class="flex justify-between text-sm font-bold"><span>Total</span><span>{{ $currency }} {{ number_format($transaction->total, 2) }}</span></div>

            <div class="my-2 border-t border-dashed border-slate-400"></div>

            <div class="flex justify-between"><span>Payment</span><span>{{ ucfirst($transaction->payment_method) }}</span></div>
            @if ($transaction->payment_method === 'cash')
                <div class="flex justify-between"><span>Tendered</span><span>{{ $currency }} {{ number_format($transaction->amount_tendered, 2) }}</span></div>
                <div class="flex justify-between"><span>Change</span><span>{{ $currency }} {{ number_format($transaction->change_due, 2) }}</span></div>
            @endif

            @if ($storeSettings->receipt_footer_text)
                <div class="my-2 border-t border-dashed border-slate-400"></div>
                <p class="text-center">{{ $storeSettings->receipt_footer_text }}</p>
            @endif
        </div>
    </div>
</x-layout>
