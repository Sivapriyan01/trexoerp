@extends('layouts.tenant')
@section('title', 'GSTR-1 — Outward Supplies')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-6" x-data="{ tab: 'summary' }">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">GSTR-1 &nbsp;<span class="text-blue-600">Outward Supplies</span></h1>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.billing.gst.download-json', ['month'=>$month,'year'=>$year, 'type'=>'gstr1']) }}"
               class="px-4 py-2 bg-slate-900 text-white text-xs font-black rounded-xl transition uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-slate-100">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                JSON
            </a>
            <a href="{{ route('tenant.billing.gst.gstr3b', ['month'=>$month,'year'=>$year]) }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition uppercase tracking-widest">
                View GSTR-3B
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl transition uppercase tracking-widest">
                Print / Export
            </button>
        </div>
    </div>

    {{-- ── Period Selector ─────────────────────────────────────────── --}}
    <form method="GET" class="glass-card rounded-2xl p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div class="space-y-1">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Month</label>
            <select name="month" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Year</label>
            <select name="year" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-xs font-black rounded-xl hover:bg-blue-700 transition uppercase tracking-widest">
            Apply
        </button>
    </form>

    {{-- ── Summary Cards ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Invoices</p>
            <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $bills->count() }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Taxable Value</p>
            <p class="text-2xl font-black text-blue-600">₹{{ number_format($totalTaxable, 2) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total GST</p>
            <p class="text-2xl font-black text-amber-600">₹{{ number_format($totalGst, 2) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Grand Total</p>
            <p class="text-2xl font-black text-emerald-600">₹{{ number_format($totalGrandTotal, 2) }}</p>
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────────────────── --}}
    <div class="flex gap-4 border-b border-slate-200 dark:border-slate-800 mb-6">
        <button @click="tab='summary'" :class="tab==='summary' ? 'text-blue-600 border-b-2 border-blue-600 font-black' : 'text-slate-400 font-bold'" class="pb-3 text-xs uppercase tracking-widest transition">GST Slab Summary</button>
        <button @click="tab='invoices'" :class="tab==='invoices' ? 'text-blue-600 border-b-2 border-blue-600 font-black' : 'text-slate-400 font-bold'" class="pb-3 text-xs uppercase tracking-widest transition">Invoice-wise Register</button>
    </div>

    {{-- ── GST Slab Summary Table ───────────────────────────────────── --}}
    <div x-show="tab==='summary'" class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                    <th class="px-6 py-4">GST Rate (%)</th>
                    <th class="px-6 py-4 text-right">Invoices</th>
                    <th class="px-6 py-4 text-right">Taxable Value (₹)</th>
                    <th class="px-6 py-4 text-right">CGST (₹)</th>
                    <th class="px-6 py-4 text-right">SGST (₹)</th>
                    <th class="px-6 py-4 text-right">Total GST (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($slabs as $slab)
                <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition">
                    <td class="px-6 py-4 font-black text-slate-800 dark:text-white">{{ $slab['rate'] }}%</td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-slate-600 dark:text-slate-400">{{ $slab['invoices'] }}</td>
                    <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-white">{{ number_format($slab['taxable'], 2) }}</td>
                    <td class="px-6 py-4 text-right font-bold text-amber-600">{{ number_format($slab['cgst'], 2) }}</td>
                    <td class="px-6 py-4 text-right font-bold text-amber-600">{{ number_format($slab['sgst'], 2) }}</td>
                    <td class="px-6 py-4 text-right font-black text-blue-600">{{ number_format($slab['total_gst'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold">No outward supplies found for this period.</td></tr>
                @endforelse
            </tbody>
            @if(count($slabs))
            <tfoot class="bg-slate-50 dark:bg-slate-800/40 border-t-2 border-slate-200 dark:border-slate-700">
                <tr class="font-black text-slate-800 dark:text-white text-sm">
                    <td class="px-6 py-4">TOTAL</td>
                    <td class="px-6 py-4 text-right">{{ $bills->count() }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($totalTaxable, 2) }}</td>
                    <td class="px-6 py-4 text-right text-amber-600">{{ number_format($totalGst / 2, 2) }}</td>
                    <td class="px-6 py-4 text-right text-amber-600">{{ number_format($totalGst / 2, 2) }}</td>
                    <td class="px-6 py-4 text-right text-blue-600">{{ number_format($totalGst, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── Invoice-wise Register ─────────────────────────────────────── --}}
    <div x-show="tab==='invoices'" class="glass-card rounded-2xl overflow-hidden" style="display:none">
        <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                    <th class="px-5 py-4">Invoice No</th>
                    <th class="px-5 py-4">Date</th>
                    <th class="px-5 py-4">Type</th>
                    <th class="px-5 py-4">Customer</th>
                    <th class="px-5 py-4 text-right">Taxable (₹)</th>
                    <th class="px-5 py-4 text-right">GST %</th>
                    <th class="px-5 py-4 text-right">CGST (₹)</th>
                    <th class="px-5 py-4 text-right">SGST (₹)</th>
                    <th class="px-5 py-4 text-right">Grand Total (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($bills as $bill)
                @php
                    $taxable = $bill->subtotal - $bill->discount_amount;
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition text-sm">
                    <td class="px-5 py-3 font-black text-blue-600">{{ $bill->invoice_no }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-slate-400 font-bold">{{ $bill->bill_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            {{ $bill->bill_type ?: 'billing' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-bold">{{ $bill->customer_name ?: 'Walk-in' }}</td>
                    <td class="px-5 py-3 text-right font-bold text-slate-700 dark:text-slate-300">{{ number_format($taxable, 2) }}</td>
                    <td class="px-5 py-3 text-right font-bold text-amber-600">{{ $bill->gst_percent }}%</td>
                    <td class="px-5 py-3 text-right font-bold text-amber-600">{{ number_format($bill->gst_amount / 2, 2) }}</td>
                    <td class="px-5 py-3 text-right font-bold text-amber-600">{{ number_format($bill->gst_amount / 2, 2) }}</td>
                    <td class="px-5 py-3 text-right font-black text-emerald-600">{{ number_format($bill->grand_total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-6 py-12 text-center text-slate-400 font-bold">No invoices found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>
@endsection

