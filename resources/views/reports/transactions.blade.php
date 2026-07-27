@php $currency = $storeSettings->currency_symbol; @endphp
<x-layout title="Transaction History">
    <x-admin-tabs />

    <form method="GET" action="{{ route('reports.transactions') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-xs text-slate-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-teal-700 px-3 py-1.5 text-sm font-medium text-white">Filter</button>
        <a
            href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}"
            class="ml-auto rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700"
        >
            Export CSV
        </a>
    </form>

    <div class="space-y-2">
        @forelse ($transactions as $transaction)
            <a href="{{ route('receipts.show', $transaction) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3">
                <div>
                    <p class="text-sm font-medium">Receipt #{{ $transaction->id }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $transaction->created_at->format('Y-m-d H:i') }} &middot; {{ $transaction->user->name }} &middot; {{ ucfirst($transaction->payment_method) }}
                    </p>
                </div>
                <p class="font-semibold">{{ $currency }} {{ number_format($transaction->total, 2) }}</p>
            </a>
        @empty
            <p class="py-10 text-center text-sm text-slate-500">No transactions in this range.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layout>
