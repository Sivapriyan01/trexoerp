@extends('layouts.tenant')

@section('page-title', 'Dashboard')

@section('content')
<div x-data="journalEntryForm()" class="space-y-6 max-w-5xl mx-auto">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.accounting.journal-entries') }}" class="p-2 bg-white dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">New Journal Entry</h1>
        </div>
        <div>
            <button type="submit" form="journal-form" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Entry
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside text-sm font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Form -->
    <form id="journal-form" action="{{ route('tenant.accounting.journal-entries.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white dark:bg-slate-900/50 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Date -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Date</label>
                    <input type="date" name="date" required value="{{ old('date', date('Y-m-d')) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                </div>

                <!-- Reference Number -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Reference #</label>
                    <input type="text" name="reference_number" placeholder="Optional" value="{{ old('reference_number') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Journal Note</label>
                    <input type="text" name="description" placeholder="Brief description for the entry" value="{{ old('description') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900/50 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest w-1/3">Account</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest w-1/3">Description</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right w-40">Debit</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right w-40">Credit</th>
                            <th class="px-6 py-4 w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-for="(line, index) in lines" :key="line.id">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-3">
                                    <select :name="'lines[' + index + '][chart_of_account_id]'" x-model="line.account_id" required
                                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code ? $account->code . ' - ' : '' }}{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-3">
                                    <input type="text" :name="'lines[' + index + '][description]'" x-model="line.description" placeholder="Line description"
                                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                                </td>
                                <td class="px-6 py-3">
                                    <input type="number" step="0.01" min="0" :name="'lines[' + index + '][debit]'" x-model.number="line.debit" @input="line.credit = 0; calculateTotals()"
                                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-right focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                                </td>
                                <td class="px-6 py-3">
                                    <input type="number" step="0.01" min="0" :name="'lines[' + index + '][credit]'" x-model.number="line.credit" @input="line.debit = 0; calculateTotals()"
                                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-right focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 2"
                                        class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                        <tr>
                            <td colspan="2" class="px-6 py-4">
                                <button type="button" @click="addLine()" class="text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center gap-2">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Line
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest mr-3">Total Debit</span>
                                <span class="text-lg font-black text-slate-900 dark:text-white" x-text="totalDebit.toFixed(2)">0.00</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest mr-3">Total Credit</span>
                                <span class="text-lg font-black text-slate-900 dark:text-white" x-text="totalCredit.toFixed(2)">0.00</span>
                            </td>
                            <td></td>
                        </tr>
                        <tr x-show="!isBalanced" class="bg-rose-50/50 dark:bg-rose-900/10">
                            <td colspan="5" class="px-6 py-3 text-center text-rose-600 dark:text-rose-400 font-bold text-sm">
                                Debits and Credits must balance to save the entry. Difference: <span x-text="Math.abs(totalDebit - totalCredit).toFixed(2)"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
function journalEntryForm() {
    const oldLines = @json(old('lines', []));
    let initialLines = [
        { id: 1, account_id: '', description: '', debit: 0, credit: 0 },
        { id: 2, account_id: '', description: '', debit: 0, credit: 0 }
    ];

    if (oldLines) {
        const linesArray = Array.isArray(oldLines) ? oldLines : Object.values(oldLines);
        if (linesArray.length > 0) {
            initialLines = linesArray.map((line, index) => ({
                id: index + 1,
                account_id: line.chart_of_account_id || '',
                description: line.description || '',
                debit: parseFloat(line.debit) || 0,
                credit: parseFloat(line.credit) || 0
            }));
        }
    }

    return {
        lines: initialLines,
        totalDebit: 0,
        totalCredit: 0,
        nextId: initialLines.length + 1,

        init() {
            this.calculateTotals();
        },

        get isBalanced() {
            return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0;
        },

        addLine() {
            this.lines.push({
                id: this.nextId++,
                account_id: '',
                description: '',
                debit: 0,
                credit: 0
            });
        },

        removeLine(index) {
            if (this.lines.length > 2) {
                this.lines.splice(index, 1);
                this.calculateTotals();
            }
        },

        calculateTotals() {
            this.totalDebit = this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
            this.totalCredit = this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
        }
    }
}
</script>
@endsection
