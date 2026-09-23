@extends('layouts.tenant')
@section('title', 'Stock Transfer History')
@section('page-title', 'Stock Transfer History')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.stock-transfer.index') }}" class="p-2 bg-white dark:bg-slate-900 rounded-xl text-slate-400 hover:text-blue-600 transition-colors shadow-sm border border-slate-100 dark:border-slate-800">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">All Transfers</h3>
        </div>
        <a href="{{ route('tenant.stock-transfer.index') }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 dark:shadow-none hover:scale-[1.02] transition-all">
            + New Transfer
        </a>
    </div>

    <div class="glass-card rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden bg-white dark:bg-slate-900/40">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-50 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-950/30">
                    <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">From</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">To</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Items</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Total Value</th>
                    <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40">
                @foreach($transfers as $t)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors group">
                        <td class="px-6 py-4 text-xs font-black text-slate-400">#{{ $t->id }}</td>
                        <td class="px-4 py-4 text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t->transfer_date }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter {{ $t->type == 'inward' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' }}">
                                {{ $t->type }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">{{ $t->fromBranch?->name ?? 'Main' }}</td>
                        <td class="px-4 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">{{ $t->toBranch?->name ?? 'Main' }}</td>
                        <td class="px-4 py-4 text-xs font-black text-slate-900 dark:text-slate-200 text-center">{{ $t->total_qty }}</td>
                        <td class="px-4 py-4 text-xs font-black text-blue-600 dark:text-blue-400 text-right">₹{{ number_format($t->total_amount, 2) }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $t->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="p-2 text-slate-300 hover:text-blue-600 transition-colors">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                        </td>
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
