@extends('layouts.tenant')
@section('title', 'GSTR-3B — Monthly Summary Return')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-6">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">GSTR-3B &nbsp;<span class="text-emerald-600">Monthly Summary Return</span></h1>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
            @if($settings['bill_gst_no'])
            <p class="text-xs text-blue-500 font-black mt-0.5">GSTIN: {{ $settings['bill_gst_no'] }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.billing.gst.download-json', ['month'=>$month,'year'=>$year, 'type'=>'gstr3b']) }}"
               class="px-4 py-2 bg-slate-900 text-white text-xs font-black rounded-xl transition uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-slate-100">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                JSON
            </a>
            <a href="{{ route('tenant.billing.gst.gstr1', ['month'=>$month,'year'=>$year]) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl transition uppercase tracking-widest">
                View GSTR-1
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-black rounded-xl transition uppercase tracking-widest">
                Print / Export
            </button>
        </div>
    </div>

    {{-- ── Period Selector ─────────────────────────────────────────── --}}
    <form method="GET" class="glass-card rounded-2xl p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div class="space-y-1">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Month</label>
            <select name="month" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-white">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Year</label>
            <select name="year" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-white">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-black rounded-xl hover:bg-emerald-700 transition uppercase tracking-widest">
            Apply
        </button>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── 3.1 Outward Supplies ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 bg-blue-600 text-white">
                    <h2 class="text-sm font-black uppercase tracking-widest">3.1 — Details of Outward Supplies</h2>
                    <p class="text-[10px] text-blue-200 mt-0.5">Sales / Tax Collected</p>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                            <th class="px-5 py-3">Nature</th>
                            <th class="px-5 py-3 text-right">Taxable Value (₹)</th>
                            <th class="px-5 py-3 text-right">CGST (₹)</th>
                            <th class="px-5 py-3 text-right">SGST (₹)</th>
                            <th class="px-5 py-3 text-right">Total Tax (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3 font-bold text-slate-700 dark:text-slate-300">(a) Taxable Outward Supplies</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">{{ number_format($outwardTaxable, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-amber-600">{{ number_format($outwardCgst, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-amber-600">{{ number_format($outwardSgst, 2) }}</td>
                            <td class="px-5 py-3 text-right font-black text-blue-600">{{ number_format($outwardGst, 2) }}</td>
                        </tr>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/20">
                            <td class="px-5 py-3 font-bold text-slate-500">(b) Zero-rated / Exempt</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-400">0.00</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-400">0.00</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-400">0.00</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-400">0.00</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-blue-50 dark:bg-blue-900/20 border-t-2 border-blue-200 dark:border-blue-700">
                        <tr class="font-black text-sm text-blue-700 dark:text-blue-300">
                            <td class="px-5 py-3">TOTAL OUTWARD TAX</td>
                            <td class="px-5 py-3 text-right">{{ number_format($outwardTaxable, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($outwardCgst, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($outwardSgst, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($outwardGst, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ── 4 ITC ──────────────────────────────────────────── --}}
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600 text-white">
                    <h2 class="text-sm font-black uppercase tracking-widest">4 — Eligible ITC (Input Tax Credit)</h2>
                    <p class="text-[10px] text-emerald-200 mt-0.5">Purchases / Tax Paid to Suppliers</p>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                            <th class="px-5 py-3">Nature</th>
                            <th class="px-5 py-3 text-right">Taxable Value (₹)</th>
                            <th class="px-5 py-3 text-right">CGST (₹)</th>
                            <th class="px-5 py-3 text-right">SGST (₹)</th>
                            <th class="px-5 py-3 text-right">Total ITC (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3 font-bold text-slate-700 dark:text-slate-300">(a) Inputs (Purchases)</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">{{ number_format($itcTaxable, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-600">{{ number_format($itcCgst, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-600">{{ number_format($itcSgst, 2) }}</td>
                            <td class="px-5 py-3 text-right font-black text-emerald-600">{{ number_format($itcGst, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-200 dark:border-emerald-700">
                        <tr class="font-black text-sm text-emerald-700 dark:text-emerald-300">
                            <td class="px-5 py-3">TOTAL ITC AVAILABLE</td>
                            <td class="px-5 py-3 text-right">{{ number_format($itcTaxable, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($itcCgst, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($itcSgst, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($itcGst, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Right Panel — Net Tax Payable ──────────────────────── --}}
        <div class="space-y-4">
            {{-- Net Tax Payable ─────────────────── --}}
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 bg-rose-600 text-white">
                    <h2 class="text-sm font-black uppercase tracking-widest">5.1 — Tax Payable</h2>
                    <p class="text-[10px] text-rose-200 mt-0.5">Net GST liability</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Output CGST</span>
                        <span class="font-black text-blue-600">₹{{ number_format($outwardCgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Less: ITC CGST</span>
                        <span class="font-black text-emerald-600">−₹{{ number_format($itcCgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl px-3">
                        <span class="text-xs font-black text-blue-700 dark:text-blue-300 uppercase tracking-widest">Net CGST</span>
                        <span class="font-black text-blue-700 dark:text-blue-300">₹{{ number_format($netCgst, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3 mt-2">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Output SGST</span>
                        <span class="font-black text-blue-600">₹{{ number_format($outwardSgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Less: ITC SGST</span>
                        <span class="font-black text-emerald-600">−₹{{ number_format($itcSgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl px-3">
                        <span class="text-xs font-black text-blue-700 dark:text-blue-300 uppercase tracking-widest">Net SGST</span>
                        <span class="font-black text-blue-700 dark:text-blue-300">₹{{ number_format($netSgst, 2) }}</span>
                    </div>

                    <div class="mt-4 p-4 bg-rose-50 dark:bg-rose-900/20 rounded-2xl border-2 border-rose-200 dark:border-rose-700 flex justify-between items-center">
                        <span class="text-sm font-black text-rose-700 dark:text-rose-300 uppercase tracking-widest">Total Tax Payable</span>
                        <span class="text-xl font-black text-rose-700 dark:text-rose-300">₹{{ number_format($netTax, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Stats ──────────────────────── --}}
            <div class="glass-card rounded-2xl p-5 space-y-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Period Overview</p>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Sales Invoices</span>
                    <span class="font-black text-slate-800 dark:text-white">{{ $bills->count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Purchase Orders</span>
                    <span class="font-black text-slate-800 dark:text-white">{{ $purchases->count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Sales Revenue</span>
                    <span class="font-black text-emerald-600">₹{{ number_format($outwardGrandTotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">ITC Available</span>
                    <span class="font-black text-blue-600">₹{{ number_format($itcGst, 2) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

