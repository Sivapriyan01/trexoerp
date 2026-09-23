@extends('layouts.tenant')
@section('title', 'Customers')
@section('page-title', 'Customer Management')

@section('content')
<div class="space-y-6 md:space-y-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Customer Management</h1>
            <p class="text-sm text-slate-500 mt-1">Directory of all customers and loyalty points.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.customers.create') }}" class="w-full md:w-auto bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-blue-100 hover:scale-[1.02] transition">
                + Add Customer
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('tenant.customers.index') }}" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300 {{ !request('filter') ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-100 dark:shadow-none' : '' }}">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Customers</p>
                <p class="text-lg md:text-xl font-black text-slate-900 dark:text-slate-100">{{ $customers->count() }}</p>
            </div>
        </a>
        <a href="{{ route('tenant.customers.index', ['filter' => 'with_points']) }}" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300 {{ request('filter') === 'with_points' ? 'ring-2 ring-emerald-500 shadow-lg shadow-emerald-100 dark:shadow-none' : '' }}">
            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Points Issued</p>
                <p class="text-lg md:text-xl font-black text-slate-900 dark:text-slate-100">{{ number_format($customers->sum('points'), 0) }}</p>
            </div>
        </a>
        <a href="{{ route('tenant.customers.index') }}" class="glass-card p-4 rounded-2xl flex items-center gap-3 col-span-2 lg:col-span-1 cursor-pointer hover:-translate-y-1 transition-all duration-300">
             <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer Health</p>
                <p class="text-lg md:text-xl font-black text-slate-900 dark:text-slate-100">Good</p>
            </div>
        </a>
    </div>

    {{-- Customers Table --}}
    <div class="glass-card rounded-[2rem] overflow-hidden">
        <div class="p-5 md:p-6 border-b border-slate-100 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between bg-white/50 dark:bg-slate-900/40 gap-4">
            <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Customer Directory</h3>
            <div>
                <form action="{{ route('tenant.customers.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" id="customer-search" value="{{ request('search') }}" placeholder="Search by name or phone..." class="bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-2 text-[10px] focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 outline-none w-full md:w-64" oninput="filterCustomerTable()">
                    @if(request('search'))
                        <a href="{{ route('tenant.customers.index') }}" class="text-[10px] font-black text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-2 rounded-xl transition">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/40">
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Customer Details</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Contact</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Membership</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Bills</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Loyalty Points</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Wallet</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse ($customers as $customer)
                        @php
                            $displayPhone = $customer->phone;
                            $displayAddress = $customer->address;

                            if (empty($displayPhone) && !empty($displayAddress)) {
                                if (preg_match('/(?:CELL|PH|MOB|PHONE)[^0-9]*([0-9\-, ]+)/i', $displayAddress, $matches)) {
                                    $displayPhone = trim($matches[0], ', ');
                                    $cleanedAddress = trim(str_replace($matches[0], '', $displayAddress), ', -');
                                    $displayAddress = !empty($cleanedAddress) ? $cleanedAddress : 'No address provided';
                                } elseif (preg_match('/[0-9]{10}/', $displayAddress, $matches)) {
                                    $displayPhone = $matches[0];
                                    $cleanedAddress = trim(str_replace($matches[0], '', $displayAddress), ', -');
                                    $displayAddress = !empty($cleanedAddress) ? $cleanedAddress : 'No address provided';
                                }
                            }
                        @endphp
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-950/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-xs shrink-0">
                                        {{ substr($customer->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $customer->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 truncate w-40" title="{{ $displayAddress }}">{{ $displayAddress ?: 'No address provided' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $displayPhone ?: 'N/A' }}</p>
                            </td>
                            <td class="px-8 py-5">
                                @if($customer->activeMembership)
                                    <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1 w-max">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                        {{ $customer->activeMembership->plan->name }}
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">None</span>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-350 rounded-lg text-[10px] font-black whitespace-nowrap inline-block">
                                    {{ $customer->bill_count }} Bills
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-black text-blue-600 dark:text-blue-400">{{ number_format($customer->points, 2) }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-black text-rose-500">₹{{ number_format($customer->wallet_balance, 2) }}</p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- View Details --}}
                                    <a href="{{ route('tenant.customers.show', $customer) }}" class="p-2 text-slate-400 hover:text-indigo-600 transition" title="View Details">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if($customer->email)
                                        <a href="{{ route('tenant.mail.index', ['compose_to' => $customer->email]) }}" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Email Customer">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('tenant.customers.edit', $customer) }}" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Edit">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('tenant.customers.destroy', $customer) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 transition" title="Delete" onclick="return confirm('Delete this customer?')">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="5" class="px-8 py-12 text-center text-slate-400 italic">No customers found.</td>
                        </tr>
                    @endforelse
                    <tr id="js-empty-row" style="display: none;">
                        <td colspan="5" class="px-8 py-12 text-center text-slate-400 italic">No matching customers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterCustomerTable() {
    const query = document.getElementById('customer-search').value.toLowerCase().trim();
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

