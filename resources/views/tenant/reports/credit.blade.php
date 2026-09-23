@extends('layouts.tenant')

@section('title', 'Credit & Due Report')

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
    .card-title i { color: #f59e0b; font-size: 18px; }

    .chart-container { position: relative; height: 320px; width: 100%; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { text-align: left; padding: 10px 12px; background: #f4f6f9; color: #888; font-size: 11px; text-transform: uppercase; }
    .dark thead th { background: #1e293b; color: #94a3b8; }
    tbody tr { border-bottom: 1px solid #f0f0f0; }
    .dark tbody tr { border-bottom-color: #334155; }
    tbody td { padding: 11px 12px; color: #1a1a2e; }
    .dark tbody td { color: #cbd5e1; }
    
    .badge-due { padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .dark .badge-due { background: rgba(194, 65, 12, 0.1); color: #fb923c; border-color: rgba(194, 65, 12, 0.2); }

    .customer-name { font-weight: 600; color: #1a1a2e; }
    .dark .customer-name { color: #f8fafc; }
    .due-amount { font-weight: 700; color: #ef4444; }

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
                <h1><i class="ti ti-wallet"></i> Credit & Due Report</h1>
                <div class="breadcrumb">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Credit Analysis
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('credit-ledger-table', 'credit_report_{{ request('month', 'all') }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

        <form action="{{ route('tenant.businessreport.credit') }}" method="GET" class="filter-bar">
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
                <div class="stat-change {{ $stat['trend'] == 'up' ? 'text-rose-500' : ($stat['trend'] == 'emerald' ? 'text-emerald-500' : 'text-slate-400') }}">
                    <i class="ti ti-trending-{{ $stat['trend'] == 'neutral' ? 'up' : ($stat['trend'] == 'emerald' ? 'up' : $stat['trend']) }}"></i>
                    {{ $stat['change'] }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-chart-pie"></i> Debt Aging Analysis (Value by Days Overdue)</div>
            <div class="chart-container">
                <canvas id="agingChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-list-check"></i> Outstanding Invoices Ledger</div>
            <div class="overflow-x-auto">
                <table id="credit-ledger-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Age</th>
                            <th style="text-align: right;">Total Bill</th>
                            <th style="text-align: right;">Amount Paid</th>
                            <th style="text-align: right;">Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingBills as $bill)
                        @php
                            $due = $bill->grand_total - $bill->paid_amount;
                            $age = (int) \Carbon\Carbon::parse($bill->bill_date)->diffInDays(now());
                        @endphp
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">#{{ $bill->invoice_no }}</td>
                            <td>{{ is_string($bill->bill_date) ? \Carbon\Carbon::parse($bill->bill_date)->format('d M, Y') : $bill->bill_date->format('d M, Y') }}</td>
                            <td class="customer-name">{{ $bill->customer?->name ?: 'Walk-in' }} <br><small class="text-slate-400">{{ $bill->customer_phone }}</small></td>
                            <td>
                                <span class="badge-due">
                                    {{ $age }} Days
                                </span>
                            </td>
                            <td style="text-align: right;">₹{{ number_format($bill->grand_total, 2) }}</td>
                            <td style="text-align: right; color: #10b981;">₹{{ number_format($bill->paid_amount, 2) }}</td>
                            <td style="text-align: right;" class="due-amount">₹{{ number_format($due, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                                <i class="ti ti-circle-check" style="font-size: 32px; display: block; margin-bottom: 8px; color: #10b981;"></i>
                                No outstanding dues found for this period.
                            </td>
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

        new Chart(document.getElementById('agingChart'), {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Outstanding Amount (₹)',
                    data: @json($chartData['values']),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)', // 0-30
                        'rgba(245, 158, 11, 0.8)', // 31-60
                        'rgba(249, 115, 22, 0.8)', // 61-90
                        'rgba(239, 68, 68, 0.8)'   // 90+
                    ],
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
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: v => '₹' + (v/1000) + 'k' } }
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
