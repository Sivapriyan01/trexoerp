@extends('layouts.tenant')
@section('title', 'Stock Transfer Report')
@section('page-title', 'Stock Transfer Report')

@section('content')
<div class="space-y-6">
    {{-- Filters & Stats Header --}}
    <div class="grid grid-cols-12 gap-6">
        {{-- Filters --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="glass-card p-6 rounded-[2.5rem] bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 shadow-sm h-full">
                <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6">Date Range Filter</h3>
                <form action="{{ route('tenant.stock-transfer.report') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest px-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl text-xs font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest px-1">End Date</label>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl text-xs font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all">
                        Update Report
                    </button>
                </form>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-span-12 lg:col-span-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 h-full">
                <div class="glass-card p-6 rounded-[2.5rem] bg-blue-600 text-white shadow-xl shadow-blue-100 dark:shadow-none">
                    <p class="text-[8px] font-black uppercase tracking-widest opacity-60 mb-1">Total Transfers</p>
                    <p class="text-3xl font-black tracking-tight">{{ number_format($stats['total_transfers']) }}</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-[9px] font-bold bg-white/20 px-2 py-0.5 rounded-full">{{ $stats['inward_count'] }} In</span>
                        <span class="text-[9px] font-bold bg-white/20 px-2 py-0.5 rounded-full">{{ $stats['outward_count'] }} Out</span>
                    </div>
                </div>
                <div class="glass-card p-6 rounded-[2.5rem] bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 shadow-sm">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Quantity</p>
                    <p class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($stats['total_qty']) }}</p>
                    <p class="text-[9px] font-bold text-emerald-500 mt-2">Units Moved</p>
                </div>
                <div class="glass-card p-6 rounded-[2.5rem] bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 shadow-sm col-span-2">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Stock Value Moved</p>
                    <p class="text-3xl font-black text-blue-600 dark:text-blue-450 tracking-tight">₹{{ number_format($stats['total_value'], 2) }}</p>
                    <div class="mt-4 h-1 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: 65%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="glass-card rounded-[2.5rem] border border-slate-100 dark:border-slate-800/80 shadow-xl overflow-hidden bg-white dark:bg-slate-900/40">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/30">
            <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest">Detailed Transfer Log</h3>
            <button class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-slate-800 dark:text-slate-200">
                Export PDF
            </button>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-50 dark:border-slate-800/60">
                    <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Transfer ID</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Source / Dest</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Items</th>
                    <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40">
                @foreach($transfers as $t)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors">
                        <td class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-tighter">#{{ $t->id }}</td>
                        <td class="px-4 py-4 text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t->transfer_date->format('d M, Y') }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter {{ $t->type == 'inward' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' }}">
                                {{ $t->type }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $t->fromBranch?->name ?? 'Main' }}</span>
                                <svg width="12" height="12" class="text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $t->toBranch?->name ?? 'Main' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-xs font-black text-slate-900 dark:text-white text-center">{{ $t->total_qty }}</td>
                        <td class="px-6 py-4 text-xs font-black text-blue-600 dark:text-blue-400 text-right">₹{{ number_format($t->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection
