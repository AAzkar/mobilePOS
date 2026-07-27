<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $summary = [
            'today' => Transaction::query()->where('created_at', '>=', $today)->sum('total'),
            'week' => Transaction::query()->where('created_at', '>=', $weekStart)->sum('total'),
            'month' => Transaction::query()->where('created_at', '>=', $monthStart)->sum('total'),
        ];

        $dailyTotals = Transaction::query()
            ->selectRaw('DATE(created_at) as day, SUM(total) as total, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topProducts = TransactionItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(line_total) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'summary' => $summary,
            'dailyTotals' => $dailyTotals,
            'topProducts' => $topProducts,
        ]);
    }

    public function transactions(Request $request): View
    {
        $transactions = Transaction::query()
            ->with('user')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('reports.transactions', [
            'transactions' => $transactions,
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Transaction::query()
            ->with('user')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->orderBy('created_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Date', 'Cashier', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method']);

            $query->chunk(200, function ($transactions) use ($out) {
                foreach ($transactions as $transaction) {
                    fputcsv($out, [
                        $transaction->id,
                        $transaction->created_at->format('Y-m-d H:i:s'),
                        $transaction->user->name,
                        $transaction->subtotal,
                        $transaction->discount_amount,
                        $transaction->tax_amount,
                        $transaction->total,
                        $transaction->payment_method,
                    ]);
                }
            });

            fclose($out);
        }, 'transactions.csv', ['Content-Type' => 'text/csv']);
    }
}
