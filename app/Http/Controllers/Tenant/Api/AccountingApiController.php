<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingApiController extends Controller
{
    // GET /api/v1/accounting/accounts
    public function accounts(Request $request)
    {
        $query = ChartOfAccount::query()->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    // POST /api/v1/accounting/accounts
    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:asset,liability,income,expense,equity',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        $account = ChartOfAccount::create($data);
        return response()->json(['success' => true, 'data' => $account], 201);
    }

    // PUT /api/v1/accounting/accounts/{id}
    public function updateAccount(Request $request, $id)
    {
        $account = ChartOfAccount::find($id);
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $account->update($data);
        return response()->json(['success' => true, 'data' => $account]);
    }

    // DELETE /api/v1/accounting/accounts/{id}
    public function destroyAccount($id)
    {
        $account = ChartOfAccount::find($id);
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }
        $account->delete();
        return response()->json(['success' => true, 'message' => 'Account deleted.']);
    }

    // GET /api/v1/accounting/journal-entries
    public function journalEntries(Request $request)
    {
        $query = JournalEntry::with('lines.account')->latest();

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $entries = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $entries->items(),
            'meta'    => ['current_page' => $entries->currentPage(), 'last_page' => $entries->lastPage(), 'total' => $entries->total()],
        ]);
    }

    // POST /api/v1/accounting/journal-entries
    public function storeJournalEntry(Request $request)
    {
        $data = $request->validate([
            'date'              => 'required|date',
            'description'       => 'required|string|max:500',
            'reference'         => 'nullable|string|max:100',
            'lines'             => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'     => 'nullable|numeric|min:0',
            'lines.*.credit'    => 'nullable|numeric|min:0',
        ]);

        $totalDebit  = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return response()->json(['success' => false, 'message' => 'Debits and credits must balance.'], 422);
        }

        DB::transaction(function () use ($data, &$entry) {
            $entry = JournalEntry::create([
                'date'        => $data['date'],
                'description' => $data['description'],
                'reference'   => $data['reference'] ?? null,
                'created_by'  => auth()->id(),
            ]);

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                    'notes'            => $line['notes'] ?? null,
                ]);
            }
        });

        return response()->json(['success' => true, 'data' => $entry->load('lines.account'), 'message' => 'Journal entry created.'], 201);
    }

    // GET /api/v1/accounting/journal-entries/{id}
    public function showJournalEntry($id)
    {
        $entry = JournalEntry::with('lines.account')->find($id);
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $entry]);
    }

    // GET /api/v1/accounting/general-ledger
    public function generalLedger(Request $request)
    {
        $request->validate(['account_id' => 'required|exists:chart_of_accounts,id']);

        $account = ChartOfAccount::find($request->account_id);
        $query   = JournalEntryLine::with('journalEntry')
            ->where('account_id', $request->account_id);

        if ($request->filled('from')) {
            $query->whereHas('journalEntry', fn($q) => $q->whereDate('date', '>=', $request->from));
        }
        if ($request->filled('to')) {
            $query->whereHas('journalEntry', fn($q) => $q->whereDate('date', '<=', $request->to));
        }

        $lines = $query->get();

        return response()->json([
            'success' => true,
            'account' => $account,
            'data'    => $lines,
            'totals'  => [
                'total_debit'  => round($lines->sum('debit'), 2),
                'total_credit' => round($lines->sum('credit'), 2),
                'balance'      => round($lines->sum('debit') - $lines->sum('credit'), 2),
            ],
        ]);
    }

    // GET /api/v1/accounting/trial-balance
    public function trialBalance()
    {
        $accounts = ChartOfAccount::with('lines')->get()->map(function ($account) {
            $debit  = $account->lines->sum('debit');
            $credit = $account->lines->sum('credit');
            return [
                'id'      => $account->id,
                'name'    => $account->name,
                'type'    => $account->type,
                'debit'   => round($debit, 2),
                'credit'  => round($credit, 2),
                'balance' => round($debit - $credit, 2),
            ];
        })->filter(fn($a) => $a['debit'] > 0 || $a['credit'] > 0)->values();

        return response()->json(['success' => true, 'data' => $accounts]);
    }
}
