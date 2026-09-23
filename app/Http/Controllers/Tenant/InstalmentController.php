<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instalment;
use App\Models\InstalmentSchedule;
use App\Models\InstalmentPayment;
use Illuminate\Support\Facades\DB;

class InstalmentController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_pending' => Instalment::where('status', 'pending')->count(),
            'total_completed' => Instalment::where('status', 'completed')->count(),
            'total_overdue' => Instalment::where('status', 'overdue')->count(),
            'total_due_amount' => Instalment::sum('due_amount'),
        ];

        $query = Instalment::with(['customer', 'bill']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $instalments = $query->latest()->paginate(15)->withQueryString();

        return view('tenant.instalments.index', compact('instalments', 'stats'));
    }

    public function show(Instalment $instalment)
    {
        $instalment->load(['customer', 'bill', 'schedules', 'payments']);
        return response()->json($instalment);
    }

    public function pay(Request $request)
    {
        if ($request->type === 'create_plan') {
            return $this->createPlan($request);
        }

        $request->validate([
            'schedule_id' => 'required|exists:instalment_schedules,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $schedule = InstalmentSchedule::findOrFail($request->schedule_id);
            $instalment = $schedule->instalment;

            // 1. Create Payment Record
            InstalmentPayment::create([
                'instalment_id' => $instalment->id,
                'schedule_id' => $schedule->id,
                'amount' => $request->amount,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'remark' => $request->remark
            ]);

            // 2. Update Schedule
            $schedule->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            // 3. Update Main Instalment
            $instalment->paid_amount += $request->amount;
            $instalment->due_amount -= $request->amount;
            
            if ($instalment->due_amount <= 0) {
                $instalment->status = 'completed';
            }

            // Update Next Due Date
            $nextSchedule = $instalment->schedules()
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();
            
            $instalment->next_due_date = $nextSchedule ? $nextSchedule->due_date : null;
            $instalment->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    protected function createPlan(Request $request)
    {
        DB::beginTransaction();
        try {
            $bill = \App\Models\Bill::findOrFail($request->bill_id);
            
            $instalment = Instalment::create([
                'bill_id' => $bill->id,
                'customer_id' => $bill->customer_id ?? \App\Models\Customer::where('phone', $bill->customer_phone)->first()->id,
                'total_amount' => $request->total_amount,
                'paid_amount' => 0,
                'due_amount' => $request->total_amount,
                'status' => 'pending',
                'next_due_date' => $request->start_date
            ]);

            $months = (int) $request->months;
            $emiAmount = $request->total_amount / $months;

            for ($i = 1; $i <= $months; $i++) {
                InstalmentSchedule::create([
                    'instalment_id' => $instalment->id,
                    'instalment_no' => $i,
                    'amount' => $emiAmount,
                    'due_date' => date('Y-m-d', strtotime("+".($i-1)." months", strtotime($request->start_date))),
                    'status' => 'pending'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
