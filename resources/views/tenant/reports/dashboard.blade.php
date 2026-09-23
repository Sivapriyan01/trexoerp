@extends('layouts.tenant')

@section('title', 'Executive Dashboard Analytics')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 800;
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
            color: #3b82f6;
            text-decoration: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
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

        .btn-primary {
            background: #3b82f6;
            color: #fff;
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.39);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .filter-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-blur: 10px;
            border: 1px solid #e8eaf0;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .dark .filter-bar {
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(51, 65, 85, 0.5);
        }

        .filter-bar label {
            font-size: 11px;
            color: #888;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        .dark .filter-bar select {
            background: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e8eaf0;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .dark .stat-card {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(51, 65, 85, 0.5);
        }

        .stat-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: #1a1a2e;
        }

        .dark .stat-value {
            color: #f8fafc;
        }

        .stat-trend {
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
        }

        .card {
            background: #fff;
            border: 1px solid #e8eaf0;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 2rem;
        }

        .dark .card {
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(51, 65, 85, 0.5);
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dark .card-title {
            color: #f8fafc;
        }

        .chart-container {
            position: relative;
            height: 380px;
            width: 100%;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .ranking-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .dark .ranking-item {
            border-bottom-color: #334155;
        }

        .ranking-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .dark .ranking-name {
            color: #f8fafc;
        }

        .ranking-value {
            font-size: 13px;
            font-weight: 600;
            color: #3b82f6;
        }

        @media print {

            aside,
            header,
            nav,
            .btn,
            .filter-bar {
                display: none !important;
            }

            .card,
            .stat-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            .stats-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 15px !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
        @include('tenant.partials.reports_sidebar', ['active' => 'analytics'])

        <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

            <div class="page-header">
                <div>
                    <h1><i class="ti ti-chart-area"></i> Dashboard Analytics</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('tenant.businessreport.index') }}">Reports</a> › Executive Summary
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="btn btn-outline">
                        <i class="ti ti-printer"></i> Print
                    </button>
                    <button onclick="exportFinancialSummary()" class="btn btn-primary">
                        <i class="ti ti-file-export"></i> Export
                    </button>
                </div>
            </div>

            <form action="{{ route('tenant.businessreport.dashboard') }}" method="GET" class="filter-bar">
                <div>
                    <label> Year</label><br>
                    <select name="year" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="ml-auto flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Revenue</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Costs</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Profit</span>
                    </div>
                </div>
            </form>

            <div class="stats-grid">
                @foreach ($stats as $stat)
                    <div class="stat-card">
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div
                            class="stat-trend {{ $stat['trend'] == 'up' ? 'text-emerald-500' : ($stat['trend'] == 'down' ? 'text-rose-500' : 'text-slate-400') }}">
                            <i class="ti ti-trending-{{ $stat['trend'] == 'neutral' ? 'up' : $stat['trend'] }}"></i>
                            {{ $stat['change'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-title"><i class="ti ti-chart-bar"></i> Monthly Financial Performance ({{ $year }})</div>
                <div class="chart-container">
                    <canvas id="financialPulseChart"></canvas>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="card-title"><i class="ti ti-building-store"></i> Branch Performance Comparison</div>
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="branchBarChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="ti ti-category-2"></i> Sales by Category Distribution</div>
                    <div class="chart-container" style="height: 280px;">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                    <div class="mt-6 space-y-2">
                        @foreach (array_slice($chartData['catLabels'], 0, 4) as $idx => $label)
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-500">{{ $label }}</span>
                                <span
                                    class="font-bold text-slate-900 dark:text-white">₹{{ number_format($chartData['catValues'][$idx]) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
            const labelColor = isDark ? '#94a3b8' : '#888';

            // 1. Financial Pulse Chart
            new Chart(document.getElementById('financialPulseChart'), {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: @json($chartData['revenue']),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            borderWidth: 4,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Total Costs',
                            data: @json($chartData['costs']),
                            borderColor: '#ef4444',
                            backgroundColor: 'transparent',
                            borderWidth: 3,
                            borderDash: [5, 5],
                            tension: 0.3
                        },
                        {
                            label: 'Net Profit',
                            data: @json($chartData['profit']),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 'bold' } } },
                        y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: (v) => '₹' + v.toLocaleString() } }
                    }
                }
            });

            // 2. Branch Bar Chart
            new Chart(document.getElementById('branchBarChart'), {
                type: 'bar',
                data: {
                    labels: @json($chartData['branchLabels']),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($chartData['branchValues']),
                        backgroundColor: '#3b82f6',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: labelColor } },
                        y: { grid: { color: gridColor }, ticks: { color: labelColor } }
                    }
                }
            });

            // 3. Category Pie Chart
            new Chart(document.getElementById('categoryPieChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['catLabels']),
                    datasets: [{
                        data: @json($chartData['catValues']),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '70%'
                }
            });
        });

        function exportFinancialSummary() {
            const labels = @json($chartData['labels']);
            const revenue = @json($chartData['revenue']);
            const costs = @json($chartData['costs']);
            const profit = @json($chartData['profit']);

            let csv = "Month,Revenue,Total Costs,Estimated Profit\n";
            for (let i = 0; i < labels.length; i++) {
                csv += `${labels[i]},${revenue[i]},${costs[i]},${profit[i]}\n`;
            }

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `financial_summary_{{ $year }}.csv`);
            a.click();
        }
    </script>
@endpush