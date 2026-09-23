<?php
// app/Http/Controllers/Tenant/DailyExpenseController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DailyExpense;
use App\Models\ExpenseDescription;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyExpenseController extends Controller
{
    /* ─────────────────────── MAIN PAGE ─────────────────────── */

    public function index(Request $request)
    {
        // Default period = today
        $period = $request->input('period', 'today');
        [$from, $to] = $this->resolvePeriod($period, $request);

        $expenses    = DailyExpense::expenses()->forPeriod($from, $to)->orderBy('expense_date')->orderBy('id')->get();
        $pettyCash   = DailyExpense::pettyCash()->forPeriod($from, $to)->orderBy('expense_date')->orderBy('id')->get();

        $totalExpenses = $expenses->sum('amount');
        $totalInwards  = $pettyCash->sum('amount');
        $netBalance    = $totalInwards - $totalExpenses;

        // Balance report: merge both, sort by date+id, compute running balance
        $allEntries = DailyExpense::forPeriod($from, $to)
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();

        $runningBalance = 0;
        $balanceRows = $allEntries->map(function ($entry) use (&$runningBalance) {
            if ($entry->type === 'petty_cash') {
                $runningBalance += $entry->amount;
            } else {
                $runningBalance -= $entry->amount;
            }
            return [
                'date'         => $entry->expense_date->format('d-m-Y'),
                'type'         => $entry->type === 'petty_cash' ? 'Inward' : 'Expense',
                'description'  => $entry->description ?? $entry->source ?? '—',
                'payment_mode' => $entry->payment_mode,
                'amount'       => $entry->amount,
                'balance'      => $runningBalance,
            ];
        });

        $descriptions = ExpenseDescription::orderBy('name')->get();

        $expenseCategories = [
            'Food & Beverages', 'Travel', 'Office Supplies', 'Utilities',
            'Rent', 'Salaries', 'Maintenance', 'Marketing', 'Miscellaneous',
        ];

        return view('tenant.daily_expense.index', compact(
            'expenses', 'pettyCash', 'totalExpenses', 'totalInwards',
            'netBalance', 'balanceRows', 'descriptions', 'expenseCategories',
            'period', 'from', 'to'
        ));
    }

    /* ─────────────────────── STORE EXPENSE ─────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'expense_date'  => 'required|date',
            'category'      => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:255',
            'payment_mode'  => 'required|string',
            'amount'        => 'required|numeric|min:0.01',
        ]);

        $expense = DailyExpense::create([
            'expense_date' => $request->expense_date,
            'type'         => 'expense',
            'category'     => $request->category,
            'description'  => $request->description,
            'payment_mode' => $request->payment_mode,
            'amount'       => $request->amount,
        ]);

        return response()->json(['success' => true, 'message' => 'Expense added successfully.', 'data' => $expense]);
    }

    /* ─────────────────────── STORE PETTY CASH ──────────────── */

    public function storePettyCash(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'source'       => 'nullable|string|max:100',
            'payment_mode' => 'required|string',
            'amount'       => 'required|numeric|min:0.01',
        ]);

        $entry = DailyExpense::create([
            'expense_date' => $request->expense_date,
            'type'         => 'petty_cash',
            'source'       => $request->source,
            'payment_mode' => $request->payment_mode,
            'amount'       => $request->amount,
        ]);

        return response()->json(['success' => true, 'message' => 'Petty cash added successfully.', 'data' => $entry]);
    }

    /* ─────────────────────── DELETE ────────────────────────── */

    public function destroy($id)
    {
        $expense = DailyExpense::findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true, 'message' => 'Entry deleted.']);
    }

    /* ─────────────────────── DESCRIPTIONS CRUD ─────────────── */

    public function storeDescription(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $desc = ExpenseDescription::create(['name' => $request->name]);
        return response()->json(['success' => true, 'data' => $desc]);
    }

    public function updateDescription(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $desc = ExpenseDescription::findOrFail($id);
        $desc->update(['name' => $request->name]);
        return response()->json(['success' => true, 'data' => $desc]);
    }

    public function destroyDescription($id)
    {
        ExpenseDescription::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /* ─────────────────────── EXPORT CSV ────────────────────── */

    public function export(Request $request)
    {
        $period = $request->input('period', 'today');
        [$from, $to] = $this->resolvePeriod($period, $request);

        $expenses  = DailyExpense::expenses()->forPeriod($from, $to)->orderBy('expense_date')->get();
        $pettyCash = DailyExpense::pettyCash()->forPeriod($from, $to)->orderBy('expense_date')->get();

        $filename = 'daily_expense_' . $from . '_to_' . $to . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($expenses, $pettyCash) {
            $handle = fopen('php://output', 'w');

            // Expense Report
            fputcsv($handle, ['=== EXPENSE REPORT ===']);
            fputcsv($handle, ['SL NO', 'DATE', 'PAYMENT MODE', 'DESCRIPTION', 'CATEGORY', 'AMOUNT']);
            foreach ($expenses as $i => $row) {
                fputcsv($handle, [
                    $i + 1,
                    $row->expense_date->format('d-m-Y'),
                    $row->payment_mode,
                    $row->description ?? '—',
                    $row->category ?? '—',
                    number_format($row->amount, 2),
                ]);
            }

            fputcsv($handle, []);

            // Petty Cash Report
            fputcsv($handle, ['=== PETTY CASH REPORT ===']);
            fputcsv($handle, ['SL NO', 'DATE', 'SOURCE', 'MODE', 'AMOUNT']);
            foreach ($pettyCash as $i => $row) {
                fputcsv($handle, [
                    $i + 1,
                    $row->expense_date->format('d-m-Y'),
                    $row->source ?? '—',
                    $row->payment_mode,
                    number_format($row->amount, 2),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ─────────────────────── HELPER ────────────────────────── */

    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'today'      => [Carbon::today()->toDateString(),         Carbon::today()->toDateString()],
            'yesterday'  => [Carbon::yesterday()->toDateString(),     Carbon::yesterday()->toDateString()],
            'this_week'  => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'this_month' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth()->toDateString(), Carbon::now()->subMonth()->endOfMonth()->toDateString()],
            'custom'     => [$request->input('from', Carbon::today()->toDateString()), $request->input('to', Carbon::today()->toDateString())],
            default      => [Carbon::today()->toDateString(), Carbon::today()->toDateString()],
        };
    }
}
