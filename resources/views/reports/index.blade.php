@php
    $currency = $storeSettings->currency_symbol;
    $maxDaily = $dailyTotals->max('total') ?: 1;
@endphp
<x-layout title="Reports">
    <x-admin-tabs />

    <div class="mb-4 grid grid-cols-3 gap-2 text-center">
        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs text-slate-500">Today</p>
            <p class="font-semibold">{{ $currency }} {{ number_format((float) $summary['today'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs text-slate-500">This week</p>
            <p class="font-semibold">{{ $currency }} {{ number_format((float) $summary['week'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs text-slate-500">This month</p>
            <p class="font-semibold">{{ $currency }} {{ number_format((float) $summary['month'], 2) }}</p>
        </div>
    </div>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4">
        <p class="mb-3 text-sm font-medium text-slate-700">Sales, last 14 days</p>
        <div class="space-y-1.5">
            @forelse ($dailyTotals as $day)
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-16 shrink-0 text-slate-500">{{ \Illuminate\Support\Carbon::parse($day->day)->format('M d') }}</span>
                    <div class="h-4 flex-1 rounded bg-slate-100">
                        <div class="h-4 rounded bg-teal-600" style="width: {{ max(4, ($day->total / $maxDaily) * 100) }}%"></div>
                    </div>
                    <span class="w-20 shrink-0 text-right text-slate-600">{{ $currency }} {{ number_format($day->total, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No sales yet.</p>
            @endforelse
        </div>
    </div>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4">
        <p class="mb-3 text-sm font-medium text-slate-700">Top-selling products</p>
        <div class="space-y-2 text-sm">
            @forelse ($topProducts as $product)
                <div class="flex justify-between">
                    <span>{{ $product->product_name }}</span>
                    <span class="text-slate-500">{{ $product->total_quantity }} sold &middot; {{ $currency }} {{ number_format($product->total_revenue, 2) }}</span>
                </div>
            @empty
                <p class="text-slate-500">No sales yet.</p>
            @endforelse
        </div>
    </div>

    <a href="{{ route('reports.transactions') }}" class="block w-full rounded-lg bg-teal-700 py-2.5 text-center text-sm font-medium text-white">
        View transaction history
    </a>
</x-layout>
