@extends('layouts.tenant')

@section('title', 'Return & Refund Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    .page-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;
    }
    .page-header h1 { font-size: 20px; font-weight: 700; color: #1a1a2e; }
    .dark .page-header h1 { color: #f8fafc; }
    .breadcrumb { font-size: 12px; color: #888; margin-top: 2px; }
    .breadcrumb a { color: #4f7cff; text-decoration: none; }

    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 8px; font-size: 13px;
        font-weight: 500; cursor: pointer; border: none; text-decoration: none; 
    }
    .btn-outline { background: #fff; color: #555; border: 1px solid #e0e0e0; }
    .dark .btn-outline { background: #0f172a; color: #cbd5e1; border-color: #334155; }
    .btn-primary { background: #4f7cff; color: #fff; }
    .btn:hover { opacity: 0.85; }

    .filter-bar {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 12px; 
        padding: 14px 18px; display: flex; align-items: center; gap: 16px;
        flex-wrap: wrap; margin-bottom: 1.5rem;
    }
    .dark .filter-bar { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .filter-bar label { font-size: 11px; color: #888; font-weight: 600; text-transform: uppercase; }
    .filter-bar select { padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 7px; font-size: 13px; outline: none; background: #f9f9fb; }
    .dark .filter-bar select { background: #0f172a; border-color: #334155; color: #f8fafc; }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px; margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 12px; padding: 18px;
    }
    .dark .stat-card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .stat-label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 6px; }
    .stat-value { font-size: 22px; font-weight: 700; color: #1a1a2e; }
    .dark .stat-value { color: #f8fafc; }
    .stat-change { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 4px; margin-top: 4px; }

    .card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 14px; 
        padding: 20px; margin-bottom: 1.5rem;
    }
    .dark .card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .card-title {
        font-size: 14px; font-weight: 700; color: #1a1a2e;
        margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    }
    .dark .card-title { color: #f8fafc; }
    .card-title i { color: #f43f5e; font-size: 18px; }

    .chart-container { position: relative; height: 320px; width: 100%; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { text-align: left; padding: 10px 12px; background: #f4f6f9; color: #888; font-size: 11px; text-transform: uppercase; }
    .dark thead th { background: #1e293b; color: #94a3b8; }
    tbody tr { border-bottom: 1px solid #f0f0f0; }
    .dark tbody tr { border-bottom-color: #334155; }
    tbody td { padding: 11px 12px; color: #1a1a2e; }
    .dark tbody td { color: #cbd5e1; }
    
    .badge-status { padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    
    @media print {
        aside, header, .ai-chatbot-container, nav, .btn-group, .flex.gap-2, .filter-bar, button, a.btn, footer, #reportSearch, .blob {
            display: none !important;
            visibility: hidden !important;
        }
        body, .flex.h-screen, .flex-1, main, .flex.h-\[calc\(100vh-3\.5rem\)\], .overflow-y-auto {
            display: block !important;
            overflow: visible !important;
            height: auto !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .stat-card, .card {
            border: 1px solid #eee !important;
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
        }
        .stats-grid { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 10px !important; }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'sales'])

    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        
        <div class="page-header">
            <div>
                <h1><i class="ti ti-refresh"></i> Return & Refund Report</h1>
                <div class="breadcrumb">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Return Analysis
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('returns-ledger-table', 'returns_report_{{ request('month', 'all') }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

        <form action="{{ route('tenant.businessreport.returns') }}" method="GET" class="filter-bar">
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
                    <option value="">All Months</option>
                    @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'] as $num => $label)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-change {{ $stat['trend'] == 'up' ? 'text-emerald-500' : ($stat['trend'] == 'down' ? 'text-rose-500' : 'text-slate-400') }}">
                    <i class="ti ti-trending-{{ $stat['trend'] == 'neutral' ? 'up' : $stat['trend'] }}"></i>
                    {{ $stat['change'] }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-chart-bar"></i> Monthly Refund Volume (Value)</div>
            <div class="chart-container">
                <canvas id="returnTrendChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-list-search"></i> Returns & Refunds Ledger</div>
            <div class="overflow-x-auto">
                <table id="returns-ledger-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Reason/Remark</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Amount Refunded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returnedInvoices as $bill)
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">#{{ $bill->invoice_no }}</td>
                            <td>{{ is_string($bill->bill_date) ? \Carbon\Carbon::parse($bill->bill_date)->format('d M, Y') : $bill->bill_date->format('d M, Y') }}</td>
                            <td style="font-weight: 600;">{{ $bill->customer?->name ?: 'Walk-in' }}</td>
                            <td class="text-slate-500 italic text-xs">{{ $bill->remarks ?: 'No reason provided' }}</td>
                            <td style="text-align: center;">
                                <span class="badge-status {{ $bill->status == 'returned' ? 'bg-rose-100 text-rose-600 border border-rose-200' : ($bill->status == 'refunded' ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                    {{ $bill->status }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #f43f5e;">₹{{ number_format($bill->grand_total, 2) }}</td>
                        </tr>
                        @endforeach
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

        new Chart(document.getElementById('returnTrendChart'), {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Refunded Value (₹)',
                    data: @json($chartData['values']),
                    backgroundColor: 'rgba(244, 63, 94, 0.8)',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: v => '₹' + v } }
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
                text = text.replace(/[\n\r]+/g, ' '); 
                text = text.replace(/₹/g, ''); 
                text = text.replace(/"/g, '""'); 
                
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
