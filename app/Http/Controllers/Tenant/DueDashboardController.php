<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstalmentSchedule;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DueDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $soon = Carbon::today()->addDays(7);

        // 1. Calculate Stats
        $stats = [
            'total_due' => InstalmentSchedule::where('status', '!=', 'paid')->sum('amount'),
            'overdue' => InstalmentSchedule::where('status', '!=', 'paid')
                ->where('due_date', '<', $today)
                ->sum('amount'),
            'due_today' => InstalmentSchedule::where('status', '!=', 'paid')
                ->where('due_date', $today)
                ->sum('amount'),
            'due_soon' => InstalmentSchedule::where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today->copy()->addDay(), $soon])
                ->sum('amount'),
            'total_paid' => InstalmentSchedule::where('status', 'paid')->sum('amount'),
        ];

        // 2. Query due schedules
        $query = InstalmentSchedule::with(['instalment.customer', 'instalment.bill']);
        
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'pending':
                    $query->where('status', '!=', 'paid');
                    break;
                case 'overdue':
                    $query->where('status', '!=', 'paid')->where('due_date', '<', $today);
                    break;
                case 'due_today':
                    $query->where('status', '!=', 'paid')->where('due_date', $today);
                    break;
                case 'due_soon':
                    $query->where('status', '!=', 'paid')->whereBetween('due_date', [$today->copy()->addDay(), $soon]);
                    break;
            }
        } else {
            // Default to showing everything pending if no status is selected
            $query->where('status', '!=', 'paid');
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('instalment', function($q) use ($search) {
                $q->whereHas('customer', function($cq) use ($search) {
                    $cq->where('name', 'ilike', "%{$search}%")
                       ->orWhere('phone', 'ilike', "%{$search}%");
                })->orWhereHas('bill', function($bq) use ($search) {
                    $bq->where('invoice_no', 'ilike', "%{$search}%");
                });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('due_date', '<', $today);
            } elseif ($request->status === 'due_today') {
                $query->where('due_date', $today);
            } elseif ($request->status === 'due_soon') {
                $query->whereBetween('due_date', [$today->copy()->addDay(), $soon]);
            }
        }

        // Date Range Filter
        if ($request->filled('start_date')) {
            $query->where('due_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('due_date', '<=', $request->end_date);
        }

        $dueItems = $query->orderBy('due_date', 'asc')->paginate(15)->withQueryString();

        return view('tenant.due_dashboard.index', compact('dueItems', 'stats'));
    }
}
