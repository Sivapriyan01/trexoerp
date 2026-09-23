<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Bill;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    // GET /api/v1/customers
    public function index(Request $request)
    {
        $query = Customer::query()->latest();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->q . '%')
                  ->orWhere('phone', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'ilike', '%' . $request->q . '%');
            });
        }

        $customers = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $customers->items(),
            'meta'    => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
            ],
        ]);
    }

    // POST /api/v1/customers
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string',
            'gstin'            => 'nullable|string|max:20',
            'anniversary_date' => 'nullable|date',
        ]);

        $customer = Customer::create($data);

        return response()->json(['success' => true, 'data' => $customer], 201);
    }

    // GET /api/v1/customers/{id}
    public function show($id)
    {
        $customer = Customer::with('activeMembership.plan')->find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $totalSpent = Bill::where('customer_id', $id)->where('status', 'completed')->sum('grand_total');

        return response()->json([
            'success' => true,
            'data'    => array_merge($customer->toArray(), ['total_spent' => round($totalSpent, 2)]),
        ]);
    }

    // PUT /api/v1/customers/{id}
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'phone'            => 'sometimes|nullable|string|max:20',
            'email'            => 'sometimes|nullable|email|max:255',
            'address'          => 'sometimes|nullable|string',
            'gstin'            => 'sometimes|nullable|string|max:20',
            'anniversary_date' => 'sometimes|nullable|date',
        ]);

        $customer->update($data);

        return response()->json(['success' => true, 'data' => $customer]);
    }

    // DELETE /api/v1/customers/{id}
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }

    // GET /api/v1/customers/{id}/bills
    public function bills($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $bills = Bill::with('items')
            ->where('customer_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $bills->items(),
            'meta'    => ['total' => $bills->total(), 'current_page' => $bills->currentPage(), 'last_page' => $bills->lastPage()],
        ]);
    }

    // GET /api/v1/customers/{id}/ledger
    public function ledger($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $bills = Bill::where('customer_id', $id)->latest()->get(['id', 'invoice_no', 'grand_total', 'paid_amount', 'bill_date', 'status']);

        $totalDue  = $bills->sum(fn($b) => $b->grand_total - $b->paid_amount);

        return response()->json([
            'success'   => true,
            'data'      => $bills,
            'total_due' => round($totalDue, 2),
        ]);
    }
}
