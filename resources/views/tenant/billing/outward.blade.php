@extends('layouts.tenant')
@section('title', 'Outward Management')
@section('page-title', 'Outward Management')

@section('content')
<div class="space-y-6 md:space-y-8" id="outwardManager" x-data="outwardForm()" x-cloak>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight dark:text-white">Outward Management</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Stock Issue & Sales Dispatch.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button @click="toggleForm(true)" class="w-full md:w-auto bg-fuchsia-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-fuchsia-200 hover:scale-[1.02] transition dark:shadow-none">
                + New Outward
            </button>
            <a href="{{ route('tenant.billing.outward.export') }}" class="w-full md:w-auto bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-4 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2 justify-center">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Export
            </a>
        </div>
    </div>

    <div x-show="!showNewForm" x-transition>
        {{-- Filters --}}
        <div class="glass-card p-4 rounded-2xl bg-white/50 dark:bg-slate-900/50 backdrop-blur-md border border-slate-100 dark:border-slate-800 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Search</label>
                    <input type="text" placeholder="Product / Customer / Invoice" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-fuchsia-100 dark:focus:ring-fuchsia-900 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">From Date</label>
                    <input type="date" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-fuchsia-100 dark:focus:ring-fuchsia-900 outline-none text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">To Date</label>
                    <input type="date" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-fuchsia-100 dark:focus:ring-fuchsia-900 outline-none text-slate-900 dark:text-white">
                </div>
                <div class="flex gap-2 lg:col-span-2 justify-end">
                    <button class="bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/30 dark:text-fuchsia-400 px-6 py-2.5 rounded-xl font-bold text-xs hover:bg-fuchsia-200 transition">
                        🔍 Search
                    </button>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="glass-card p-5 rounded-2xl flex flex-col gap-2 bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Today's Orders</p>
                <p class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white">{{ $summary['today_orders'] }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl flex flex-col gap-2 bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Today's Qty</p>
                <p class="text-2xl md:text-3xl font-black text-blue-600">{{ $summary['today_qty'] }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl flex flex-col gap-2 bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 border-l-4 border-l-amber-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending Orders</p>
                <p class="text-2xl md:text-3xl font-black text-amber-600">{{ $summary['pending_orders'] }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl flex flex-col gap-2 bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Value</p>
                <p class="text-2xl md:text-3xl font-black text-emerald-600">₹ {{ number_format($summary['total_value'], 2) }}</p>
            </div>
        </div>

        {{-- Outward List --}}
        <div class="glass-card rounded-[2rem] overflow-hidden bg-white/50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80">
            <div class="p-5 md:p-6 border-b border-slate-100 dark:border-slate-800/80 flex justify-between items-center">
                <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Outward List</h3>
            </div>
            @if($outwards->isEmpty())
                <div class="p-10 text-center text-slate-500 dark:text-slate-400 text-sm font-bold">
                    No outward records found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/40">
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Date / Outward No</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Details</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Value</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            @foreach($outwards as $outward)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition">
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($outward->bill_date)->format('d-m-Y') }}</p>
                                    <p class="text-[10px] font-black text-fuchsia-600">{{ $outward->invoice_no }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $outward->customer_name ?: 'Unknown' }}</p>
                                    <p class="text-[10px] font-bold text-slate-500">{{ $outward->remarks ?: '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Items: {{ $outward->items->count() }}</p>
                                    <p class="text-[10px] font-bold text-slate-500">Qty: {{ $outward->items->sum('quantity') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-slate-900 dark:text-white">₹ {{ number_format($outward->grand_total, 2) }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $outward->status == 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ ucfirst($outward->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('tenant.billing.invoice.view', $outward->id) }}" target="_blank" class="p-2 text-slate-400 hover:text-blue-500 transition inline-block">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- New Outward Form --}}
    <div x-show="showNewForm" x-transition style="display: none;">
        <div class="flex items-center gap-3 mb-6">
            <button @click="toggleForm(false)" class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:scale-105 transition">
                <svg width="20" height="20" class="text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </button>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New Outward</h2>
        </div>
        {{-- Form Container --}}
        <div class="space-y-6 mt-6">
            {{-- Basic Information (Full Width) --}}
            <div class="glass-card p-6 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Outward No</label>
                            <input type="text" value="AUTO GENERATED" disabled class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Date</label>
                            <input type="date" x-model="form.date" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fuchsia-100 outline-none text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="form.customer_name" placeholder="Enter Customer Name" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fuchsia-100 outline-none text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Warehouse</label>
                            <input type="text" x-model="form.warehouse" placeholder="Enter Warehouse" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fuchsia-100 outline-none text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Invoice No (Optional)</label>
                            <input type="text" x-model="form.invoice_no" placeholder="Enter Invoice No" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fuchsia-100 outline-none text-slate-900 dark:text-white">
                        </div>
                        <div class="lg:col-span-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Remarks</label>
                            <input type="text" x-model="form.remarks" placeholder="Enter any notes..." class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fuchsia-100 outline-none text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Products --}}
                <div class="glass-card rounded-2xl overflow-hidden bg-white/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Product Details</h3>
                        <button @click="addItem()" class="bg-slate-800 text-white dark:bg-slate-700 px-4 py-2 rounded-xl text-xs font-bold hover:bg-slate-700 transition">
                            + Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-950/40">
                                    <th class="px-4 py-3 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest min-w-[200px]">Product Name</th>
                                    <th class="px-4 py-3 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest min-w-[120px]">Batch</th>
                                    <th class="px-4 py-3 text-center text-[9px] font-black text-slate-400 uppercase tracking-widest w-24">Qty</th>
                                    <th class="px-4 py-3 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest w-28">Rate (₹)</th>
                                    <th class="px-4 py-3 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest w-24">GST (%)</th>
                                    <th class="px-4 py-3 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest w-32">Total (₹)</th>
                                    <th class="px-4 py-3 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="px-4 py-3 relative">
                                            <input type="text" x-model="item.product_name" @input="searchProduct($event.target.value, index)" @focus="searchProduct($event.target.value, index)" @click.away="item.showSuggestions = false" placeholder="Search or type product..." class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-fuchsia-100">
                                            <div x-show="item.showSuggestions && item.suggestions?.length > 0" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl overflow-hidden max-h-40 overflow-y-auto">
                                                <template x-for="p in item.suggestions" :key="p.id">
                                                    <div @click="selectProduct(p, index)" class="px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors border-b border-slate-100 dark:border-slate-700 last:border-0">
                                                        <div class="text-[11px] font-bold text-slate-900 dark:text-white" x-text="p.product_name"></div>
                                                        <div class="text-[9px] font-bold text-slate-400" x-text="(p.brand || 'No Brand') + ' | ₹' + (p.dealer_price || 0)"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" x-model="item.batch" placeholder="Batch No" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-fuchsia-100">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" x-model.number="item.qty" @input="calculateTotals()" min="1" class="w-16 text-center bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-2 text-xs font-black outline-none focus:ring-2 focus:ring-fuchsia-100 text-slate-900 dark:text-white">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model.number="item.rate" @input="calculateTotals()" min="0" class="w-full text-right bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-fuchsia-100 text-slate-900 dark:text-white" placeholder="0.00">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model.number="item.gst_percent" @input="calculateTotals()" min="0" class="w-full text-right bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-fuchsia-100 text-slate-900 dark:text-white" placeholder="0">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="text" :value="item.total.toFixed(2)" readonly class="w-full text-right bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-3 py-2 text-xs font-black text-slate-500 outline-none">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button @click="removeItem(index)" class="text-rose-400 hover:text-rose-600 transition p-1">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-xs font-bold text-slate-400">
                                        No items added. Click "+ Add Row" to begin.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>

            {{-- Bottom Row: Payment Summary (Full Width Bar) --}}
            <div class="glass-card p-6 md:p-8 rounded-2xl bg-slate-900 text-white relative overflow-hidden shadow-2xl flex flex-col xl:flex-row items-center justify-between gap-8 mt-6">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-fuchsia-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                
                {{-- Breakdown --}}
                <div class="flex flex-wrap items-center justify-center xl:justify-start gap-6 md:gap-10 w-full xl:w-auto z-10">
                    <div class="text-center xl:text-left">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subtotal</span>
                        <span class="font-bold text-lg" x-text="'₹ ' + summary.subtotal.toFixed(2)"></span>
                    </div>
                    <div class="hidden md:block w-px h-10 bg-slate-700/50"></div>
                    <div class="text-center xl:text-left">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Discount (₹)</span>
                        <input type="number" x-model.number="summary.discount" @input="calculateTotals()" class="w-24 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-sm font-bold outline-none text-rose-400 text-center xl:text-left">
                    </div>
                    <div class="hidden md:block w-px h-10 bg-slate-700/50"></div>
                    <div class="text-center xl:text-left">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">GST Total</span>
                        <span class="font-bold text-lg" x-text="'₹ ' + summary.gst.toFixed(2)"></span>
                    </div>
                    <div class="hidden md:block w-px h-10 bg-slate-700/50"></div>
                    <div class="text-center xl:text-left">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Round Off</span>
                        <span class="font-bold text-lg" x-text="'₹ ' + summary.roundoff.toFixed(2)"></span>
                    </div>
                </div>
                
                {{-- Grand Total & Actions --}}
                <div class="flex flex-col md:flex-row items-center gap-6 w-full xl:w-auto z-10 bg-slate-800/50 p-4 md:p-5 rounded-2xl border border-slate-700/50">
                    <div class="text-center md:text-right min-w-[120px]">
                        <span class="block text-[10px] font-black text-fuchsia-400 uppercase tracking-widest mb-1">Grand Total</span>
                        <span class="text-3xl font-black text-white tracking-tight" x-text="'₹ ' + summary.grand_total.toFixed(2)"></span>
                    </div>
                    <div class="w-full md:w-px h-px md:h-12 bg-slate-700/50"></div>
                    <div class="flex flex-wrap items-center justify-center gap-3 w-full md:w-auto">
                        <button @click="saveOutward()" class="flex-1 md:flex-none bg-fuchsia-600 hover:bg-fuchsia-500 text-white px-8 py-3 md:py-4 rounded-xl font-black text-xs uppercase tracking-widest transition shadow-lg shadow-fuchsia-900/50">
                            <span x-show="!isSaving">⚡ Generate Invoice</span>
                            <span x-show="isSaving">⏳ Generating...</span>
                        </button>
                        <button @click="toggleForm(false)" :disabled="isSaving" class="flex-1 md:flex-none bg-transparent hover:bg-white/10 text-slate-300 px-6 py-3 md:py-4 rounded-xl font-bold text-xs uppercase tracking-widest transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Modal (AlpineJS) --}}
        <div x-show="showInvoiceModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.away="closeInvoiceModal()" class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 border border-slate-100 dark:border-slate-800 animate-in zoom-in duration-300">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg width="32" height="32" class="text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Invoice Generated!</h3>
                    <p class="text-sm font-bold text-slate-500 mt-1" x-text="generatedInvoiceData?.invoice_no"></p>
                </div>
                
                <div class="bg-slate-50 dark:bg-slate-950/50 rounded-xl p-5 mb-6 space-y-3 text-sm">
                    <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-3">
                        <span class="font-bold text-slate-500">Invoice No</span>
                        <span class="font-black text-slate-900 dark:text-white" x-text="generatedInvoiceData?.invoice_no"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="font-bold text-slate-500">Grand Total</span>
                        <span class="text-xl font-black text-emerald-600" x-text="'₹ ' + (parseFloat(generatedInvoiceData?.grand_total) || 0).toFixed(2)"></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <button @click="markForDelivery()" class="bg-amber-500 hover:bg-amber-600 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl transition shadow-lg shadow-amber-500/30">Mark for Delivery</button>
                    <button @click="printInvoice('pos')" class="bg-violet-600 hover:bg-violet-700 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl transition shadow-lg shadow-violet-500/30">Print POS</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="printInvoice('a4')" class="bg-blue-600 hover:bg-blue-700 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl transition shadow-lg shadow-blue-500/30">Print A4 Invoice</button>
                    <button @click="closeInvoiceModal()" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-black text-[10px] uppercase tracking-widest py-3 rounded-xl transition border border-slate-200 dark:border-slate-700">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function outwardForm() {
        return {
            showNewForm: false,
            isSaving: false,
            showInvoiceModal: false,
            generatedInvoiceData: null,
            products: @json($products),
            form: {
                date: '{{ date('Y-m-d') }}',
                customer_name: '',
                customer_phone: '',
                warehouse: 'Main Warehouse',
                invoice_no: '',
                remarks: ''
            },
            items: [
                {
                    product_id: '',
                    product_name: '',
                    batch: '',
                    qty: 1,
                    rate: 0,
                    gst_percent: 0,
                    total: 0,
                    showSuggestions: false,
                    suggestions: []
                }
            ],
            summary: {
                subtotal: 0,
                discount: 0,
                gst: 0,
                roundoff: 0,
                grand_total: 0
            },
            toggleForm(show) {
                this.showNewForm = show;
                if(show && this.items.length === 0) {
                    this.addItem();
                }
            },
            addItem() {
                this.items.push({
                    product_id: '',
                    product_name: '',
                    batch: '',
                    qty: 1,
                    rate: 0,
                    gst_percent: 0,
                    total: 0,
                    showSuggestions: false,
                    suggestions: []
                });
            },
            searchProduct(query, index) {
                if (!query || query.length < 2) {
                    this.items[index].showSuggestions = false;
                    return;
                }

                let lowerQuery = query.toLowerCase();
                this.items[index].suggestions = this.products.filter(p => 
                    (p.product_name && p.product_name.toLowerCase().includes(lowerQuery)) ||
                    (p.barcode && p.barcode.toLowerCase().includes(lowerQuery))
                ).slice(0, 8);
                
                this.items[index].showSuggestions = true;
            },
            selectProduct(product, index) {
                let item = this.items[index];
                item.product_id = product.id;
                item.product_name = product.product_name;
                item.rate = parseFloat(product.dealer_price) || 0;
                item.gst_percent = parseFloat(product.gst) || 0;
                
                item.showSuggestions = false;
                this.calculateTotals();
            },
            removeItem(index) {
                this.items.splice(index, 1);
                this.calculateTotals();
            },
            calculateTotals() {
                let subtotal = 0;
                let gstTotal = 0;

                this.items.forEach(item => {
                    let qty = parseFloat(item.qty) || 0;
                    let rate = parseFloat(item.rate) || 0;
                    let gst = parseFloat(item.gst_percent) || 0;

                    let baseTotal = qty * rate;
                    let taxAmount = baseTotal * (gst / 100);
                    
                    item.total = baseTotal + taxAmount;
                    
                    subtotal += baseTotal;
                    gstTotal += taxAmount;
                });

                this.summary.subtotal = subtotal;
                this.summary.gst = gstTotal;
                
                let discount = parseFloat(this.summary.discount) || 0;
                let totalBeforeRound = (subtotal - discount) + gstTotal;
                
                let roundedTotal = Math.round(totalBeforeRound);
                this.summary.roundoff = roundedTotal - totalBeforeRound;
                this.summary.grand_total = roundedTotal;
            },
            
            async saveOutward(printAfter = false) {
                if (this.items.length === 0 || !this.items[0].product_id) {
                    alert('Please add at least one valid product.');
                    return;
                }
                if (!this.form.customer_name) {
                    alert('Customer Name is required.');
                    return;
                }
                
                this.isSaving = true;
                
                let payload = {
                    _token: '{{ csrf_token() }}',
                    bill_type: 'outward',
                    customer_name: this.form.customer_name,
                    customer_phone: this.form.customer_phone || '9999999999', // Dummy phone for outward if not provided
                    payment_mode: 'credit',
                    amount_paid: 0,
                    bill_date: this.form.date,
                    remarks: this.form.remarks + (this.form.invoice_no ? ` | Inv: ${this.form.invoice_no}` : ''),
                    subtotal: this.summary.subtotal,
                    discount_amount: this.summary.discount,
                    gst_amount: this.summary.gst,
                    grand_total: this.summary.grand_total,
                    items: this.items.filter(i => i.product_id).map(i => ({
                        id: i.product_id,
                        qty: i.qty,
                        price: i.rate
                    }))
                };

                try {
                    const res = await fetch('{{ route('tenant.billing.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await res.json();
                    
                    if (data.success) {
                        this.generatedInvoiceData = data;
                        this.showInvoiceModal = true;
                        if (printAfter) {
                            this.printInvoice('a4');
                        }
                    } else if (data.errors) {
                        alert(Object.values(data.errors).flat().join('\n'));
                    } else {
                        alert(data.message || 'Failed to save outward.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Network error while saving.');
                } finally {
                    this.isSaving = false;
                }
            },
            
            printInvoice(type) {
                if (!this.generatedInvoiceData) return;
                if (type === 'pos') {
                    window.open(`/billing/print/${this.generatedInvoiceData.bill_id}`, '_blank');
                } else {
                    window.open(`/billing/invoice/${this.generatedInvoiceData.bill_id}`, '_blank');
                }
            },
            
            async markForDelivery() {
                if (!this.generatedInvoiceData) return;
                try {
                    const response = await fetch(`/billing/pre-orders/${this.generatedInvoiceData.bill_id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: 'Delivery' })
                    });
                    const data = await response.json();
                    if (data.success) {
                        alert('Order marked for delivery');
                        this.closeInvoiceModal();
                    } else {
                        alert('Failed to update status');
                    }
                } catch (e) {
                    alert('Error updating status');
                }
            },
            
            closeInvoiceModal() {
                this.showInvoiceModal = false;
                window.location.reload();
            }
        }
    }
</script>
@endsection
