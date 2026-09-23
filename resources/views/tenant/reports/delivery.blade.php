@extends('layouts.tenant')

@section('title', 'Delivery/Order Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Premium visual overrides */
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
    .up { 
        color: #10b981; 
    }
    .down { 
        color: #ef4444; 
    }
    .neutral { 
        color: #888; 
    }

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
    .card-title i { 
        color: #4f7cff; 
        font-size: 18px; 
    }

    /* ── Chart ── */
    .chart-wrap { 
        position: relative; 
        height: 250px; 
        width: 100%; 
    }

    /* ── Table ── */
    table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 13px; 
    }
    thead th {
        text-align: left; 
        padding: 10px 12px;
        background: #f4f6f9; 
        color: #888;
        font-weight: 600; 
        font-size: 11px;
        text-transform: uppercase; 
        letter-spacing: 0.5px;
    }
    .dark thead th {
        background: #1e293b;
        color: #94a3b8;
    }
    tbody tr { 
        border-bottom: 1px solid #f0f0f0; 
    }
    .dark tbody tr {
        border-bottom-color: #334155;
    }
    tbody tr:last-child { 
        border-bottom: none; 
    }
    tbody td { 
        padding: 11px 12px; 
        color: #1a1a2e; 
    }
    .dark tbody td {
        color: #cbd5e1;
    }
    tbody tr:hover { 
        background: #f9f9fb; 
    }
    .dark tbody tr:hover {
        background: #1e293b/40;
    }

    /* ── Badges ── */
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .badge-delivered { background: #e0f2fe; color: #0369a1; }
    .badge-pending { background: #fef3c7; color: #b45309; }
    .badge-transit { background: #e0e7ff; color: #4338ca; }
    .badge-returned { background: #fee2e2; color: #b91c1c; }

    /* ── Two col grid ── */
    .two-col { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 16px; 
    }

    @media (max-width: 700px) {
        .two-col { 
            grid-template-columns: 1fr; 
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
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-truck-delivery" style="color:#4f7cff;"></i> Delivery/Order Report</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Delivery Report
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('delivery-table', 'delivery_report.csv')" class="btn btn-primary">
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

        {{-- Two Col: Charts --}}
        <div class="two-col">

            {{-- Status Doughnut Chart --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-chart-donut"></i> Delivery Status
                </div>
                <div class="chart-wrap">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Courier Bar Chart --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-chart-bar"></i> Courier On-Time Rate (%)
                </div>
                <div class="chart-wrap">
                    <canvas id="courierChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="card">
            <div class="card-title">
                <i class="ti ti-list-details"></i> Recent Orders & Fulfillment
            </div>
            <div style="overflow-x: auto;">
                <table id="delivery-table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Courier</th>
                            <th>Est. Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        @php
                            $badgeClass = 'badge-pending';
                            if ($order['status'] === 'Delivered') $badgeClass = 'badge-delivered';
                            elseif ($order['status'] === 'In Transit') $badgeClass = 'badge-transit';
                            elseif ($order['status'] === 'Returned') $badgeClass = 'badge-returned';
                        @endphp
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">{{ $order['invoice_no'] }}</td>
                            <td>{{ $order['customer'] }}</td>
                            <td>{{ $order['date'] }}</td>
                            <td style="font-weight: 600;">₹{{ number_format($order['amount'], 2) }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $order['status'] }}</span>
                            </td>
                            <td>{{ $order['courier'] }}</td>
                            <td>{{ $order['delivery_time'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No orders found.
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
        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        const labelColor = isDark ? '#94a3b8' : '#888';
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['statusLabels']),
                    datasets: [{
                        data: @json($chartData['statusValues']),
                        backgroundColor: [
                            'rgba(3, 105, 161, 0.8)',  // Delivered
                            'rgba(245, 158, 11, 0.8)',  // Pending
                            'rgba(99, 102, 241, 0.8)',  // Transit
                            'rgba(239, 68, 68, 0.8)'    // Returned
                        ],
                        borderWidth: 1,
                        borderColor: isDark ? '#1e293b' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: labelColor, font: { family: 'Segoe UI' } }
                        }
                    }
                }
            });
        }

        // Courier Chart
        const courierCtx = document.getElementById('courierChart').getContext('2d');
        if (courierCtx) {
            new Chart(courierCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['courierLabels']),
                    datasets: [{
                        label: 'On-Time Rate (%)',
                        data: @json($chartData['courierValues']),
                        backgroundColor: 'rgba(79, 124, 255, 0.8)',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: gridColor },
                            ticks: { color: labelColor, font: { family: 'Segoe UI' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: labelColor, font: { family: 'Segoe UI' } }
                        }
                    }
                }
            });
        }
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
                text = text.replace(/"/g, '""'); // escape double quotes
                text = text.replace(/₹/g, ''); // remove currency symbol
                
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
