<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::latest();
        $totalVendors = Supplier::count();
        $activeAccounts = Supplier::where('is_active', true)->count();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%")
                  ->orWhere('gstin', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%")
                  ->orWhere('state', 'ilike', "%{$search}%");
            });
        }
        if ($request->filled('filter') && $request->filter === 'active') {
            $query->where('is_active', true);
        }
        $suppliers = $query->get();
        return view('tenant.suppliers.index', compact('suppliers', 'totalVendors', 'activeAccounts'));
    }

    public function create()
    {
        return view('tenant.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin'   => ['nullable', 'string', 'size:15', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[A-Z0-9]{1}[0-9A-Z]{1}$/i'],
            'city'    => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
        ], [
            'gstin.regex' => 'Please enter a valid 15-character GSTIN number (e.g. 22AAAAA0000A1Z5).',
            'gstin.size'  => 'GSTIN must be exactly 15 characters.',
        ]);

        $supplier = Supplier::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Vendor added successfully', 'supplier' => $supplier]);
        }

        return redirect()->route('tenant.suppliers.index')->with('success', 'Vendor added successfully');
    }

    public function show(Supplier $supplier)
    {
        return view('tenant.suppliers.edit', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('tenant.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'address'   => 'nullable|string',
            'gstin'     => ['nullable', 'string', 'size:15', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[A-Z0-9]{1}[0-9A-Z]{1}$/i'],
            'city'      => 'nullable|string|max:255',
            'state'     => 'nullable|string|max:255',
            'pincode'   => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ], [
            'gstin.regex' => 'Please enter a valid 15-character GSTIN number (e.g. 22AAAAA0000A1Z5).',
            'gstin.size'  => 'GSTIN must be exactly 15 characters.',
        ]);

        $supplier->update($validated);

        return redirect()->route('tenant.suppliers.index')->with('success', 'Vendor updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('tenant.suppliers.index')->with('success', 'Vendor deleted successfully');
    }
}
