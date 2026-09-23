<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

class WebsiteOrdersController extends Controller
{
    /**
     * Display a listing of website orders
     */
    public function index(Request $request)
    {
        $query = Bill::with(['customer', 'items'])
            ->where(function ($q) {
                $q->where('bill_type', 'website_order')
                  ->orWhere('remarks', 'like', '%WEB-%');
            });

        // Optional status filter
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('status', $status);
            }
        }

        // Search query
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Summary metrics
        $allWebsiteBills = Bill::where(function ($q) {
            $q->where('bill_type', 'website_order')
              ->orWhere('remarks', 'like', '%WEB-%');
        });

        $totalOrders = (clone $allWebsiteBills)->count();
        $totalRevenue = (clone $allWebsiteBills)->sum('grand_total');
        $newOrdersCount = (clone $allWebsiteBills)->whereIn('status', ['NEW', 'Pending', 'Draft', null])->count();
        $deliveredCount = (clone $allWebsiteBills)->where('status', 'DELIVERED')->count();

        return view('tenant.website_orders.index', compact(
            'orders',
            'totalOrders',
            'totalRevenue',
            'newOrdersCount',
            'deliveredCount'
        ));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:NEW,CONFIRMED,SHIPPED,DELIVERED,CANCELLED',
        ]);

        $bill->update([
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$bill->invoice_no} status updated to {$bill->status}.",
                'status' => $bill->status,
            ]);
        }

        return back()->with('success', "Order #{$bill->invoice_no} status updated to {$bill->status}.");
    }
}
