@extends('layouts.tenant')

@section('title', 'Order Cancellation Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Premium visual overrides */
    .sales-page-wrap { max-width: 1200px; margin: 0 auto; padding: 1rem 0; }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 20px; font-weight: 700; color: #1a1a2e; }
    .dark .page-header h1 { color: #f8fafc; }
    .breadcrumb { font-size: 12px; color: #888; margin-top: 2px; }
    .breadcrumb a { color: #4f7cff; text-decoration: none; }
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: opacity 0.15s; }
    .btn:hover { opacity: 0.85; }
    .btn-primary { background: #4f7cff; color: #fff; }
    .btn-outline { background: #fff; color: #555; border: 1px solid #e0e0e0; }
    .dark .btn-outline { background: #0f172a; color: #cbd5e1; border-color: #334155; }
    .btn-group { display: flex; gap: 8px; }

    /* Stat Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 1.5rem; }
    .stat-card { background: #fff; border: 1px solid #e8eaf0; border-radius: 12px; padding: 18px; }
    .dark .stat-card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .stat-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 6px; }
    .stat-value { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .dark .stat-value { color: #f8fafc; }
    .stat-change { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 4px; }
    .up { color: #10b981; }
    .down { color: #ef4444; }
    .neutral { color: #888; }

    /* Card & Table */
    .card { background: #fff; border: 1px solid #e8eaf0; border-radius: 14px; padding: 20px; margin-bottom: 1.5rem; }
    .dark .card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .card-title { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .dark .card-title { color: #f8fafc; }
    .card-title i { color: #4f7cff; font-size: 18px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { text-align: left; padding: 10px 12px; background: #f4f6f9; color: #888; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .dark thead th { background: #1e293b; color: #94a3b8; }
    tbody tr { border-bottom: 1px solid #f0f0f0; }
    .dark tbody tr { border-bottom-color: #334155; }
    tbody tr:last-child { border-bottom: none; }
    tbody td { padding: 11px 12px; color: #1a1a2e; }
    .dark tbody td { color: #cbd5e1; }
    tbody tr:hover { background: #f9f9fb; }
    .dark tbody tr:hover { background: #1e293b/40; }

    /* Badges */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    .badge-refunded { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fef3c7; color: #b45309; }
    .badge-norefund { background: #f3f4f6; color: #4b5563; }
    .dark .badge-norefund { background: #374151; color: #9ca3af; }
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
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-ban" style="color:#ef4444;"></i> Order Cancellation Report</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Cancellation Report
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('cancellation-table', 'cancellation_report.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export
                </button>
            </div>
        </div>

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

        {{-- Table --}}
        <div class="card">
            <div class="card-title">
                <i class="ti ti-list-details"></i> Cancelled Orders List
            </div>
            <div style="overflow-x: auto;">
                <table id="cancellation-table">
                    <thead>
                        <tr>
                            <th>Order Ref</th>
                            <th>Date Cancelled</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Refund Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cancellations as $canc)
                        @php
                            $badgeClass = 'badge-pending';
                            if ($canc['status'] === 'Refunded') $badgeClass = 'badge-refunded'; 
                            elseif ($canc['status'] === 'No Refund Required') $badgeClass = 'badge-norefund'; 
                        @endphp
                        <tr>
                            <td style="font-weight: 700; color: #ef4444;">{{ $canc['order_id'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($canc['date'])->format('d M Y, h:i A') }}</td>
                            <td style="font-weight: 600;">{{ $canc['customer'] }}</td>
                            <td style="font-weight: 700;">₹{{ number_format($canc['amount'], 2) }}</td>
                            <td style="color: #64748b;">{{ $canc['reason'] }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $canc['status'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No cancellations found for this period.
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
<script>
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
                text = text.replace(/"/g, '""'); 
                text = text.replace(/₹/g, ''); 
                
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
