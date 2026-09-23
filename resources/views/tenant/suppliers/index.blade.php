@extends('layouts.tenant')
@section('title', 'Vendors')
@section('page-title', 'Vendor Management')

@section('content')
<div class="space-y-6">
    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('tenant.suppliers.index') }}" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300 {{ !request('filter') ? 'ring-2 ring-teal-500 shadow-lg shadow-teal-100 dark:shadow-none' : '' }} block">
            <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center shrink-0">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Vendors</p>
                <p class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $totalVendors }}</p>
            </div>
        </a>
        <a href="{{ route('tenant.suppliers.index', ['filter' => 'active']) }}" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300 {{ request('filter') === 'active' ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-100 dark:shadow-none' : '' }} block">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Accounts</p>
                <p class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $activeAccounts }}</p>
            </div>
        </a>
        <div class="glass-card p-4 rounded-2xl flex items-center justify-center">
            <a href="{{ route('tenant.suppliers.create') }}" class="w-full bg-teal-600 text-white py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-teal-100 hover:scale-[1.02] transition">
                + Add Vendor
            </a>
        </div>
    </div>

    {{-- Vendors Table --}}
    <div class="glass-card rounded-[1.5rem] overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Vendor Directory</h3>
            <div>
                <form action="{{ route('tenant.suppliers.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" id="vendor-search" value="{{ request('search') }}" placeholder="Search vendors..." class="bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-1.5 text-[10px] focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900 outline-none w-48" oninput="filterVendorTable()">
                    @if(request('search'))
                        <a href="{{ route('tenant.suppliers.index') }}" class="text-[10px] font-black text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl transition">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/40">
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Vendor Details</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Contact Info</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">GSTIN</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-teal-50/30 dark:hover:bg-slate-950/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-teal-100/30 shrink-0">
                                        {{ substr($supplier->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $supplier->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ $supplier->address }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $supplier->phone }}</p>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ $supplier->email }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg whitespace-nowrap inline-block">
                                    {{ $supplier->gstin ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($supplier->email)
                                        <a href="{{ route('tenant.mail.index', ['compose_to' => $supplier->email]) }}" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Email Vendor">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('tenant.suppliers.edit', $supplier) }}" class="p-2 text-slate-400 hover:text-teal-600 transition">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('tenant.suppliers.destroy', $supplier) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 transition" onclick="return confirm('Delete this vendor?')">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="5" class="px-8 py-12 text-center">
                                <p class="text-sm font-bold text-slate-400 italic">No vendors found.</p>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="js-empty-row" style="display: none;">
                        <td colspan="5" class="px-8 py-12 text-center">
                            <p class="text-sm font-bold text-slate-400 italic">No matching vendors found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterVendorTable() {
    const query = document.getElementById('vendor-search').value.toLowerCase().trim();
    const rows = document.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.id === 'empty-row' || row.id === 'js-empty-row') return;
        
        const text = row.textContent.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const jsEmptyRow = document.getElementById('js-empty-row');
    if (jsEmptyRow) {
        if (visibleCount === 0) {
            jsEmptyRow.style.display = '';
        } else {
            jsEmptyRow.style.display = 'none';
        }
    }
}
</script>
@endsection

