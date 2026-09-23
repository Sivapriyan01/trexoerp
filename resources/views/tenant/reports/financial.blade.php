@extends('layouts.tenant')

@section('title', 'Financial Reports & Accounting')

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
    .chart-wrap { position: relative; height: 320px; width: 100%; }

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

    /* ── Statement rows styling ── */
    .row-header { font-weight: 700; color: #1e293b; }
    .dark .row-header { color: #f8fafc; }
    .row-indent { padding-left: 28px !important; color: #64748b; }
    .dark .row-indent { color: #94a3b8; }
    .row-subtotal { font-weight: 700; background: #f8fafc; }
    .dark .row-subtotal { background: #1e293b/60; }
    .row-total { font-weight: 800; font-size: 15px; background: #ecfdf5; color: #065f46; }
    .dark .row-total { background: rgba(16, 185, 129, 0.1); color: #34d399; }

    /* ── Print Media Styles ── */
    @media print {
        aside, header, .ai-chatbot-container, nav, .btn-group, .filter-bar, .tab-pills, button, a.btn, footer, #reportSearch, .blob {
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
        table { width: 100% !important; border-collapse: collapse !important; }
        th, td { border-bottom: 1px solid #eee !important; padding: 10px !important; color: #000 !important; }
        tr { page-break-inside: avoid !important; }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'finance'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ti ti-coin" style="color:#10b981;"></i> Financial Statements & Accounting</h1>
                <div class="breadcrumb" style="margin-top: 2px;">
                    <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Financial Reports
                </div>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Statement
                </button>
                <button onclick="exportTableToCSV('financial-statement-table', 'financial_statement_{{ $tab }}_{{ $year }}.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form action="{{ route('tenant.businessreport.financial') }}" method="GET" class="filter-bar">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label>Fiscal Year</label><br>
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
            <a href="{{ route('tenant.businessreport.financial', ['tab' => 'pnl', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'pnl' ? 'active' : '' }}">
                <i class="ti ti-report-money"></i> Profit & Loss Statement (P&L)
            </a>
            <a href="{{ route('tenant.businessreport.financial', ['tab' => 'cashflow', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'cashflow' ? 'active' : '' }}">
                <i class="ti ti-wave-sine"></i> Cash Flow Statement
            </a>
            <a href="{{ route('tenant.businessreport.financial', ['tab' => 'balancesheet', 'year' => $year, 'month' => $month]) }}" class="tab-pill {{ $tab === 'balancesheet' ? 'active' : '' }}">
                <i class="ti ti-scale"></i> Balance Sheet Summary
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

        {{-- Dual-bar Chart: Revenue vs Expense --}}
        <div class="card">
            <div class="card-title">
                <i class="ti ti-chart-bar"></i> Monthly Revenue vs Expense Analysis — {{ $year }}
            </div>
            <div class="chart-wrap">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

        {{-- Tab Specific Statement Tables --}}
        @if($tab === 'pnl')
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-report-money"></i> Profit & Loss (Income Statement)
                </div>
                <table id="financial-statement-table">
                    <thead>
                        <tr>
                            <th>Accounting Line Item</th>
                            <th style="text-align: right; width: 250px;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-header">Gross Sales / Revenue</td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">₹{{ number_format($pnl['revenue'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Less: Cost of Goods Sold (COGS)</td>
                            <td style="text-align: right; font-weight: 600; color: #ef4444;">₹{{ number_format($pnl['cogs'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Gross Profit</td>
                            <td style="text-align: right;">₹{{ number_format($pnl['gross_profit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Operating Expenses (Opex & Daily Spend)</td>
                            <td style="text-align: right; font-weight: 600; color: #ef4444;">₹{{ number_format($pnl['operating_expenses'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>EBITDA (Operating Income)</td>
                            <td style="text-align: right; color: #4f7cff;">₹{{ number_format($pnl['ebitda'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Less: Depreciation & Amortization</td>
                            <td style="text-align: right; color: #64748b;">₹{{ number_format($pnl['depreciation'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Less: Interest Expense</td>
                            <td style="text-align: right; color: #64748b;">₹{{ number_format($pnl['interest'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Earnings Before Taxes (EBT)</td>
                            <td style="text-align: right;">₹{{ number_format($pnl['ebt'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Less: Provision for Income Tax (25%)</td>
                            <td style="text-align: right; font-weight: 600; color: #ef4444;">₹{{ number_format($pnl['tax'], 2) }}</td>
                        </tr>
                        <tr class="row-total">
                            <td>Net Profit (Net Income)</td>
                            <td style="text-align: right;">₹{{ number_format($pnl['net_profit'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @elseif($tab === 'cashflow')
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-wave-sine"></i> Statement of Cash Flows
                </div>
                <table id="financial-statement-table">
                    <thead>
                        <tr>
                            <th>Cash Flow Activity</th>
                            <th style="text-align: right; width: 250px;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-header">Cash Flows from Operating Activities</td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">₹{{ number_format($cashflow['operating_cash'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-header">Cash Flows from Investing Activities (Capex)</td>
                            <td style="text-align: right; font-weight: 600; color: #ef4444;">₹{{ number_format($cashflow['investing_cash'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-header">Cash Flows from Financing Activities</td>
                            <td style="text-align: right; font-weight: 600; color: #ef4444;">₹{{ number_format($cashflow['financing_cash'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Net Change in Cash & Cash Equivalents</td>
                            <td style="text-align: right; color: #4f7cff;">₹{{ number_format($cashflow['net_change'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Cash & Equivalents, Beginning of Period</td>
                            <td style="text-align: right;">₹{{ number_format($cashflow['beginning_cash'], 2) }}</td>
                        </tr>
                        <tr class="row-total">
                            <td>Cash & Equivalents, End of Period</td>
                            <td style="text-align: right;">₹{{ number_format($cashflow['ending_cash'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @elseif($tab === 'balancesheet')
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-scale"></i> Balance Sheet Summary
                </div>
                <table id="financial-statement-table">
                    <thead>
                        <tr>
                            <th>Balance Sheet Account</th>
                            <th style="text-align: right; width: 250px;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Assets --}}
                        <tr class="row-subtotal"><td colspan="2" style="color: #4f7cff;">ASSETS</td></tr>
                        <tr>
                            <td class="row-indent">Cash & Bank Balances</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['cash_bank'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Accounts Receivable</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['accounts_receivable'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Inventory & Stock</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['inventory'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Total Current Assets</td>
                            <td style="text-align: right;">₹{{ number_format($balancesheet['current_assets'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-header">Property, Plant & Equipment (Fixed Assets)</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['fixed_assets'], 2) }}</td>
                        </tr>
                        <tr class="row-total">
                            <td>TOTAL ASSETS</td>
                            <td style="text-align: right;">₹{{ number_format($balancesheet['total_assets'], 2) }}</td>
                        </tr>
                        
                        {{-- Liabilities --}}
                        <tr class="row-subtotal"><td colspan="2" style="color: #ef4444; margin-top: 10px;">LIABILITIES & EQUITY</td></tr>
                        <tr>
                            <td class="row-indent">Accounts Payable (Suppliers)</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['accounts_payable'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-indent">Short-Term Borrowings</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['short_term_debt'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Total Current Liabilities</td>
                            <td style="text-align: right;">₹{{ number_format($balancesheet['current_liabilities'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-header">Long-Term Debt / Term Loans</td>
                            <td style="text-align: right; font-weight: 600;">₹{{ number_format($balancesheet['long_term_debt'], 2) }}</td>
                        </tr>
                        <tr class="row-subtotal">
                            <td>Total Liabilities</td>
                            <td style="text-align: right;">₹{{ number_format($balancesheet['total_liabilities'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="row-header">Retained Earnings & Owner's Equity</td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">₹{{ number_format($balancesheet['retained_earnings'], 2) }}</td>
                        </tr>
                        <tr class="row-total">
                            <td>TOTAL LIABILITIES & EQUITY</td>
                            <td style="text-align: right;">₹{{ number_format($balancesheet['total_assets'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Operating Expenses Ledger Table --}}
        <div class="card" style="margin-top: 16px;">
            <div class="card-title">
                <i class="ti ti-receipt"></i> Daily Operating Expenses Ledger
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Payment Mode</th>
                            <th style="text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $exp)
                        <tr>
                            <td>{{ $exp->expense_date->format('d M Y') }}</td>
                            <td>
                                <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #334155;">
                                    {{ ucfirst($exp->category ?: $exp->type) }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $exp->description ?: 'Operating Expense' }}</div>
                                <div style="font-size: 11px; color: #888;">Paid to: {{ $exp->source ?: 'General Vendor' }}</div>
                            </td>
                            <td>
                                <span style="font-weight: 600; text-transform: uppercase; font-size: 11px;">{{ $exp->payment_mode ?: 'Cash' }}</span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #ef4444;">
                                ₹{{ number_format($exp->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #888; font-style: italic;">
                                No expense records found for selected period.
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
        const ctx = document.getElementById('financialChart')?.getContext('2d');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Gross Revenue (₹)',
                        data: @json($chartData['revenues']),
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Purchases & COGS (₹)',
                        data: @json($chartData['expenses']),
                        backgroundColor: 'rgba(239, 68, 68, 0.85)',
                        hoverBackgroundColor: 'rgba(239, 68, 68, 1)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: { color: labelColor, font: { family: 'Segoe UI', weight: '600' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: context => context.dataset.label + ': ₹ ' + context.parsed.y.toLocaleString()
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
