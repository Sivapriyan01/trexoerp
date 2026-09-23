<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\ManufacturingProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturingApiController extends Controller
{
    // GET /api/v1/manufacturing
    public function index(Request $request)
    {
        $query = Production::with(['logs'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $jobs = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $jobs->items(),
            'meta'    => ['current_page' => $jobs->currentPage(), 'last_page' => $jobs->lastPage(), 'total' => $jobs->total()],
        ]);
    }

    // POST /api/v1/manufacturing
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_name'    => 'required|string|max:255',
            'product_id'  => 'nullable|exists:categories,id',
            'quantity'    => 'required|numeric|min:1',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'notes'       => 'nullable|string',
            'processes'   => 'nullable|array',
        ]);

        $job = Production::create([
            'job_name'   => $data['job_name'],
            'product_id' => $data['product_id'] ?? null,
            'quantity'   => $data['quantity'],
            'start_date' => $data['start_date'] ?? today(),
            'due_date'   => $data['due_date'] ?? null,
            'notes'      => $data['notes'] ?? null,
            'status'     => 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $job, 'message' => 'Production job created.'], 201);
    }

    // GET /api/v1/manufacturing/{id}
    public function show($id)
    {
        $job = Production::with(['logs'])->find($id);

        if (!$job) {
            return response()->json(['success' => false, 'message' => 'Job not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $job]);
    }

    // PUT /api/v1/manufacturing/{id}
    public function update(Request $request, $id)
    {
        $job = Production::find($id);

        if (!$job) {
            return response()->json(['success' => false, 'message' => 'Job not found.'], 404);
        }

        $data = $request->validate([
            'job_name'  => 'sometimes|string|max:255',
            'quantity'  => 'sometimes|numeric|min:1',
            'due_date'  => 'sometimes|nullable|date',
            'notes'     => 'sometimes|nullable|string',
        ]);

        $job->update($data);

        return response()->json(['success' => true, 'data' => $job]);
    }

    // POST /api/v1/manufacturing/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $job = Production::find($id);

        if (!$job) {
            return response()->json(['success' => false, 'message' => 'Job not found.'], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,on_hold,completed,cancelled',
            'notes'  => 'nullable|string',
        ]);

        $job->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $job, 'message' => 'Status updated.']);
    }

    // DELETE /api/v1/manufacturing/{id}
    public function destroy($id)
    {
        $job = Production::find($id);

        if (!$job) {
            return response()->json(['success' => false, 'message' => 'Job not found.'], 404);
        }

        $job->delete();

        return response()->json(['success' => true, 'message' => 'Job deleted.']);
    }

    // GET /api/v1/manufacturing/processes
    public function processes()
    {
        $processes = ManufacturingProcess::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $processes]);
    }

    // GET /api/v1/manufacturing/summary
    public function summary()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total'       => Production::count(),
                'pending'     => Production::where('status', 'pending')->count(),
                'in_progress' => Production::where('status', 'in_progress')->count(),
                'completed'   => Production::where('status', 'completed')->count(),
                'on_hold'     => Production::where('status', 'on_hold')->count(),
            ],
        ]);
    }
}
