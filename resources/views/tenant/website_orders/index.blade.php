@extends('layouts.tenant')
@section('title', 'Website Orders')
@section('page-title', 'Website Orders')

@section('content')
<div class="space-y-6 md:space-y-8 animate-in fade-in duration-500" x-data="{
    selectedOrder: null,
    websiteUrl: window.location.protocol + '//' + window.location.hostname + ':5173'
}">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-sm">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Website Orders</h1>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500">Live incoming orders from your e-commerce storefront</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a :href="websiteUrl" target="_blank" class="flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white px-5 py-2.5 rounded-2xl font-black text-xs tracking-wide shadow-lg shadow-indigo-200 dark:shadow-none hover:scale-[1.02] transition-all">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>Open Storefront Website</span>
            </a>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-[2rem] flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Orders</p>
                <p class="text-xl md:text-2xl font-black text-slate-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[2rem] flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Online Revenue</p>
                <p class="text-xl md:text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($totalRevenue, 2) }}</p>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[2rem] flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending / New</p>
                <p class="text-xl md:text-2xl font-black text-amber-600">{{ number_format($newOrdersCount) }}</p>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[2rem] flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Delivered</p>
                <p class="text-xl md:text-2xl font-black text-teal-600">{{ number_format($deliveredCount) }}</p>
            </div>
        </div>
    </div>

    {{-- Orders Container --}}
    <div class="glass-card p-6 md:p-8 rounded-[2.5rem] space-y-6">
        {{-- Filters & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Status Tabs --}}
            @php $currentStatus = request('status', 'ALL'); @endphp
            <div class="flex flex-wrap gap-2">
                @foreach(['ALL' => 'All Orders', 'NEW' => 'New', 'CONFIRMED' => 'Confirmed', 'SHIPPED' => 'Shipped', 'DELIVERED' => 'Delivered', 'CANCELLED' => 'Cancelled'] as $sKey => $sLabel)
                    <a href="{{ route('tenant.website-orders.index', array_merge(request()->query(), ['status' => $sKey])) }}"
                       class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentStatus === $sKey ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100 dark:shadow-none' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ $sLabel }}
                    </a>
                @endforeach
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('tenant.website-orders.index') }}" class="relative w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <svg width="14" height="14" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, customer, phone..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white">
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <th class="pb-4">Invoice / Web ID</th>
                        <th class="pb-4">Date</th>
                        <th class="pb-4">Customer</th>
                        <th class="pb-4">Items</th>
                        <th class="pb-4">Grand Total</th>
                        <th class="pb-4">Status</th>
                        <th class="pb-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-bold">
                    @forelse($orders as $order)
                        @php
                            $statusClass = match(strtoupper($order->status ?? 'NEW')) {
                                'DELIVERED' => 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400',
                                'SHIPPED'   => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                                'CONFIRMED' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                                'CANCELLED' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                                default     => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4">
                                <span class="font-black text-slate-900 dark:text-white block">{{ $order->invoice_no }}</span>
                                @if($order->remarks)
                                    <span class="text-[10px] text-slate-400 font-semibold">{{ Str::limit($order->remarks, 30) }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                            <td class="py-4">
                                <span class="font-black text-slate-800 dark:text-slate-200 block">{{ $order->customer_name ?: ($order->customer->name ?? 'Online Guest') }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $order->customer_phone ?: ($order->customer->phone ?? 'No phone') }}</span>
                            </td>
                            <td class="py-4 text-slate-600 dark:text-slate-300">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-black">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </span>
                            </td>
                            <td class="py-4">
                                <span class="font-black text-slate-900 dark:text-white text-sm">₹{{ number_format($order->grand_total, 2) }}</span>
                                <span class="block text-[9px] uppercase tracking-wider text-slate-400">{{ $order->payment_mode ?: 'ONLINE' }}</span>
                            </td>
                            <td class="py-4">
                                <form method="POST" action="{{ route('tenant.website-orders.status', $order->id) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" 
                                            class="text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-xl border-none outline-none cursor-pointer {{ $statusClass }}">
                                        @foreach(['NEW', 'CONFIRMED', 'SHIPPED', 'DELIVERED', 'CANCELLED'] as $st)
                                            <option value="{{ $st }}" {{ strtoupper($order->status ?? 'NEW') === $st ? 'selected' : '' }}>
                                                {{ $st }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('tenant.billing.invoice.view', $order->id) }}" class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition" title="Print Invoice">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="opacity-40">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <p class="font-bold text-sm">No website orders found</p>
                                    <p class="text-xs">Orders placed on your storefront website will appear here automatically.</p>
                                    <a :href="websiteUrl" target="_blank" class="mt-2 text-indigo-600 dark:text-indigo-400 font-bold hover:underline flex items-center gap-1">
                                        <span>Visit Storefront Website</span>
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
