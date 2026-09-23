<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ServiceClaim;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = ServiceClaim::latest('claim_date');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('claim_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $services = $query->paginate(15)->withQueryString();

        // Calculate stats
        $totalClaims = ServiceClaim::count();
        $resolvedCount = ServiceClaim::where('status', 'Resolved')->count();
        $inProgressCount = ServiceClaim::whereIn('status', ['In Progress', 'Pending Parts'])->count();
        $resolutionRate = $totalClaims > 0 ? round(($resolvedCount / $totalClaims) * 100) : 0;

        $stats = [
            'total' => $totalClaims,
            'resolved' => $resolvedCount,
            'in_progress' => $inProgressCount,
            'rate' => $resolutionRate
        ];

        return view('tenant.service.index', compact('services', 'stats', 'search', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'claim_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'issue_description' => 'required|string',
            'status' => 'required|string',
            'claim_id' => 'nullable|string|max:255',
        ]);

        $claimId = $request->input('claim_id');
        if (empty($claimId)) {
            $lastClaim = ServiceClaim::orderBy('id', 'desc')->first();
            $nextId = $lastClaim ? $lastClaim->id + 1 : 1;
            $claimId = 'CLM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        ServiceClaim::create([
            'claim_id' => $claimId,
            'claim_date' => $request->input('claim_date'),
            'customer_name' => $request->input('customer_name'),
            'customer_phone' => $request->input('customer_phone'),
            'product_name' => $request->input('product_name'),
            'issue_description' => $request->input('issue_description'),
            'status' => $request->input('status'),
            'resolution_time' => $request->input('resolution_time'),
        ]);

        return redirect()->route('tenant.service.index')->with('success', 'Service claim logged successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $service = ServiceClaim::findOrFail($id);
        $service->update([
            'status' => $request->input('status'),
            'resolution_time' => $request->input('status') === 'Resolved' ? ($service->resolution_time ?: '1 day') : null
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $service = ServiceClaim::findOrFail($id);
        $service->delete();

        return redirect()->route('tenant.service.index')->with('success', 'Service claim deleted.');
    }
}
