@extends('layouts.tenant')

@section('page-title', 'Accounting Command Center')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Accounting Command Center</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Manage your chart of accounts, journal entries, and general ledger.</p>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Chart of Accounts -->
        <a href="{{ route('tenant.accounting.accounts') }}" class="glass-card group p-8 rounded-[2rem] flex flex-col items-center justify-center gap-4 text-center hover:-translate-y-1 hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-teal-100 dark:hover:border-teal-900/50">
            <div class="w-16 h-16 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-1">Chart of Accounts</h3>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">Manage all system accounts</p>
            </div>
        </a>

        <!-- Journal Entries -->
        <a href="{{ route('tenant.accounting.journal-entries') }}" class="glass-card group p-8 rounded-[2rem] flex flex-col items-center justify-center gap-4 text-center hover:-translate-y-1 hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-blue-100 dark:hover:border-blue-900/50">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-1">Journal Entries</h3>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">Record manual transactions</p>
            </div>
        </a>

        <!-- General Ledger -->
        <a href="{{ route('tenant.accounting.general-ledger') }}" class="glass-card group p-8 rounded-[2rem] flex flex-col items-center justify-center gap-4 text-center hover:-translate-y-1 hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-emerald-100 dark:hover:border-emerald-900/50">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-1">General Ledger</h3>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">View running account balances</p>
            </div>
        </a>
    </div>
</div>
@endsection
