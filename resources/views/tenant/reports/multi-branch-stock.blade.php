@extends('layouts.tenant')

@section('title', 'Multi-Branch Stock Consolidated Report')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    :root {
        --branch-primary: #a855f7;
        --branch-glow: rgba(168, 85, 247, 0.4);
    }
    .page-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; gap: 16px;
    }
    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: stretch; }
    }
    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px; margin-bottom: 2rem;
    }
    .stat-card {
        background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; padding: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;
    }
    .dark .stat-card {
        background: rgba(15, 23, 42, 0.45); border-color: rgba(255, 255, 255, 0.05);
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -8px var(--branch-glow); }
    .stat-label { font-size: 10px; font-weight: 900; text-transform: uppercase; color: #94a3b8; tracking: 0.1em; }
    .stat-value { font-size: 22px; font-weight: 800; color: #1a1a2e; margin-top: 6px; font-family: 'DM Mono', monospace; }
    .dark .stat-value { color: #f8fafc; }

    .card {
        background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 20px; padding: 28px;
    }
    .dark .card {
        background: rgba(15, 23, 42, 0.4); border-color: rgba(255, 255, 255, 0.04);
    }
    .table-container {
        overflow-x: auto; width: 100%; border-radius: 12px; border: 1px solid rgba(226, 232, 240, 0.5);
    }
    .dark .table-container {
        border-color: rgba(255, 255, 255, 0.05);
    }
    .branch-table {
        width: 100%; border-collapse: collapse; text-align: left;
    }
    .branch-table th {
        background: rgba(248, 250, 252, 0.8); padding: 14px 18px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;
    }
    .dark .branch-table th {
        background: rgba(30, 41, 59, 0.5); color: #94a3b8;
    }
    .branch-table td {
        padding: 14px 18px; font-size: 12px; border-top: 1px solid rgba(226, 232, 240, 0.4); color: #334155;
    }
    .dark .branch-table td {
        border-color: rgba(255, 255, 255, 0.04); color: #cbd5e1;
    }
    .branch-table tr:hover td {
        background: rgba(168, 85, 247, 0.03);
    }
    .dark .branch-table tr:hover td {
        background: rgba(168, 85, 247, 0.04);
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'all'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-3 text-slate-800 dark:text-slate-100" style="font-family:'Sora', sans-serif;">
                    <i class="ti ti-git-fork text-purple-500" style="filter: drop-shadow(0 0 8px var(--branch-glow));"></i> Multi-Branch Stock Consolidated View
                </h1>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mt-1 tracking-wider">Consolidated stock levels across all branches in one view</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('tenant.businessreport.multi-branch-stock') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search products..." class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 w-48 shadow-sm">
                    <button type="submit" class="px-3 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-xl text-xs font-bold shadow-sm transition-colors flex items-center gap-1.5"><i class="ti ti-search"></i> Search</button>
                    @if($search)
                        <a href="{{ route('tenant.businessreport.multi-branch-stock') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition-colors">Clear</a>
                    @endif
                </form>
                <button onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5"><i class="ti ti-printer"></i> Print</button>
                <button onclick="exportToCSV()" class="px-3 py-2 bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5"><i class="ti ti-download"></i> Export CSV</button>
            </div>
        </div>

        <!-- KPI Metrics Grid -->
        <div class="stats-grid">
            @foreach ($stats as $index => $stat)
            <div class="stat-card">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-{{ $stat['color'] }}-500/5 dark:bg-{{ $stat['color'] }}-500/10 rounded-full"></div>
                <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-500/10 text-{{ $stat['color'] }}-500 dark:text-{{ $stat['color'] }}-400 flex items-center justify-center text-sm mb-4">
                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                </div>
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-2">{{ $stat['change'] }}</div>
            </div>
            @endforeach
        </div>

        <!-- Main Card with Consolidated Stock Table -->
        <div class="card space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ti ti-building-store text-purple-500 text-lg"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100" style="font-family:'Sora', sans-serif;">Consolidated Branch Stock Ledger</h3>
                </div>
                <button onclick="window.print()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5"><i class="ti ti-printer"></i> Print Stock Sheet</button>
            </div>

            <div class="table-container">
                <table class="branch-table">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Brand</th>
                            @foreach ($branches as $b)
                            <th class="text-center bg-slate-50/50 dark:bg-slate-900/30">{{ $b->name }} <span class="block text-[8px] font-extrabold text-slate-400 mt-0.5">({{ $b->code }})</span></th>
                            @endforeach
                            <th class="text-center font-extrabold text-purple-600 dark:text-purple-400">Total Stock</th>
                            <th class="text-right">Dealer Price</th>
                            <th class="text-right">Est. Valuation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                        <tr>
                            <td class="font-mono text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $row['barcode'] ?: 'N/A' }}</td>
                            <td class="font-bold text-slate-800 dark:text-slate-200">{{ $row['name'] }}</td>
                            <td><span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-md text-[10px] font-extrabold text-slate-500 dark:text-slate-400">{{ $row['brand'] ?: 'Generic' }}</span></td>
                            @foreach ($branches as $b)
                            <td class="text-center font-mono font-bold">{{ $row['branch_stocks'][$b->code] ?? 0 }}</td>
                            @endforeach
                            <td class="text-center font-mono font-extrabold text-purple-600 dark:text-purple-400 bg-purple-500/5">{{ $row['total_stock'] }}</td>
                            <td class="text-right font-mono font-bold">₹{{ number_format($row['dealer_price'], 2) }}</td>
                            <td class="text-right font-mono font-bold text-purple-500">₹{{ number_format($row['valuation'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 6 + $branches->count() }}" class="text-center py-8 text-slate-400 dark:text-slate-500 font-bold">
                                <i class="ti ti-circle-x text-rose-500 text-3xl mb-2 block"></i>
                                No active products found in inventory.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function exportToCSV() {
    let csv = [];
    let rows = document.querySelectorAll(".branch-table tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));        
    }
    let csvString = csv.join("\n");
    let filename = "multi_branch_stock_" + new Date().toISOString().slice(0, 10) + ".csv";
    let link = document.createElement("a");
    link.setAttribute("href", "data:text/csv;charset=utf-8," + encodeURIComponent(csvString));
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
