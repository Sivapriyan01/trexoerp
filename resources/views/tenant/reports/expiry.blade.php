@extends('layouts.tenant')

@section('title', 'Expiry Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    .page-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;
    }
    .page-header h1 { font-size: 24px; font-weight: 800; color: #1a1a2e; }
    .dark .page-header h1 { color: #f8fafc; }
    .breadcrumb { font-size: 12px; color: #888; margin-top: 2px; }
    .breadcrumb a { color: #4f7cff; text-decoration: none; }

    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 12px; font-size: 14px;
        font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s;
    }
    .btn-outline { background: #fff; color: #555; border: 1px solid #e0e0e0; }
    .dark .btn-outline { background: #0f172a; color: #cbd5e1; border-color: #334155; }
    .btn-emerald { background: #4f7cff; color: #fff; box-shadow: 0 4px 14px 0 rgba(79, 124, 255, 0.39); }
    .btn:hover { transform: translateY(-2px); opacity: 0.9; }

    .filter-bar {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 16px; 
        padding: 18px 24px; display: flex; align-items: center; gap: 24px;
        flex-wrap: wrap; margin-bottom: 2rem;
    }
    .dark .filter-bar { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .filter-bar label { font-size: 11px; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-bar select { padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; outline: none; background: #f9f9fb; }
    .dark .filter-bar select { background: #0f172a; border-color: #334155; color: #f8fafc; }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px; margin-bottom: 2rem;
    }
    .stat-card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 20px; padding: 24px;
    }
    .dark .stat-card { background: rgba(15, 23, 42, 0.6); border-color: rgba(51, 65, 85, 0.5); }
    .stat-label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; }
    .stat-value { font-size: 28px; font-weight: 800; color: #1a1a2e; }
    .dark .stat-value { color: #f8fafc; }
    .stat-meta { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; margin-top: 6px; }

    .grid-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    @media (max-width: 1024px) { .grid-layout { grid-template-columns: 1fr; } }

    .card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 24px; 
        padding: 24px; margin-bottom: 2rem;
    }
    .dark .card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .card-title {
        font-size: 16px; font-weight: 800; color: #1a1a2e;
        margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .dark .card-title { color: #f8fafc; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; padding: 12px 16px; background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .dark thead th { background: #1e293b; color: #94a3b8; }
    tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
    .dark tbody tr { border-bottom-color: #334155; }
    tbody tr:hover { background: #f8fafc; }
    .dark tbody tr:hover { background: rgba(30, 41, 59, 0.5); }
    tbody td { padding: 14px 16px; color: #1e293b; }
    .dark tbody td { color: #cbd5e1; }

    .badge {
        padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    }
    .badge-expired { background: #fef2f2; color: #dc2626; }
    .badge-expiring { background: #fffbeb; color: #d97706; }

    .chart-container { height: 350px; position: relative; }

    @media print {
        aside, header, nav, .btn, .filter-bar { display: none !important; }
        .card, .stat-card { border: 1px solid #eee !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'inventory'])

    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        
        <div class="page-header">
            <div>
                <h1><i class="ti ti-calendar-xmark"></i> Expiry Report</h1>
                <div class="breadcrumb">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Expiry Tracking
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Report
                </button>
                <button onclick="exportTableToCSV('expiry-table', 'expiry_report.csv')" class="btn btn-emerald">
                    <i class="ti ti-download"></i> Export Data
                </button>
            </div>
        </div>

        <form action="{{ route('tenant.businessreport.expiry') }}" method="GET" class="filter-bar">
            <div>
                <label>Status</label><br>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Items</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="expiring" {{ request('status') == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                </select>
            </div>
            <div>
                <label>Timeframe</label><br>
                <select name="timeframe" onchange="this.form.submit()">
                    <option value="30" {{ request('timeframe') == '30' ? 'selected' : '' }}>Next 30 Days</option>
                    <option value="60" {{ request('timeframe') == '60' ? 'selected' : '' }}>Next 60 Days</option>
                    <option value="90" {{ request('timeframe') == '90' ? 'selected' : '' }}>Next 90 Days</option>
                </select>
            </div>
        </form>

        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card border-b-4 border-{{ $stat['trend'] == 'up' ? 'rose' : ($stat['trend'] == 'down' ? 'emerald' : 'slate') }}-500">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-meta">{{ $stat['change'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid-layout">
            <div class="card">
                <div class="card-title"><i class="ti ti-chart-bar"></i> Expiry Timeline</div>
                <div class="chart-container">
                    <canvas id="expiryTimelineChart"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-title"><i class="ti ti-chart-pie"></i> Expiry by Category</div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-list-search"></i> Expiring & Expired Items</div>
            <div class="overflow-x-auto">
                <table id="expiry-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Qty</th>
                            <th>Cost Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                        <tr>
                            <td>
                                <div class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $item['name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $item['brand'] }}</div>
                            </td>
                            <td class="text-xs font-bold text-slate-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item['expiry_date'])->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $item['status'] }}">
                                    {{ $item['status'] == 'expired' ? 'Expired' : 'Expiring Soon' }}
                                </span>
                            </td>
                            <td class="font-bold">{{ number_format($item['qty']) }}</td>
                            <td class="font-bold text-slate-700 dark:text-slate-300">₹{{ number_format($item['value']) }}</td>
                            <td>
                                <form action="{{ route('tenant.businessreport.expiry.dispose') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to dispose of this item?')">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                    <input type="hidden" name="qty" value="{{ $item['qty'] }}">
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors">
                                        <i class="ti ti-trash"></i> Dispose
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400 italic">No items found matching the criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        // 1. Expiry Timeline Chart
        new Chart(document.getElementById('expiryTimelineChart'), {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Expired Value (₹)',
                        data: @json($chartData['expiredValues']),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Expiring Value (₹)',
                        data: @json($chartData['expiringValues']),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { color: labelColor, usePointStyle: true, font: { weight: 'bold' } } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor } }
                }
            }
        });

        // 2. Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: @json($chartData['catLabels']),
                datasets: [{
                    data: @json($chartData['catValues']),
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: labelColor, boxWidth: 10, font: { size: 10 } } }
                },
                cutout: '75%'
            }
        });
    });

    function exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        let csv = [];
        const rows = table.querySelectorAll('tr');
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cols = row.querySelectorAll('th, td');
            let rowData = [];
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.trim().replace(/[\n\r]+/g, ' ');
                if (text.includes(',') || text.includes('"')) text = `"${text.replace(/"/g, '""')}"`;
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
        link.click();
    }
</script>
@endpush
