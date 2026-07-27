<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function show(Transaction $transaction): View
    {
        $transaction->load(['items', 'user']);

        return view('receipts.show', [
            'transaction' => $transaction,
        ]);
    }

    public function pdf(Transaction $transaction): Response
    {
        $transaction->load(['items', 'user']);

        $pdf = Pdf::loadView('receipts.pdf', [
            'transaction' => $transaction,
            'storeSettings' => StoreSetting::current(),
        ])->setPaper([0, 0, 226.77, 700], 'portrait'); // ~80mm thermal receipt width

        return $pdf->download("receipt-{$transaction->id}.pdf");
    }
}
