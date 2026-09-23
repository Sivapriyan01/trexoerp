<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Http\Request;

class DueInvoiceApiController extends Controller
{
    // GET /api/v1/due-invoices
    public function index(Request $request)
    {
        $query = Bill::where('payment_mode', 'credit')
            ->where('status', 'completed')
            ->whereRaw('(grand_total - paid_amount) > 0')
            ->with('customer')
            ->latest('bill_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('overdue')) {
            $query->where('bill_date', '<', now()->subDays(30));
        }

        $bills = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $bills->items(),
            'meta'    => [
                'current_page'       => $bills->currentPage(),
                'last_page'          => $bills->lastPage(),
                'total'              => $bills->total(),
                'total_outstanding'  => round(
                    Bill::where('payment_mode', 'credit')
                        ->where('status', 'completed')
                        ->whereRaw('(grand_total - paid_amount) > 0')
                        ->sum(\Illuminate\Support\Facades\DB::raw('grand_total - paid_amount')),
                    2
                ),
            ],
        ]);
    }

    // POST /api/v1/due-invoices/{id}/pay
    public function pay(Request $request, $id)
    {
        $bill = Bill::find($id);

        if (!$bill) {
            return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
        }

        $data = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'payment_mode' => 'nullable|string',
        ]);

        $newPaid = $bill->paid_amount + $data['amount'];
        $bill->update([
            'paid_amount'  => min($newPaid, $bill->grand_total),
            'payment_mode' => $data['payment_mode'] ?? $bill->payment_mode,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Payment recorded.',
            'paid_amount' => $bill->fresh()->paid_amount,
            'balance'     => max(0, $bill->grand_total - $bill->fresh()->paid_amount),
        ]);
    }

    // GET /api/v1/due-invoices/summary
    public function summary()
    {
        $query = Bill::where('payment_mode', 'credit')
            ->where('status', 'completed')
            ->whereRaw('(grand_total - paid_amount) > 0');

        $total        = $query->count();
        $outstanding  = round($query->sum(\Illuminate\Support\Facades\DB::raw('grand_total - paid_amount')), 2);
        $overdue      = (clone $query)->where('bill_date', '<', now()->subDays(30))->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_due_invoices' => $total,
                'total_outstanding'  => $outstanding,
                'overdue_count'      => $overdue,
            ],
        ]);
    }
}
