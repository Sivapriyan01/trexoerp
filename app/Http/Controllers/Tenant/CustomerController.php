<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%")
                  ->orWhere('state', 'ilike', "%{$search}%");
            });
        }
        if ($request->filled('filter') && $request->filter === 'with_points') {
            $query->where('points', '>', 0);
        }
        $customers = $query->get();
        return view('tenant.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('tenant.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string|size:15',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'anniversary_date' => 'nullable|date',
            'anniversary_reminder_enabled' => 'nullable|boolean',
        ]);

        try {
            Customer::create($validated);
            return redirect()->route('tenant.customers.index')->with('success', 'Customer added successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['bills' => function ($q) {
            $q->latest()->take(20);
        }]);

        $totalSpent   = $customer->bills->where('bill_type', '!=', 'credit_note')->sum('grand_total');
        $averageBill  = $customer->bills->where('bill_type', '!=', 'credit_note')->count() > 0 ? $totalSpent / $customer->bills->where('bill_type', '!=', 'credit_note')->count() : 0;
        $lastBill     = $customer->bills->first();
        
        $totalRefunded = $customer->bills->where('bill_type', 'credit_note')->sum('grand_total');

        return view('tenant.customers.show', compact('customer', 'totalSpent', 'averageBill', 'lastBill', 'totalRefunded'));
    }

    public function edit(Customer $customer)
    {
        return view('tenant.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,'.$customer->id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string|size:15',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'anniversary_date' => 'nullable|date',
            'anniversary_reminder_enabled' => 'nullable|boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('tenant.customers.index')->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('tenant.customers.index')->with('success', 'Customer deleted successfully');
    }
}
