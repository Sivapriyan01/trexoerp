<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Instalment;
use App\Models\InstalmentSchedule;
use App\Models\InstalmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstalmentApiController extends Controller
{
    // GET /api/v1/instalments
    public function index(Request $request)
    {
        $query = Instalment::with(['customer', 'schedules'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $records = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $records->items(),
            'meta'    => ['current_page' => $records->currentPage(), 'last_page' => $records->lastPage(), 'total' => $records->total()],
        ]);
    }

    // GET /api/v1/instalments/{id}
    public function show($id)
    {
        $instalment = Instalment::with(['customer', 'schedules', 'payments'])->find($id);

        if (!$instalment) {
            return response()->json(['success' => false, 'message' => 'Instalment not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $instalment]);
    }

    // POST /api/v1/instalments/pay
    public function pay(Request $request)
    {
        $data = $request->validate([
            'instalment_id' => 'required|exists:instalments,id',
            'schedule_id'   => 'nullable|exists:instalment_schedules,id',
            'amount'        => 'required|numeric|min:1',
            'payment_mode'  => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, &$payment) {
            $instalment = Instalment::find($data['instalment_id']);

            $payment = InstalmentPayment::create([
                'instalment_id'         => $data['instalment_id'],
                'instalment_schedule_id' => $data['schedule_id'] ?? null,
                'amount'                => $data['amount'],
                'payment_mode'          => $data['payment_mode'] ?? 'cash',
                'notes'                 => $data['notes'] ?? null,
                'paid_at'               => now(),
            ]);

            $instalment->increment('paid_amount', $data['amount']);

            if ($instalment->fresh()->paid_amount >= $instalment->total_amount) {
                $instalment->update(['status' => 'paid']);
            } else {
                $instalment->update(['status' => 'partial']);
            }
        });

        return response()->json(['success' => true, 'data' => $payment, 'message' => 'Payment recorded.'], 201);
    }

    // GET /api/v1/instalments/due-today
    public function dueToday()
    {
        $due = InstalmentSchedule::with('instalment.customer')
            ->whereDate('due_date', today())
            ->where('status', '!=', 'paid')
            ->get();

        return response()->json(['success' => true, 'data' => $due]);
    }
}
