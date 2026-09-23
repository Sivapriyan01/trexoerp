<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceClaim;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
    // GET /api/v1/services
    public function index(Request $request)
    {
        $query = ServiceClaim::with('customer')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('issue', 'ilike', '%' . $request->q . '%');
        }

        $records = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $records->items(),
            'meta'    => ['current_page' => $records->currentPage(), 'last_page' => $records->lastPage(), 'total' => $records->total()],
        ]);
    }

    // POST /api/v1/services
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id'  => 'nullable|exists:categories,id',
            'issue'       => 'required|string|max:1000',
            'notes'       => 'nullable|string',
            'priority'    => 'nullable|in:low,medium,high',
        ]);

        $data['status']     = 'open';
        $data['created_by'] = auth()->id();

        $claim = ServiceClaim::create($data);

        return response()->json(['success' => true, 'data' => $claim, 'message' => 'Service claim created.'], 201);
    }

    // GET /api/v1/services/{id}
    public function show($id)
    {
        $claim = ServiceClaim::with('customer')->find($id);

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Service claim not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $claim]);
    }

    // POST /api/v1/services/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $claim = ServiceClaim::find($id);

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Service claim not found.'], 404);
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'notes'  => 'nullable|string',
        ]);

        $claim->update([
            'status'       => $request->status,
            'resolution'   => $request->notes ?? $claim->resolution,
            'resolved_at'  => in_array($request->status, ['resolved', 'closed']) ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $claim, 'message' => 'Status updated.']);
    }

    // DELETE /api/v1/services/{id}
    public function destroy($id)
    {
        $claim = ServiceClaim::find($id);

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Service claim not found.'], 404);
        }

        $claim->delete();

        return response()->json(['success' => true, 'message' => 'Service claim deleted.']);
    }
}
