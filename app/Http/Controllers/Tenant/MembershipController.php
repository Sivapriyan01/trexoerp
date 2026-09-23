<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\CustomerMembership;
use App\Models\Customer;

class MembershipController extends Controller
{
    public function index()
    {
        $memberships = CustomerMembership::with(['customer', 'plan'])->latest()->paginate(20);
        $plans = MembershipPlan::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();

        return view('tenant.memberships.index', compact('memberships', 'plans', 'customers'));
    }

    public function plans()
    {
        $plans = MembershipPlan::latest()->get();
        return view('tenant.memberships.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        MembershipPlan::create($validated);

        return back()->with('success', 'Membership plan created successfully.');
    }

    public function updatePlan(Request $request, MembershipPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        $plan->update($validated);

        return back()->with('success', 'Membership plan updated successfully.');
    }

    public function deletePlan(MembershipPlan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Membership plan deleted.');
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'required|date',
            'payment_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
        $validated['end_date'] = \Carbon\Carbon::parse($validated['start_date'])->addDays($plan->duration_days);
        $validated['status'] = 'active';

        CustomerMembership::create($validated);

        return back()->with('success', 'Membership assigned to customer successfully.');
    }

    public function updateMembership(Request $request, CustomerMembership $membership)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,expired,cancelled',
            'payment_status' => 'required|string',
            'end_date' => 'required|date',
        ]);

        $membership->update($validated);

        return back()->with('success', 'Customer membership updated.');
    }
}
