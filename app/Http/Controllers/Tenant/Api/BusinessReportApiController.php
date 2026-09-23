<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DailyExpense;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessReportApiController extends Controller
{
    private function dateFilter($query, Request $request, string $column = 'created_at')
    {
        if ($request->filled('from')) {
            $query->whereDate($column, '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate($column, '<=', $request->to);
        }
        return $query;
    }

    // GET /api/v1/reports/sales
    public function sales(Request $request)
    {
        $query = Bill::where('status', 'completed');
        $this->dateFilter($query, $request, 'bill_date');

        $groupBy = $request->get('group_by', 'day');
        $format  = match ($groupBy) {
            'month' => "DATE_TRUNC('month', bill_date)",
            'week'  => "DATE_TRUNC('week', bill_date)",
            default => "DATE(bill_date)",
        };

        $data = $query->selectRaw("$format as period, count(*) as total_bills, sum(grand_total) as total_sales, sum(gst_amount) as total_tax, sum(discount_amount) as total_discount")
            ->groupByRaw($format)
            ->orderByRaw("$format")
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // GET /api/v1/reports/purchase
    public function purchase(Request $request)
    {
        $query = Purchase::query();
        $this->dateFilter($query, $request, 'invoice_date');

        $data = $query->selectRaw("DATE(invoice_date) as period, count(*) as total_orders, sum(total_amount) as total_amount, sum(total_amount - balance_amount) as paid, sum(balance_amount) as balance")
            ->groupByRaw("DATE(invoice_date)")
            ->orderByRaw("DATE(invoice_date)")
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // GET /api/v1/reports/profit
    public function profit(Request $request)
    {
        $query = Bill::where('status', 'completed');
        $this->dateFilter($query, $request, 'bill_date');

        $bills = $query->with('items.category')->get();

        $revenue = $bills->sum('grand_total');
        $cogs    = $bills->flatMap(fn($b) => $b->items)->sum(fn($i) => ($i->category?->dealer_price ?? 0) * $i->quantity);
        $expense = DailyExpense::when($request->filled('from'), fn($q) => $q->whereDate('expense_date', '>=', $request->from))
                               ->when($request->filled('to'), fn($q) => $q->whereDate('expense_date', '<=', $request->to))
                               ->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'revenue'        => round($revenue, 2),
                'cost_of_goods'  => round($cogs, 2),
                'gross_profit'   => round($revenue - $cogs, 2),
                'expenses'       => round($expense, 2),
                'net_profit'     => round($revenue - $cogs - $expense, 2),
                'margin_percent' => $revenue > 0 ? round(($revenue - $cogs) / $revenue * 100, 2) : 0,
            ],
        ]);
    }

    // GET /api/v1/reports/stock
    public function stock(Request $request)
    {
        $query = Category::query();

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_alert');
        }

        $products = $query->select('id', 'product_name', 'barcode', 'stock', 'low_stock_alert', 'dealer_price')
            ->selectRaw('(stock * dealer_price) as stock_value')
            ->orderBy('product_name')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => ['total' => $products->total(), 'current_page' => $products->currentPage(), 'last_page' => $products->lastPage()],
        ]);
    }

    // GET /api/v1/reports/customers
    public function customers(Request $request)
    {
        $data = Customer::selectRaw('customers.id, customers.name, customers.phone, customers.bill_count, customers.points, sum(bills.grand_total) as total_spent')
            ->leftJoin('bills', 'bills.customer_id', '=', 'customers.id')
            ->where(function ($q) {
                $q->where('bills.status', 'completed')->orWhereNull('bills.status');
            })
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.bill_count', 'customers.points')
            ->orderByRaw('sum(bills.grand_total) desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => ['total' => $data->total()],
        ]);
    }

    // GET /api/v1/reports/product-performance
    public function productPerformance(Request $request)
    {
        $query = DB::table('bill_items')
            ->join('categories', 'bill_items.category_id', '=', 'categories.id')
            ->join('bills', 'bill_items.bill_id', '=', 'bills.id')
            ->where('bills.status', 'completed')
            ->select('categories.id', 'categories.product_name', 'categories.product_type',
                DB::raw('sum(bill_items.quantity) as total_qty'),
                DB::raw('sum(bill_items.quantity * bill_items.mrp) as total_revenue'))
            ->groupBy('categories.id', 'categories.product_name', 'categories.product_type')
            ->orderByRaw('sum(bill_items.quantity) desc');

        if ($request->filled('from')) {
            $query->whereDate('bills.bill_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('bills.bill_date', '<=', $request->to);
        }

        $data = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => ['total' => $data->total()],
        ]);
    }

    // GET /api/v1/reports/payment
    public function payment(Request $request)
    {
        $query = Bill::where('status', 'completed');
        $this->dateFilter($query, $request, 'bill_date');

        $data = $query->selectRaw('payment_mode, count(*) as count, sum(grand_total) as total')
            ->groupBy('payment_mode')
            ->orderByRaw('sum(grand_total) desc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // GET /api/v1/reports/tax
    public function tax(Request $request)
    {
        $query = Bill::where('status', 'completed');
        $this->dateFilter($query, $request, 'bill_date');

        $data = $query->selectRaw("DATE_TRUNC('month', bill_date) as month, sum(gst_amount) as total_gst, sum(grand_total) as total_sales, sum(gst_percent) / nullif(count(*), 0) as avg_rate")
            ->groupByRaw("DATE_TRUNC('month', bill_date)")
            ->orderByRaw("DATE_TRUNC('month', bill_date)")
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // GET /api/v1/reports/expense
    public function expense(Request $request)
    {
        $query = DailyExpense::query();
        $this->dateFilter($query, $request, 'expense_date');

        $total      = (clone $query)->sum('amount');
        $byCategory = (clone $query)
            ->selectRaw('category, sum(amount) as total, count(*) as count')
            ->groupBy('category')
            ->orderByRaw('sum(amount) desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ['total' => round($total, 2), 'by_category' => $byCategory],
        ]);
    }

    // GET /api/v1/reports/credit (outstanding dues)
    public function credit(Request $request)
    {
        $data = Bill::where('payment_mode', 'credit')
            ->where('status', 'completed')
            ->selectRaw('customer_id, customer_name, customer_phone, sum(grand_total - paid_amount) as outstanding')
            ->groupBy('customer_id', 'customer_name', 'customer_phone')
            ->having(DB::raw('sum(grand_total - paid_amount)'), '>', 0)
            ->orderByRaw('sum(grand_total - paid_amount) desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => ['total' => $data->total()],
        ]);
    }

    // GET /api/v1/reports/dashboard
    public function dashboard(Request $request)
    {
        $today     = today();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'success' => true,
            'data'    => [
                'today_sales'         => round(Bill::where('status', 'completed')->whereDate('bill_date', $today)->sum('grand_total'), 2),
                'today_bills'         => Bill::where('status', 'completed')->whereDate('bill_date', $today)->count(),
                'month_sales'         => round(Bill::where('status', 'completed')->where('bill_date', '>=', $thisMonth)->sum('grand_total'), 2),
                'month_bills'         => Bill::where('status', 'completed')->where('bill_date', '>=', $thisMonth)->count(),
                'total_customers'     => Customer::count(),
                'low_stock_products'  => Category::whereColumn('stock', '<=', 'low_stock_alert')->count(),
                'pending_purchases'   => Purchase::where('status', 'pending')->count(),
                'credit_outstanding'  => round(Bill::where('payment_mode', 'credit')->sum(DB::raw('grand_total - paid_amount')), 2),
            ],
        ]);
    }

    // GET /api/v1/reports/inventory-movement
    public function inventoryMovement(Request $request)
    {
        $query = StockLog::with('product');
        $this->dateFilter($query, $request);

        if ($request->filled('product_id')) {
            $query->where('category_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
            'meta'    => ['total' => $logs->total(), 'current_page' => $logs->currentPage(), 'last_page' => $logs->lastPage()],
        ]);
    }

    // GET /api/v1/reports/vendor
    public function vendor(Request $request)
    {
        $data = DB::table('purchases')
            ->join('suppliers', 'purchases.vendor_id', '=', 'suppliers.id')
            ->selectRaw('suppliers.id as vendor_id, suppliers.name as supplier_name, suppliers.phone,
                count(*) as total_orders,
                sum(purchases.total_amount) as total_purchased,
                sum(purchases.total_amount - purchases.balance_amount) as total_paid,
                sum(purchases.balance_amount) as outstanding')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone')
            ->orderByRaw('sum(purchases.total_amount) desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => ['total' => $data->total()],
        ]);
    }

    // GET /api/v1/reports/discount
    public function discount(Request $request)
    {
        $query = Bill::where('status', 'completed')->where('discount_amount', '>', 0);
        $this->dateFilter($query, $request, 'bill_date');

        $total = $query->sum('discount_amount');
        $count = $query->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_discount'   => round($total, 2),
                'discounted_bills' => $count,
                'avg_discount'     => $count > 0 ? round($total / $count, 2) : 0,
            ],
        ]);
    }

    // GET /api/v1/reports/returns
    public function returns(Request $request)
    {
        $query = \App\Models\RmaRequest::with('customer');
        $this->dateFilter($query, $request);

        $data = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => ['total' => $data->total()],
        ]);
    }

    // GET /api/v1/reports/financial
    public function financial(Request $request)
    {
        $from  = $request->get('from', now()->startOfMonth()->toDateString());
        $to    = $request->get('to', now()->toDateString());

        $sales     = Bill::where('status', 'completed')->whereBetween('bill_date', [$from, $to])->sum('grand_total');
        $purchase  = Purchase::whereBetween('invoice_date', [$from, $to])->sum('total_amount');
        $expense   = DailyExpense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $taxColl   = Bill::where('status', 'completed')->whereBetween('bill_date', [$from, $to])->sum('gst_amount');

        return response()->json([
            'success' => true,
            'period'  => compact('from', 'to'),
            'data'    => [
                'total_sales'      => round($sales, 2),
                'total_purchase'   => round($purchase, 2),
                'total_expense'    => round($expense, 2),
                'tax_collected'    => round($taxColl, 2),
                'net_cash_flow'    => round($sales - $purchase - $expense, 2),
            ],
        ]);
    }
}
