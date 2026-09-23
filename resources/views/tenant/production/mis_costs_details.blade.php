@extends('layouts.tenant')
@section('title', 'MIS Cost Details')
@section('page-title', 'MIS Cost Analytics')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    
    {{-- HEADER BLOCK --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">
                <a href="{{ route('tenant.production.index') }}" class="hover:text-blue-600 transition-colors">Production</a>
                <span>/</span>
                <a href="{{ route('tenant.production.mis-costs') }}" class="hover:text-blue-600 transition-colors">MIS Costs</a>
                <span>/</span>
                <span class="text-slate-900 dark:text-white">Details</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                @if($type === 'all')
                    Detailed Cost Ledger
                @elseif($type === 'average')
                    Average Cost Analysis
                @elseif($type === 'category')
                    Category Expenditure Breakdown
                @elseif($type === 'latest')
                    Latest Entry Overview
                @endif
            </h1>
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">
                @if($type === 'all')
                    Comprehensive database of all manufacturing costs logged across time
                @elseif($type === 'average')
                    Statistical distribution and benchmark calculations of production costs
                @elseif($type === 'category')
                    Lifetime consumption metrics and highest cost centers
                @elseif($type === 'latest')
                    Audit of the most recent manufacturing cost transaction
                @endif
            </p>
        </div>
        
        <div>
            <a href="{{ route('tenant.production.mis-costs') }}" 
               class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to MIS Dashboard
            </a>
        </div>
    </div>

    {{-- INTERACTIVE TAB BAR --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'all']) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'all' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            📊 Detailed Ledger
        </a>
        <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'average']) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'average' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            📈 Averages & Benchmarks
        </a>
        <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'category']) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'category' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            🔥 Category Breakdown
        </a>
        <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'latest']) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'latest' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            🎯 Latest Entry Receipt
        </a>
    </div>

    {{-- DETAILED LEDGER VIEW --}}
    @if($type === 'all')
        <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Historical Cost Ledger</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Exportable & searchable directory of cost statements</p>
                </div>
                
                <div class="flex gap-3">
                    <input type="text" id="ledgerSearch" placeholder="Search entries or remarks..." 
                           class="bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white border-none rounded-xl px-4 py-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 w-64 text-black">
                    
                    <button onclick="downloadCSV()" 
                            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                        📥 Export to CSV
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                <table class="w-full text-left border-collapse min-w-[1500px]" id="ledgerTable">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Entry Date</th>
                            <th class="px-5 py-4 text-[10px] font-black text-violet-500 uppercase tracking-widest">📅 Shift</th>
                            <th class="px-5 py-4 text-[10px] font-black text-amber-500 uppercase tracking-widest">🛍️ Bags Count</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">⚡ Electricity (Rs)</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🔌 Power Units</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">💧 Water Bill</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">📦 Raw Material</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">👷 Labour Charge</th>
                            <th class="px-5 py-4 text-[10px] font-black text-rose-500 uppercase tracking-widest">❤️ Welfare</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🔧 Machine Maint.</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🛍️ Packing Cost</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🚚 Transport/Load</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🗑️ Wastage Cost</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">🌐 Other Exp.</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">💰 Total Cost</th>
                            <th class="px-5 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Remarks / Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($costs as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-4 text-xs font-black text-slate-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($item->month)->format('d F Y') }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">
                                    {{ $item->shift ?? 'B SHIFT ONLY' }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $item->bags_40kg ?? 0 }} bags ({{ number_format($item->total_kg, 0) }} kg)
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->electricity, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $item->power_units ?? 0 }} units
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->water_bill, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->raw_material, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->labour_charge, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->welfare, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->machine_maintenance, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->packing_cost, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->transport_loading, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->wastage_cost, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    ₹{{ number_format($item->other_expenses, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs font-black text-blue-600 dark:text-blue-400">
                                    ₹{{ number_format($item->total, 2) }}
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $item->notes ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-5 py-12 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    No logged cost entries found in the database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Client-side instant filter
            document.getElementById('ledgerSearch').addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#ledgerTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if(text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Fast local CSV exporter
            function downloadCSV() {
                let csv = 'Entry Date,Shift,Bags Count,Electricity (INR),Power Units,Water Bill (INR),Raw Material (INR),Labour Charge (INR),Welfare (INR),Machine Maintenance (INR),Packing Cost (INR),Transport/Loading (INR),Wastage Cost (INR),Other Expenses (INR),Total Cost (INR),Remarks\n';
                const rows = document.querySelectorAll('#ledgerTable tbody tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if(cols.length > 1) {
                        const line = Array.from(cols).map(col => {
                            let data = col.textContent.trim().replace(/₹/g, '').replace(/,/g, '');
                            return `"${data}"`;
                        }).join(',');
                        csv += line + '\n';
                    }
                });
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.setAttribute('href', url);
                a.setAttribute('download', 'Manufacturing_Costs_Ledger.csv');
                a.click();
            }
        </script>
    @endif

    {{-- AVERAGES AND BENCHMARKS VIEW --}}
    @if($type === 'average')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Metrics Breakdown Column --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Statistical Benchmarks</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Calculated baseline values based on {{ $costs->count() }} active statements</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $avgElectricity = $costs->average('electricity');
                            $avgWater = $costs->average('water_bill');
                            $avgMaterial = $costs->average('raw_material');
                            $avgLabour = $costs->average('labour_charge');
                            $avgWelfare = $costs->average('welfare');
                            $avgMaintenance = $costs->average('machine_maintenance');
                            $avgPacking = $costs->average('packing_cost');
                            $avgTransport = $costs->average('transport_loading');
                            $avgWastage = $costs->average('wastage_cost');
                            $avgOther = $costs->average('other_expenses');
                        @endphp
                        {{-- Avg Electricity --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">⚡ Avg Electricity</p>
                            <p class="text-xl font-black text-yellow-600 dark:text-yellow-400">₹{{ number_format($avgElectricity, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-yellow-400 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgElectricity / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Water --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">💧 Avg Water Bill</p>
                            <p class="text-xl font-black text-blue-600 dark:text-blue-400">₹{{ number_format($avgWater, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-blue-400 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgWater / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Material --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">📦 Avg Raw Material</p>
                            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($avgMaterial, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-emerald-400 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgMaterial / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Labour --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">👷 Avg Labour Charge</p>
                            <p class="text-xl font-black text-orange-600 dark:text-orange-400">₹{{ number_format($avgLabour, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-orange-400 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgLabour / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Welfare --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">❤️ Avg Welfare</p>
                            <p class="text-xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($avgWelfare, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-rose-400 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgWelfare / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Maintenance --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">🔧 Avg Machine Maint.</p>
                            <p class="text-xl font-black text-purple-600 dark:text-purple-400">₹{{ number_format($avgMaintenance, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-purple-500 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgMaintenance / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Packing --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">🛍️ Avg Packing Cost</p>
                            <p class="text-xl font-black text-pink-600 dark:text-pink-400">₹{{ number_format($avgPacking, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-pink-500 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgPacking / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Transport --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">🚚 Avg Transport/Load</p>
                            <p class="text-xl font-black text-cyan-600 dark:text-cyan-400">₹{{ number_format($avgTransport, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-cyan-500 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgTransport / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Wastage --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">🗑️ Avg Wastage Cost</p>
                            <p class="text-xl font-black text-red-600 dark:text-red-400">₹{{ number_format($avgWastage, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-red-500 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgWastage / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Avg Other --}}
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border-slate-100 dark:border-slate-800 col-span-1 md:col-span-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">🌐 Avg Other Expenses</p>
                            <p class="text-xl font-black text-slate-600 dark:text-slate-400">₹{{ number_format($avgOther, 2) }}</p>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                <div class="bg-slate-500 h-1.5" style="width: {{ $avgMonthly > 0 ? ($avgOther / $avgMonthly) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operational Analytics Column --}}
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Overall Performance</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Consolidated monthly operating baseline</p>
                    
                    <div class="space-y-4">
                        <div class="p-5 bg-violet-50/50 dark:bg-violet-900/10 rounded-[1.5rem] border border-violet-100 dark:border-violet-800/30">
                            <p class="text-[10px] font-black text-violet-500 uppercase tracking-widest mb-1">Normalized Average Monthly Cost</p>
                            <p class="text-3xl font-black text-violet-600 dark:text-violet-400 tracking-tight">₹{{ number_format($avgMonthly, 0) }}</p>
                        </div>

                        <div class="p-5 bg-slate-50 dark:bg-slate-850 rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Highest Single Recorded Entry</p>
                            <p class="text-lg font-black text-slate-800 dark:text-white mt-1">₹{{ number_format($peakRecord ? $peakRecord->total : 0, 0) }}</p>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase font-black mt-1">On {{ $peakDate }}</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 text-[10px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide leading-relaxed">
                    💡 <strong class="text-slate-600 dark:text-slate-300">Operational Target:</strong> Keep raw materials and labour costs below 75% of your combined baseline metrics.
                </div>
            </div>
        </div>
    @endif

    {{-- CATEGORY EXPENDITURE BREAKDOWN VIEW --}}
    @if($type === 'category')
        <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-8">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Lifetime Category Breakdown</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Visualizing cumulative shares of manufacturing expenses across all recorded transactions</p>
            </div>

            @php
                $grandTotal = array_sum($categories);
                $electricityPercent = $grandTotal > 0 ? ($categories['electricity'] / $grandTotal) * 100 : 0;
                $waterPercent = $grandTotal > 0 ? ($categories['water_bill'] / $grandTotal) * 100 : 0;
                $materialPercent = $grandTotal > 0 ? ($categories['raw_material'] / $grandTotal) * 100 : 0;
                $labourPercent = $grandTotal > 0 ? ($categories['labour_charge'] / $grandTotal) * 100 : 0;
                $welfarePercent = $grandTotal > 0 ? ($categories['welfare'] / $grandTotal) * 100 : 0;
                $maintenancePercent = $grandTotal > 0 ? ($categories['machine_maintenance'] / $grandTotal) * 100 : 0;
                $packingPercent = $grandTotal > 0 ? ($categories['packing_cost'] / $grandTotal) * 100 : 0;
                $transportPercent = $grandTotal > 0 ? ($categories['transport_loading'] / $grandTotal) * 100 : 0;
                $wastagePercent = $grandTotal > 0 ? ($categories['wastage_cost'] / $grandTotal) * 100 : 0;
                $otherPercent = $grandTotal > 0 ? ($categories['other_expenses'] / $grandTotal) * 100 : 0;
            @endphp

            <div class="space-y-6">
                {{-- Electricity Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-yellow-600 dark:text-yellow-400">⚡ Electricity</span>
                        <span class="text-slate-900 dark:text-white">₹{{ number_format($categories['electricity'], 0) }} ({{ round($electricityPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-yellow-400 h-full rounded-full" style="width: {{ $electricityPercent }}%"></div>
                    </div>
                </div>

                {{-- Water Bill Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-blue-600 dark:text-blue-400">💧 Water Bill</span>
                        <span class="text-slate-900 dark:text-white">₹{{ number_format($categories['water_bill'], 0) }} ({{ round($waterPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-blue-400 h-full rounded-full" style="width: {{ $waterPercent }}%"></div>
                    </div>
                </div>

                {{-- Raw Material Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-emerald-600 dark:text-emerald-400">📦 Raw Materials</span>
                        <span class="text-slate-900 dark:text-white">₹{{ number_format($categories['raw_material'], 0) }} ({{ round($materialPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-emerald-400 h-full rounded-full" style="width: {{ $materialPercent }}%"></div>
                    </div>
                </div>

                {{-- Labour Charge Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-orange-600 dark:text-orange-400 font-bold font-bold">👷 Labour Charge</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['labour_charge'], 0) }} ({{ round($labourPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-orange-400 h-full rounded-full" style="width: {{ $labourPercent }}%"></div>
                    </div>
                </div>

                {{-- Welfare Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-rose-600 dark:text-rose-400 font-bold">❤️ Welfare Expense</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['welfare'], 0) }} ({{ round($welfarePercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-rose-400 h-full rounded-full" style="width: {{ $welfarePercent }}%"></div>
                    </div>
                </div>

                {{-- Machine Maintenance Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-purple-600 dark:text-purple-400 font-bold font-bold">🔧 Machine Maintenance</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['machine_maintenance'], 0) }} ({{ round($maintenancePercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-purple-500 h-full rounded-full" style="width: {{ $maintenancePercent }}%"></div>
                    </div>
                </div>

                {{-- Packing Cost Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-pink-600 dark:text-pink-400 font-bold font-bold">🛍️ Packing Cost</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['packing_cost'], 0) }} ({{ round($packingPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-pink-500 h-full rounded-full" style="width: {{ $packingPercent }}%"></div>
                    </div>
                </div>

                {{-- Transport/Loading Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-cyan-600 dark:text-cyan-400 font-bold font-bold">🚚 Transport/Loading Cost</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['transport_loading'], 0) }} ({{ round($transportPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-cyan-500 h-full rounded-full" style="width: {{ $transportPercent }}%"></div>
                    </div>
                </div>

                {{-- Wastage Cost Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-red-600 dark:text-red-400 font-bold font-bold">🗑️ Wastage Cost</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['wastage_cost'], 0) }} ({{ round($wastagePercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-red-500 h-full rounded-full" style="width: {{ $wastagePercent }}%"></div>
                    </div>
                </div>

                {{-- Other Expenses Progress --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-black uppercase tracking-wider">
                        <span class="text-slate-600 dark:text-slate-400 font-bold font-bold">🌐 Other Expenses</span>
                        <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($categories['other_expenses'], 0) }} ({{ round($otherPercent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                        <div class="bg-slate-500 h-full rounded-full" style="width: {{ $otherPercent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight uppercase mb-2">Highest Individual Category Peak</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        The single highest expense category recorded is <strong class="text-rose-500">{{ $highestCategoryLabel }}</strong> which reached a transaction peak on <span class="text-slate-800 dark:text-white font-bold">{{ $highestCategoryDate }}</span>.
                    </p>
                </div>

                <div class="flex items-center justify-end">
                    <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-2xl">
                        <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest block mb-0.5">Peak Target Date</span>
                        <span class="text-lg font-black text-slate-900 dark:text-white">{{ $highestCategoryDate }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- LATEST ENTRY DETAIL VIEW --}}
    @if($type === 'latest')
        @if($latestRecord)
            @php
                $latestTotal = $latestRecord->total;
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- DIGITAL RECEIPT CARD --}}
                <div class="lg:col-span-2 glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-bl-[10rem] flex items-center justify-center">
                        <span class="text-3xl text-emerald-500 font-bold">✓</span>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                Transaction Receipt
                            </span>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-3">Manufacturing Statement Details</h2>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Date: {{ \Carbon\Carbon::parse($latestRecord->month)->format('d F Y') }}</p>
                        </div>

                        <div class="border-t border-b border-slate-100 dark:border-slate-800 py-6 space-y-4">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">⚡ Electricity Bill</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->electricity, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">💧 Water Utility</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->water_bill, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">📦 Raw Material Purchases</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->raw_material, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">👷 Labour Wages</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->labour_charge, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">❤️ Welfare Expense</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->welfare, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs border-t border-dashed border-slate-100 dark:border-slate-800 pt-3">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">📅 Shift Name</span>
                                <span class="text-violet-600 dark:text-violet-400 font-black uppercase">{{ $latestRecord->shift ?? 'B SHIFT ONLY' }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🛍️ Bags Produced</span>
                                <span class="text-slate-900 dark:text-white font-black">{{ $latestRecord->bags_40kg ?? 0 }} bags ({{ number_format($latestRecord->total_kg, 0) }} kg)</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🎯 Target Achievement</span>
                                <span class="text-slate-900 dark:text-white font-black">{{ number_format($latestRecord->achievement_percent, 2) }}% (vs {{ $latestRecord->target_bags }})</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">⏱️ Downtime</span>
                                <span class="text-rose-500 font-bold">{{ $latestRecord->downtime ?? 'Not mentioned' }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs pb-3">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">👥 Worker Count</span>
                                <span class="text-slate-900 dark:text-white font-black">{{ $latestRecord->labour_count ?? 0 }} workers</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🔧 Machine Maintenance</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->machine_maintenance, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🛍️ Packing Cost</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->packing_cost, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🚚 Transport/Loading</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->transport_loading, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🗑️ Wastage Cost</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->wastage_cost, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">🌐 Other Expenses</span>
                                <span class="text-slate-900 dark:text-white font-black">₹{{ number_format($latestRecord->other_expenses, 2) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-850 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Total Cost Incurred</span>
                                <span class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">₹{{ number_format($latestTotal, 2) }}</span>
                            </div>
                            
                            <div class="text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Logged By</span>
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase">{{ $latestRecord->creator ? $latestRecord->creator->name : 'Administrator' }}</span>
                            </div>
                        </div>

                        @if($latestRecord->notes)
                            <div class="p-5 bg-slate-50 dark:bg-slate-850 border-l-4 border-blue-500 rounded-r-2xl">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Transaction remarks</span>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 italic">"{{ $latestRecord->notes }}"</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SIDE ACTION CONTROLS --}}
                <div class="space-y-6">
                    <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider mb-2">Manage Transaction</h3>
                            <p class="text-xs text-slate-400 font-bold mb-6">Modify or correct this specific manufacturing cost entry</p>

                            <div class="space-y-3">
                                <a href="{{ route('tenant.production.mis-costs', ['date' => \Carbon\Carbon::parse($latestRecord->month)->format('Y-m-d')]) }}" 
                                   class="w-full py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-blue-700 transition-all text-center block">
                                    ✏️ Quick Edit Entry
                                </a>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-6">
                            Verified Ledger Record • Timestamp: {{ $latestRecord->created_at ? $latestRecord->created_at->format('d M Y H:i') : now()->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="glass-card rounded-[2.5rem] p-12 bg-white dark:bg-slate-900 border border-white/20 shadow-xl text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No transaction statements have been logged yet.</p>
            </div>
        @endif
    @endif

</div>
@endsection
