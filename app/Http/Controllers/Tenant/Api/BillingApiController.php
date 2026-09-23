<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingApiController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/v1/billing
    // List bills with filters (date, status, payment mode, customer, search)
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Bill::with(['customer', 'items'])
            ->latest();

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('bill_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('bill_date', '<=', $request->to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment mode
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', strtolower($request->payment_mode));
        }

        // Filter by customer phone
        if ($request->filled('phone')) {
            $query->where('customer_phone', $request->phone);
        }

        // Filter by invoice number search
        if ($request->filled('q')) {
            $query->where('invoice_no', 'ilike', '%' . $request->q . '%');
        }

        $bills = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $bills->items(),
            'meta'    => [
                'current_page' => $bills->currentPage(),
                'last_page'    => $bills->lastPage(),
                'per_page'     => $bills->perPage(),
                'total'        => $bills->total(),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/billing/quick-data
    // Get products, categories, and settings needed for Quick Bill POS UI
    // -------------------------------------------------------------------------
    public function quickData()
    {
        $products = Category::where('is_active', true)->get();
        $categories = Category::select('product_type')
                              ->distinct()
                              ->whereNotNull('product_type')
                              ->pluck('product_type');
        
        $settings = [
            'gst_enabled'      => \App\Models\Setting::get('gst_enabled', '1') == '1',
            'default_gst'      => \App\Models\Setting::get('gst_default_percent', 0),
            'gst_app_type'     => \App\Models\Setting::get('gst_app_type', 'per_product'),
            'gst_calc_type'    => \App\Models\Setting::get('gst_calc_type', 'inclusive'),
            'inv_print_pos'    => \App\Models\Setting::get('inv_print_pos', '1') == '1',
            'inv_print_a4'     => \App\Models\Setting::get('inv_print_a4', '0') == '1',
            'inv_auto_print'   => \App\Models\Setting::get('inv_auto_print', '0') == '1',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'products'   => $products,
                'categories' => $categories,
                'settings'   => $settings,
            ]
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/billing/{id}
    // Get a single bill with all items
    // -------------------------------------------------------------------------
    public function show($id)
    {
        $bill = Bill::with(['items.category', 'customer'])->find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $bill,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/billing
    // Create a new bill (generate invoice)
    // -------------------------------------------------------------------------
    public function store(Request $request)
    {
        // Normalize items: support both 'id'/'category_id' and 'qty'/'quantity'
        if ($request->has('items')) {
            $normalized = array_map(function ($item) {
                if (!isset($item['id']) && isset($item['category_id'])) {
                    $item['id'] = $item['category_id'];
                }
                if (!isset($item['qty']) && isset($item['quantity'])) {
                    $item['qty'] = $item['quantity'];
                }
                if (!isset($item['price']) && isset($item['mrp'])) {
                    $item['price'] = $item['mrp'];
                }
                return $item;
            }, $request->input('items'));
            $request->merge(['items' => $normalized]);
        }

        $data = $request->validate([
            'customer_phone'   => 'nullable|string',
            'customer_name'    => 'nullable|string',
            'customer_address' => 'nullable|string',
            'customer_gstin'   => 'nullable|string',
            'customer_email'   => 'nullable|email',
            'bill_date'        => 'nullable|date',
            'bill_type'        => 'nullable|string',
            'payment_mode'     => 'required|string',
            'amount_paid'      => 'nullable|numeric',
            'subtotal'         => 'nullable|numeric',
            'discount_amount'  => 'nullable|numeric',
            'discount_percent' => 'nullable|numeric',
            'gst_amount'       => 'nullable|numeric',
            'gst_percent'      => 'nullable|numeric',
            'grand_total'      => 'nullable|numeric',
            'reminder_enabled' => 'nullable|boolean',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:categories,id',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            // Upsert customer
            $customer = null;
            if (!empty($data['customer_phone'])) {
                $customer = Customer::updateOrCreate(
                    ['phone' => $data['customer_phone']],
                    [
                        'name'    => $data['customer_name']    ?? null,
                        'address' => $data['customer_address'] ?? null,
                        'gstin'   => $data['customer_gstin']   ?? null,
                    ]
                );
                $customer->increment('bill_count');
            }

            $billType = $data['bill_type'] ?? 'billing';

            $bill = Bill::create([
                'invoice_no'       => Bill::generateInvoiceNo($billType),
                'bill_type'        => $billType,
                'customer_id'      => $customer?->id,
                'customer_phone'   => $data['customer_phone']   ?? null,
                'customer_email'   => $data['customer_email']   ?? null,
                'customer_name'    => $data['customer_name']    ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_gstin'   => $data['customer_gstin']   ?? null,
                'bill_date'        => $data['bill_date']        ?? now(),
                'subtotal'         => $data['subtotal']         ?? 0,
                'discount_amount'  => $data['discount_amount']  ?? 0,
                'discount_percent' => $data['discount_percent'] ?? 0,
                'gst_amount'       => $data['gst_amount']       ?? 0,
                'gst_percent'      => $data['gst_percent']      ?? 18,
                'grand_total'      => $data['grand_total']      ?? 0,
                'paid_amount'      => $data['amount_paid']      ?? $data['grand_total'] ?? 0,
                'payment_mode'     => strtolower($data['payment_mode']),
                'status'           => 'completed',
                'reminder_enabled' => $data['reminder_enabled'] ?? false,
                'created_by'       => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Category::find($item['id']);
                if (!$product) continue;

                BillItem::create([
                    'bill_id'      => $bill->id,
                    'category_id'  => $product->id,
                    'barcode'      => $product->barcode,
                    'product_name' => $product->product_name,
                    'brand'        => $product->brand,
                    'size'         => $product->size,
                    'mrp'          => $item['price'],
                    'quantity'     => $item['qty'],
                ]);

                \App\Models\StockLog::log($product->id, $item['qty'], 'out', $bill->id, 'bill');
                $product->decrement('stock', $item['qty']);
            }

            $bill->recalculate();

            // Award loyalty points (1 point per ₹100)
            if ($customer) {
                $points = floor($bill->grand_total / 100);
                if ($points > 0) {
                    $customer->increment('points', $points);
                }
            }

            // Credit payment: honour partial amount_paid
            if (strtolower($bill->payment_mode) === 'credit') {
                $bill->update(['paid_amount' => $data['amount_paid'] ?? 0]);
            } elseif (!isset($data['amount_paid'])) {
                $bill->update(['paid_amount' => $bill->grand_total]);
            }

            return response()->json([
                'success'     => true,
                'message'     => 'Bill created successfully.',
                'data'        => [
                    'id'          => $bill->id,
                    'invoice_no'  => $bill->invoice_no,
                    'grand_total' => $bill->grand_total,
                    'status'      => $bill->status,
                    'bill_date'   => $bill->bill_date,
                ],
            ], 201);
        });
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/billing/{id}/status
    // Update bill status (e.g. completed, cancelled, refunded)
    // -------------------------------------------------------------------------
    public function updateStatus(Request $request, $id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        $request->validate([
            'status' => 'required|string|in:completed,cancelled,refunded,draft,pending',
        ]);

        $bill->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Bill status updated.',
            'data'    => ['id' => $bill->id, 'status' => $bill->status],
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/billing/{id}
    // Cancel/delete a bill and restore stock
    // -------------------------------------------------------------------------
    public function destroy($id)
    {
        $bill = Bill::with('items.category')->find($id);

        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        DB::transaction(function () use ($bill) {
            // Restore stock for each item
            foreach ($bill->items as $item) {
                if ($item->category) {
                    $item->category->increment('stock', $item->quantity);
                    \App\Models\StockLog::log($item->category_id, $item->quantity, 'in', $bill->id, 'bill_cancel');
                }
            }

            $bill->items()->delete();
            $bill->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Bill cancelled and stock restored.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/billing/summary
    // Daily/weekly/monthly sales summary
    // -------------------------------------------------------------------------
    public function summary(Request $request)
    {
        $period = $request->get('period', 'today'); // today | week | month

        $query = Bill::where('status', 'completed');

        match ($period) {
            'today'  => $query->whereDate('bill_date', today()),
            'week'   => $query->whereBetween('bill_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'  => $query->whereMonth('bill_date', now()->month)->whereYear('bill_date', now()->year),
            default  => $query->whereDate('bill_date', today()),
        };

        $bills = $query->get();

        return response()->json([
            'success' => true,
            'period'  => $period,
            'data'    => [
                'total_bills'     => $bills->count(),
                'total_sales'     => round($bills->sum('grand_total'), 2),
                'total_tax'       => round($bills->sum('gst_amount'), 2),
                'total_discount'  => round($bills->sum('discount_amount'), 2),
                'cash_sales'      => round($bills->where('payment_mode', 'cash')->sum('grand_total'), 2),
                'card_sales'      => round($bills->where('payment_mode', 'card')->sum('grand_total'), 2),
                'upi_sales'       => round($bills->whereIn('payment_mode', ['upi', 'qr'])->sum('grand_total'), 2),
                'credit_sales'    => round($bills->where('payment_mode', 'credit')->sum('grand_total'), 2),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/billing/search-customer?phone=
    // -------------------------------------------------------------------------
    public function searchCustomer(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $customer = Customer::with('activeMembership.plan')
            ->where('phone', $request->phone)
            ->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $customer->id,
                'name'       => $customer->name,
                'phone'      => $customer->phone,
                'email'      => $customer->email,
                'address'    => $customer->address,
                'points'     => $customer->points,
                'bill_count' => $customer->bill_count,
                'membership' => $customer->activeMembership?->plan?->name,
            ],
        ]);
    }
}
