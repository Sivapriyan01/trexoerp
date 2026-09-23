@extends('layouts.tenant')
@section('title', 'Profit & Loss Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    .profit-page-wrap {
        max-width: 1400px;
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
        font-size: 22px; 
        font-weight: 800; 
        color: #1e293b; 
        font-family: 'Sora', sans-serif;
    }
    .dark .page-header h1 {
        color: #f8fafc;
    }
    .breadcrumb { 
        font-size: 11px; 
        font-weight: 700;
        color: #64748b; 
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 2px; 
    }
    .breadcrumb a { 
        color: #3b82f6; 
        text-decoration: none; 
    }

    .btn {
        display: inline-flex; 
        align-items: center; 
        gap: 8px;
        padding: 10px 20px; 
        border-radius: 12px; 
        font-size: 13px;
        font-weight: 600; 
        cursor: pointer; 
        border: none;
        text-decoration: none; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Sora', sans-serif;
    }
    .btn:hover { 
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .btn:active {
        transform: translateY(0);
    }
    .btn-primary { 
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
        color: #fff; 
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
    }
    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    .btn-outline { 
        background: #fff; 
        color: #334155; 
        border: 1px solid #e2e8f0; 
    }
    .dark .btn-outline {
        background: #1e293b;
        color: #cbd5e1;
        border-color: #334155;
    }
    .dark .btn-outline:hover {
        background: #334155;
    }

    .btn-group { 
        display: flex; 
        gap: 10px; 
    }

    /* ── Filter Bar ── */
    .filter-bar {
        background: #fff; 
        border: 1px solid #e2e8f0;
        border-radius: 16px; 
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .dark .filter-bar {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-bar label { 
        font-size: 10px; 
        color: #64748b; 
        font-weight: 700; 
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .filter-bar select {
        padding: 10px 14px; 
        border-radius: 10px; 
        border: 1px solid #cbd5e1; 
        font-size: 13px; 
        font-weight: 500;
        background-color: #fff;
        color: #334155;
        outline: none;
        transition: all 0.15s;
        min-width: 140px;
    }
    .dark .filter-bar select {
        background-color: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }
    .filter-bar select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* ── Premium Glass Cards ── */
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dark .glass-card {
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 100px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }

    /* ── Print Media Styles ── */
    @media print {
        aside, 
        header,
        .ai-chatbot-container,
        nav, 
        .btn-group, 
        .filter-bar, 
        button, 
        a.btn,
        .page-header .btn-group,
        footer,
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

        /* Standardize print container behavior */
        body > div.flex.h-screen,
        body > div.flex.h-screen > div.flex-1.flex.flex-col,
        body > div.flex.h-screen > div.flex-1.flex.flex-col > main,
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\],
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\] > div.flex-1.overflow-y-auto,
        .profit-page-wrap {
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

        .space-y-6 > * + * {
            margin-top: 15px !important;
        }

        .stat-card, .panel-box, .glass-card, .card {
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

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border-bottom: 1px solid #eee !important;
            padding: 8px !important;
            color: #000 !important;
            font-size: 11px !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
    .active-card-revenue {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4) !important;
        transform: scale(1.02) !important;
    }
    .active-card-revenue span, .active-card-revenue p, .active-card-revenue i {
        color: #ffffff !important;
    }

    .active-card-expenses {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.4) !important;
        transform: scale(1.02) !important;
    }
    .active-card-expenses span, .active-card-expenses p, .active-card-expenses i {
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<!-- Google Fonts & Font Awesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'financial'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-6 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/20 profit-page-wrap">
        
        {{-- Page Header --}}
        <div class="page-header">
            <div>
                <p class="breadcrumb">
                    <a href="#">Dashboard</a> / Reports / <span class="text-slate-900 dark:text-white">P&L Financials</span>
                </p>
                <h1>Profit & Loss Statement</h1>
            </div>
            
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Statement
                </button>
                <button onclick="exportProfitTableToCSV('Profit_And_Loss_Statement.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Interactive Filter Bar Form --}}
        <form method="GET" action="{{ route('tenant.businessreport.profit') }}" class="filter-bar">
            <div class="filter-group">
                <label for="year">Select Year</label>
                <select name="year" id="year" onchange="this.form.submit()">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="filter-group">
                <label for="month">Select Month</label>
                <select name="month" id="month" onchange="this.form.submit()">
                    <option value="">All Months (Full Year)</option>
                    @for ($m = 1; $m <= 12; $m++)
                        @php
                            $monthVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                        @endphp
                        <option value="{{ $monthVal }}" {{ $month == $monthVal ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
        </form>

        {{-- 4-Column Financial Summary Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 stats-grid">
            
            {{-- Total Revenue Card --}}
            <div id="card-revenue" onclick="switchFinancialTab('revenue')" class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between cursor-pointer transition-all duration-300 hover:scale-[1.02] active:scale-95 text-slate-800 dark:text-slate-200 active-card-revenue">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Sales (Revenue)</span>
                        <div class="h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center">
                            <i class="ti ti-coin text-blue-600 dark:text-blue-400 text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($stats['revenue'], 2) }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">
                    Gross receipts generated
                </div>
            </div>

            {{-- COGS Card --}}
            <div id="card-cogs" onclick="switchFinancialTab('revenue')" class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between cursor-pointer transition-all duration-300 hover:scale-[1.02] active:scale-95">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Cost of Sales (COGS)</span>
                        <div class="h-8 w-8 rounded-lg bg-slate-50 dark:bg-slate-950/30 flex items-center justify-center">
                            <i class="ti ti-package text-slate-500 dark:text-slate-400 text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($stats['cogs'], 2) }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider">
                    <span>Gross Profit: ₹{{ number_format($stats['gross_profit'], 2) }}</span>
                    <span>{{ number_format($stats['gross_margin'], 1) }}% Margin</span>
                </div>
            </div>

            {{-- Expenses Card --}}
            <div id="card-expenses" onclick="switchFinancialTab('expenses')" class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between cursor-pointer transition-all duration-300 hover:scale-[1.02] active:scale-95">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Operating Expenses</span>
                        <div class="h-8 w-8 rounded-lg bg-orange-50 dark:bg-orange-950/30 flex items-center justify-center">
                            <i class="ti ti-receipt text-orange-600 dark:text-orange-400 text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($stats['expenses'], 2) }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-[10px] text-orange-500 font-bold uppercase tracking-wider">
                    <span>Total Overhead & Inward cash</span>
                </div>
            </div>

            {{-- Net Profit Card --}}
            <div id="card-profit" onclick="switchFinancialTab('revenue')" class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between cursor-pointer transition-all duration-300 hover:scale-[1.02] active:scale-95">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Net Cash Profit</span>
                        <div class="h-8 w-8 rounded-lg @if($stats['net_profit'] >= 0) bg-emerald-50 dark:bg-emerald-950/30 @else bg-rose-50 dark:bg-rose-950/30 @endif flex items-center justify-center">
                            <i class="ti ti-chart-arrows text-lg @if($stats['net_profit'] >= 0) text-emerald-600 dark:text-emerald-400 @else text-rose-500 dark:text-rose-400 @endif"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold tracking-tight @if($stats['net_profit'] >= 0) text-emerald-600 dark:text-emerald-400 @else text-rose-600 dark:text-rose-400 @endif">
                        ₹{{ number_format($stats['net_profit'], 2) }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-[10px] font-bold uppercase tracking-wider @if($stats['net_profit'] >= 0) text-emerald-600 dark:text-emerald-400 @else text-rose-500 @endif">
                    <span>Net Margin: {{ number_format($stats['net_margin'], 1) }}%</span>
                </div>
            </div>
        </div>

        {{-- Monthly Breakdown Chart Section --}}
        <div class="glass-card rounded-[1.5rem] p-6 border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl shadow-slate-100/50 dark:shadow-none">
            <h3 class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-4">Monthly Financial Performance Trend</h3>
            <div class="h-[280px] w-full">
                <canvas id="profitTrendChart"></canvas>
            </div>
        </div>

        {{-- Panel 1: Detailed Sales Invoices (Visible by Default) --}}
        <div id="panel-revenue" class="glass-card rounded-[1.5rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-xl shadow-slate-100/50 dark:shadow-none bg-white dark:bg-slate-900">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">Itemized Invoices with estimated margin</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Showing latest transactions for the selected range</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                    Active Ledger: Sales
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="profit-loss-table">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Bill / Invoice</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Customer Details</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Date</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Revenue</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Estimated Cost (COGS)</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Gross Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($transactions as $transaction)
                            @php
                                $billCogs = 0.0;
                                foreach ($transaction->items as $item) {
                                    $cost = $item->category ? $item->category->dealer_price : ($item->mrp * 0.7);
                                    $billCogs += ($cost * $item->quantity);
                                }
                                $billProfit = $transaction->grand_total - $billCogs;
                                $billMargin = $transaction->grand_total > 0 ? ($billProfit / $transaction->grand_total) * 100 : 0.0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">#{{ $transaction->bill_no }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-wider">
                                        {{ $transaction->items->count() }} items sold
                                    </p>
                                </td>
                                
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $transaction->customer_name }}</p>
                                    <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 mt-1 uppercase tracking-wider">
                                        {{ $transaction->customer_phone ?: 'No Phone' }}
                                    </p>
                                </td>
                                
                                <td class="px-8 py-5 text-center">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $transaction->bill_date->format('d M Y') }}
                                    </span>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">₹{{ number_format($transaction->grand_total, 2) }}</span>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">₹{{ number_format($billCogs, 2) }}</span>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <p class="text-sm font-black @if($billProfit >= 0) text-emerald-600 dark:text-emerald-400 @else text-rose-500 @endif">
                                        ₹{{ number_format($billProfit, 2) }}
                                    </p>
                                    <p class="text-[9px] font-extrabold mt-0.5 @if($billProfit >= 0) text-emerald-600/70 dark:text-emerald-400/70 @else text-rose-500/70 @endif">
                                        {{ number_format($billMargin, 1) }}% Margin
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center opacity-30">
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em]">No invoice transactions for this range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($transactions->hasPages())
                <div class="px-8 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

        {{-- Panel 2: Detailed Operating Expenses (Hidden by Default) --}}
        <div id="panel-expenses" style="display: none;" class="glass-card rounded-[1.5rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-xl shadow-slate-100/50 dark:shadow-none bg-white dark:bg-slate-900">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-black text-orange-500 dark:text-orange-400 uppercase tracking-[0.2em]">Itemized Operating Expenses</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Showing expense list for the selected range</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border border-orange-100 dark:border-orange-900/50">
                    Active Ledger: Expenses
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="expenses-table">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Category</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Description</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Payment Mode</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Date</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $expense->category }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">
                                        Type: {{ $expense->type }}
                                    </p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm text-slate-800 dark:text-slate-200">{{ $expense->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ strtoupper($expense->payment_mode) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $expense->expense_date->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-sm font-black text-rose-600 dark:text-rose-400">₹{{ number_format($expense->amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center opacity-30">
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em]">No operating expenses recorded for this range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab toggling logic for premium financials dashboard
    function switchFinancialTab(tab) {
        const revCard = document.getElementById('card-revenue');
        const expCard = document.getElementById('card-expenses');
        
        const revPanel = document.getElementById('panel-revenue');
        const expPanel = document.getElementById('panel-expenses');
        
        if (tab === 'revenue') {
            revPanel.style.display = 'block';
            expPanel.style.display = 'none';
            
            revCard.classList.add('active-revenue');
            expCard.classList.remove('active-expenses');
        } else if (tab === 'expenses') {
            revPanel.style.display = 'none';
            expPanel.style.display = 'block';
            
            expCard.classList.add('active-expenses');
            revCard.classList.remove('active-revenue');
        }
    }

    // Load and render Chart.js performance trend chart
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('profitTrendChart').getContext('2d');
        
        const monthlyData = @json(array_values($monthlyData));
        
        const labels = monthlyData.map(d => d.month_name);
        const revenues = monthlyData.map(d => d.revenue);
        const cogs = monthlyData.map(d => d.cogs);
        const expenses = monthlyData.map(d => d.expenses);
        const profits = monthlyData.map(d => d.profit);

        const isDark = document.documentElement.classList.contains('dark');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue (Sales)',
                        data: revenues,
                        backgroundColor: 'rgba(59, 130, 246, 0.85)',
                        borderRadius: 6,
                        borderWidth: 0,
                        maxBarThickness: 32,
                    },
                    {
                        label: 'Operating Expenses',
                        data: expenses,
                        backgroundColor: 'rgba(249, 115, 22, 0.85)',
                        borderRadius: 6,
                        borderWidth: 0,
                        maxBarThickness: 32,
                    },
                    {
                        label: 'Net Margin (Profit)',
                        data: profits,
                        type: 'line',
                        borderColor: '#10b981',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Sora', size: 10, weight: '700' },
                            color: isDark ? '#cbd5e1' : '#475569'
                        }
                    },
                    tooltip: {
                        padding: 12,
                        titleFont: { family: 'Sora', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        cornerRadius: 12
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.04)'
                        },
                        ticks: {
                            color: isDark ? '#94a3b8' : '#64748b',
                            font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: isDark ? '#94a3b8' : '#64748b',
                            font: { family: 'Sora', size: 10, weight: '700' }
                        }
                    }
                }
            }
        });
    });

    // Premium high-fidelity CSV statement downloader (exports currently visible panel)
    function exportProfitTableToCSV(filename) {
        const isRevenueVisible = document.getElementById('panel-revenue').style.display !== 'none';
        const tableId = isRevenueVisible ? "profit-loss-table" : "expenses-table";
        const actualFilename = isRevenueVisible ? "Sales_Revenue_Ledger.csv" : "Operating_Expenses_Ledger.csv";

        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll("tr");
        let csv = [];
        
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
        link.setAttribute("download", actualFilename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
@endsection
