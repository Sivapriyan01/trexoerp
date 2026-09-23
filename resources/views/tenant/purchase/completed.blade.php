@extends('layouts.tenant')

@section('title', 'Completed Purchases')

@section('content')
<div class="p-6 min-h-screen bg-[#F8FAFC] dark:bg-slate-950" x-data="{ 
    search: '{{ request('search') }}',
    applyFilters() {
        let url = new URL(window.location.href);
        if (this.search) url.searchParams.set('search', this.search);
        else url.searchParams.delete('search');
        window.location.href = url.toString();
    }
}">
    
    <!-- 📑 Tabs Section -->
    <div class="flex items-center gap-8 border-b border-slate-200 dark:border-slate-800 mb-8 px-4">
        <a href="{{ route('tenant.purchase.index') }}" 
           class="pb-4 text-sm font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all">Purchase</a>
        <a href="{{ route('tenant.purchase.settlement') }}" 
           class="pb-4 text-sm font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all tracking-tight">Settlements</a>
        <a href="{{ route('tenant.purchase.completed') }}" 
           class="pb-4 text-sm font-black text-blue-600 border-b-2 border-blue-600 tracking-tight transition-all">Completed</a>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tighter uppercase">Completed Purchases</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium mt-1">View and manage all finalized purchase orders</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.purchase.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-6 py-3 rounded-2xl transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2 text-sm uppercase tracking-tight">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Purchase Settlement
            </a>
        </div>
    </div>

    <!-- 🔍 Filters Section -->
    <div class="glass-card mb-8 p-4 flex flex-col md:flex-row gap-4 items-center">
        <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   x-model="search"
                   @keyup.enter="applyFilters()"
                   placeholder="Search completed purchases..." 
                   class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm outline-none text-slate-900 dark:text-white">
        </div>
        
        <div class="flex items-center gap-2">
            <button class="p-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg></button>
            <button class="p-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg></button>
            <button @click="window.location.reload()" class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></button>
        </div>
    </div>

    <!-- 📊 Table Section -->
    <div class="glass-card overflow-hidden rounded-[2.5rem] shadow-xl border border-white/20 dark:border-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 uppercase">
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                SL.NO
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                DATE
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                VENDOR
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01M19 7h.01M19 11h.01M19 15h.01M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"></path></svg>
                                TYPE
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                DOC NO
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                DESCRIPTION
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                PAYMENT MODE
                            </div>
                        </th>
                        <th class="px-6 py-5">
                            <div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                CF
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($purchases as $index => $p)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group border-b border-slate-100 dark:border-slate-800/50">
                        <td class="px-6 py-4 text-xs font-bold text-slate-400">
                            {{ $purchases->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ $p->invoice_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $p->vendor->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 uppercase font-bold">
                            purchase
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-slate-600 dark:text-slate-400">
                            {{ $p->id }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 italic">
                            {{ $p->remark ?: 'no' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">
                            Multiple
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-slate-900 dark:text-white text-right pr-10">
                            0
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-full mb-4 text-slate-300">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Completed Purchases</h3>
                                <p class="text-slate-500 dark:text-slate-400">Finalize your pending orders to see them here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

