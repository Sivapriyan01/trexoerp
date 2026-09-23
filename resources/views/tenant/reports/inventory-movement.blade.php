@extends('layouts.tenant')

@section('title', 'Inventory Movement Report')

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
    .btn-emerald { background: #4f7cff; color: #fff; box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39); }
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
    .badge-in { background: #ecfdf5; color: #4f7cff; }
    .badge-out { background: #fef2f2; color: #dc2626; }

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
                <h1><i class="ti ti-truck-moving"></i> Inventory Movement</h1>
                <div class="breadcrumb">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Stock Logistics
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('movement-table', 'inventory_movement_{{ $year }}.csv')" class="btn btn-emerald">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

        <form action="{{ route('tenant.businessreport.inventory-movement') }}" method="GET" class="filter-bar">
            <div>
                <label>Year</label><br>
                <select name="year" onchange="this.form.submit()">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label>Month</label><br>
                <select name="month" onchange="this.form.submit()">
                    <option value="">Full Year</option>
                    @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'] as $num => $label)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card border-b-4 border-{{ $stat['trend'] == 'up' ? 'emerald' : ($stat['trend'] == 'down' ? 'rose' : 'slate') }}-500">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-meta">{{ $stat['change'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid-layout">
            <div class="card">
                <div class="card-title"><i class="ti ti-chart-bar"></i> Monthly Stock Velocity (In vs Out)</div>
                <div class="chart-container">
                    <canvas id="movementTrendChart"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-title"><i class="ti ti-flame"></i> High Turnover Products</div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-list-search"></i> Movement Ledger</div>
            <div class="overflow-x-auto">
                <table id="movement-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Old Stock</th>
                            <th>New Stock</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $log)
                        <tr>
                            <td class="text-xs font-bold text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <div class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $log->product?->product_name ?: 'System Adjustment' }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $log->product?->brand ?: 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->type }}">
                                    {{ $log->type }}
                                </span>
                            </td>
                            <td class="font-black {{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->type == 'in' ? '+' : '-' }}{{ number_format($log->quantity) }}
                            </td>
                            <td class="text-slate-500">{{ number_format($log->old_stock) }}</td>
                            <td class="font-bold">{{ number_format($log->new_stock) }}</td>
                            <td class="text-xs text-slate-400 italic">{{ $log->remark ?: '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 italic">No movement logs found for the selected period.</td>
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

        // 1. Movement Trend Chart
        new Chart(document.getElementById('movementTrendChart'), {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Stock In',
                        data: @json($chartData['inbound']),
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    },
                    {
                        label: 'Stock Out',
                        data: @json($chartData['outbound']),
                        backgroundColor: '#ef4444',
                        borderRadius: 6
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

        // 2. Top Products Chart
        new Chart(document.getElementById('topProductsChart'), {
            type: 'doughnut',
            data: {
                labels: @json($chartData['topLabels']),
                datasets: [{
                    data: @json($chartData['topValues']),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444'],
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
