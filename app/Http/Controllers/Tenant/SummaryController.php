<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        // Parse date filter
        $filter = $request->get('filter', 'today');
        
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        if ($filter == 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
        } elseif ($filter == 'this_week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($filter == 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($filter == 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        }

        // Custom dates
        if ($request->has('start_date') && $request->has('end_date') && $filter === 'custom') {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
            $filter = 'custom';
        }

        // --- Sales Data ---
        $bills = Bill::whereBetween('bill_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        
        $totalInvoices = $bills->count();
        $totalSales = $bills->sum('grand_total');
        
        // Group by payment mode
        $cashSales = $bills->where('payment_mode', 'cash')->sum('grand_total');
        $qrSales = $bills->whereIn('payment_mode', ['upi', 'bank_transfer', 'qr'])->sum('grand_total');
        $cardSales = $bills->where('payment_mode', 'card')->sum('grand_total');
        $creditSales = $bills->whereIn('payment_mode', ['credit', 'instalment'])->sum('grand_total');

        // Chart Data (Daily breakdown for the selected period)
        $chartLabels = [];
        $chartData = [
            'cash' => [],
            'qr' => [],
            'card' => [],
            'credit' => []
        ];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $chartLabels[] = $currentDate->format('d M');
            
            $dayBills = $bills->filter(function($b) use ($dateStr) {
                return $b->bill_date && $b->bill_date->format('Y-m-d') == $dateStr;
            });

            $chartData['cash'][] = $dayBills->where('payment_mode', 'cash')->sum('grand_total');
            $chartData['qr'][] = $dayBills->whereIn('payment_mode', ['upi', 'bank_transfer', 'qr'])->sum('grand_total');
            $chartData['card'][] = $dayBills->where('payment_mode', 'card')->sum('grand_total');
            $chartData['credit'][] = $dayBills->whereIn('payment_mode', ['credit', 'instalment'])->sum('grand_total');

            $currentDate->addDay();
        }

        // --- Purchases Data ---
        $purchases = Purchase::whereBetween('invoice_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        $totalPurchasesCount = $purchases->count();
        $totalPurchases = $purchases->sum('total_amount');
        // Mock settled and pending since we don't track payments in Purchase yet
        $amountSettled = 0;
        $pendingPayment = $totalPurchases;

        // --- Expenses & Cash Movement ---
        // Mocked as 0 for now
        $totalExpenses = 0;
        $openingCash = 0;
        $bankDeposits = 0;
        $closingCash = $cashSales; // Simplistic closing cash calculation

        return view('tenant.summary.index', compact(
            'startDate', 'endDate', 'filter',
            'totalInvoices', 'totalSales', 'cashSales', 'qrSales', 'cardSales', 'creditSales',
            'chartLabels', 'chartData',
            'totalPurchasesCount', 'totalPurchases', 'amountSettled', 'pendingPayment',
            'totalExpenses', 'openingCash', 'bankDeposits', 'closingCash'
        ));
    }
}
