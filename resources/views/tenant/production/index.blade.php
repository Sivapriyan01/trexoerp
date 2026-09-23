@extends('layouts.tenant')
@section('title', 'Production Manager')
@section('page-title', 'Production Workflow')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<div class="space-y-6" x-data="{ 
    showStartModal: false, 
    currentJob: null, 
    workers: [],
    workerInput: '',
    startTime: '{{ date('Y-m-d\TH:i') }}',
    showDetailModal: false,
    detailJob: null,
    openStartModal(jobId, productName) {
        this.currentJob = { id: jobId, name: productName };
        this.workers = [];
        this.showStartModal = true;
    },
    openDetailModal(job) {
        this.detailJob = job;
        this.showDetailModal = true;
    },
    addWorker() {
        if (this.workerInput.trim()) {
            this.workers.push(this.workerInput.trim());
            this.workerInput = '';
        }
    },
    removeWorker(index) {
        this.workers.splice(index, 1);
    }
}">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Manufacturing</h3>
            <p class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Production Workflow</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.production.index', ['stage' => 'Analysis']) }}" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                📊 Analysis
            </a>
            <a href="{{ route('tenant.production.mis-costs') }}" class="px-6 py-3 bg-violet-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-violet-100 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2 flex items-center gap-2">
                ⚡ MIS Cost
            </a>
            <a href="{{ route('tenant.production.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">
                + Create New Job
            </a>
        </div>
    </div>
    <!-- TABS NAVIGATION -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <a href="{{ route('tenant.production.index') }}" 
           class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ !$stage ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            Job List
        </a>
        <a href="{{ route('tenant.production.index', ['stage' => 'Scheduler']) }}" 
           class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ $stage === 'Scheduler' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            Workflow Board
        </a>
        @foreach($availableProcesses as $proc)
            <a href="{{ route('tenant.production.index', ['stage' => $proc]) }}" 
               class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ $stage === $proc ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                {{ $proc }}
            </a>
        @endforeach
        <a href="{{ route('tenant.production.index', ['stage' => 'Completed']) }}" 
           class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ $stage === 'Completed' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            Completed Jobs
        </a>
    </div>

    @if($stage === 'MIS')
        {{-- ════════════════════════════════════════════ MIS COST VIEW ══════════════════════════════════════════ --}}
        @php
            $totalCost   = $costs->sum(fn($c) => $c->electricity + $c->water_bill + $c->raw_material + $c->labour_charge);
            $avgMonthly  = $costs->count() > 0 ? $totalCost / $costs->count() : 0;
            
            // Get the actual latest cost entry (first element since sorted desc)
            $latestCost  = $costs->first();
            $latestTotal = $latestCost ? ($latestCost->electricity + $latestCost->water_bill + $latestCost->raw_material + $latestCost->labour_charge) : 0;
            $latestDate  = $latestCost ? \Carbon\Carbon::parse($latestCost->month)->format('Y-m-d') : null;

            $categories  = [
                'electricity'   => $costs->sum('electricity'),
                'water_bill'    => $costs->sum('water_bill'),
                'raw_material'  => $costs->sum('raw_material'),
                'labour_charge' => $costs->sum('labour_charge'),
            ];
            $highestCategory = collect($categories)->sortDesc()->keys()->first();
            $highestCategoryLabel = match($highestCategory) {
                'electricity'   => 'Electricity',
                'water_bill'    => 'Water Bill',
                'raw_material'  => 'Raw Material',
                'labour_charge' => 'Labour Charge',
                default         => 'N/A',
            };

            // Find the record with the peak overall expenditure
            $peakRecord = $costs->sortByDesc(fn($c) => $c->electricity + $c->water_bill + $c->raw_material + $c->labour_charge)->first();
            $peakDate = $peakRecord ? \Carbon\Carbon::parse($peakRecord->month)->format('Y-m-d') : null;

            // Find the record where the highest category peaked
            $highestCategoryRecord = $highestCategory ? $costs->sortByDesc($highestCategory)->first() : null;
            $highestCategoryDate = $highestCategoryRecord ? \Carbon\Carbon::parse($highestCategoryRecord->month)->format('Y-m-d') : null;
        @endphp

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-400">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="space-y-8">
            
            {{-- ── TRENDS CHART ── --}}
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Cost Trends</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Visualize total manufacturing costs over time</p>
                    </div>
                    
                    <form action="{{ route('tenant.production.mis-costs') }}" method="GET" class="flex flex-wrap items-end gap-3 bg-slate-50 dark:bg-slate-800 p-3 rounded-2xl">
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ $fromDate }}" class="bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-xs font-bold dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ $toDate }}" class="bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-xs font-bold dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-colors">Filter</button>
                    </form>
                </div>

                <div class="w-full h-72">
                    <canvas id="misTrendsChart"></canvas>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById('misTrendsChart').getContext('2d');
                    
                    const labels = {!! json_encode($chartLabels) !!};
                    const data = {!! json_encode($chartData) !!};

                    // Gradient for the line chart
                    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(124, 58, 237, 0.5)'); // violet-600
                    gradient.addColorStop(1, 'rgba(124, 58, 237, 0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Manufacturing Cost (₹)',
                                data: data,
                                borderColor: '#7c3aed', // violet-600
                                backgroundColor: gradient,
                                borderWidth: 3,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#7c3aed',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { size: 11, family: "'Inter', sans-serif" },
                                    bodyFont: { size: 13, weight: 'bold', family: "'Inter', sans-serif" },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            return '₹ ' + context.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false, drawBorder: false },
                                    ticks: { font: { size: 10, family: "'Inter', sans-serif" }, color: '#94a3b8' }
                                },
                                y: {
                                    grid: { color: '#f1f5f9', borderDash: [5, 5], drawBorder: false },
                                    ticks: { 
                                        font: { size: 10, family: "'Inter', sans-serif" }, 
                                        color: '#94a3b8',
                                        callback: function(value) {
                                            if (value >= 1000) return '₹' + (value/1000).toFixed(1) + 'k';
                                            return '₹' + value;
                                        }
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            </script>

            {{-- ── COST ENTRY FORM ── --}}
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Log Manufacturing Cost</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Enter utility & production costs</p>
                    </div>
                    <div class="p-3 bg-violet-50 dark:bg-violet-900/20 rounded-2xl">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-violet-600"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                <form action="{{ route('tenant.production.mis-costs.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Date Picker --}}
                        <div class="lg:col-span-3 flex flex-col md:flex-row md:items-end gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Date *</label>
                                <input type="date" name="month" id="mis_cost_entry_date"
                                    value="{{ isset($currentRecord) ? \Carbon\Carbon::parse($currentRecord->month)->format('Y-m-d') : now()->format('Y-m-d') }}"
                                    class="w-full md:w-64 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-xs font-bold dark:text-white focus:ring-2 focus:ring-violet-500 text-black" required>
                            </div>
                        </div>

                        {{-- Electricity --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-yellow-500">⚡</span> Electricity (₹)
                            </label>
                            <input type="number" name="electricity" step="0.01" min="0"
                                value="{{ $currentRecord->electricity ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-yellow-400 text-black" required>
                        </div>

                        {{-- Water Bill --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-blue-500">💧</span> Water Bill (₹)
                            </label>
                            <input type="number" name="water_bill" step="0.01" min="0"
                                value="{{ $currentRecord->water_bill ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-blue-400 text-black" required>
                        </div>

                        {{-- Raw Material --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-emerald-500">📦</span> Raw Material (₹)
                            </label>
                            <input type="number" name="raw_material" step="0.01" min="0"
                                value="{{ $currentRecord->raw_material ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-emerald-400 text-black" required>
                        </div>

                        {{-- Labour Charge --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-orange-500">👷</span> Labour Charge (₹)
                            </label>
                            <input type="number" name="labour_charge" step="0.01" min="0"
                                value="{{ $currentRecord->labour_charge ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-orange-400 text-black" required>
                        </div>

                        {{-- Machine Maintenance --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-purple-500">🔧</span> Machine Maintenance (₹)
                            </label>
                            <input type="number" name="machine_maintenance" step="0.01" min="0"
                                value="{{ $currentRecord->machine_maintenance ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-purple-400 text-black" required>
                        </div>

                        {{-- Packing Cost --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-pink-500">🛍️</span> Packing Cost (₹)
                            </label>
                            <input type="number" name="packing_cost" step="0.01" min="0"
                                value="{{ $currentRecord->packing_cost ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-pink-400 text-black" required>
                        </div>

                        {{-- Transport/Loading --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-cyan-500">🚚</span> Transport/Loading (₹)
                            </label>
                            <input type="number" name="transport_loading" step="0.01" min="0"
                                value="{{ $currentRecord->transport_loading ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-cyan-400 text-black" required>
                        </div>

                        {{-- Wastage Cost --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-red-500">🗑️</span> Wastage Cost (₹)
                            </label>
                            <input type="number" name="wastage_cost" step="0.01" min="0"
                                value="{{ $currentRecord->wastage_cost ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-red-400 text-black" required>
                        </div>

                        {{-- Other Expenses --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                <span class="text-slate-500">🌐</span> Other Expenses (₹)
                            </label>
                            <input type="number" name="other_expenses" step="0.01" min="0"
                                value="{{ $currentRecord->other_expenses ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-slate-400 text-black" required>
                        </div>

                        {{-- Shift & KPI Section Divider --}}
                        <div class="lg:col-span-3 border-t border-slate-100 dark:border-slate-800 my-2 pt-6">
                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Shift Performance KPI & Labour Costs</h4>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Enter production units, bags count, downtime, power, and labour breakdown for this shift</p>
                        </div>

                        {{-- Shift Name --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                📅 Shift Name
                            </label>
                            <input type="text" name="shift"
                                value="{{ $currentRecord->shift ?? 'B SHIFT ONLY' }}"
                                placeholder="e.g. B SHIFT ONLY"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Output Quantity --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                🛍️ Total Output Quantity
                            </label>
                            <div class="flex items-stretch gap-2" x-data="{ unit: '{{ $currentRecord->qty_unit ?? 'KG' }}' }">
                                <input type="number" name="total_qty" step="0.01" min="0"
                                    value="{{ ($currentRecord->total_qty ?? 0) > 0 ? $currentRecord->total_qty : ($currentRecord->bags_40kg ?? '') }}"
                                    placeholder="Qty"
                                    class="flex-1 min-w-0 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                                <select name="qty_unit" x-model="unit"
                                    class="shrink-0 bg-violet-600 text-white rounded-xl px-2 py-3 text-xs font-black focus:ring-2 focus:ring-violet-400 cursor-pointer border-none">
                                    <option value="KG">KG</option>
                                    <option value="TON">TON</option>
                                    <option value="LITRE">LITRE</option>
                                    <option value="PCS">PCS</option>
                                </select>
                                <input type="number" name="weight_per_pc" step="0.01" min="0.01" value="{{ $currentRecord->weight_per_pc ?? 40 }}"
                                    x-show="unit === 'PCS'"
                                    class="w-16 bg-slate-100 dark:bg-slate-700 border-none rounded-xl px-2 py-3 text-xs font-black dark:text-white text-center" title="KG/PC">
                            </div>
                        </div>

                        {{-- Target Quantity --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                🎯 Target Quantity
                            </label>
                            <input type="number" name="target_bags" min="1"
                                value="{{ $currentRecord->target_bags ?? 414 }}"
                                placeholder="e.g. 414"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Downtime --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                ⏱️ Downtime
                            </label>
                            <input type="text" name="downtime"
                                value="{{ $currentRecord->downtime ?? 'Not mentioned' }}"
                                placeholder="e.g. 1 hour or Not mentioned"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Labour Count --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                👥 Labour Count
                            </label>
                            <input type="number" name="labour_count" min="0"
                                value="{{ $currentRecord->labour_count ?? '' }}"
                                placeholder="e.g. 5"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Welfare Expense (₹) --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                ❤️ Welfare Expense (₹)
                            </label>
                            <input type="number" name="welfare" step="0.01" min="0"
                                value="{{ $currentRecord->welfare ?? '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Power Units Used --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">
                                🔌 Power Units Used
                            </label>
                            <input type="number" name="power_units" step="0.01" min="0"
                                value="{{ $currentRecord->power_units ?? '' }}"
                                placeholder="e.g. 27"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-black dark:text-white focus:ring-2 focus:ring-violet-400 text-black">
                        </div>

                        {{-- Notes --}}
                        <div class="lg:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Notes (optional)</label>
                            <input type="text" name="notes"
                                value="{{ $currentRecord->notes ?? '' }}"
                                placeholder="Any remarks..."
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-xs font-bold dark:text-white focus:ring-2 focus:ring-violet-500 text-black">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-8 py-4 bg-violet-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-violet-100 hover:scale-[1.02] active:scale-95 transition-all">
                            💾 Save Cost Entry
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── SUMMARY CARDS ── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Peak Expenditure Card --}}
                <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'all']) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-violet-500">
                        📊 Click to View Details
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Cost (All)</p>
                    <p class="text-2xl font-black text-violet-600 dark:text-violet-400 tracking-tight">₹{{ number_format($totalCost, 0) }}</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Last {{ $costs->count() }} entries</p>
                </a>

                {{-- Avg Monthly Card --}}
                <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'average']) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-blue-500">
                        ⚡ Click to View Averages
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Monthly</p>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight">₹{{ number_format($avgMonthly, 0) }}</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Per entry average</p>
                </a>

                {{-- Highest Category Peak Card --}}
                <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'category']) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-rose-500">
                        🔥 View Categories
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Highest Category</p>
                    <p class="text-2xl font-black text-rose-500 dark:text-rose-400 tracking-tight">{{ $highestCategoryLabel }}</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">₹{{ number_format($categories[$highestCategory] ?? 0, 0) }}</p>
                </a>

                {{-- Latest Entry Cost Card --}}
                <a href="{{ route('tenant.production.mis-costs.details', ['type' => 'latest']) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-emerald-500">
                        ✅ View Latest Entry
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Latest Entry Cost</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">₹{{ number_format($latestTotal, 0) }}</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">
                        {{ $latestCost ? \Carbon\Carbon::parse($latestCost->month)->format('d M Y') : 'No data' }}
                    </p>
                </a>
            </div>

            {{-- ── SINGLE DATE FILTER BAR ── --}}
            <div class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Cost Analytics by Date</span>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Select a specific date to view its breakdown chart</p>
                    </div>
                </div>
                <form action="{{ route('tenant.production.mis-costs') }}" method="GET" class="flex items-center gap-4">
                    <input type="hidden" name="stage" value="MIS">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select Date</span>
                        <input type="date" id="filter_selected_date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                            class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-xs font-bold dark:text-white shadow-sm text-black">
                    </div>
                </form>
            </div>

            {{-- ── THREE PANELS: SHIFT KPI CARD, BAR CHART & PIE CHART ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Panel 1: Shift KPI Card --}}
                <div class="glass-card rounded-[2.5rem] p-8 bg-slate-950 text-slate-100 border border-slate-800 shadow-xl relative min-h-[380px] font-mono flex flex-col justify-between">
                    <div>
                        <div class="mb-4 text-center">
                            <span class="text-xs font-black text-violet-400 uppercase tracking-widest block">⚡ SHIFT PERFORMANCE KPI</span>
                        </div>

                        @if($currentRecord)
                            <div class="space-y-2 text-xs">
                                <div class="border-t-2 border-b-2 border-dashed border-slate-800 py-2">
                                    <div class="flex justify-between font-bold">
                                        <span>KPI</span>
                                        <span class="text-violet-400">{{ $currentRecord->shift ?? 'B SHIFT ONLY' }}</span>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 mt-0.5">
                                        <span>Date</span>
                                        <span>{{ \Carbon\Carbon::parse($currentRecord->month)->format('d/m/y') }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between font-bold py-1">
                                    <span>Total Output</span>
                                    <span class="text-amber-400">{{ $currentRecord->total_qty > 0 ? $currentRecord->total_qty . ' ' . $currentRecord->qty_unit : ($currentRecord->bags_40kg ?? 0) . ' Bags' }}</span>
                                </div>

                                <div class="border-t border-dashed border-slate-800 py-1.5 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span>Total kg</span>
                                        <span class="text-slate-300">{{ number_format($currentRecord->total_kg, 0) }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span>Achievement % (vs {{ $currentRecord->target_bags }} {{ $currentRecord->qty_unit ?? 'Bags' }})</span>
                                        <span class="{{ $currentRecord->achievement_percent >= 100 ? 'text-emerald-400' : 'text-yellow-400' }}">
                                            {{ number_format($currentRecord->achievement_percent, 2) }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="border-t border-dashed border-slate-800 py-1.5">
                                    <div class="flex justify-between">
                                        <span>Downtime</span>
                                        <span class="text-rose-400 font-bold">{{ $currentRecord->downtime ?? 'Not mentioned' }}</span>
                                    </div>
                                </div>

                                <div class="border-t border-dashed border-slate-800 py-1.5 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span>Labour Count</span>
                                        <span class="text-slate-300">{{ $currentRecord->labour_count ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Labour Wages (Rs)</span>
                                        <span class="text-slate-300">₹{{ number_format($currentRecord->labour_charge, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Welfare (Rs)</span>
                                        <span class="text-slate-300">₹{{ number_format($currentRecord->welfare, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-slate-200 border-t border-slate-900 pt-1">
                                        <span>Total Labour Cost (Rs)</span>
                                        <span>₹{{ number_format($currentRecord->total_labour_cost, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Power Units</span>
                                        <span class="text-slate-300">{{ $currentRecord->power_units ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Power Cost (Rs)</span>
                                        <span class="text-slate-300">₹{{ number_format($currentRecord->electricity, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-violet-400 border-t border-slate-900 pt-1">
                                        <span>Total Cost (Rs)</span>
                                        <span>₹{{ number_format($currentRecord->total_shift_cost, 2) }}</span>
                                    </div>
                                </div>

                                <div class="border-t-2 border-b-2 border-dashed border-slate-800 py-2 space-y-1 text-slate-300">
                                    <div class="flex justify-between">
                                        <span>Labour Cost/kg (Rs)</span>
                                        <span class="text-amber-400 font-bold">₹{{ number_format($currentRecord->labour_cost_per_kg, 4) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Energy Cost/kg (Rs)</span>
                                        <span class="text-blue-400 font-bold">₹{{ number_format($currentRecord->energy_cost_per_kg, 4) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <span>Total Cost/kg (Rs)</span>
                                        <span class="text-violet-400 font-bold">₹{{ number_format($currentRecord->total_cost_per_kg, 4) }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-center p-6 h-64">
                                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center text-slate-600 mb-3 text-lg">⚠️</div>
                                <h4 class="text-sm font-black text-slate-400 uppercase tracking-tight">No Shift Data</h4>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Please log costs above for this date</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Graph 2: Bar Chart — Cost Comparison --}}
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl relative min-h-[380px]">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Cost Comparison</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest" id="barChartSubtitle">Loading comparison...</p>
                    </div>
                    
                    {{-- No Data message overlay --}}
                    <div id="noDataOverlayBar" class="hidden absolute inset-0 flex flex-col items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm rounded-[2.5rem] z-10 text-center p-6">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3 text-lg">⚠️</div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">No Cost Record</h4>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">Please log costs above for this date</p>
                    </div>

                    <div style="height:280px" class="w-full">
                        <canvas id="misCostBarChart"></canvas>
                    </div>
                </div>

                {{-- Graph 3: Pie Chart — Cost Distribution --}}
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl relative min-h-[380px]">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Cost Distribution</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest" id="pieChartSubtitle">Loading chart...</p>
                    </div>
                    
                    {{-- No Data message overlay --}}
                    <div id="noDataOverlayPie" class="hidden absolute inset-0 flex flex-col items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm rounded-[2.5rem] z-10 text-center p-6">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3 text-lg">⚠️</div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">No Cost Record</h4>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">Please log costs above for this date</p>
                    </div>

                    <div style="height:280px" class="w-full flex items-center justify-center">
                        <div class="w-full max-w-[280px] h-full">
                            <canvas id="misCostPieChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COST HISTORY TABLE ── --}}
            @if($costs->count() > 0)
            <div class="glass-card rounded-[2.5rem] border-white/40 shadow-xl overflow-hidden bg-white dark:bg-slate-900">
                <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Cost History</h3>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">All recorded entries</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1400px]">
                        <thead>
                            <tr class="border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-4 py-4 text-[9px] font-black text-violet-500 uppercase tracking-widest">📅 Shift</th>
                                <th class="px-4 py-4 text-[9px] font-black text-amber-500 uppercase tracking-widest">🛍️ Bags Count</th>
                                <th class="px-4 py-4 text-[9px] font-black text-yellow-500 uppercase tracking-widest">⚡ Electricity (Rs)</th>
                                <th class="px-4 py-4 text-[9px] font-black text-yellow-600 uppercase tracking-widest">🔌 Power Units</th>
                                <th class="px-4 py-4 text-[9px] font-black text-blue-500 uppercase tracking-widest">💧 Water Bill</th>
                                <th class="px-4 py-4 text-[9px] font-black text-emerald-500 uppercase tracking-widest">📦 Raw Material</th>
                                <th class="px-4 py-4 text-[9px] font-black text-orange-500 uppercase tracking-widest">👷 Labour Wages</th>
                                <th class="px-4 py-4 text-[9px] font-black text-rose-500 uppercase tracking-widest">❤️ Welfare</th>
                                <th class="px-4 py-4 text-[9px] font-black text-purple-500 uppercase tracking-widest">🔧 Machine Maint</th>
                                <th class="px-4 py-4 text-[9px] font-black text-pink-500 uppercase tracking-widest">🛍️ Packing</th>
                                <th class="px-4 py-4 text-[9px] font-black text-cyan-500 uppercase tracking-widest">🚚 Transport</th>
                                <th class="px-4 py-4 text-[9px] font-black text-red-500 uppercase tracking-widest">🗑️ Wastage</th>
                                <th class="px-4 py-4 text-[9px] font-black text-slate-500 uppercase tracking-widest">🌐 Other Exp</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($costs->sortByDesc('month') as $cost)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="text-xs font-black text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($cost->month)->format('d-m-Y') }}</span>
                                    </td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">{{ $cost->shift ?? 'B SHIFT ONLY' }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $cost->total_qty > 0 ? $cost->total_qty . ' ' . $cost->qty_unit : ($cost->bags_40kg ?? 0) . ' bags' }} ({{ number_format($cost->total_kg, 0) }} kg)</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->electricity, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $cost->power_units ?? 0 }} units</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->water_bill, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->raw_material, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->labour_charge, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->welfare, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->machine_maintenance, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->packing_cost, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->transport_loading, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->wastage_cost, 2) }}</span></td>
                                    <td class="px-4 py-4"><span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-bold">₹{{ number_format($cost->other_expenses, 2) }}</span></td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="text-sm font-black text-violet-600 dark:text-violet-400 font-black">₹{{ number_format($cost->total, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- Chart.js CDN + Script --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_URL = '{{ route('tenant.production.mis-costs.data') }}';

            let barChart, pieChart;

            function isDark() {
                return document.documentElement.classList.contains('dark');
            }

            function gridColor() {
                return isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            }

            function textColor() {
                return isDark() ? '#94a3b8' : '#64748b';
            }

            async function fetchAndRender() {
                const dateVal = document.getElementById('filter_selected_date')?.value || '';
                const url     = `${API_URL}?date=${dateVal}`;

                try {
                    const res  = await fetch(url);
                    const responseData = await res.json();

                    if (!responseData.found) {
                        document.getElementById('noDataOverlayBar').classList.remove('hidden');
                        document.getElementById('noDataOverlayPie').classList.remove('hidden');
                        document.getElementById('barChartSubtitle').textContent = 'No entry for this date';
                        document.getElementById('pieChartSubtitle').textContent = 'No entry for this date';
                        if (barChart) barChart.destroy();
                        if (pieChart) pieChart.destroy();
                        return;
                    }

                    document.getElementById('noDataOverlayBar').classList.add('hidden');
                    document.getElementById('noDataOverlayPie').classList.add('hidden');
                    document.getElementById('barChartSubtitle').textContent = 'Rupee value comparison for ' + responseData.date;
                    document.getElementById('pieChartSubtitle').textContent = 'Percentage breakdown for ' + responseData.date;

                    // 1. Render Bar Chart
                    const ctxBar = document.getElementById('misCostBarChart').getContext('2d');
                    if (barChart) barChart.destroy();

                    barChart = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: responseData.labels,
                            datasets: [{
                                data: responseData.data,
                                backgroundColor: [
                                    'rgba(250,204,21,0.85)', // Electricity
                                    'rgba(96,165,250,0.85)', // Water Bill
                                    'rgba(52,211,153,0.85)', // Raw Material
                                    'rgba(251,146,60,0.85)', // Labour Charge
                                    'rgba(244,63,94,0.85)',  // Welfare
                                    'rgba(168,85,247,0.85)', // Machine Maintenance
                                    'rgba(236,72,153,0.85)', // Packing Cost
                                    'rgba(6,182,212,0.85)',  // Transport/Loading
                                    'rgba(239,68,68,0.85)',  // Wastage Cost
                                    'rgba(100,116,139,0.85)', // Other Expenses
                                ],
                                borderRadius: 8,
                                borderWidth: 0,
                                barThickness: 32
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ` ₹${ctx.raw.toLocaleString('en-IN', {minimumFractionDigits:2})}`,
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: textColor(), font: { size: 10, weight: '700' } }
                                },
                                y: {
                                    grid: { color: gridColor() },
                                    ticks: {
                                        color: textColor(),
                                        font: { size: 10, weight: '700' },
                                        callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                                    }
                                }
                            }
                        }
                    });

                    // 2. Render Pie Chart
                    const ctxPie = document.getElementById('misCostPieChart').getContext('2d');
                    if (pieChart) pieChart.destroy();

                    pieChart = new Chart(ctxPie, {
                        type: 'pie',
                        data: {
                            labels: responseData.labels,
                            datasets: [{
                                data: responseData.data,
                                backgroundColor: [
                                    'rgba(250,204,21,0.9)', // Electricity
                                    'rgba(96,165,250,0.9)', // Water Bill
                                    'rgba(64, 234, 171, 0.9)', // Raw Material
                                    'rgba(251,146,60,0.9)', // Labour Charge
                                    'rgba(244,63,94,0.9)',  // Welfare
                                    'rgba(168,85,247,0.9)', // Machine Maintenance
                                    'rgba(236,72,153,0.9)', // Packing Cost
                                    'rgba(6,182,212,0.9)',  // Transport/Loading
                                    'rgba(239,68,68,0.9)',  // Wastage Cost
                                    'rgba(100,116,139,0.9)', // Other Expenses
                                ],
                                borderWidth: 0,
                                hoverOffset: 12,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        color: textColor(),
                                        font: { size: 9, weight: '700' },
                                        padding: 12,
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ` ${ctx.label}: ₹${ctx.raw.toLocaleString('en-IN', {minimumFractionDigits:2})}`,
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Error rendering charts', e);
                }
            }

            // Initial load
            fetchAndRender();

            // Auto-refresh every 60 seconds
            setInterval(fetchAndRender, 60000);

            // Autofill Ledger logic
            const btnAutofill = document.getElementById('btn-autofill-ledger');
            if (btnAutofill) {
                btnAutofill.addEventListener('click', async function() {
                    const dateInput = document.getElementById('mis_cost_entry_date');
                    if (!dateInput || !dateInput.value) {
                        alert('Please select a valid date first.');
                        return;
                    }

                    const originalText = btnAutofill.innerHTML;
                    btnAutofill.disabled = true;
                    btnAutofill.innerHTML = '⏳ Fetching Ledger...';

                    try {
                        const response = await fetch(`{{ route('tenant.production.mis-costs.autofill') }}?date=${dateInput.value}`);
                        const result = await response.json();

                        if (result.success) {
                            // Helper to set input value
                            const setVal = (name, val) => {
                                const el = document.querySelector(`input[name="${name}"]`);
                                if (el) el.value = val;
                            };

                            setVal('electricity', result.electricity);
                            setVal('water_bill', result.water_bill);
                            setVal('raw_material', result.raw_material);
                            setVal('labour_charge', result.labour_charge);
                            setVal('machine_maintenance', result.machine_maintenance);
                            setVal('packing_cost', result.packing_cost);
                            setVal('transport_loading', result.transport_loading);
                            setVal('welfare', result.welfare);
                            setVal('other_expenses', result.other_expenses);
                            setVal('labour_count', result.labour_count);

                            // Highlight inputs briefly to show they updated
                            const inputs = ['electricity', 'water_bill', 'raw_material', 'labour_charge', 'machine_maintenance', 'packing_cost', 'transport_loading', 'welfare', 'other_expenses', 'labour_count'];
                            inputs.forEach(name => {
                                const el = document.querySelector(`input[name="${name}"]`);
                                if (el) {
                                    el.classList.add('ring-2', 'ring-violet-500');
                                    setTimeout(() => {
                                        el.classList.remove('ring-2', 'ring-violet-500');
                                    }, 1500);
                                }
                            });

                            btnAutofill.innerHTML = '✅ Autofilled!';
                            setTimeout(() => {
                                btnAutofill.disabled = false;
                                btnAutofill.innerHTML = originalText;
                            }, 2000);
                        } else {
                            alert(result.message || 'Failed to fetch ledger data.');
                            btnAutofill.disabled = false;
                            btnAutofill.innerHTML = originalText;
                        }
                    } catch (error) {
                        console.error('Error fetching ledger data:', error);
                        alert('An error occurred while fetching ledger data.');
                        btnAutofill.disabled = false;
                        btnAutofill.innerHTML = originalText;
                    }
                });
            }
        });
        </script>

    @elseif($stage === 'Analysis')
        <!-- ANALYSIS VIEW -->
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form action="{{ route('tenant.production.index') }}" method="GET" class="flex items-center gap-3">
                    <input type="hidden" name="stage" value="Analysis">
                    <input type="date" name="date" value="{{ $today }}" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-xs font-bold dark:text-white shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Shift Analysis</span>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Total Workers Card --}}
                <a href="{{ route('tenant.production.analysis.details', ['type' => 'workers', 'date' => $today]) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-blue-500">
                        👥 View Shift Workers
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Workers</p>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalWorkers }}</p>
                        <span class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-xl">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                    </div>
                </a>

                {{-- Packets Done Card --}}
                <a href="{{ route('tenant.production.analysis.details', ['type' => 'packets', 'date' => $today]) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-emerald-500">
                        📊 View Throughput
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Packets Done</p>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($packetsDone) }}</p>
                        <span class="p-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-xl">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    </div>
                </a>

                {{-- Avg. Workers/Job Card --}}
                <a href="{{ route('tenant.production.analysis.details', ['type' => 'workloads', 'date' => $today]) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-indigo-500">
                        📈 View Workloads
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg. Workers/Job</p>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">{{ isset($avgWorkersPerJob) ? number_format($avgWorkersPerJob, 1) : 0 }}</p>
                        <span class="p-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 rounded-xl">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                    </div>
                </a>

                {{-- Efficiency Index Card --}}
                <a href="{{ route('tenant.production.analysis.details', ['type' => 'efficiency', 'date' => $today]) }}" 
                   class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-900 border border-white/20 shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 block group relative overflow-hidden">
                    <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-[8px] font-black uppercase tracking-widest text-amber-500">
                        ⚡ View Efficiency History
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Efficiency Index</p>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ $totalWorkers > 0 ? number_format($packetsDone / $totalWorkers, 1) : 0 }}</p>
                        <span class="p-2 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-xl">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Stage Throughput -->
                <div class="lg:col-span-2 glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Stage Throughput</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Units processed today</p>
                    </div>
                    <div class="space-y-6">
                        @foreach($availableProcesses as $proc)
                            @php
                                $completed = $stageThroughput[$proc] ?? 0;
                                $active = $currentlyInStages[$proc] ?? 0;
                                $total = $completed + $active;
                                $max = max(1, collect($stageThroughput)->max() + collect($currentlyInStages)->max());
                                $perc = ($total / $max) * 100;
                            @endphp
                            <div class="space-y-2">
                                <div class="flex justify-between items-end">
                                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ $proc }}</span>
                                    <div class="flex gap-4">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active: <span class="text-blue-600">{{ number_format($active) }}</span></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Done: <span class="text-emerald-600">{{ number_format($completed) }}</span></span>
                                    </div>
                                </div>
                                <div class="h-3 bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden flex">
                                    <div class="h-full bg-blue-500 shadow-sm transition-all duration-1000" style="width: {{ ($active / $max) * 100 }}%"></div>
                                    <div class="h-full bg-emerald-500 shadow-sm transition-all duration-1000" style="width: {{ ($completed / $max) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Active Workers List -->
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Shift Workers</h3>
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-full text-[9px] font-black uppercase tracking-widest">{{ $totalWorkers }}</span>
                    </div>
                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 scrollbar-hide">
                        @forelse($uniqueWorkers as $worker)
                            <div class="flex items-center gap-4 p-4 bg-slate-50/50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-blue-500/20 transition-all">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-sm">
                                    {{ substr($worker, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white tracking-tight">{{ $worker }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Status: Active</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No workers recorded today</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- SHIFT PERFORMANCE TABLE CARD -->
            <div class="glass-card rounded-[2.5rem] border border-white/20 shadow-xl overflow-hidden bg-white dark:bg-slate-900">
                <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Shift Performance History</h3>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Last 7 Days Breakdown</span>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Shift</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Employees Worked</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Packets Completed</th>
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Avg. Packet / Employee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            @foreach($shiftAnalysis as $row)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-8 py-4">
                                        <span class="text-xs font-black text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ $row['shift'] === 'Morning' ? 'bg-amber-400' : ($row['shift'] === 'Evening' ? 'bg-indigo-400' : 'bg-slate-600') }}"></div>
                                            <span class="text-[10px] font-black uppercase tracking-widest {{ $row['shift'] === 'Morning' ? 'text-amber-600' : ($row['shift'] === 'Evening' ? 'text-indigo-600' : 'text-slate-500') }}">{{ $row['shift'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ number_format($row['employees']) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ number_format($row['packets']) }}</span>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($row['avg']) }}</span>
                                            <svg class="w-4 h-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SHIFT PERFORMANCE CHARTS GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Bar Chart (Takes 2 Columns) -->
                <div class="lg:col-span-2 glass-card rounded-[2.5rem] border border-white/20 shadow-xl p-8 bg-white dark:bg-slate-900 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Shift Production Output</h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Packets completed per shift (Bar Graph)</p>
                            </div>
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-xl">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </span>
                        </div>
                        <div class="relative h-[300px] w-full">
                            <canvas id="shiftBarChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pie/Doughnut Chart (Takes 1 Column) -->
                <div class="glass-card rounded-[2.5rem] border border-white/20 shadow-xl p-8 bg-white dark:bg-slate-900 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Output Contribution</h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Share of total completed packets (Pie Chart)</p>
                            </div>
                            <span class="p-2 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-xl">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                            </span>
                        </div>
                        <div class="relative h-[250px] w-full flex items-center justify-center">
                            <canvas id="shiftPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctxBar = document.getElementById('shiftBarChart');
            const ctxPie = document.getElementById('shiftPieChart');
            if (!ctxBar || !ctxPie) return;

            const shiftData = @json($shiftAnalysis);
            const chartData = [...shiftData].reverse().filter(item => item.packets > 0 || item.employees > 0);

            if (chartData.length === 0) {
                chartData.push(...[...shiftData].reverse().slice(-7));
            }

            const labels = chartData.map(item => {
                const dateParts = item.date.split('-');
                const formattedDate = `${dateParts[2]}/${dateParts[1]}`;
                const shiftShort = item.shift === 'Morning' ? 'M' : (item.shift === 'Evening' ? 'E' : 'N');
                return `${formattedDate} (${shiftShort})`;
            });

            const packetsData = chartData.map(item => item.packets);

            // Dynamically assign bar background color corresponding to individual shift names
            const barColors = chartData.map(item => {
                if (item.shift === 'Morning') return 'rgba(245, 158, 11, 0.85)'; // Vibrant Amber
                if (item.shift === 'Evening') return 'rgba(99, 102, 241, 0.85)';  // Vibrant Indigo
                return 'rgba(100, 116, 139, 0.85)'; // Slate Gray
            });

            const isDark = () => document.documentElement.classList.contains('dark');
            const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = () => isDark() ? '#94a3b8' : '#64748b';

            // 1. Render Bar Chart
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Packets Completed',
                        data: packetsData,
                        backgroundColor: barColors,
                        borderRadius: 8,
                        borderWidth: 0,
                        barThickness: 'flex'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            padding: 12,
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleFont: { size: 10, weight: '700' },
                            bodyFont: { size: 10 },
                            cornerRadius: 12,
                            callbacks: {
                                label: ctx => ` Packets Completed: ${ctx.raw.toLocaleString()}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor(),
                                font: { size: 8, weight: '700' },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            grid: { color: gridColor() },
                            ticks: {
                                color: textColor(),
                                font: { size: 9, weight: '700' },
                                callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v
                            }
                        }
                    }
                }
            });

            // 2. Aggregate Pie Chart Data (Morning vs Evening vs Night)
            const aggregates = { Morning: 0, Evening: 0, Night: 0 };
            chartData.forEach(item => {
                if (aggregates[item.shift] !== undefined) {
                    aggregates[item.shift] += item.packets;
                }
            });

            const pieLabels = ['Morning Shift', 'Evening Shift', 'Night Shift'];
            const pieData = [aggregates.Morning, aggregates.Evening, aggregates.Night];
            const pieColors = [
                'rgba(245, 158, 11, 0.9)', // Amber
                'rgba(99, 102, 241, 0.9)', // Indigo
                'rgba(100, 116, 139, 0.9)'  // Slate
            ];

            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors,
                        borderWidth: 0,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: textColor(),
                                font: { size: 9, weight: '700' },
                                padding: 8,
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            padding: 12,
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleFont: { size: 10, weight: '700' },
                            bodyFont: { size: 10 },
                            cornerRadius: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = ctx.raw;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        });
        </script>
        </div>
    @elseif($stage === 'Scheduler')
        <!-- KANBAN WORKFLOW BOARD -->
        <div class="space-y-6">

            <!-- Kanban Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start overflow-x-auto pb-4">
                @foreach($availableProcesses as $proc)
                    <div class="glass-card rounded-[2rem] p-5 bg-slate-50/50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 flex flex-col min-h-[500px] transition-all">

                        <!-- Column Header -->
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ $proc }}</span>
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-[9px] font-bold text-slate-500 rounded-lg">
                                {{ $productions->where('current_stage', $proc)->count() }}
                            </span>
                        </div>

                        <!-- Column Body -->
                        <div class="flex-1 space-y-4">
                            @forelse($productions->where('current_stage', $proc) as $p)
                                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-white/10 shadow-md hover:shadow-lg transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase {{ $p->priority === 'Urgent' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/20' : 'bg-blue-50 text-blue-600 dark:bg-blue-950/20' }}">
                                            {{ $p->priority }}
                                        </span>
                                        <span class="text-[9px] font-bold text-slate-400">#{{ $p->id }}</span>
                                    </div>
                                    <h4 class="text-xs font-black text-slate-900 dark:text-white tracking-tight mb-1">{{ $p->product->product_name ?? 'N/A' }}</h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Client: <span class="text-slate-700 dark:text-slate-300">{{ $p->customer->name ?? 'Direct' }}</span></p>

                                    <div class="mt-4 flex items-center justify-between pt-2 border-t border-slate-50 dark:border-slate-750">
                                        <span class="text-[9px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-widest">{{ number_format($p->total_qty) }} {{ $p->qty_unit ?? '' }}</span>
                                        @if($p->deadline_date)
                                            <span class="text-[8px] font-bold text-slate-400">{{ $p->deadline_date->format('d M') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-slate-300 dark:text-slate-700 select-none">
                                    <span class="text-[9px] font-black uppercase tracking-widest">Empty Stage</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($stage && $stage !== 'Completed')
        <!-- DEPARTMENT VIEW -->
        <div class="space-y-6">
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $stage }} Department</h2>
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-1">Type: Inhouse</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <p class="text-[8px] font-black text-emerald-600 uppercase tracking-widest">Active</p>
                            <p class="text-sm font-black text-emerald-700 dark:text-emerald-400">{{ $stageStats['active'] }}</p>
                        </div>
                        <div class="px-4 py-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                            <p class="text-[8px] font-black text-amber-600 uppercase tracking-widest">On Hold</p>
                            <p class="text-sm font-black text-amber-700 dark:text-amber-400">{{ $stageStats['on_hold'] }}</p>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                            <p class="text-sm font-black text-slate-600 dark:text-slate-400">{{ $stageStats['pending'] }}</p>
                        </div>
                        <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                            <p class="text-sm font-black text-slate-600 dark:text-slate-400">{{ $stageStats['total'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                @forelse($productions as $p)
                    <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl relative overflow-hidden group">
                        @if($p->priority === 'Urgent')
                            <div class="absolute top-0 right-0 px-8 py-1 bg-rose-500 text-white text-[8px] font-black uppercase tracking-widest transform rotate-45 translate-x-6 translate-y-2">Urgent</div>
                        @endif

                        <div class="flex flex-col md:flex-row justify-between gap-8">
                            <div class="flex-1 space-y-4">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $p->product->product_name ?? 'N/A' }}</h3>
                                    <div class="flex items-center gap-4 mt-2">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer: <span class="text-slate-900 dark:text-white">{{ $p->customer->name ?? 'Direct' }}</span></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Quantity: <span class="text-blue-600">{{ number_format($p->total_qty) }} {{ $p->qty_unit ?? '' }}</span></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Deadline: <span class="text-rose-500">{{ $p->deadline_date ? $p->deadline_date->format('d/m/Y') : 'N/A' }}</span></p>
                                    </div>
                                    @if($p->assigned_workers)
                                        <div class="flex items-center gap-2 mt-4">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Workers:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($p->assigned_workers as $worker)
                                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-[8px] font-bold text-slate-600 dark:text-slate-400 rounded-lg">{{ $worker }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($p->current_stage_started_at)
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Accepted: <span class="text-emerald-600">{{ $p->current_stage_started_at->format('d/m/Y, h:i:s A') }}</span></p>
                                @endif

                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Process Notes</label>
                                    <form action="{{ route('tenant.production.save-notes', $p->id) }}" method="POST" class="relative">
                                        @csrf
                                        <textarea 
                                            name="notes" 
                                            rows="3" 
                                            class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-bold focus:ring-2 focus:ring-blue-500 transition-all dark:text-white"
                                            placeholder="Add notes about this process...">{{ $p->current_stage_notes }}</textarea>
                                        <button type="submit" class="absolute bottom-4 right-4 p-2 bg-white dark:bg-slate-700 rounded-xl shadow-sm text-[9px] font-black uppercase tracking-widest hover:text-blue-600 transition-colors">Save</button>
                                    </form>
                                </div>
                            </div>

                            <div class="md:w-64 flex flex-col justify-between items-end">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $p->status === 'In Progress' ? 'bg-emerald-50 text-emerald-600' : ($p->status === 'On Hold' ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400') }}">
                                    {{ $p->status === 'In Progress' ? 'Active' : ($p->status === 'On Hold' ? 'On Hold' : 'Pending') }}
                                </span>

                                <div class="w-full space-y-3">
                                    @if(!$p->current_stage_started_at)
                                        <button 
                                            @click="openStartModal('{{ $p->id }}', '{{ $p->product->product_name ?? 'Job' }}')"
                                            class="w-full py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">
                                            Start {{ $stage }}
                                        </button>
                                    @elseif($p->status === 'On Hold')
                                        <form action="{{ route('tenant.production.resume', $p->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:scale-[1.02] active:scale-95 transition-all">
                                                Resume Process
                                            </button>
                                        </form>
                                    @else
                                        <div class="grid grid-cols-2 gap-3 w-full">
                                            <form action="{{ route('tenant.production.hold', $p->id) }}" method="POST" class="col-span-1">
                                                @csrf
                                                <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-100 hover:scale-[1.02] active:scale-95 transition-all">
                                                    Hold
                                                </button>
                                            </form>
                                            <form action="{{ route('tenant.production.update-stage', $p->id) }}" method="POST" class="col-span-1">
                                                @csrf
                                                <button type="submit" class="w-full py-4 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:scale-[1.02] active:scale-95 transition-all">
                                                    Finish
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="glass-card rounded-[2.5rem] p-12 text-center bg-white dark:bg-slate-900 border border-white/20">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-200">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No active jobs in {{ $stage }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- STANDARD LIST VIEW (All or Completed) -->
        <div class="glass-card rounded-[2.5rem] border-white/40 shadow-xl overflow-hidden bg-white dark:bg-slate-900">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Job Info</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Current Stage</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Deadline</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($productions as $p)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-900 dark:text-white">{{ $p->product->product_name ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Qty: {{ number_format($p->total_qty) }} {{ $p->qty_unit ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $p->customer->name ?? 'Direct' }}</span>
                                    <span class="px-2 py-0.5 w-fit rounded-lg text-[8px] font-black uppercase mt-1 {{ $p->priority === 'Urgent' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600' }}">
                                        {{ $p->priority }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $p->status === 'Completed' ? 'bg-emerald-500' : ($p->status === 'On Hold' ? 'bg-amber-500' : 'bg-blue-500 animate-pulse') }}"></div>
                                    <span class="text-[10px] font-black {{ $p->status === 'Completed' ? 'text-emerald-600' : ($p->status === 'On Hold' ? 'text-amber-600' : 'text-blue-600') }} dark:text-blue-400 uppercase tracking-tight">{{ $p->current_stage }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-500">
                                {{ $p->deadline_date ? $p->deadline_date->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest {{ $p->status === 'Completed' ? 'bg-emerald-50 text-emerald-600' : ($p->status === 'On Hold' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($p->status === 'Completed')
                                    <button type="button" 
                                            @click="openDetailModal({
                                                id: '{{ $p->id }}',
                                                product_name: '{{ addslashes(e($p->product->product_name ?? 'N/A')) }}',
                                                quantity: '{{ number_format($p->total_qty) }} {{ $p->qty_unit }}',
                                                customer: '{{ addslashes(e($p->customer->name ?? 'Direct')) }}',
                                                deadline: '{{ $p->deadline_date ? $p->deadline_date->format('d M Y') : 'N/A' }}',
                                                completed_date: '{{ $p->end_date ? $p->end_date->format('d M Y') : ($p->updated_at ? $p->updated_at->format('d M Y') : 'N/A') }}',
                                                cost_electricity: {{ $p->cost_electricity ?? 0 }},
                                                cost_water: {{ $p->cost_water_bill ?? 0 }},
                                                cost_material: {{ $p->cost_raw_material ?? 0 }},
                                                cost_labour: {{ $p->cost_labour ?? 0 }},
                                                cost_total: {{ ($p->cost_electricity ?? 0) + ($p->cost_water_bill ?? 0) + ($p->cost_raw_material ?? 0) + ($p->cost_labour ?? 0) }},
                                                cost_notes: '{{ addslashes(e($p->cost_notes ?? 'No remarks recorded.')) }}'
                                            })"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:scale-105 hover:bg-blue-700 active:scale-95 transition-all inline-block shadow-md shadow-blue-100 dark:shadow-none">
                                        View Job
                                    </button>
                                @else
                                    <a href="{{ route('tenant.production.index', ['stage' => $p->current_stage]) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all inline-block">
                                        View Job
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No jobs found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($productions->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800">
                    {{ $productions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- START PROCESS MODAL -->
    <div 
        x-show="showStartModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">
        
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl border border-white/20">
            <div class="p-8 border-b border-slate-50 dark:border-slate-800">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight" x-text="'Start ' + '{{ $stage }}' + ' for ' + (currentJob ? currentJob.name : '')"></h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Please assign workers and confirm the start time.</p>
            </div>

            <form :action="currentJob ? '/production/' + currentJob.id + '/accept' : ''" method="POST" class="p-8 space-y-6">
                @csrf
                <!-- Assigned Workers -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Assigned Workers *</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <template x-for="(worker, index) in workers" :key="index">
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl text-xs font-bold">
                                <span x-text="worker"></span>
                                <button type="button" @click="removeWorker(index)" class="hover:text-rose-500">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <select 
                            x-model="workerInput" 
                            class="flex-1 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-xs font-bold dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a worker...</option>
                            @foreach($availableWorkers as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="addWorker()" class="px-4 py-3 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all">
                            + Add Worker
                        </button>
                    </div>
                    <template x-for="worker in workers">
                        <input type="hidden" name="workers[]" :value="worker">
                    </template>
                </div>

                <!-- Start Date & Time -->
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Start Date & Time *</label>
                    <input 
                        type="datetime-local" 
                        name="start_time" 
                        x-model="startTime"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-xs font-bold dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" @click="showStartModal = false" class="flex-1 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Cancel
                    </button>
                    <button type="submit" :disabled="workers.length === 0" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50">
                        Start Process
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- COMPLETED JOB DETAIL MODAL -->
    <div 
        x-show="showDetailModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-4xl overflow-hidden shadow-2xl border border-white/20 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-850">
                <div>
                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                        Job Portfolio
                    </span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-2">Completed Job Records</h3>
                </div>
                <button type="button" @click="showDetailModal = false" class="p-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 rounded-2xl transition-all">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-8 overflow-y-auto space-y-8 flex-1 grid grid-cols-1 md:grid-cols-2 gap-8 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800" x-show="detailJob">
                {{-- Left: Product & Order Information --}}
                <div class="space-y-6">
                    <div>
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Product Information</h4>
                        <p class="text-xl font-black text-slate-900 dark:text-white tracking-tight" x-text="detailJob?.product_name"></p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between py-2 border-b border-slate-50 dark:border-slate-800 text-xs">
                            <span class="text-slate-400 font-bold uppercase">Customer / Client</span>
                            <span class="text-slate-900 dark:text-white font-black" x-text="detailJob?.customer"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50 dark:border-slate-800 text-xs">
                            <span class="text-slate-400 font-bold uppercase">Production Quantity</span>
                            <span class="text-blue-600 dark:text-blue-400 font-black" x-text="detailJob?.quantity"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50 dark:border-slate-800 text-xs">
                            <span class="text-slate-400 font-bold uppercase">Estimated Deadline</span>
                            <span class="text-slate-700 dark:text-slate-300 font-black" x-text="detailJob?.deadline"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50 dark:border-slate-800 text-xs">
                            <span class="text-slate-400 font-bold uppercase">Completion Date</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black" x-text="detailJob?.completed_date"></span>
                        </div>
                    </div>

                    {{-- Spec Badge or Box --}}
                    <div class="p-4 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800/50">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Quality Assurance</span>
                        <p class="text-xs text-slate-600 dark:text-slate-300 font-bold">This job successfully passed all checks on the production line and has been marked as completed.</p>
                    </div>
                </div>

                {{-- Right: Product Cost Breakdown --}}
                <div class="space-y-6 md:pl-8 pt-6 md:pt-0">
                    <div>
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Product Cost Breakdown</h4>
                        <p class="text-xs font-bold text-slate-500">Breakdown of manufacturing costs logged for this specific job</p>
                    </div>

                    <div class="space-y-4">
                        {{-- Electricity --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-yellow-600 dark:text-yellow-400">⚡ Electricity</span>
                                <span class="text-slate-950 dark:text-white font-black" x-text="'₹' + Number(detailJob?.cost_electricity).toLocaleString()"></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-yellow-400 h-full rounded-full" :style="'width: ' + (detailJob?.cost_total > 0 ? (detailJob?.cost_electricity / detailJob?.cost_total) * 100 : 0) + '%'"></div>
                            </div>
                        </div>

                        {{-- Water --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-blue-600 dark:text-blue-400">💧 Water Utility</span>
                                <span class="text-slate-950 dark:text-white font-black" x-text="'₹' + Number(detailJob?.cost_water).toLocaleString()"></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-400 h-full rounded-full" :style="'width: ' + (detailJob?.cost_total > 0 ? (detailJob?.cost_water / detailJob?.cost_total) * 100 : 0) + '%'"></div>
                            </div>
                        </div>

                        {{-- Material --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-emerald-600 dark:text-emerald-400">📦 Raw Materials</span>
                                <span class="text-slate-950 dark:text-white font-black" x-text="'₹' + Number(detailJob?.cost_material).toLocaleString()"></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-400 h-full rounded-full" :style="'width: ' + (detailJob?.cost_total > 0 ? (detailJob?.cost_material / detailJob?.cost_total) * 100 : 0) + '%'"></div>
                            </div>
                        </div>

                        {{-- Labour --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-orange-600 dark:text-orange-400">👷 Labour Charge</span>
                                <span class="text-slate-950 dark:text-white font-black" x-text="'₹' + Number(detailJob?.cost_labour).toLocaleString()"></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-orange-400 h-full rounded-full" :style="'width: ' + (detailJob?.cost_total > 0 ? (detailJob?.cost_labour / detailJob?.cost_total) * 100 : 0) + '%'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Summary Box --}}
                    <div class="p-5 bg-blue-50/50 dark:bg-blue-900/10 rounded-3xl border border-blue-100 dark:border-blue-800/30 flex justify-between items-center">
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Total Cost Incurred</span>
                            <span class="text-2xl font-black text-blue-600 dark:text-blue-400" x-text="'₹' + Number(detailJob?.cost_total).toLocaleString()"></span>
                        </div>
                        <span class="text-lg">💰</span>
                    </div>

                    {{-- Notes --}}
                    <div class="p-4 bg-slate-50 dark:bg-slate-850 rounded-2xl text-xs text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-800" x-show="detailJob?.cost_notes">
                        <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">Cost Notes</span>
                        <p class="italic font-medium" x-text="'&ldquo;' + detailJob?.cost_notes + '&rdquo;'"></p>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-8 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50/50 dark:bg-slate-850">
                <button type="button" @click="showDetailModal = false" class="px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-950 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

