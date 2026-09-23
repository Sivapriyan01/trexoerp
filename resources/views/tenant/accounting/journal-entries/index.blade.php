@extends('layouts.tenant')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Journal Entries</h1>
        </div>
        <div>
            <a href="{{ route('tenant.accounting.journal-entries.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Journal Entry
            </a>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Reference #</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Description</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-slate-900 dark:text-white font-bold">{{ \Carbon\Carbon::parse($entry->date)->format('M d, Y') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-500 font-medium">{{ $entry->reference_number ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-600 dark:text-slate-300">{{ $entry->description ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                            {{ number_format($entry->total_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">No journal entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
