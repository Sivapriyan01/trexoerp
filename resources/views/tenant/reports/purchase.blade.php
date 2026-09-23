@extends('layouts.tenant')

@section('title', 'Purchase Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Premium visual overrides that blend perfectly with the layout's dark mode and standard look */
    .purchase-page-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 0;
    }

    /* ── Header ── */
    .page-header {
        display: flex; 
        align-items: center;
        justify-content: space-between; 
        margin-bottom: 1.5rem;
    }
    .page-header h1 { 
        font-size: 20px; 
        font-weight: 700; 
        color: #1a1a2e; 
    }
    .dark .page-header h1 {
        color: #f8fafc;
    }
    .breadcrumb { 
        font-size: 12px; 
        color: #888; 
        margin-top: 2px; 
    }
    .breadcrumb a { 
        color: #4f7cff; 
        text-decoration: none; 
    }

    .btn {
        display: inline-flex; 
        align-items: center; 
        gap: 6px;
        padding: 8px 16px; 
        border-radius: 8px; 
        font-size: 13px;
        font-weight: 500; 
        cursor: pointer; 
        border: none;
        text-decoration: none; 
        transition: opacity 0.15s;
    }
    .btn:hover { 
        opacity: 0.85; 
    }
    .btn-primary { 
        background: #4f7cff; 
        color: #fff; 
    }
    .btn-outline { 
        background: #fff; 
        color: #555; 
        border: 1px solid #e0e0e0; 
    }
    .dark .btn-outline {
        background: #0f172a;
        color: #cbd5e1;
        border-color: #334155;
    }
    .btn-group { 
        display: flex; 
        gap: 8px; 
    }

    /* ── Filter Bar ── */
    .filter-bar {
        background: #fff; 
        border: 1px solid #e8eaf0;
        border-radius: 12px; 
        padding: 14px 18px;
        display: flex; 
        align-items: center; 
        gap: 16px;
        flex-wrap: wrap; 
        margin-bottom: 1.5rem;
    }
    .dark .filter-bar {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .filter-bar label { 
        font-size: 11px; 
        color: #888; 
        font-weight: 600; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .filter-bar select, .filter-bar input {
        padding: 6px 10px; 
        border: 1px solid #e0e0e0;
        border-radius: 7px; 
        font-size: 13px; 
        color: #1a1a2e;
        outline: none; 
        background: #f9f9fb;
    }
    .dark .filter-bar select, .dark .filter-bar input {
        background: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }
    .filter-bar select:focus, .filter-bar input:focus {
        border-color: #4f7cff;
    }

    /* ── Stat Cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px; 
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #fff; 
        border: 1px solid #e8eaf0;
        border-radius: 12px; 
        padding: 18px;
    }
    .dark .stat-card {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .stat-label { 
        font-size: 11px; 
        color: #888; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        font-weight: 600; 
        margin-bottom: 6px; 
    }
    .stat-value { 
        font-size: 22px; 
        font-weight: 700; 
        color: #1a1a2e; 
        margin-bottom: 4px; 
    }
    .dark .stat-value {
        color: #f8fafc;
    }
    .stat-meta { 
        font-size: 12px; 
        display: flex; 
        align-items: center; 
        gap: 4px; 
    }
    .meta-up { color: #2ec4b6; font-weight: 600; }
    .meta-down { color: #e71d36; font-weight: 600; }
    .meta-neutral { color: #888; font-weight: 600; }

    /* ── Main Dashboard Layout Grid ── */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 18px;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 900px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }

    .panel-box {
        background: #fff;
        border: 1px solid #e8eaf0;
        border-radius: 16px;
        padding: 20px;
    }
    .dark .panel-box {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .panel-title {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .dark .panel-title {
        color: #f8fafc;
    }

    /* ── Top Suppliers List ── */
    .supplier-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .supplier-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .supplier-info {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        font-weight: 600;
    }
    .supplier-name {
        color: #1a1a2e;
    }
    .dark .supplier-name {
        color: #cbd5e1;
    }
    .supplier-val {
        color: #4f7cff;
        font-weight: 700;
    }
    .progress-track {
        height: 6px;
        background: #f0f2f5;
        border-radius: 3px;
        overflow: hidden;
    }
    .dark .progress-track {
        background: #334155;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #4f7cff, #38bdf8);
        border-radius: 3px;
    }

    /* ── Ledger Table ── */
    .ledger-section {
        background: #fff;
        border: 1px solid #e8eaf0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 1rem;
    }
    .dark .ledger-section {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .ledger-table-wrap {
        overflow-x: auto;
        margin: 0 -20px;
    }
    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .ledger-table th {
        background: #f8fafc;
        padding: 12px 20px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .ledger-table th {
        background: #0f172a;
        color: #94a3b8;
        border-bottom-color: #334155;
    }
    .ledger-table td {
        padding: 14px 20px;
        font-size: 12.5px;
        border-bottom: 1px solid #f8fafc;
    }
    .dark .ledger-table td {
        border-bottom-color: rgba(51, 65, 85, 0.2);
    }
    .ledger-table tr:last-child td {
        border-bottom: none;
    }
    .ledger-table tr:hover {
        background: #fbfcfe;
    }
    .dark .ledger-table tr:hover {
        background: rgba(30, 41, 59, 0.3);
    }

    /* Custom thin scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.2);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.4);
    }

    /* ── Print Media Styles ── */
    @media print {
        /* Hide all layout structures, sidebars, chatbot, search, filters, and decorative components */
        aside, 
        header,
        .ai-chatbot-container,
        nav, 
        .btn-group, 
        .filter-bar, 
        button, 
        a.btn,
        .page-header .btn-group,
        footer,
        #reportSearch,
        .blob {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
        }

        body, html {
            background: #fff !important;
            color: #000 !important;
            font-family: 'Segoe UI', sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            height: auto !important;
        }

        /* Specific high-priority selectors for layout containers to override Tailwind h-screen / overflow */
        body > div.flex.h-screen,
        body > div.flex.h-screen > div.flex-1.flex.flex-col,
        body > div.flex.h-screen > div.flex-1.flex.flex-col > main,
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\],
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\] > div.flex-1.overflow-y-auto,
        .purchase-page-wrap {
            display: block !important;
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            transform: none !important;
            left: 0 !important;
            top: 0 !important;
        }

        .space-y-8 > * + * {
            margin-top: 20px !important;
        }

        .stat-card, .panel-box, .ledger-section, .glass-card, .card {
            border: 1px solid #ddd !important;
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
            border-radius: 8px !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid !important;
        }

        /* Support grid alignments in print layout */
        .dashboard-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 15px !important;
        }
        
        #purchaseChart {
            max-width: 100% !important;
            height: 250px !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border-bottom: 1px solid #eee !important;
            padding: 10px !important;
            color: #000 !important;
        }
        
        tr {
            page-break-inside: avoid !important;
        }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'sales'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;" class="dark:text-white"><i class="ti ti-shopping-cart" style="color:#4f7cff;"></i> Purchase Report</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Purchase Report
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('purchase-history-table', 'purchase_report_{{ request('month', 'all') }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

        {{-- Interactive Filter Bar --}}
        <div class="filter-bar">
            <form action="{{ route('tenant.businessreport.purchase') }}" method="GET" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; width: 100%;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="year">Year:</label>
                    <select name="year" id="year" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="month">Month:</label>
                    <select name="month" id="month" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $mName = \Carbon\Carbon::create()->month($m)->format('F');
                            @endphp
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $mName }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px; font-weight: 700;">
                    <i class="ti ti-filter"></i> Apply Filter
                </button>
                @if(request('month') || request('year') != now()->year)
                    <a href="{{ route('tenant.businessreport.purchase') }}" class="btn btn-outline" style="padding: 6px 14px; font-size: 12px; font-weight: 700;">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Statistics Grid --}}
        <div class="stats-grid">
            @foreach($stats as $stat)
                <div class="stat-card">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    <div class="stat-meta">
                        @if($stat['trend'] == 'up')
                            <span class="meta-up"><i class="ti ti-trending-up"></i> {{ $stat['change'] }}</span>
                            <span style="color: #888;">from last period</span>
                        @elseif($stat['trend'] == 'down')
                            <span class="meta-down"><i class="ti ti-trending-down"></i> {{ $stat['change'] }}</span>
                            <span style="color: #888;">reduction</span>
                        @else
                            <span class="meta-neutral"><i class="ti ti-minus"></i> {{ $stat['change'] }}</span>
                            <span style="color: #888;">volume peak</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts & Analytics Panel --}}
        <div class="dashboard-grid">
            <!-- Purchase Expense Chart -->
            <div class="panel-box">
                <div class="panel-title">
                    <i class="ti ti-chart-bar" style="color: #4f7cff;"></i> Purchase Expenses Trend
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="purchaseChart"></canvas>
                </div>
            </div>

            <!-- Top Suppliers Panel -->
            <div class="panel-box">
                <div class="panel-title">
                    <i class="ti ti-building-store" style="color: #2ec4b6;"></i> Top Suppliers
                </div>
                <div class="supplier-list">
                    @foreach($topSuppliers as $supplier)
                        <div class="supplier-item">
                            <div class="supplier-info">
                                <span class="supplier-name">{{ $supplier['name'] }}</span>
                                <span class="supplier-val">{{ $supplier['total'] }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-bar" style="width: {{ $supplier['percent'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Ledger Table --}}
        <div class="ledger-section">
            <div class="panel-title" style="margin-bottom: 1.5rem; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-receipt" style="color: #4f7cff;"></i> Inward Purchase Ledgers
                </div>
                <span style="font-size: 11px; font-weight: 700; color: #888;" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-md">
                    Showing Page {{ $purchases->currentPage() }} of {{ $purchases->lastPage() }}
                </span>
            </div>

            <div class="ledger-table-wrap custom-scrollbar">
                <table class="ledger-table" id="purchase-history-table">
                    <thead>
                        <tr>
                            <th>Supplier Details</th>
                            <th>Ref Invoice</th>
                            <th>Issue Date</th>
                            <th>Payment Status</th>
                            <th style="text-align: right;">Total Amount</th>
                            <th style="text-align: right;">Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>
                                <div style="font-weight: 700;" class="text-slate-800 dark:text-slate-200">
                                    {{ $purchase->vendor->name ?? 'Direct Inward Supplier' }}
                                </div>
                                <div style="font-size: 10px; color: #888; font-weight: 600;">
                                    {{ $purchase->vendor->phone ?? 'No Phone' }} | {{ $purchase->vendor->gstin ?? 'No GSTIN' }}
                                </div>
                            </td>
                            <td style="font-family: 'DM Mono', monospace; font-weight: 700; color: #4f7cff;" class="uppercase">
                                {{ $purchase->invoice_ref ?: 'INW-' . $purchase->id }}
                            </td>
                            <td style="font-weight: 600;" class="text-slate-600 dark:text-slate-400">
                                {{ $purchase->invoice_date ? $purchase->invoice_date->format('d M, Y') : $purchase->created_at->format('d M, Y') }}
                            </td>
                            <td>
                                @php
                                    $status = strtolower($purchase->status ?: 'unpaid');
                                    if ($status == 'paid') {
                                        $color = '#2ec4b6';
                                    } elseif ($status == 'partial') {
                                        $color = '#ff9f1c';
                                    } else {
                                        $color = '#e71d36';
                                    }
                                @endphp
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; border: 1px solid {{ $color }}; color: {{ $color }}; background: {{ $color }}10;">
                                    {{ $purchase->status ?: 'Unpaid' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700;" class="text-slate-800 dark:text-slate-200">
                                ₹{{ number_format($purchase->total_amount, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: {{ $purchase->balance_amount > 0 ? '#e71d36' : '#2ec4b6' }};">
                                ₹{{ number_format($purchase->balance_amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No inward purchase entries found for the selected month/year.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($purchases->hasPages())
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                {{ $purchases->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('purchaseChart').getContext('2d');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Purchases (₹)',
                    data: @json($chartData['purchases']),
                    backgroundColor: 'rgba(79, 124, 255, 0.85)',
                    hoverBackgroundColor: 'rgba(79, 124, 255, 1)',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: context => '₹ ' + context.parsed.y.toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            callback: val => '₹' + (val/1000) + 'k',
                            color: labelColor,
                            font: { family: 'Segoe UI' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: labelColor,
                            font: { family: 'Segoe UI' }
                        }
                    }
                }
            }
        });
    });

    function exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cols = row.querySelectorAll('th, td');
            let rowData = [];
            
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.trim();
                text = text.replace(/[\n\r]+/g, ' '); // remove line breaks
                text = text.replace(/₹/g, ''); // remove currency symbol
                text = text.replace(/"/g, '""'); // escape double quotes
                
                if (text.includes(',') || text.includes(' ') || text.includes('"')) {
                    text = `"${text}"`;
                }
                rowData.push(text);
            }
            csv.push(rowData.join(','));
        }

        const csvContent = "\uFEFF" + csv.join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
