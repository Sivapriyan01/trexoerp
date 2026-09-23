<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        // Fetch bills marked for delivery
        $deliveries = Bill::with('customer')
            ->where('status', 'Delivery')
            ->latest()
            ->paginate(20);

        return view('tenant.delivery.index', compact('deliveries'));
    }

    public function updateStatus(Request $request, Bill $bill)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $bill->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery status updated successfully.',
            'status' => $bill->status
        ]);
    }
}
