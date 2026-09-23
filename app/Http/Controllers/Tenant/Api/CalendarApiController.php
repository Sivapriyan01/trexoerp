<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarApiController extends Controller
{
    // GET /api/v1/calendar
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->endOfMonth()->toDateString());

        // Bills due in range
        $bills = Bill::with('customer')
            ->where('payment_mode', 'credit')
            ->where('status', 'completed')
            ->whereRaw('(grand_total - paid_amount) > 0')
            ->whereBetween('bill_date', [$from, $to])
            ->get(['id', 'invoice_no', 'customer_name', 'grand_total', 'paid_amount', 'bill_date'])
            ->map(fn($b) => [
                'type'   => 'due_invoice',
                'date'   => $b->bill_date,
                'title'  => "Due: {$b->invoice_no} — {$b->customer_name}",
                'amount' => round($b->grand_total - $b->paid_amount, 2),
                'ref_id' => $b->id,
            ]);

        // Birthdays in range
        $monthRange = collect();
        $start = \Carbon\Carbon::parse($from);
        $end   = \Carbon\Carbon::parse($to);

        $birthdays = Customer::whereNotNull('dob')
            ->get(['id', 'name', 'phone', 'dob'])
            ->filter(function ($c) use ($start, $end) {
                $dob = \Carbon\Carbon::parse($c->dob)->setYear($start->year);
                return $dob->between($start, $end);
            })
            ->map(fn($c) => [
                'type'   => 'birthday',
                'date'   => \Carbon\Carbon::parse($c->dob)->setYear($start->year)->toDateString(),
                'title'  => "🎂 {$c->name}'s Birthday",
                'ref_id' => $c->id,
            ]);

        // Anniversaries in range
        $anniversaries = Customer::whereNotNull('anniversary')
            ->get(['id', 'name', 'phone', 'anniversary'])
            ->filter(function ($c) use ($start, $end) {
                $ann = \Carbon\Carbon::parse($c->anniversary)->setYear($start->year);
                return $ann->between($start, $end);
            })
            ->map(fn($c) => [
                'type'   => 'anniversary',
                'date'   => \Carbon\Carbon::parse($c->anniversary)->setYear($start->year)->toDateString(),
                'title'  => "💍 {$c->name}'s Anniversary",
                'ref_id' => $c->id,
            ]);

        $events = $bills->merge($birthdays)->merge($anniversaries)->sortBy('date')->values();

        return response()->json([
            'success' => true,
            'from'    => $from,
            'to'      => $to,
            'data'    => $events,
        ]);
    }
}
