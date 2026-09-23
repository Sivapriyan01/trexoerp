@extends('layouts.tenant')

@section('title', 'Warranty/Service Report')

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

        .badge-delivered {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-transit {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-returned {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            width: 100%;
            max-width: 500px;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-height: 90vh;
            overflow-y: auto;
        }

        .dark .modal-content {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid #334155;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
        }

        .dark .form-group label {
            color: #94a3b8;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .dark .form-control {
            background: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        .form-control:focus {
            border-color: #4f7cff;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #888;
        }

        .dark .close-btn {
            color: #cbd5e1;
        }

        .close-btn:hover {
            color: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
        @include('tenant.partials.reports_sidebar', ['active' => 'product'])

        <!-- Right Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

            {{-- Header --}}
            <div class="page-header">
                <div>
                    <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-tool"
                            style="color:#4f7cff;"></i> Warranty/Service Report</h1>
                    <div class="breadcrumb" style="margin-top: 2px;">
                        <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Warranty Report
                    </div>
                </div>
                <div class="btn-group">
                    <button onclick="window.print()" class="btn btn-outline">
                        <i class="ti ti-printer"></i> Print
                    </button>
                    <button onclick="exportTableToCSV('warranty-table', 'warranty_report.csv')" class="btn btn-primary">
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
                            @if($stat['trend'] === 'up') <i class="ti ti-trending-up"></i>
                            @elseif($stat['trend'] === 'down') <i class="ti ti-trending-down"></i>
                            @endif
                            {{ $stat['change'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Claims Table --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-list-details"></i> Recent Warranty Claims
                </div>
                <div style="overflow-x: auto;">
                    <table id="warranty-table">
                        <thead>
                            <tr>
                                <th>Claim ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Issue Description</th>
                                <th>Status</th>
                                <th>Res. Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($claims as $claim)
                                @php
                                    $badgeClass = 'badge-pending';
                                    if ($claim['status'] === 'Resolved')
                                        $badgeClass = 'badge-delivered';
                                    elseif ($claim['status'] === 'In Progress')
                                        $badgeClass = 'badge-transit';
                                    elseif ($claim['status'] === 'Rejected')
                                        $badgeClass = 'badge-returned'; 
                                @endphp
                                <tr>
                                    <td style="font-weight: 700; color: #4f7cff;">{{ $claim['claim_id'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($claim['date'])->format('d M Y') }}</td>
                                    <td>
                                        <div>{{ $claim['customer_name'] }}</div>
                                        <div style="font-size: 10px; color: #888;">{{ $claim['customer_phone'] }}</div>
                                    </td>
                                    <td style="font-weight: 600;">{{ $claim['product_name'] }}</td>
                                    <td>{{ $claim['issue'] }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $claim['status'] }}</span>
                                    </td>
                                    <td>{{ $claim['resolution_time'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                        No warranty claims found for this period.
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