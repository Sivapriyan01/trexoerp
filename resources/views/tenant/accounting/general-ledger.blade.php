@extends('layouts.tenant')

@section('page-title', 'General Ledger')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="generalLedger()">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">General Ledger</h1>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900/50 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 mb-8">
        <form action="{{ route('tenant.accounting.general-ledger') }}" method="GET" class="flex flex-col md:flex-row items-end gap-6">
            
            <div class="flex-1 w-full space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Account</label>
                <div class="relative">
                    <select name="account_id" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-none text-slate-900 dark:text-white rounded-xl pl-4 pr-10 py-3 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ (request('account_id') == $account->id) ? 'selected' : '' }}>
                                {{ $account->code ? $account->code . ' - ' : '' }}{{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex-1 w-full space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">From</label>
                <input type="date" name="from" value="{{ request('from') }}" 
                    class="w-full bg-slate-50 dark:bg-slate-800 border-none text-slate-900 dark:text-white rounded-xl px-4 py-3 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 outline-none transition-all">
            </div>

            <div class="flex-1 w-full space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">To</label>
                <input type="date" name="to" value="{{ request('to') }}" 
                    class="w-full bg-slate-50 dark:bg-slate-800 border-none text-slate-900 dark:text-white rounded-xl px-4 py-3 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 outline-none transition-all">
            </div>

            <div class="w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all whitespace-nowrap">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    @if(!$selectedAccount)
    <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
        <div class="w-20 h-20 bg-blue-50 dark:bg-blue-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg width="32" height="32" class="text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">No Account Selected</h3>
        <p class="text-slate-500 dark:text-slate-400 font-medium">Please select an account from the dropdown above to view its ledger.</p>
    </div>
    @else
    
    <!-- Account Summary Card -->
    <div class="bg-white dark:bg-slate-900/50 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ $selectedAccount->type }} Account</span>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2 mt-1">
                @if($selectedAccount->icon)
                    <span class="text-blue-500">{!! $selectedAccount->icon !!}</span>
                @endif
                {{ $selectedAccount->code ? $selectedAccount->code . ' - ' : '' }}{{ $selectedAccount->name }}
            </h2>
        </div>
        <div class="text-center md:text-right bg-slate-50 dark:bg-slate-800/50 px-6 py-4 rounded-2xl border border-slate-100 dark:border-slate-700 w-full md:w-auto">
            <span class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-1">Closing Balance</span>
            <span class="text-3xl font-black {{ $runningBalance < 0 ? 'text-rose-500' : 'text-blue-600 dark:text-blue-400' }}">
                {{ number_format(abs($runningBalance), 2) }}
                <span class="text-sm text-slate-400">{{ in_array($selectedAccount->type, ['Asset', 'Expense']) ? ($runningBalance >= 0 ? 'Dr' : 'Cr') : ($runningBalance >= 0 ? 'Cr' : 'Dr') }}</span>
            </span>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white dark:bg-slate-900/50 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Reference</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Description</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Debit</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Credit</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right bg-slate-100/50 dark:bg-slate-700/20">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    <!-- Opening Balance Row (Optional, shown if > 0 conceptually) -->
                    <tr class="bg-slate-50/30 dark:bg-slate-800/20">
                        <td class="px-6 py-3 font-medium text-slate-500">--</td>
                        <td class="px-6 py-3 text-slate-500">--</td>
                        <td class="px-6 py-3 text-slate-900 dark:text-white font-bold italic">Opening Balance</td>
                        <td class="px-6 py-3 text-right"></td>
                        <td class="px-6 py-3 text-right"></td>
                        <td class="px-6 py-3 text-right font-black text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/30">0.00</td>
                    </tr>
                    
                    @forelse($lines as $line)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">
                            {{ \Carbon\Carbon::parse($line->journalEntry->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">
                            {{ $line->journalEntry->reference_number ?? '-' }}
                        </td>
                        <td class="px-6 py-3 text-slate-900 dark:text-white font-medium">
                            {{ $line->description ?: ($line->journalEntry->description ?: 'Journal Entry') }}
                        </td>
                        <td class="px-6 py-3 text-right font-medium text-slate-700 dark:text-slate-300">
                            {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right font-medium text-slate-700 dark:text-slate-300">
                            {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right font-black text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/30">
                            {{ number_format(abs($line->running_balance), 2) }}
                            <span class="text-[10px] text-slate-400 font-bold uppercase ml-1">
                                {{ in_array($selectedAccount->type, ['Asset', 'Expense']) ? ($line->running_balance >= 0 ? 'Dr' : 'Cr') : ($line->running_balance >= 0 ? 'Cr' : 'Dr') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 font-medium italic">
                            No transactions found for this account.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
function generalLedger() {
    return {
        // Alpine data if needed later
    }
}
</script>
@endsection
