<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\RmaRequest;
use Illuminate\Http\Request;

class ReturnApiController extends Controller
{
    /**
     * Get a list of returns (RMAs)
     */
    public function index(Request $request)
    {
        $query = RmaRequest::with(['bill.customer', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $returns = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $returns
        ]);
    }

    /**
     * Get details of a specific return (RMA)
     */
    public function show($id)
    {
        $rma = RmaRequest::with(['bill.customer', 'items.billItem', 'items.category', 'customer'])->find($id);

        if (!$rma) {
            return response()->json([
                'success' => false,
                'message' => 'Return/RMA not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rma
        ]);
    }

    /**
     * Create a new return (RMA)
     */
    public function store(Request $request)
    {
        $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'type' => 'required|string',
            'items' => 'required|array',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $bill = \App\Models\Bill::findOrFail($request->bill_id);

        $selectedCount = 0;
        foreach ($request->items as $itemId => $itemData) {
            if (!empty($itemData['selected']) && $itemData['selected'] == 1) {
                $selectedCount++;
            }
        }

        if ($selectedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'You must select at least one item to return or exchange.'
            ], 422);
        }

        $rmaNumber = 'RMA-' . date('Ymd') . '-' . rand(1000, 9999);

        $rma = RmaRequest::create([
            'rma_number' => $rmaNumber,
            'bill_id' => $bill->id,
            'customer_id' => $bill->customer_id,
            'type' => $request->type,
            'status' => 'pending',
            'reason' => $request->reason,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->items as $itemId => $itemData) {
            if (!empty($itemData['selected']) && $itemData['selected'] == 1) {
                $billItem = \App\Models\BillItem::findOrFail($itemId);

                \App\Models\RmaRequestItem::create([
                    'rma_request_id' => $rma->id,
                    'bill_item_id' => $billItem->id,
                    'category_id' => $billItem->category_id,
                    'quantity' => $itemData['quantity'],
                    'condition' => $itemData['condition'] ?? 'good',
                ]);
            }
        }

        $rma->load(['items.billItem']);

        return response()->json([
            'success' => true,
            'message' => 'RMA Request Created Successfully',
            'data' => $rma
        ], 201);
    }
}
