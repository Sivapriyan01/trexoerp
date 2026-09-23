<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;

class VendorApiController extends Controller
{
    // GET /api/v1/vendors
    public function index(Request $request)
    {
        $query = Supplier::query()->latest();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->q . '%')
                  ->orWhere('phone', 'like', '%' . $request->q . '%');
            });
        }

        $suppliers = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $suppliers->items(),
            'meta'    => ['current_page' => $suppliers->currentPage(), 'last_page' => $suppliers->lastPage(), 'total' => $suppliers->total()],
        ]);
    }

    // POST /api/v1/vendors
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:20',
        ]);

        $supplier = Supplier::create($data);

        return response()->json(['success' => true, 'data' => $supplier], 201);
    }

    // GET /api/v1/vendors/{id}
    public function show($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $totalPurchased = Purchase::where('vendor_id', $id)->sum('total_amount');

        return response()->json([
            'success' => true,
            'data'    => array_merge($supplier->toArray(), ['total_purchased' => round($totalPurchased, 2)]),
        ]);
    }

    // PUT /api/v1/vendors/{id}
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $data = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'sometimes|nullable|string|max:20',
            'email'   => 'sometimes|nullable|email|max:255',
            'address' => 'sometimes|nullable|string',
            'gstin'   => 'sometimes|nullable|string|max:20',
        ]);

        $supplier->update($data);

        return response()->json(['success' => true, 'data' => $supplier]);
    }

    // DELETE /api/v1/vendors/{id}
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $supplier->delete();

        return response()->json(['success' => true, 'message' => 'Vendor deleted.']);
    }

    // GET /api/v1/vendors/{id}/purchases
    public function purchases($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $purchases = Purchase::with('items')
            ->where('vendor_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $purchases->items(),
            'meta'    => ['total' => $purchases->total(), 'current_page' => $purchases->currentPage(), 'last_page' => $purchases->lastPage()],
        ]);
    }
}
