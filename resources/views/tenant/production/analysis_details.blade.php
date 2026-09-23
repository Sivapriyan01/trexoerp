@extends('layouts.tenant')
@section('title', 'Shift Analysis Details')
@section('page-title', 'Shift Analysis Analytics')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    
    {{-- HEADER BLOCK --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">
                <a href="{{ route('tenant.production.index') }}" class="hover:text-blue-600 transition-colors">Production</a>
                <span>/</span>
                <a href="{{ route('tenant.production.index', ['stage' => 'Analysis']) }}" class="hover:text-blue-600 transition-colors">Shift Analysis</a>
                <span>/</span>
                <span class="text-slate-900 dark:text-white">Details</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                @if($type === 'workers')
                    Workforce Roster Details
                @elseif($type === 'packets')
                    Production Packet Ledgers
                @elseif($type === 'workloads')
                    Workload Allocation Matrix
                @elseif($type === 'efficiency')
                    Efficiency Performance Indexes
                @endif
            </h1>
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">
                @if($type === 'workers')
                    Operational roster audit of all technicians active on the selected shift date
                @elseif($type === 'packets')
                    Consolidated record of packets produced and processes completed today
                @elseif($type === 'workloads')
                    Benchmark distribution analysis of worker capacity assigned across active manufacturing jobs
                @elseif($type === 'efficiency')
                    30-Day performance timeline evaluating relative shift efficiencies and volumes
                @endif
            </p>
        </div>
        
        <div>
            <a href="{{ route('tenant.production.index', ['stage' => 'Analysis']) }}" 
               class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to Analysis Dashboard
            </a>
        </div>
    </div>

    {{-- INTERACTIVE TAB BAR --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <a href="{{ route('tenant.production.analysis.details', ['type' => 'workers', 'date' => $today]) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'workers' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            👥 Workforce Roster
        </a>
        <a href="{{ route('tenant.production.analysis.details', ['type' => 'packets', 'date' => $today]) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'packets' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            📦 Packet Ledgers
        </a>
        <a href="{{ route('tenant.production.analysis.details', ['type' => 'workloads', 'date' => $today]) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'workloads' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            👷 Workload Allocation
        </a>
        <a href="{{ route('tenant.production.analysis.details', ['type' => 'efficiency', 'date' => $today]) }}" 
           class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2 {{ $type === 'efficiency' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-white dark:bg-slate-850 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            ⚡ Efficiency Timelines
        </a>
    </div>

    {{-- TOTAL WORKERS DETAIL VIEW --}}
    @if($type === 'workers')
        <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Active Shift Workforce Directory</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Technicians recorded on duty for {{ \Carbon\Carbon::parse($today)->format('d F Y') }}</p>
                </div>
                
                <input type="text" id="workersSearch" placeholder="Search technician names..." 
                       class="bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white border-none rounded-xl px-4 py-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 w-64 text-black">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="workersGrid">
                @forelse($uniqueWorkers as $worker)
                    <div class="p-6 bg-slate-50/50 dark:bg-slate-850 rounded-[2rem] border border-slate-100 dark:border-slate-800 flex items-center gap-4 hover:scale-[1.01] transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-md shadow-blue-100 dark:shadow-none">
                            {{ substr($worker, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-base font-black text-slate-900 dark:text-white tracking-tight name-field">{{ $worker }}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Role: Operations Specialist</p>
                            <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mt-0.5">● Active Shift Duty</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No active technicians logged on this date</p>
                    </div>
                @endforelse
            </div>
        </div>

        <script>
            document.getElementById('workersSearch').addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const cards = document.querySelectorAll('#workersGrid > div');
                cards.forEach(card => {
                    const name = card.querySelector('.name-field').textContent.toLowerCase();
                    if(name.includes(term)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        </script>
    @endif

    {{-- COMPLETED PACKETS DETAIL VIEW --}}
    @if($type === 'packets')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Stage Throughput Columns --}}
            <div class="lg:col-span-2 glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Today's Stage-by-Stage Throughput</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total manufacturing packet movements logged today across processes</p>
                </div>

                <div class="space-y-6">
                    @foreach($availableProcesses as $proc)
                        @php
                            $completed = $stageThroughput[$proc] ?? 0;
                            $max = max(1, collect($stageThroughput)->max());
                            $perc = ($completed / $max) * 100;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ $proc }}</span>
                                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">{{ number_format($completed) }} Packets</span>
                            </div>
                            <div class="h-4 bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full" style="width: {{ $perc }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Summary Card Right --}}
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Completion Totals</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Fully packaged and ready product packets completed today</p>

                    <div class="p-6 bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 rounded-3xl text-center space-y-1">
                        <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Completed Packets Today</span>
                        <span class="text-4xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight block">{{ number_format($packetsDone) }}</span>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider leading-relaxed">
                    💡 <strong class="text-slate-700 dark:text-slate-300">Throughput Note:</strong> Completed units reflect the total volumes recorded when jobs successfully advanced into the finished stage.
                </div>
            </div>
        </div>
    @endif

    {{-- WORKLOAD ALLOCATION MATRIX VIEW --}}
    @if($type === 'workloads')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Metrics Breakdown Column --}}
            <div class="lg:col-span-2 glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Active Workload distribution</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Live active worker assignment matrices matching open jobs</p>
                </div>

                <div class="overflow-x-auto rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Active Jobs / Products</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Assigned Technicians</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Workforce count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($activeJobs as $job)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-black text-slate-900 dark:text-white">{{ $job->product->product_name ?? 'Job #'.$job->id }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Quantity: {{ number_format($job->total_qty) }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if($job->assigned_workers)
                                                @foreach($job->assigned_workers as $worker)
                                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-[9px] font-bold text-slate-600 dark:text-slate-300 rounded-lg">{{ $worker }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-xs font-bold text-slate-400 italic">No assigned workers</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs font-black text-slate-900 dark:text-white">
                                        {{ count($job->assigned_workers ?? []) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">
                                        No active manufacturing jobs in progress today
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary Card Right --}}
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Workload Indicators</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Normalized workforce coverage metrics</p>

                    <div class="space-y-4">
                        <div class="p-5 bg-indigo-50/50 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl">
                            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest block mb-0.5">Total Distinct Active Jobs</span>
                            <span class="text-2xl font-black text-indigo-700 dark:text-indigo-400 tracking-tight">{{ $totalJobsToday }}</span>
                        </div>
                        <div class="p-5 bg-slate-50 dark:bg-slate-850 border border-slate-100 dark:border-slate-800 rounded-2xl">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Average Workers / Job</span>
                            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($avgWorkersPerJob, 1) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider leading-relaxed">
                    💡 <strong class="text-slate-700 dark:text-slate-300">Optimal Workload:</strong> Keeping workers per job between 1.0 and 2.5 ensures maximum shift efficiency and minimizes operational bottlenecks.
                </div>
            </div>
        </div>
    @endif

    {{-- EFFICIENCY PERFORMANCE TIMELINE VIEW --}}
    @if($type === 'efficiency')
        <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 border border-white/20 shadow-xl space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">30-Day Shift Performance Index</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Exportable historical log tracking completed packets, active staff, and average output indexes</p>
                </div>
                
                <div class="flex gap-3">
                    <input type="text" id="efficiencySearch" placeholder="Search dates..." 
                           class="bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white border-none rounded-xl px-4 py-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 w-64 text-black">
                    
                    <button onclick="downloadEfficiencyCSV()" 
                            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                        📥 Export CSV
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-[1.5rem] border border-slate-100 dark:border-slate-800">
                <table class="w-full text-left border-collapse" id="efficiencyTable">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Date Statement</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Active Workforce</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Completed Jobs Count</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Packets Manufactured</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Avg workload / job</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Efficiency Index</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($history as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-xs font-black text-slate-900 dark:text-white date-field">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('d F Y') }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ number_format($row['workers']) }} Workers
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ number_format($row['jobs']) }} Jobs
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($row['packets']) }} Packets
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ number_format($row['workload'], 1) }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-black text-blue-600 dark:text-blue-400">
                                    {{ number_format($row['efficiency'], 1) }} packets/worker
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Client-side quick filter
            document.getElementById('efficiencySearch').addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#efficiencyTable tbody tr');
                rows.forEach(row => {
                    const dateText = row.querySelector('.date-field').textContent.toLowerCase();
                    if(dateText.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Local CSV Exporter
            function downloadEfficiencyCSV() {
                let csv = 'Date Statement,Active Workforce,Completed Jobs,Packets Manufactured,Avg Workload,Efficiency Index (packets/worker)\n';
                const rows = document.querySelectorAll('#efficiencyTable tbody tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if(cols.length > 1) {
                        const line = Array.from(cols).map(col => {
                            let data = col.textContent.trim().replace(/,/g, '');
                            return `"${data}"`;
                        }).join(',');
                        csv += line + '\n';
                    }
                });
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.setAttribute('href', url);
                a.setAttribute('download', 'Shift_Efficiency_History.csv');
                a.click();
            }
        </script>
    @endif

</div>
@endsection
