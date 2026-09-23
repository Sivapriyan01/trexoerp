<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\CustomerMembership;
use Illuminate\Http\Request;

class MembershipApiController extends Controller
{
    // GET /api/v1/membership/plans
    public function plans()
    {
        $plans = MembershipPlan::orderBy('price')->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    // POST /api/v1/membership/plans
    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'benefits'         => 'nullable|string',
        ]);

        $plan = MembershipPlan::create($data);
        return response()->json(['success' => true, 'data' => $plan], 201);
    }

    // PUT /api/v1/membership/plans/{id}
    public function updatePlan(Request $request, $id)
    {
        $plan = MembershipPlan::find($id);
        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan not found.'], 404);
        }

        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'price'            => 'sometimes|numeric|min:0',
            'duration_days'    => 'sometimes|integer|min:1',
            'discount_percent' => 'sometimes|numeric|min:0|max:100',
            'benefits'         => 'sometimes|nullable|string',
        ]);

        $plan->update($data);
        return response()->json(['success' => true, 'data' => $plan]);
    }

    // DELETE /api/v1/membership/plans/{id}
    public function destroyPlan($id)
    {
        $plan = MembershipPlan::find($id);
        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan not found.'], 404);
        }
        $plan->delete();
        return response()->json(['success' => true, 'message' => 'Plan deleted.']);
    }

    // GET /api/v1/membership
    public function index(Request $request)
    {
        $query = CustomerMembership::with(['customer', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $memberships = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $memberships->items(),
            'meta'    => ['current_page' => $memberships->currentPage(), 'last_page' => $memberships->lastPage(), 'total' => $memberships->total()],
        ]);
    }

    // POST /api/v1/membership/assign
    public function assign(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_id'     => 'required|exists:membership_plans,id',
            'start_date'  => 'nullable|date',
        ]);

        $plan      = MembershipPlan::find($data['plan_id']);
        $startDate = $data['start_date'] ?? now();
        $endDate   = date('Y-m-d', strtotime($startDate . ' +' . $plan->duration_days . ' days'));

        $membership = CustomerMembership::create([
            'customer_id' => $data['customer_id'],
            'plan_id'     => $data['plan_id'],
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'status'      => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $membership->load('plan'), 'message' => 'Membership assigned.'], 201);
    }

    // PUT /api/v1/membership/{id}
    public function update(Request $request, $id)
    {
        $membership = CustomerMembership::find($id);
        if (!$membership) {
            return response()->json(['success' => false, 'message' => 'Membership not found.'], 404);
        }

        $data = $request->validate([
            'status'    => 'sometimes|in:active,expired,cancelled',
            'end_date'  => 'sometimes|date',
        ]);

        $membership->update($data);
        return response()->json(['success' => true, 'data' => $membership]);
    }
}
