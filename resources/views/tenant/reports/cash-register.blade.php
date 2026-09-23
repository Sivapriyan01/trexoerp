@extends('layouts.tenant')

@section('title', 'Cash Register Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
    .page-header h1 { font-size: 24px; font-weight: 800; color: #1a1a2e; }
    .dark .page-header h1 { color: #f8fafc; }
    .breadcrumb { font-size: 12px; color: #888; margin-top: 2px; }
    .breadcrumb a { color: #4f7cff; text-decoration: none; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
    .btn-outline { background: #fff; color: #555; border: 1px solid #e0e0e0; }
    .dark .btn-outline { background: #0f172a; color: #cbd5e1; border-color: #334155; }
    .btn-emerald { background: #4f7cff; color: #fff; box-shadow: 0 4px 14px 0 rgba(79, 124, 255, 0.39); }
    .btn:hover { transform: translateY(-2px); opacity: 0.9; }
    .card { background: #fff; border: 1px solid #e8eaf0; border-radius: 24px; padding: 24px; margin-bottom: 2rem; }
    .dark .card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .card-title { font-size: 16px; font-weight: 800; color: #1a1a2e; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
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

    @media print {
        aside, header, nav, .btn, .filter-bar { display: none !important; }
        .card, .stat-card { border: 1px solid #eee !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'financial'])

    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        
        <div class="page-header">
            <div>
                <h1><i class="ti ti-cash-register"></i> Cash Register Report</h1>
                <div class="breadcrumb">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Cash Summaries
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('cash-table', 'cash_register_report.csv')" class="btn btn-emerald">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

        <form action="{{ route('tenant.businessreport.cash-register') }}" method="GET" class="filter-bar">
            <div>
                <label>Year</label><br>
                <select name="year" onchange="this.form.submit()">
                    @foreach (range(now()->year, now()->year - 5) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Month</label><br>
                <select name="month" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card border-b-4 border-blue-500">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-meta">{{ $stat['change'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title"><i class="ti ti-list"></i> Daily Cash Summaries</div>
            <div class="overflow-x-auto">
                <table id="cash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Cash Sales</th>
                            <th>Cash Expenses</th>
                            <th>Net Cash</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summaries as $summary)
                        <tr>
                            <td class="text-xs font-bold text-slate-500">{{ date('d M Y', strtotime($summary['date'])) }}</td>
                            <td class="font-bold text-emerald-600">₹{{ number_format($summary['sales'], 2) }}</td>
                            <td class="font-bold text-rose-600">₹{{ number_format($summary['expenses'], 2) }}</td>
                            <td class="font-bold {{ $summary['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">₹{{ number_format($summary['net'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-12 text-slate-400 italic">No cash transactions found for this period.</td>
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
<script>
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
