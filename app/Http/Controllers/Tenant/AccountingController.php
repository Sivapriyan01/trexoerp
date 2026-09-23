<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;

class AccountingController extends Controller
{
    /**
     * Accounting Dashboard/Index
     */
    public function index()
    {
        return view('tenant.accounting.index');
    }

    /**
     * Display the Chart of Accounts page.
     */
    public function accounts()
    {
        $accounts = ChartOfAccount::all();
        return view('tenant.accounting.accounts.index', compact('accounts'));
    }

    /**
     * Store a new account.
     */
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'type' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        ChartOfAccount::create($validated);
        
        return redirect()->route('tenant.accounting.accounts')->with('success', 'Account created successfully.');
    }

    /**
     * Update an existing account.
     */
    public function updateAccount(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'type' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $account->update($validated);
        
        return redirect()->route('tenant.accounting.accounts')->with('success', 'Account updated successfully.');
    }

    /**
     * Display the Journal Entries list.
     */
    public function journalEntries()
    {
        $entries = \App\Models\JournalEntry::with('lines')->latest()->get();
        return view('tenant.accounting.journal-entries.index', compact('entries'));
    }

    /**
     * Show form to create a new Journal Entry.
     */
    public function createJournalEntry()
    {
        $accounts = ChartOfAccount::all();
        return view('tenant.accounting.journal-entries.create', compact('accounts'));
    }

    /**
     * Store a new Journal Entry.
     */
    public function storeJournalEntry(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        $totalDebit = collect($validated['lines'])->sum(function($line) { return (float)($line['debit'] ?? 0); });
        $totalCredit = collect($validated['lines'])->sum(function($line) { return (float)($line['credit'] ?? 0); });

        \Illuminate\Support\Facades\Log::info('Journal Entry Submission', [
            'raw_lines' => $request->input('lines'),
            'validated_lines' => $validated['lines'],
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit
        ]);

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->with('error', "Debits and Credits must balance. (Debits: $totalDebit, Credits: $totalCredit)")->withInput();
        }

        if ($totalDebit == 0) {
            return back()->with('error', 'Journal entry must have a non-zero amount.')->withInput();
        }

        $entry = \App\Models\JournalEntry::create([
            'date' => $validated['date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'description' => $validated['description'] ?? null,
            'total_amount' => $totalDebit,
        ]);

        foreach ($validated['lines'] as $line) {
            $entry->lines()->create([
                'chart_of_account_id' => $line['chart_of_account_id'],
                'description' => $line['description'] ?? null,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
            ]);
        }

        return redirect()->route('tenant.accounting.journal-entries')->with('success', 'Journal Entry created successfully.');
    }

    /**
     * General Ledger View
     */
    public function generalLedger(Request $request)
    {
        $accounts = ChartOfAccount::all();
        $selectedAccount = null;
        $lines = [];
        $openingBalance = 0; // Keeping simple for now, would normally calculate based on fiscal year
        $runningBalance = $openingBalance;

        if ($request->has('account_id') && $request->account_id != '') {
            $selectedAccount = ChartOfAccount::find($request->account_id);
            if ($selectedAccount) {
                // Get all lines for this account, eager load journal entry, sort by date
                $query = \App\Models\JournalEntryLine::where('chart_of_account_id', $selectedAccount->id)
                    ->with('journalEntry')
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->select('journal_entry_lines.*');

                if ($request->has('from') && $request->from) {
                    $query->where('journal_entries.date', '>=', $request->from);
                }
                
                if ($request->has('to') && $request->to) {
                    $query->where('journal_entries.date', '<=', $request->to);
                }

                $lines = $query->orderBy('journal_entries.date', 'asc')
                    ->orderBy('journal_entries.id', 'asc')
                    ->get();

                // Calculate running balance
                $isDebitNormal = in_array($selectedAccount->type, ['Asset', 'Expense']);

                foreach ($lines as $line) {
                    if ($isDebitNormal) {
                        $runningBalance += $line->debit - $line->credit;
                    } else {
                        $runningBalance += $line->credit - $line->debit;
                    }
                    $line->running_balance = $runningBalance;
                }
            }
        }

        return view('tenant.accounting.general-ledger', compact('accounts', 'selectedAccount', 'lines', 'runningBalance'));
    }
}
