<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

class DeliveryApiController extends Controller
{
    // GET /api/v1/deliveries
    public function index(Request $request)
    {
        $query = Bill::where('bill_type', 'pre-order')
            ->with('customer')
            ->latest();

        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('expected_delivery_date', $request->date);
        }

        $bills = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $bills->items(),
            'meta'    => ['current_page' => $bills->currentPage(), 'last_page' => $bills->lastPage(), 'total' => $bills->total()],
        ]);
    }

    // POST /api/v1/deliveries/{bill}/status
    public function updateStatus(Request $request, $bill)
    {
        $record = Bill::find($bill);

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,out_for_delivery,delivered,failed',
            'notes'  => 'nullable|string',
        ]);

        $record->update([
            'delivery_status' => $request->status,
            'delivery_notes'  => $request->notes ?? $record->delivery_notes,
        ]);

        return response()->json(['success' => true, 'data' => $record, 'message' => 'Delivery status updated.']);
    }
}
