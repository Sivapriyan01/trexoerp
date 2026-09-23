<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->query('view', 'month');
        $date = $request->filled('date') ? now()->parse($request->date) : now();

        $start = match($view) {
            'day'   => $date->copy()->startOfDay(),
            'week'  => $date->copy()->startOfWeek(),
            'year'  => $date->copy()->startOfYear(),
            default => $date->copy()->startOfMonth(),
        };

        $end = match($view) {
            'day'   => $date->copy()->endOfDay(),
            'week'  => $date->copy()->endOfWeek(),
            'year'  => $date->copy()->endOfYear(),
            default => $date->copy()->endOfMonth(),
        };

        $sales = Bill::whereBetween('bill_date', [$start, $end])->get();
        $purchases = Purchase::whereBetween('invoice_date', [$start, $end])->get();

        return view('tenant.calendar.index', compact('sales', 'purchases', 'view', 'date'));
    }
}
