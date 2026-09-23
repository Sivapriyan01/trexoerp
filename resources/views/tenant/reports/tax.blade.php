@extends('layouts.tenant')

@section('title', 'Tax & GST Filing Report')

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

    /* ── Tab Navigation Pills ── */
    .tab-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        padding: 6px;
        border-radius: 12px;
        border: 1px solid #e8eaf0;
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }
    .dark .tab-pills {
        background: rgba(15, 23, 42, 0.4);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .tab-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .tab-pill:hover {
        color: #1e293b;
        background: #f1f5f9;
    }
    .dark .tab-pill:hover {
        color: #f8fafc;
        background: #1e293b;
    }
    .tab-pill.active {
        background: #4f7cff;
        color: #fff;
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
        height: 300px; 
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

    /* ── Progress bar ── */
    .progress-wrap { 
        background: #f0f0f0; 
        border-radius: 99px; 
        height: 6px; 
        width: 100%; 
    }
    .dark .progress-wrap {
        background: #334155;
    }
    .progress-bar { 
        background: #f59e0b; 
        border-radius: 99px; 
        height: 6px; 
        transition: width 0.6s; 
    }

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
        .stats-grid { 
            grid-template-columns: 1fr 1fr; 
        }
    }

    /* ── Print Media Styles ── */
    @media print {
        aside, 
        header,
        .ai-chatbot-container,
        nav, 
        .btn-group, 
        .filter-bar, 
        .tab-pills,
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

        body > div.flex.h-screen,
        body > div.flex.h-screen > div.flex-1.flex.flex-col,
        body > div.flex.h-screen > div.flex-1.flex.flex-col > main,
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\],
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\] > div.flex-1.overflow-y-auto,
        .sales-page-wrap {
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

        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 15px !important;
        }

        .two-col {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 20px !important;
        }
        
        #taxChart {
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
    @include('tenant.partials.reports_sidebar', ['active' => 'tax'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-receipt" style="color:#f59e0b;"></i> Tax & GST Filing Report</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Tax & GST Reports
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Filing
                </button>
                <button onclick="exportTableToCSV('taxes-history-table', 'gst_report_{{ $tab }}_{{ request('month', 'all') }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export GSTR
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form action="{{ route('tenant.businessreport.tax') }}" method="GET" class="filter-bar">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label>Filing Year</label><br>
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
            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search"></i> Apply Filter
                </button>
            </div>
        </form>

        {{-- Tab Navigation Pills --}}
        <div class="tab-pills">
            <a href="{{ route('tenant.businessreport.tax', ['tab' => 'summary', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'summary' ? 'active' : '' }}">
                <i class="ti ti-dashboard"></i> Tax Summary Overview
            </a>
            <a href="{{ route('tenant.businessreport.tax', ['tab' => 'gstr1', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'gstr1' ? 'active' : '' }}">
                <i class="ti ti-file-text"></i> GSTR-1 (Outward Supplies)
            </a>
            <a href="{{ route('tenant.businessreport.tax', ['tab' => 'gstr3b', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'gstr3b' ? 'active' : '' }}">
                <i class="ti ti-file-analytics"></i> GSTR-3B (Monthly Summary)
            </a>
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

        @if($tab === 'summary')
            {{-- Bar Chart --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-chart-bar"></i> Monthly Outward GST Accumulation — {{ $year }}
                </div>
                <div class="chart-wrap">
                    <canvas id="taxChart"></canvas>
                </div>
            </div>

            {{-- Two Col: Tax Slabs Distribution + Monthly Summary Table --}}
            <div class="two-col">

                {{-- Slab Breakdown --}}
                <div class="card">
                    <div class="card-title">
                        <i class="ti ti-percentage"></i> Tax Slab Summary (HSN Level)
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Slab</th>
                                <th>Taxable Value (₹)</th>
                                <th>Tax Amount (₹)</th>
                                <th style="width:100px">Tax Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($slabsBreakdown as $slab)
                            <tr>
                                <td style="font-weight: 700; color: #f59e0b;">{{ $slab['slab'] }}</td>
                                <td style="font-weight: 600;">₹{{ number_format($slab['taxable'], 2) }}</td>
                                <td style="font-weight: 600; color: #10b981;">₹{{ number_format($slab['tax'], 2) }}</td>
                                <td>
                                    <div class="progress-wrap">
                                        <div class="progress-bar" style="width:{{ $slab['percent'] }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Monthly Breakdown Table --}}
                <div class="card">
                    <div class="card-title">
                        <i class="ti ti-calendar"></i> Monthly Tax Ledger
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Collected Tax (₹)</th>
                                <th>Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $labels = $chartData['labels'];
                                $taxes = $chartData['taxes'];
                            @endphp
                            @foreach ($labels as $i => $monthName)
                            <tr>
                                <td style="font-weight: 600;">{{ $monthName }}</td>
                                <td style="font-weight: 600; color: #10b981;">₹{{ number_format($taxes[$i]) }}</td>
                                <td>
                                    @if ($i > 0)
                                        @php $diff = $taxes[$i] - $taxes[$i-1]; @endphp
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
        @elseif($tab === 'gstr1')
            {{-- GSTR-1 View --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-building"></i> Table 4: B2B Invoices (Taxable Outward Supplies to Registered Persons)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Documents</th>
                            <th>Total Taxable Value (₹)</th>
                            <th>Total Integrated / Central / State GST (₹)</th>
                            <th>Total Invoice Value (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">Registered B2B Supplies</td>
                            <td>{{ $gstr1['b2b']['count'] }} Invoices</td>
                            <td style="font-weight: 600;">₹{{ number_format($gstr1['b2b']['taxable'], 2) }}</td>
                            <td style="font-weight: 700; color: #10b981;">₹{{ number_format($gstr1['b2b']['tax'], 2) }}</td>
                            <td style="font-weight: 700;">₹{{ number_format($gstr1['b2b']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-title">
                    <i class="ti ti-users"></i> Table 5 & 7: B2C Invoices (Taxable Supplies to Unregistered Persons)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Documents</th>
                            <th>Total Taxable Value (₹)</th>
                            <th>Total Integrated / Central / State GST (₹)</th>
                            <th>Total Invoice Value (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700; color: #8b5cf6;">Unregistered B2C Supplies</td>
                            <td>{{ $gstr1['b2c']['count'] }} Invoices</td>
                            <td style="font-weight: 600;">₹{{ number_format($gstr1['b2c']['taxable'], 2) }}</td>
                            <td style="font-weight: 700; color: #10b981;">₹{{ number_format($gstr1['b2c']['tax'], 2) }}</td>
                            <td style="font-weight: 700;">₹{{ number_format($gstr1['b2c']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @elseif($tab === 'gstr3b')
            {{-- GSTR-3B View --}}
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-receipt"></i> Table 3.1: Details of Outward Supplies & Inward Supplies Liable to Reverse Charge
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nature of Supplies</th>
                            <th>Total Taxable Value (₹)</th>
                            <th>Central Tax (CGST) (₹)</th>
                            <th>State Tax (SGST) (₹)</th>
                            <th>Total Output Tax (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">(a) Outward Taxable Supplies (other than zero rated, nil rated, exempted)</td>
                            <td style="font-weight: 600;">₹{{ number_format($gstr3b['outward_taxable'], 2) }}</td>
                            <td style="font-weight: 600; color: #4f7cff;">₹{{ number_format($gstr3b['cgst'], 2) }}</td>
                            <td style="font-weight: 600; color: #4f7cff;">₹{{ number_format($gstr3b['sgst'], 2) }}</td>
                            <td style="font-weight: 700; color: #10b981;">₹{{ number_format($gstr3b['outward_tax'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-title">
                    <i class="ti ti-download"></i> Table 4: Eligible Input Tax Credit (ITC) Available
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Details</th>
                            <th>Integrated Tax (₹)</th>
                            <th>Central Tax (CGST) (₹)</th>
                            <th>State Tax (SGST) (₹)</th>
                            <th>Total ITC Available (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">(A) ITC Available (All Other ITC from Purchases & Expenses)</td>
                            <td style="font-weight: 600;">₹0.00</td>
                            <td style="font-weight: 600; color: #8b5cf6;">₹{{ number_format($gstr3b['itc_cgst'], 2) }}</td>
                            <td style="font-weight: 600; color: #8b5cf6;">₹{{ number_format($gstr3b['itc_sgst'], 2) }}</td>
                            <td style="font-weight: 700; color: #10b981;">₹{{ number_format($gstr3b['itc_available'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-title">
                    <i class="ti ti-cash"></i> Table 6.1: Payment of Tax & Cash Reconciliation
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Total Tax Liability (₹)</th>
                            <th>Paid through ITC (₹)</th>
                            <th>Tax Paid in Cash / Net Payable (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">Tax Set-off & Liability Reconciliation</td>
                            <td style="font-weight: 600; color: #ef4444;">₹{{ number_format($gstr3b['outward_tax'], 2) }}</td>
                            <td style="font-weight: 600; color: #8b5cf6;">₹{{ number_format($gstr3b['itc_available'], 2) }}</td>
                            <td style="font-weight: 700; color: #10b981; font-size: 15px;">₹{{ number_format($gstr3b['net_payable'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Transaction Ledger Table --}}
        <div class="card" style="margin-top: 16px;">
            <div class="card-title">
                <i class="ti ti-list-details"></i> Tax & GST Ledger (# Invoices)
            </div>
            <div style="overflow-x: auto;">
                <table id="taxes-history-table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer & GSTIN</th>
                            <th style="text-align: right;">Taxable Value (₹)</th>
                            <th>Tax Slab</th>
                            <th style="text-align: right;">GST Amount (₹)</th>
                            <th style="text-align: right;">Gross Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                        <tr>
                            <td style="font-weight: 700; color: #4f7cff;">
                                #{{ $tx->invoice_no }}
                            </td>
                            <td>{{ $tx->bill_date->format('d M Y') }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $tx->customer_name ?: ($tx->customer?->name ?: 'Walk-in') }}</div>
                                <div style="font-size: 11px; color: #888;">{{ $tx->customer_gstin ?: 'Unregistered (B2C)' }}</div>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                ₹{{ number_format($tx->subtotal, 2) }}
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #d97706;">
                                    {{ (float) $tx->gst_percent }}% GST
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                ₹{{ number_format($tx->gst_amount, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #1e293b;">
                                ₹{{ number_format($tx->grand_total, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No tax records found for selected period.
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
        const ctx = document.getElementById('taxChart')?.getContext('2d');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'GST Collected (₹)',
                    data: @json($chartData['taxes']),
                    backgroundColor: 'rgba(245, 158, 11, 0.85)',
                    hoverBackgroundColor: 'rgba(245, 158, 11, 1)',
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
