@extends('layouts.tenant')

@section('title', 'Vendor & Supplier Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Premium visual overrides that blend perfectly with the layout's dark mode and standard look */
    .sales-page-wrap {
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
    .stat-change { 
        font-size: 12px; 
        font-weight: 500; 
        display: flex; 
        align-items: center; 
        gap: 4px; 
    }
    .up { color: #10b981; }
    .down { color: #ef4444; }
    .neutral { color: #888; }

    /* ── Card ── */
    .card {
        background: #fff; 
        border: 1px solid #e8eaf0;
        border-radius: 14px; 
        padding: 20px; 
        margin-bottom: 1.5rem;
    }
    .dark .card {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .card-title {
        font-size: 14px; 
        font-weight: 700; 
        color: #1a1a2e;
        margin-bottom: 16px; 
        display: flex; 
        align-items: center; 
        gap: 8px;
    }
    .dark .card-title {
        color: #f8fafc;
    }
    .card-title i { color: #4f7cff; font-size: 18px; }

    /* ── Chart ── */
    .chart-wrap { position: relative; height: 300px; width: 100%; }

    /* ── Table ── */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th {
        text-align: left; padding: 10px 12px; background: #f4f6f9; color: #888; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .dark thead th { background: #1e293b; color: #94a3b8; }
    tbody tr { border-bottom: 1px solid #f0f0f0; }
    .dark tbody tr { border-bottom-color: #334155; }
    tbody tr:last-child { border-bottom: none; }
    tbody td { padding: 11px 12px; color: #1a1a2e; }
    .dark tbody td { color: #cbd5e1; }
    tbody tr:hover { background: #f9f9fb; }
    .dark tbody tr:hover { background: #1e293b/40; }

    /* ── Progress bar ── */
    .progress-wrap { background: #f0f0f0; border-radius: 99px; height: 6px; width: 100%; }
    .dark .progress-wrap { background: #334155; }
    .progress-bar { background: #10b981; border-radius: 99px; height: 6px; transition: width 0.6s; }

    /* ── Two col grid ── */
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 700px) {
        .two-col { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }

    /* ── Print Media Styles ── */
    @media print {
        aside, header, .ai-chatbot-container, nav, .btn-group, .filter-bar, button, a.btn, footer, #reportSearch, .blob {
            display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important;
        }
        body, html {
            background: #fff !important; color: #000 !important; font-family: 'Segoe UI', sans-serif !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; height: auto !important;
        }
        body > div.flex.h-screen, body > div.flex.h-screen > div.flex-1.flex.flex-col, body > div.flex.h-screen > div.flex-1.flex.flex-col > main, main > div.flex.h-\[calc\(100vh-3\.5rem\)\], main > div.flex.h-\[calc\(100vh-3\.5rem\)\] > div.flex-1.overflow-y-auto, .sales-page-wrap {
            display: block !important; overflow: visible !important; height: auto !important; width: 100% !important; max-width: 100% !important; position: relative !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; transform: none !important; left: 0 !important; top: 0 !important;
        }
        .stat-card, .panel-box, .glass-card, .card {
            border: 1px solid #ddd !important; background: #fff !important; color: #000 !important; box-shadow: none !important; border-radius: 8px !important; margin-bottom: 20px !important; page-break-inside: avoid !important;
        }
        .stats-grid { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 15px !important; }
        .two-col { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 20px !important; }
        table { width: 100% !important; border-collapse: collapse !important; }
        th, td { border-bottom: 1px solid #eee !important; padding: 10px !important; color: #000 !important; }
        tr { page-break-inside: avoid !important; }
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
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-truck" style="color:#10b981;"></i> Vendor & Supplier Performance Report</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Supplier Analytics
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Report
                </button>
                <button onclick="exportTableToCSV('vendor-ledger-table', 'vendor_report_{{ request('month', 'all') }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form action="{{ route('tenant.businessreport.vendor') }}" method="GET" class="filter-bar">
            <div>
                <label>Year</label><br>
                <select name="year">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label>Month</label><br>
                <select name="month">
                    <option value="">All Months</option>
                    @foreach([
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                    ] as $num => $label)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Filter Vendor / Supplier</label><br>
                <select name="vendor_id">
                    <option value="">All Suppliers</option>
                    @foreach($vendorsList as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search"></i> Apply Filter
                </button>
            </div>
        </form>

        {{-- Stat Cards --}}
        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-change {{ $stat['trend'] }}">
                    @if($stat['trend'] === 'up')   <i class="ti ti-trending-up"></i>
                    @elseif($stat['trend'] === 'down') <i class="ti ti-trending-down"></i>
                    @endif
                    {{ $stat['change'] }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bar Chart --}}
        <div class="card">
            <div class="card-title">
                <i class="ti ti-chart-bar"></i> Monthly Purchase & Inflow Volume — {{ $year }}
            </div>
            <div class="chart-wrap">
                <canvas id="vendorChart"></canvas>
            </div>
        </div>

        {{-- Two Col: Top Suppliers & Monthly Accumulation Table --}}
        <div class="two-col">

            {{-- Top Suppliers Breakdown --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-trophy"></i> Top Suppliers by Spend Volume
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Supplier Name & GSTIN</th>
                            <th>Invoices</th>
                            <th style="text-align: right;">Total Volume (₹)</th>
                            <th style="text-align: right;">Balance Due (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topVendors as $vendor)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #4f7cff;">{{ $vendor['name'] }}</div>
                                <div style="font-size: 11px; color: #888;">GSTIN: {{ $vendor['gstin'] }}</div>
                            </td>
                            <td style="font-weight: 600;">{{ $vendor['invoices'] }}</td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">₹{{ number_format($vendor['total'], 2) }}</td>
                            <td style="text-align: right; font-weight: 600; color: {{ $vendor['balance'] > 0 ? '#ef4444' : '#64748b' }};">
                                ₹{{ number_format($vendor['balance'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Monthly Summary Table --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-calendar"></i> Monthly Procurement Ledger
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Purchase Volume (₹)</th>
                            <th>MoM Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $labels = $chartData['labels'];
                            $purchases = $chartData['purchases'];
                        @endphp
                        @foreach ($labels as $i => $monthName)
                        <tr>
                            <td style="font-weight: 600;">{{ $monthName }}</td>
                            <td style="font-weight: 600; color: #10b981;">₹{{ number_format($purchases[$i]) }}</td>
                            <td>
                                @if ($i > 0)
                                    @php $diff = $purchases[$i] - $purchases[$i-1]; @endphp
                                    <span style="color: {{ $diff >= 0 ? '#10b981' : '#ef4444' }}; font-size:12px; font-weight:600;">
                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }}
                                    </span>
                                @else
                                    <span style="color:#888; font-size:12px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Transaction Ledger Table --}}
        <div class="card" style="margin-top: 16px;">
            <div class="card-title">
                <i class="ti ti-list-details"></i> Purchase Invoices Ledger
            </div>
            <div style="overflow-x: auto;">
                <table id="vendor-ledger-table">
                    <thead>
                        <tr>
                            <th>Invoice Ref</th>
                            <th>Date</th>
                            <th>Supplier Name & GSTIN</th>
                            <th style="text-align: right;">Total Amount (₹)</th>
                            <th style="text-align: right;">Balance Pending (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">
                                #{{ $tx->invoice_ref ?: 'PUR-' . $tx->id }}
                            </td>
                            <td>{{ $tx->invoice_date->format('d M Y') }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $tx->vendor?->name ?: 'Vendor #' . $tx->vendor_id }}</div>
                                <div style="font-size: 11px; color: #888;">{{ $tx->vendor?->gstin ?: 'Unregistered' }}</div>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                ₹{{ number_format($tx->total_amount, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: {{ $tx->balance_amount > 0 ? '#ef4444' : '#64748b' }};">
                                ₹{{ number_format($tx->balance_amount, 2) }}
                            </td>
                            <td>
                                @if($tx->status === 'paid' || $tx->balance_amount <= 0)
                                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #dcfce7; color: #15803d;">Paid</span>
                                @elseif($tx->balance_amount < $tx->total_amount)
                                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #b45309;">Partial</span>
                                @else
                                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #b91c1c;">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No purchase records found for selected period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($transactions->hasPages())
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                {{ $transactions->links() }}
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
        const ctx = document.getElementById('vendorChart')?.getContext('2d');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Purchase Inflow Volume (₹)',
                    data: @json($chartData['purchases']),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
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
                        ticks: { color: labelColor, font: { family: 'Segoe UI' } }
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
