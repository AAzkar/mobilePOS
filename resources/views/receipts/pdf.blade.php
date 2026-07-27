<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: monospace; font-size: 11px; color: #111; margin: 0; padding: 8px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .row { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .rule { border-top: 1px dashed #666; margin: 6px 0; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <p class="center bold" style="font-size: 13px;">{{ $storeSettings->store_name }}</p>
    <p class="center">Receipt #{{ $transaction->id }}</p>
    <p class="center">{{ $transaction->created_at->format('Y-m-d H:i') }}</p>
    <p class="center">Cashier: {{ $transaction->user->name }}</p>

    <div class="rule"></div>

    <table>
        @foreach ($transaction->items as $item)
            <tr>
                <td colspan="2">{{ $item->quantity }} x {{ $item->product_name }}</td>
            </tr>
            <tr class="muted">
                <td>&nbsp;&nbsp;@ {{ $storeSettings->currency_symbol }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="rule"></div>

    <table>
        <tr><td>Subtotal</td><td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($transaction->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">-{{ $storeSettings->currency_symbol }} {{ number_format($transaction->discount_amount, 2) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($transaction->tax_amount, 2) }}</td></tr>
        <tr class="bold"><td>Total</td><td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($transaction->total, 2) }}</td></tr>
    </table>

    <div class="rule"></div>

    <table>
        <tr><td>Payment</td><td class="right">{{ ucfirst($transaction->payment_method) }}</td></tr>
        @if ($transaction->payment_method === 'cash')
            <tr><td>Tendered</td><td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($transaction->amount_tendered, 2) }}</td></tr>
            <tr><td>Change</td><td class="right">{{ $storeSettings->currency_symbol }} {{ number_format($transaction->change_due, 2) }}</td></tr>
        @endif
    </table>

    @if ($storeSettings->receipt_footer_text)
        <div class="rule"></div>
        <p class="center">{{ $storeSettings->receipt_footer_text }}</p>
    @endif
</body>
</html>
