<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Production;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $totalSales = Bill::sum('grand_total');
        $totalOrders = Bill::count();
        $totalCustomers = Customer::count();
        $inventoryCount = Category::sum('stock');
        $activeProductions = Production::where('status', 'In Progress')->count();

        // Stock Health Metrics
        $stockHealth = [
            'total' => Category::count(),
            'low' => Category::where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_alert')->count(),
            'out' => Category::where('stock', '<=', 0)->count(),
            'expired' => Category::whereNotNull('expiry_date')->where('expiry_date', '<=', now()->toDateString())->count(),
        ];
        $stockHealth['percentage'] = $stockHealth['total'] > 0 ? round((($stockHealth['total'] - $stockHealth['out']) / $stockHealth['total']) * 100) : 100;

        // Recent Activity
        $recentBills = Bill::latest()->limit(5)->get();

        // Stock Alerts (Items with stock <= 10)
        $stockAlerts = Category::whereColumn('stock', '<=', 'low_stock_alert')
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        $period = $request->input('period', 'this_month');
        $now = now();
        if ($period == 'last_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
            $prevStart = $now->copy()->subMonths(2)->startOfMonth();
            $prevEnd = $now->copy()->subMonths(2)->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;
        } else {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $prevStart = $now->copy()->subMonth()->startOfMonth();
            $prevEnd = $now->copy()->subMonth()->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;
        }

        // Chart Data: Selected Period (Sales Revenue)
        $salesData = Bill::selectRaw('DATE(bill_date) as date, SUM(grand_total) as total')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Chart Data: Selected Period (Order Count)
        $ordersData = Bill::selectRaw('DATE(bill_date) as date, COUNT(*) as total')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Fill missing days with 0
        $chartData = [];
        $ordersChartData = [];
        $chartLabels = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $startDate->copy()->addDays($i - 1)->format('Y-m-d');
            $chartLabels[] = $startDate->copy()->addDays($i - 1)->format('d M');
            $chartData[] = (float) ($salesData[$date] ?? 0);
            $ordersChartData[] = (int) ($ordersData[$date] ?? 0);
        }

        $overviewSales = array_sum($chartData);
        $overviewOrders = array_sum($ordersChartData);

        // Trends vs Previous Period
        $prevOverviewSales = Bill::whereBetween('bill_date', [$prevStart, $prevEnd])->sum('grand_total');
        $prevOverviewOrders = Bill::whereBetween('bill_date', [$prevStart, $prevEnd])->count();

        $overviewSalesTrend = $prevOverviewSales > 0 ? round((($overviewSales - $prevOverviewSales) / $prevOverviewSales) * 100, 1) : ($overviewSales > 0 ? 100 : 0);
        $overviewOrdersTrend = $prevOverviewOrders > 0 ? round((($overviewOrders - $prevOverviewOrders) / $prevOverviewOrders) * 100, 1) : ($overviewOrders > 0 ? 100 : 0);

        // Top level total trend (vs yesterday)
        $prevSales = Bill::where('bill_date', '<', now()->startOfDay())->where('bill_date', '>=', now()->subDays(1)->startOfDay())->sum('grand_total');
        $salesTrend = $prevSales > 0 ? (($totalSales - $prevSales) / $prevSales) * 100 : 0;

        // Avg Daily
        $firstBill = Bill::orderBy('bill_date', 'asc')->first();
        $daysDiff = $firstBill ? now()->diffInDays($firstBill->bill_date) + 1 : 1;
        $avgDaily = $totalSales / $daysDiff;

        // Peak Time (Hour with most bills)
        $peakHourData = Bill::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();
        $peakTime = $peakHourData ? sprintf('%02d:00 %s', ($peakHourData->hour % 12 ?: 12), $peakHourData->hour >= 12 ? 'PM' : 'AM') : 'N/A';

        // Due Date Stats
        $today = \Carbon\Carbon::today();
        $dueStats = [
            'today_count' => \App\Models\InstalmentSchedule::where('status', '!=', 'paid')->where('due_date', $today)->count(),
            'today_amount' => \App\Models\InstalmentSchedule::where('status', '!=', 'paid')->where('due_date', $today)->sum('amount'),
            'overdue_count' => \App\Models\InstalmentSchedule::where('status', '!=', 'paid')->where('due_date', '<', $today)->count(),
            'overdue_amount' => \App\Models\InstalmentSchedule::where('status', '!=', 'paid')->where('due_date', '<', $today)->sum('amount'),
        ];

        // Upcoming Dues for Alert (Next 3 days)
        $upcomingDues = \App\Models\InstalmentSchedule::with(['instalment.customer'])
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(3)])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('tenant.dashboard', compact(
            'totalSales',
            'totalOrders',
            'totalCustomers',
            'inventoryCount',
            'recentBills',
            'stockAlerts',
            'chartData',
            'ordersChartData',
            'chartLabels',
            'salesTrend',
            'avgDaily',
            'peakTime',
            'stockHealth',
            'activeProductions',
            'dueStats',
            'upcomingDues',
            'period',
            'overviewSales',
            'overviewOrders',
            'overviewSalesTrend',
            'overviewOrdersTrend'
        ));
    }
}
