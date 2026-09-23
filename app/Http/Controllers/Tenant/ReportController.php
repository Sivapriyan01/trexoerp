<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request, $type = null)
    {
        $type = $type ?: $request->get('type', 'invoice');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $data = [];
        $stats = [];

        switch ($type) {
            case 'invoice':
                $query = Bill::with('customer')->whereBetween('bill_date', [$startDate, $endDate]);
                $data = $query->latest()->paginate(20);
                $stats = [
                    ['label' => 'Total Sales', 'value' => '₹' . number_format($query->sum('grand_total'), 2), 'color' => 'indigo'],
                    ['label' => 'Total Invoices', 'value' => $query->count(), 'color' => 'blue'],
                    ['label' => 'Total GST', 'value' => '₹' . number_format($query->sum('gst_amount'), 2), 'color' => 'emerald'],
                    ['label' => 'Net Profit', 'value' => '₹' . number_format($query->sum('grand_total') * 0.15, 2), 'color' => 'rose'], // Simplified profit logic
                ];
                break;

            case 'purchase':
                $query = Purchase::with(['vendor'])->withCount('items')->whereBetween('invoice_date', [$startDate, $endDate]);
                $data = $query->latest()->paginate(20);
                $stats = [
                    ['label' => 'Total Purchase', 'value' => '₹' . number_format($query->sum('total_amount'), 2), 'color' => 'emerald'],
                    ['label' => 'Total Invoices', 'value' => $query->count(), 'color' => 'sky'],
                    ['label' => 'Stock Inwarded', 'value' => number_format(DB::table('purchase_items')->whereIn('purchase_id', $query->pluck('id'))->sum('quantity')), 'color' => 'amber'],
                ];
                break;

            case 'product':
                $data = BillItem::select(
                        'bill_items.product_name', 
                        'bill_items.product_type', 
                        DB::raw('SUM(bill_items.quantity) as total_qty'), 
                        DB::raw('SUM(bill_items.total) as total_sales'),
                        DB::raw('SUM(bill_items.total - (COALESCE(categories.dealer_price, 0) * bill_items.quantity)) as total_profit')
                    )
                    ->leftJoin('categories', 'bill_items.category_id', '=', 'categories.id')
                    ->whereHas('bill', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('bill_date', [$startDate, $endDate]);
                    })
                    ->groupBy('bill_items.product_name', 'bill_items.product_type')
                    ->orderBy('total_qty', 'desc')
                    ->paginate(20);
                
                $stats = [
                    ['label' => 'Top Selling', 'value' => $data->first()->product_name ?? 'N/A', 'color' => 'violet'],
                    ['label' => 'Units Sold', 'value' => number_format(BillItem::whereHas('bill', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('bill_date', [$startDate, $endDate]);
                    })->sum('quantity')), 'color' => 'indigo'],
                    ['label' => 'Total Profit', 'value' => '₹' . number_format($data->sum('total_profit'), 2), 'color' => 'emerald'],
                ];
                break;

            case 'gst':
                $query = Bill::whereBetween('bill_date', [$startDate, $endDate]);
                $data = $query->latest()->paginate(20);
                $totalGst = $query->sum('gst_amount');
                $stats = [
                    ['label' => 'Total Output GST', 'value' => '₹' . number_format($totalGst, 2), 'color' => 'emerald'],
                    ['label' => 'CGST (9%)', 'value' => '₹' . number_format($totalGst / 2, 2), 'color' => 'blue'],
                    ['label' => 'SGST (9%)', 'value' => '₹' . number_format($totalGst / 2, 2), 'color' => 'indigo'],
                ];
                break;
        }

        return view('tenant.reports.index', compact('type', 'startDate', 'endDate', 'data', 'stats'));
    }

    public function download(Request $request)
    {
        $type = $request->get('type', 'invoice');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $filename = "report_{$type}_{$startDate}_to_{$endDate}.csv";

        return response()->streamDownload(function () use ($type, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            
            if ($type === 'invoice' || $type === 'gst') {
                fputcsv($handle, ['Invoice No', 'Date', 'Customer', 'Subtotal', 'Discount', 'GST Amount', 'Grand Total', 'Status']);
                $bills = Bill::with('customer')->whereBetween('bill_date', [$startDate, $endDate])->get();
                foreach ($bills as $bill) {
                    fputcsv($handle, [
                        $bill->invoice_no,
                        $bill->bill_date,
                        $bill->customer->name ?? 'N/A',
                        $bill->subtotal,
                        $bill->discount_amount,
                        $bill->gst_amount,
                        $bill->grand_total,
                        $bill->status
                    ]);
                }
            } elseif ($type === 'purchase') {
                fputcsv($handle, ['Invoice No', 'Date', 'Vendor', 'Total Amount', 'Status']);
                $purchases = Purchase::with('vendor')->whereBetween('invoice_date', [$startDate, $endDate])->get();
                foreach ($purchases as $purchase) {
                    fputcsv($handle, [
                        $purchase->invoice_no,
                        $purchase->invoice_date,
                        $purchase->vendor->name ?? 'N/A',
                        $purchase->total_amount,
                        $purchase->status
                    ]);
                }
            } elseif ($type === 'product') {
                fputcsv($handle, ['Product Name', 'Type', 'Total Qty Sold', 'Total Sales', 'Total Profit']);
                $data = BillItem::select(
                        'bill_items.product_name', 
                        'bill_items.product_type', 
                        DB::raw('SUM(bill_items.quantity) as total_qty'), 
                        DB::raw('SUM(bill_items.total) as total_sales'),
                        DB::raw('SUM(bill_items.total - (COALESCE(categories.dealer_price, 0) * bill_items.quantity)) as total_profit')
                    )
                    ->leftJoin('categories', 'bill_items.category_id', '=', 'categories.id')
                    ->whereHas('bill', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('bill_date', [$startDate, $endDate]);
                    })
                    ->groupBy('bill_items.product_name', 'bill_items.product_type')
                    ->orderBy('total_qty', 'desc')
                    ->get();

                foreach ($data as $item) {
                    fputcsv($handle, [
                        $item->product_name,
                        $item->product_type,
                        $item->total_qty,
                        $item->total_sales,
                        $item->total_profit
                    ]);
                }
            } else {
                fputcsv($handle, ['Notice']);
                fputcsv($handle, ['CSV export for this report type is under development.']);
            }
            
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
