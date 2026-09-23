@extends('layouts.tenant')
@section('title', 'Real-time Inventory')
@section('page-title', 'Stock Management')

@section('content')
<div class="space-y-6" x-data="{ tab: 'all', search: '' }">
    {{-- Filtering & Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div @click="tab = 'all'" :class="tab === 'all' ? 'ring-2 ring-blue-500 shadow-lg shadow-blue-100 dark:shadow-none' : ''" class="glass-card p-4 rounded-2xl bg-blue-600 text-white cursor-pointer hover:-translate-y-1 transition-all duration-300">
            <p class="text-[9px] font-black text-blue-200 uppercase tracking-widest">Total Valuation</p>
            <p class="text-xl font-black">₹{{ number_format($products->sum(fn($p) => $p->stock * $p->dealer_price), 2) }}</p>
        </div>
        <div @click="tab = 'out'" :class="tab === 'out' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100 dark:shadow-none' : ''" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300">
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Out of Stock</p>
                <p class="text-xl font-black text-rose-600">{{ $products->where('stock', '<=', 0)->count() }} Items</p>
            </div>
        </div>
        <div @click="tab = 'low'" :class="tab === 'low' ? 'ring-2 ring-amber-500 shadow-lg shadow-amber-100 dark:shadow-none' : ''" class="glass-card p-4 rounded-2xl flex items-center gap-3 cursor-pointer hover:-translate-y-1 transition-all duration-300">
             <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Low Stock Alert</p>
                <p class="text-xl font-black text-amber-600">{{ $products->filter(fn($p) => $p->stock > 0 && $p->stock <= $p->low_stock_alert)->count() }} Items</p>
            </div>
        </div>
        <div class="glass-card p-4 rounded-2xl flex items-center gap-3">
            <a href="{{ route('tenant.inventory.history') }}" class="w-full h-full flex flex-col items-center justify-center gap-1 group">
                 <svg width="20" height="20" class="text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                 <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-blue-600 transition">Stock History</span>
            </a>
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="glass-card rounded-[1.5rem] overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Live Stock Ledger</h3>
            <div class="flex items-center gap-3">
                 <div class="flex bg-slate-50 dark:bg-slate-800/50 p-1 rounded-xl border border-slate-100 dark:border-slate-800">
                    <button @click="tab = 'all'" :class="tab === 'all' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase transition-colors">All Items</button>
                    <button @click="tab = 'low'" :class="tab === 'low' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase transition-colors">Low Stock</button>
                    <button @click="tab = 'out'" :class="tab === 'out' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase transition-colors">Out of Stock</button>
                </div>
                <input type="text" x-model="search" placeholder="Search inventory..." class="bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl px-4 py-1.5 text-[10px] focus:ring-2 focus:ring-blue-100 outline-none w-64 dark:text-white transition-all">
            </div>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item & Barcode</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category / Brand</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Current Stock</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Unit Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse ($products as $product)
                        @php
                            $searchString = strtolower($product->product_name . ' ' . $product->barcode . ' ' . $product->brand . ' ' . $product->product_type);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors"
                            x-show="(tab === 'all' || 
                                     (tab === 'low' && {{ $product->stock }} <= {{ $product->low_stock_alert }} && {{ $product->stock }} > 0) || 
                                     (tab === 'out' && {{ $product->stock }} <= 0)) && 
                                     (search === '' || {{ json_encode($searchString) }}.includes(search.toLowerCase()))"
                            x-transition>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-900 dark:text-white leading-tight uppercase">{{ $product->product_name }}</p>
                                <p class="text-[9px] font-black text-blue-600 dark:text-blue-400 tracking-widest">{{ $product->barcode ?: 'MANUAL-SKU' }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $product->product_type }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $product->brand }}</p>
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if($product->stock <= 0)
                                    <span class="px-3 py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 rounded-full text-[9px] font-black uppercase tracking-widest">Out of Stock</span>
                                @elseif($product->stock <= $product->low_stock_alert)
                                    <span class="px-3 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-full text-[9px] font-black uppercase tracking-widest">Low Stock</span>
                                @else
                                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest">In Stock</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <p class="text-base font-black {{ $product->stock <= $product->low_stock_alert ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">{{ $product->stock }}</p>
                                    <p class="text-[8px] font-black text-slate-400 uppercase">Units</p>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-sm font-black text-slate-900 dark:text-white">₹{{ number_format($product->dealer_price, 2) }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Cost Price</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mb-4 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <p class="text-xs font-black uppercase tracking-widest">No inventory records found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

