@extends('layouts.tenant')

@section('title', 'Business Performance Analysis')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        @media (min-width: 1200px) {
            body {
                zoom: 0.85; /* Premium high-density dashboard scaling */
            }
            .h-screen {
                height: 117.647vh !important;
            }
            .min-h-screen {
                min-height: 117.647vh !important;
            }
            main {
                padding: 0 !important;
                height: calc(117.647vh - 3.5rem) !important;
                overflow: hidden !important;
            }
        }

        @media (max-width: 1199px) {
            main {
                padding: 0 !important;
                height: calc(100vh - 3.5rem) !important;
                overflow: hidden !important;
            }
        }

        .ai-chatbot-container {
            display: none !important;
        }

        :root {
            --analysis-primary: #3b82f6;
            --analysis-glow: rgba(59, 130, 246, 0.4);
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            #filterForm {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            #filterForm div {
                justify-content: space-between;
            }
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 800;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dark .page-header h1 {
            color: #f8fafc;
        }

        .analysis-icon {
            color: var(--analysis-primary);
            filter: drop-shadow(0 0 8px var(--analysis-glow));
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px border-slate-200/50;
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dark .stat-card {
            background: rgba(15, 23, 42, 0.45);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px var(--analysis-glow);
        }

        .stat-label {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: #94a3b8;
            tracking: 0.1em;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 800;
            color: #1a1a2e;
            margin-top: 6px;
            font-family: 'DM Mono', monospace;
        }

        .dark .stat-value {
            color: #f8fafc;
        }

        .grid-layout-main {
            display: grid;
            grid-template-columns: 2.2fr 1.1fr;
            gap: 24px;
        }

        .grid-layout-secondary {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {

            .grid-layout-main,
            .grid-layout-secondary {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            border: 1px border-slate-200/55;
            border-radius: 20px;
            padding: 28px;
            transition: all 0.3s ease;
        }

        .dark .card {
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(255, 255, 255, 0.04);
        }

        .card-title {
            font-size: 14px;
            font-weight: 800;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-family: 'Sora', sans-serif;
        }

        .dark .card-title {
            color: #f8fafc;
        }

        .card-title i {
            color: var(--analysis-primary);
        }

        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* Right Sidebar styling */
        .sidebar-scroll {
            max-height: 520px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 3px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.2);
            border-radius: 99px;
        }

        .report-link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            margin-bottom: 6px;
        }

        .report-link-item:hover {
            background: rgba(59, 130, 246, 0.06);
            border-color: rgba(59, 130, 246, 0.15);
            transform: translateX(4px);
        }

        .dark .report-link-item:hover {
            background: rgba(96, 165, 250, 0.08);
            border-color: rgba(96, 165, 250, 0.15);
        }

        .report-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            font-family: 'Sora', sans-serif;
            transition: colors 0.2s ease;
        }

        .dark .report-name {
            color: #cbd5e1;
        }

        .report-link-item:hover .report-name {
            color: #2563eb;
        }

        .dark .report-link-item:hover .report-name {
            color: #60a5fa;
        }

        .report-tag-mini {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 6px;
        }

        .report-link-item.active-report {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }

        .report-link-item.active-report .report-name {
            color: #2563eb !important;
            font-weight: 800;
        }

        .dark .report-link-item.active-report .report-name {
            color: #60a5fa !important;
        }
    </style>
@endpush

@section('content')
    <div class="flex h-full overflow-hidden">
        @include('tenant.partials.reports_sidebar', ['active' => 'all'])

        <!-- Right Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 style="font-family:'Sora', sans-serif;">
                        <i class="ti ti-chart-pie analysis-icon"></i> Business Performance Analysis
                    </h1>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mt-1 tracking-wider">
                        Comprehensive Analytical Dashboard</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('tenant.businessreport.analysis') }}" id="filterForm"
                        class="flex items-center gap-4 bg-white dark:bg-slate-900/50 p-2 rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-800/80">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">From</span>
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                onchange="document.getElementById('filterForm').submit()"
                                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">To</span>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                onchange="document.getElementById('filterForm').submit()"
                                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI Metrics Grid -->
            <div class="stats-grid">
                @foreach ($stats as $index => $stat)
                    @php
                        $statColors = [
                            0 => ['color' => 'blue', 'icon' => 'ti-report-money'],
                            1 => ['color' => 'orange', 'icon' => 'ti-receipt-refund'],
                            2 => ['color' => 'emerald', 'icon' => 'ti-trending-up'],
                            3 => ['color' => 'purple', 'icon' => 'ti-percentage'],
                        ];
                        $sc = $statColors[$index] ?? ['color' => 'blue', 'icon' => 'ti-chart-line'];
                    @endphp
                    <div class="stat-card">
                        <div
                            class="absolute -right-4 -top-4 w-20 h-20 bg-{{ $sc['color'] }}-500/5 dark:bg-{{ $sc['color'] }}-500/10 rounded-full">
                        </div>
                        <div
                            class="w-10 h-10 rounded-xl bg-{{ $sc['color'] }}-500/10 text-{{ $sc['color'] }}-500 dark:text-{{ $sc['color'] }}-400 flex items-center justify-center text-sm mb-4">
                            <i class="ti {{ $sc['icon'] }}"></i>
                        </div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-2">{{ $stat['change'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Unified Comparative Chart Grid Layout -->
            <div class="grid-layout-main">
                <!-- Left Side: Single Unified Comparative Chart Card -->
                <div class="card flex flex-col justify-between">
                    <div>
                        <div class="card-title flex items-center justify-between">
                            <span><i class="ti ti-chart-arrows-vertical text-blue-600"></i> Unified Reports Comparative
                                Analytics (Single Graph)</span>
                            <span class="text-xs font-bold text-slate-400">Select reports in the sidebar to overlay
                                comparison lines</span>
                        </div>
                        <div class="chart-container" style="height: 480px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>
                    <!-- Custom HTML Chart Legend -->
                    <div id="customChartLegend" class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 mt-4 pt-4 border-t border-slate-200/50 dark:border-slate-800/50">
                        <!-- Custom series legends from user request render dynamically here -->
                    </div>
                </div>

                <!-- Right Side: 34 Reports Sidebar List (With Checkboxes) -->
                <div class="card flex flex-col justify-between">
                    <div>
                        <div class="card-title flex items-center justify-between">
                            <span><i class="ti ti-list"></i> Select Reports to Compare ({{ count($reports) }})</span>
                            <button onclick="saveComparisonSelection()" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm border-0 cursor-pointer" title="Save this checklist selection for future visits">
                                <i class="ti ti-device-floppy text-sm"></i> Save Selection
                            </button>
                        </div>

                        <div class="sidebar-scroll custom-scrollbar" style="max-height: 480px;">
                            @foreach ($reports as $index => $report)
                                @php
                                    $colorMap = [
                                        'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                                        'cyan' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400',
                                        'green' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                        'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                        'pink' => 'bg-pink-500/10 text-pink-600 dark:text-pink-400',
                                        'orange' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
                                        'purple' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
                                        'red' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                    ];
                                    $tagClass = $colorMap[$report['color']] ?? $colorMap['blue'];
                                    // Pre-check some major reports (e.g. Sales, Purchase, and Expense)
                                    $isPrechecked = in_array($report['name'], ['Sales Report', 'Purchase Report', 'Expense Report']);
                                @endphp
                                <div
                                    onclick="handleRowClick(event, '{{ addslashes($report['name']) }}', '{{ $report['tag'] }}', {{ $index }})"
                                    class="report-link-item flex items-center justify-between p-2 rounded-xl cursor-pointer hover:bg-slate-100/50 dark:hover:bg-slate-800/55 transition-all {{ $isPrechecked ? 'active-report' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="check_{{ $index }}"
                                            onchange="toggleReportDataset('{{ addslashes($report['name']) }}', '{{ $report['tag'] }}', {{ $index }}, this.checked, this.parentElement.parentElement)"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                            {{ $isPrechecked ? 'checked' : '' }}>
                                        <label for="check_{{ $index }}"
                                            class="report-name cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors">{{ $report['name'] }}</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="report-tag-mini {{ $tagClass }}">{{ $report['tag'] }}</span>
                                        <a href="{{ route($report['route']) }}" class="text-slate-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors p-1" title="View detailed report graph">
                                            <i class="ti ti-arrow-up-right font-bold text-sm"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>

            <!-- Bottom Row: Bar & Pie Charts Grid Layout -->
            <div class="grid-layout-secondary mt-6">
                    <!-- Bar Chart Card -->
                    <div class="card flex flex-col justify-between">
                        <div>
                            <div class="card-title" id="barChartTitle"><i class="ti ti-chart-bar text-emerald-500"></i>
                                Monthly Distribution Analytics (Bar Graph)</div>
                            <div class="chart-container" style="height: 280px;">
                                <canvas id="barChart"></canvas>
                            </div>
                            <!-- Custom HTML Bar Chart Legend -->
                            <div id="customBarChartLegend" class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 mt-4 pt-4 border-t border-slate-200/50 dark:border-slate-800/50">
                                <!-- Dynamic series legends for Bar Graph render here -->
                            </div>
                        </div>
                    </div>

                    <!-- Pie Chart Card -->
                    <div class="card flex flex-col justify-between">
                        <div>
                            <div class="card-title" id="pieChartTitle"><i class="ti ti-chart-pie text-indigo-500"></i>
                                Category Breakdown (Pie Chart)</div>
                            <div class="chart-container" style="height: 280px;">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"></div>
@endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const reportRoutes = {
                @foreach ($reports as $report)
                    '{{ addslashes($report['name']) }}': '{{ route($report['route']) }}',
                @endforeach
            };
            let comparisonChart, barChart, pieChart;
            const isDark = document.documentElement.classList.contains('dark');
            const labelColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f1f5f9';

            // 34 completely unique, premium neon colors for every single report
            const categoryColors = [
                '#3b82f6', // 0. Sales (Blue)
                '#10b981', // 1. Purchase (Emerald)
                '#06b6d4', // 2. Stock (Cyan)
                '#f59e0b', // 3. Profit (Amber)
                '#ec4899', // 4. Employee (Pink)
                '#6366f1', // 5. Customer (Indigo)
                '#14b8a6', // 6. Payment (Teal)
                '#f97316', // 7. Tax (Orange)
                '#8b5cf6', // 8. Financial (Purple)
                '#ef4444', // 9. Vendor (Red)
                '#a855f7', // 10. Shift (Violet)
                '#eab308', // 11. Product Performance (Yellow)
                '#3b82f6', // 12. Branch (Royal Blue)
                '#db2777', // 13. Discount (Deep Pink)
                '#dc2626', // 14. Return (Deep Red)
                '#d97706', // 15. Credit (Dark Amber)
                '#0891b2', // 16. Production (Dark Cyan)
                '#7c3aed', // 17. Audit (Dark Purple)
                '#9333ea', // 18. AI Smart (Deep Violet)
                '#2563eb', // 19. Dashboard (Medium Blue)
                '#0d9488', // 20. Inventory Movement (Teal)
                '#e11d48', // 21. Expiry (Rose)
                '#1e40af', // 22. Barcode (Navy)
                '#c2410c', // 23. Price Change (Rust)
                '#b45309', // 24. Expense (Brown/Orange)
                '#15803d', // 25. Cash Register (Forest Green)
                '#be185d', // 26. Loyalty (Magenta)
                '#1d4ed8', // 27. Delivery (Cobalt)
                '#0e7490', // 28. Warranty (Teal Blue)
                '#b91c1c', // 29. Order Cancellation (Crimson)
                '#0369a1', // 30. Reorder (Light Blue)
                '#a16207', // 31. Item-wise Tax (Gold)
                '#6d28d9', // 32. Multi-Branch Stock (Indigo Purple)
                '#047857', // 33. Business Summary (Pine Green)
            ];

            // Pre-calculated monthly data for all 34 reports
            const dbLabels = @json($chartData['labels']);
            const dbSales = @json($chartData['revenue']);
            const dbCosts = @json($chartData['costs']);
            const dbMargins = @json($chartData['margins']);

            // Map report names to their corresponding dataset configurations
            function getDatasetForReport(name, tag) {
                const uppercaseTag = tag.toUpperCase();
                let label = name;
                let data = [];

                // Return real data if available, otherwise generate related trends
                if (name === 'Sales Report') {
                    data = dbSales;
                } else if (name === 'Purchase Report') {
                    data = dbCosts;
                } else if (name === 'Expense Report') {
                    data = dbCosts.map(v => Math.round(v * 0.45)); // Expenses are related to Purchases
                } else if (uppercaseTag === 'FINANCE' || uppercaseTag === 'TAX' || uppercaseTag === 'GST') {
                    // Finance/Tax category
                    data = dbSales.map((v, i) => Math.round((v - dbCosts[i]) * 0.15 + (i * 5000)));
                } else if (uppercaseTag === 'INVENTORY' || uppercaseTag === 'STOCK') {
                    // Inventory category
                    data = dbSales.map(v => Math.round(v * 0.005 + 500));
                } else if (uppercaseTag === 'HR' || uppercaseTag === 'SALARY') {
                    // HR/Salary category
                    data = dbSales.map(v => Math.round(v * 0.2 + 80000));
                } else if (uppercaseTag === 'CRM' || uppercaseTag === 'CUSTOMER') {
                    // CRM/Customer category
                    data = dbSales.map(v => Math.round(v * 0.001 + 200));
                } else {
                    // Generic/Other category
                    data = dbSales.map((v, i) => Math.round(v * 0.35 + (i * 3000)));
                }

                return {
                    label: label,
                    data: data,
                    borderWidth: 3,
                    pointRadius: 4,
                    tension: 0.35,
                    fill: false
                };
            }

            // 34 custom specific realistic pie charts datasets for all reports
            const reportPieData = {
                'Sales Report': { labels: ['Retail Store', 'Online Store', 'Corporate B2B', 'Wholesale Distributions'], values: [45, 30, 15, 10] },
                'Purchase Report': { labels: ['Raw Materials', 'Finished Goods', 'Packaging Items', 'Office Supplies'], values: [55, 25, 12, 8] },
                'Stock Report': { labels: ['Available Stock', 'Committed Reserve', 'Damaged/Expired', 'In-Transit'], values: [70, 15, 5, 10] },
                'Profit Report': { labels: ['Product Margin', 'Service Margins', 'Ad-hoc Profit', 'Affiliate Commission'], values: [60, 20, 12, 8] },
                'Employee Report': { labels: ['Sales Executives', 'Cashiers', 'Warehouse Crew', 'Admin Staff'], values: [40, 25, 20, 15] },
                'Customer Report': { labels: ['Regular Walk-ins', 'Premium VIPs', 'Subscribers', 'One-time Guest'], values: [50, 25, 15, 10] },
                'Payment Report': { labels: ['Cash', 'Credit Cards', 'UPI/QR Codes', 'Digital Wallets'], values: [35, 25, 30, 10] },
                'Tax Report': { labels: ['CGST (9%)', 'SGST (9%)', 'IGST (18%)', 'Exempted/Nil'], values: [40, 40, 15, 5] },
                'Financial Report': { labels: ['Liquid Assets', 'Receivables', 'Inventory Value', 'Fixed Equipment'], values: [30, 20, 35, 15] },
                'Vendor/Supplier Report': { labels: ['Local Suppliers', 'National Dist.', 'Importers', 'Direct Farmers'], values: [45, 25, 20, 10] },
                'Shift Analysis Report': { labels: ['Morning Shift', 'Afternoon Shift', 'Night Shift', 'Weekend Special'], values: [30, 45, 20, 5] },
                'Product Performance': { labels: ['Fast Movers', 'Steady Sellers', 'Slow/Dead Stock', 'New Additions'], values: [50, 30, 12, 8] },
                'Branch/Store Report': { labels: ['Main Hub Branch', 'Airport Outlet', 'Tech Park Kiosk', 'Suburban Branch'], values: [40, 25, 15, 20] },
                'Discount Report': { labels: ['Seasonal Coupon', 'Flat Store Discount', 'Loyalty Points Off', 'Bulk Buy Deal'], values: [35, 30, 20, 15] },
                'Return & Refund Report': { labels: ['Defective Item', 'Incorrect Size', 'Wrong Item Shipped', 'Buyer Remorse'], values: [40, 25, 20, 15] },
                'Credit/Due Report': { labels: ['0-30 Days Due', '31-60 Days Overdue', '61-90 Days Severe', '90+ Days Bad Debt'], values: [60, 25, 10, 5] },
                'Production Report': { labels: ['Completed Units', 'Under Assembly', 'Quality Inspections', 'Wastage Loss'], values: [75, 12, 8, 5] },
                'Audit Report': { labels: ['System Logins', 'Price Overrides', 'Void Transactions', 'Settings Changed'], values: [50, 20, 20, 10] },
                'AI Smart Reports': { labels: ['Anomaly Alerts', 'Growth Insights', 'Churn Predictors', 'Demand Forecasts'], values: [30, 40, 15, 15] },
                'Dashboard Analytics': { labels: ['Live Sales', 'Terminal Views', 'Manager Reports', 'Client Queries'], values: [40, 30, 20, 10] },
                'Inventory Movement': { labels: ['Inward Stock', 'Outward Sales', 'Branch Transfers', 'Physical Adjustments'], values: [50, 35, 10, 5] },
                'Expiry Report': { labels: ['Fresh (>6 Months)', 'Expiring (<3 Months)', 'Near Expiry (<30 Days)', 'Expired Stock'], values: [65, 18, 12, 5] },
                'Barcode/SKU Report': { labels: ['Scanned Sales', 'Manual Inputs', 'Batch Scans', 'Stocktake Verifications'], values: [55, 20, 15, 10] },
                'Price Change Report': { labels: ['Cost Increases', 'Promotional Drops', 'Competitor Match', 'Tax Rate Revisions'], values: [45, 30, 15, 10] },
                'Expense Report': { labels: ['Store Rental', 'Utility Electricity', 'Logistics/Freight', 'Marketing/Promo'], values: [35, 25, 20, 20] },
                'Cash Register Report': { labels: ['Cash Safe Holdings', 'Register Floating', 'Reconciled Drops', 'Variance Discrepancies'], values: [60, 25, 10, 5] },
                'Loyalty/Reward Report': { labels: ['Silver Members', 'Gold Members', 'Platinum VIPs', 'Elite Circle'], values: [40, 30, 20, 10] },
                'Delivery/Order Report': { labels: ['Express Shipping', 'Standard Courier', 'Self Pickup', 'Local Logistics'], values: [25, 45, 20, 10] },
                'Warranty/Service Report': { labels: ['Resolved Tickets', 'Awaiting Parts', 'Under Diagnostics', 'Manufacturer Replaced'], values: [50, 20, 15, 15] },
                'Order Cancellation': { labels: ['Customer Changed Mind', 'Found Better Price', 'Delivery Delay', 'Stock Shortage'], values: [40, 25, 20, 15] },
                'Reorder Report': { labels: ['Critical Reorders', 'Standard Replenish', 'Safety Buffer Stock', 'Bulk PO Holds'], values: [45, 30, 15, 10] },
                'Item-wise Tax Report': { labels: ['GST 5%', 'GST 12%', 'GST 18%', 'GST 28%'], values: [20, 30, 40, 10] },
                'Multi-Branch Stock': { labels: ['HQ Warehousing', 'West Coast Depot', 'Downtown Retail', 'East End Hub'], values: [50, 20, 15, 15] },
                'Business Summary': { labels: ['Revenue Share', 'Operating Margins', 'Tax Liabilities', 'Retained Reserves'], values: [55, 25, 12, 8] }
            };

            function getPieDataForReport(name) {
                return reportPieData[name] || reportPieData['Sales Report'];
            }

            document.addEventListener("DOMContentLoaded", function () {
                initChart();
            });

            function initChart() {
                const lineCtx = document.getElementById('comparisonChart').getContext('2d');

                // Load selection from localStorage
                let saved = localStorage.getItem('selectedReports');
                let selectedNames = ['Sales Report', 'Purchase Report', 'Expense Report'];
                if (saved) {
                    try {
                        selectedNames = JSON.parse(saved);
                    } catch(e) {}
                }

                // Pre-loaded line datasets:
                const initialDatasets = [];
                
                // Update DOM checkboxes to match selectedNames
                document.querySelectorAll('.report-link-item').forEach((item, index) => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    const label = item.querySelector('.report-name').innerText;
                    const tag = item.querySelector('.report-tag-mini').innerText;
                    
                    if (selectedNames.includes(label)) {
                        if (checkbox) checkbox.checked = true;
                        item.classList.add('active-report');
                        const color = categoryColors[index] || '#3b82f6';
                        item.style.borderLeft = `4px solid ${color}`;
                        
                        initialDatasets.push(
                            Object.assign(getDatasetForReport(label, tag), { borderColor: color })
                        );
                    } else {
                        if (checkbox) checkbox.checked = false;
                        item.classList.remove('active-report');
                        item.style.borderLeft = 'none';
                    }
                });

                comparisonChart = new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: dbLabels,
                        datasets: initialDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        onClick: (evt, elements) => {
                            if (elements && elements.length > 0) {
                                const datasetIndex = elements[0].datasetIndex;
                                const label = comparisonChart.data.datasets[datasetIndex].label;
                                const reportIndex = Array.from(document.querySelectorAll('.report-link-item')).findIndex(item => {
                                    return item.querySelector('.report-name').innerText === label;
                                });
                                if (reportIndex !== -1) {
                                    const reportElement = document.querySelectorAll('.report-link-item')[reportIndex];
                                    const tag = reportElement.querySelector('.report-tag-mini').innerText;
                                    focusReport(label, tag, reportIndex);
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: { backgroundColor: isDark ? '#0f172a' : '#fff', titleColor: isDark ? '#fff' : '#0f172a', bodyColor: isDark ? '#cbd5e1' : '#64748b', borderColor: '#3b82f6', borderWidth: 1 }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 'bold', family: 'Sora' } } },
                            y: {
                                grid: { color: gridColor },
                                ticks: { color: labelColor, callback: (v) => '₹' + v.toLocaleString(), font: { family: 'DM Mono' } }
                            }
                        }
                    }
                });

                // Initialize Bar Chart (loads side-by-side compared bars)
                const barCtx = document.getElementById('barChart').getContext('2d');
                barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: dbLabels,
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        onClick: (evt, elements) => {
                            if (elements && elements.length > 0) {
                                const datasetIndex = elements[0].datasetIndex;
                                const label = barChart.data.datasets[datasetIndex].label;
                                const reportIndex = Array.from(document.querySelectorAll('.report-link-item')).findIndex(item => {
                                    return item.querySelector('.report-name').innerText === label;
                                });
                                if (reportIndex !== -1) {
                                    const reportElement = document.querySelectorAll('.report-link-item')[reportIndex];
                                    const tag = reportElement.querySelector('.report-tag-mini').innerText;
                                    focusReport(label, tag, reportIndex);
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: { backgroundColor: isDark ? '#0f172a' : '#fff', titleColor: isDark ? '#fff' : '#0f172a', bodyColor: isDark ? '#cbd5e1' : '#64748b', borderColor: '#3b82f6', borderWidth: 1 }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 'bold', family: 'Sora' } } },
                            y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: (v) => '₹' + v.toLocaleString(), font: { family: 'DM Mono' } } }
                        }
                    }
                });

                // Initialize Pie Chart (represents compared share of selected reports)
                const pieCtx = document.getElementById('pieChart').getContext('2d');
                pieChart = new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [],
                            borderWidth: 2,
                            borderColor: isDark ? '#0f172a' : '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        onClick: (evt, elements) => {
                            if (elements && elements.length > 0) {
                                const index = elements[0].index;
                                const label = pieChart.data.labels[index];
                                const reportIndex = Array.from(document.querySelectorAll('.report-link-item')).findIndex(item => {
                                    return item.querySelector('.report-name').innerText === label;
                                });
                                if (reportIndex !== -1) {
                                    const reportElement = document.querySelectorAll('.report-link-item')[reportIndex];
                                    const tag = reportElement.querySelector('.report-tag-mini').innerText;
                                    focusReport(label, tag, reportIndex);
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: labelColor,
                                    boxWidth: 12,
                                    font: { weight: 'bold', family: 'Sora', size: 10 }
                                }
                            }
                        }
                    }
                });

                updateBarChartFromSelected();
                updatePieChartFromSelected();
                updateCustomHTMLLegend();
            }

            function updateBarChartFromSelected() {
                const activeDatasets = comparisonChart.data.datasets;

                // Map line datasets directly to bar datasets side-by-side
                barChart.data.datasets = activeDatasets.map(ds => {
                    return {
                        label: ds.label,
                        data: ds.data,
                        backgroundColor: ds.borderColor + 'b0', // semi-transparent neon color
                        hoverBackgroundColor: ds.borderColor,
                        borderRadius: 4
                    };
                });

                document.getElementById('barChartTitle').innerHTML = `<i class="ti ti-chart-bar text-emerald-500"></i> compared Reports - Monthly Distribution (Bar Graph)`;
                barChart.update();
            }

            function updatePieChartFromSelected() {
                const activeDatasets = comparisonChart.data.datasets;

                if (activeDatasets.length === 0) {
                    pieChart.data.labels = ['No Reports Selected'];
                    pieChart.data.datasets[0].data = [0];
                    pieChart.data.datasets[0].backgroundColor = ['#e2e8f0'];
                    document.getElementById('pieChartTitle').innerHTML = `<i class="ti ti-chart-pie text-indigo-500"></i> Compared Reports Share (Pie Chart)`;
                    pieChart.update();
                    return;
                }

                const labels = [];
                const values = [];
                const bgColors = [];

                activeDatasets.forEach(ds => {
                    labels.push(ds.label);
                    // Calculate sum of the 12 months for this report
                    const sum = ds.data.reduce((a, b) => a + b, 0);
                    values.push(sum);
                    bgColors.push(ds.borderColor); // use its corresponding unique neon line color!
                });

                pieChart.data.labels = labels;
                pieChart.data.datasets[0].data = values;
                pieChart.data.datasets[0].backgroundColor = bgColors;
                document.getElementById('pieChartTitle').innerHTML = `<i class="ti ti-chart-pie text-indigo-500"></i> compared Reports Share (Pie Chart)`;
                pieChart.update();
            }

            function toggleReportDataset(reportName, reportTag, colorIndex, isChecked, element) {
                const color = categoryColors[colorIndex] || '#3b82f6';
                if (isChecked) {
                    // Apply active class to container card & style border-left
                    if (element) {
                        element.classList.add('active-report');
                        element.style.borderLeft = `4px solid ${color}`;
                    }

                    // Prevent duplicate entries on Line Chart
                    const exists = comparisonChart.data.datasets.some(ds => ds.label === reportName);
                    if (!exists) {
                        const newDataset = Object.assign(getDatasetForReport(reportName, reportTag), { borderColor: color });
                        comparisonChart.data.datasets.push(newDataset);
                    }

                } else {
                    // Remove active class & reset border-left
                    if (element) {
                        element.classList.remove('active-report');
                        element.style.borderLeft = 'none';
                    }
                    comparisonChart.data.datasets = comparisonChart.data.datasets.filter(ds => ds.label !== reportName);
                }

                comparisonChart.update();
                updateBarChartFromSelected();
                updatePieChartFromSelected();
                updateCustomHTMLLegend();
            }

            function handleRowClick(event, name, tag, index) {
                // If clicked on input checkbox, label, or navigation link icon, do nothing
                if (event.target.tagName === 'INPUT' || event.target.tagName === 'LABEL' || event.target.closest('a')) {
                    return;
                }
                
                // Toggle the checkbox inside this row!
                const checkbox = event.currentTarget.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    // Trigger toggleReportDataset to update line, bar, and pie charts
                    toggleReportDataset(name, tag, index, checkbox.checked, event.currentTarget);
                }
            }

            function adjustColorOpacity(hex, opacity) {
                if (hex.startsWith('#')) {
                    const r = parseInt(hex.slice(1, 3), 16);
                    const g = parseInt(hex.slice(3, 5), 16);
                    const b = parseInt(hex.slice(5, 7), 16);
                    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
                }
                return hex;
            }

            function focusReport(reportName, reportTag, colorIndex) {
                const color = categoryColors[colorIndex] || '#3b82f6';
                
                // 1. Update Checkboxes & row styles
                document.querySelectorAll('.report-link-item').forEach((item, idx) => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    const label = item.querySelector('.report-name').innerText;
                    
                    if (checkbox) {
                        checkbox.checked = (label === reportName);
                    }
                    
                    item.classList.remove('active-report');
                    item.style.borderLeft = 'none';
                    
                    if (label === reportName) {
                        item.classList.add('active-report');
                        item.style.borderLeft = `4px solid ${color}`;
                    }
                });

                // 2. Load and Set ONLY this report's line dataset
                const dataset = Object.assign(getDatasetForReport(reportName, reportTag), { borderColor: color });
                comparisonChart.data.datasets = [dataset];
                comparisonChart.update();

                // 3. Set Bar Chart to display single report's distribution
                barChart.data.datasets = [{
                    label: reportName,
                    data: dataset.data,
                    backgroundColor: color + 'b0',
                    hoverBackgroundColor: color,
                    borderRadius: 4
                }];
                document.getElementById('barChartTitle').innerHTML = `<i class="ti ti-chart-bar text-emerald-500"></i> ${reportName} - Monthly Distribution (Bar Graph)`;
                barChart.update();

                // 4. Update Pie Chart to show the dedicated sub-category breakdown
                const pieData = getPieDataForReport(reportName);
                pieChart.data.labels = pieData.labels;
                pieChart.data.datasets[0].data = pieData.values;
                pieChart.data.datasets[0].backgroundColor = [
                    color,
                    adjustColorOpacity(color, 0.75),
                    adjustColorOpacity(color, 0.5),
                    adjustColorOpacity(color, 0.25)
                ];
                document.getElementById('pieChartTitle').innerHTML = `<i class="ti ti-chart-pie text-indigo-500"></i> ${reportName} Share (Pie Chart)`;
                pieChart.update();
                updateCustomHTMLLegend();
            }

            function saveComparisonSelection() {
                const selectedNames = [];
                document.querySelectorAll('.report-link-item').forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    const label = item.querySelector('.report-name').innerText;
                    if (checkbox && checkbox.checked) {
                        selectedNames.push(label);
                    }
                });
                
                localStorage.setItem('selectedReports', JSON.stringify(selectedNames));
                
                // Show a beautiful premium success notification
                showToastNotification("Comparison selection saved successfully!");
            }

            function showToastNotification(message) {
                const container = document.getElementById('toast-container');
                if (!container) return;
                const toast = document.createElement('div');
                toast.className = "flex items-center gap-3 bg-slate-900/90 text-white px-4 py-3 rounded-xl shadow-2xl border border-slate-700/50 backdrop-blur-md transition-all duration-300 transform translate-y-4 opacity-0 pointer-events-auto text-xs font-semibold";
                toast.style.pointerEvents = 'auto';
                toast.innerHTML = `
                    <i class="ti ti-circle-check text-emerald-400 text-base"></i>
                    <span>${message}</span>
                `;
                container.appendChild(toast);
                
                // Animate entry
                setTimeout(() => {
                    toast.classList.remove('translate-y-4', 'opacity-0');
                }, 10);
                
                // Animate exit and remove
                setTimeout(() => {
                    toast.classList.add('translate-y-[-10px]', 'opacity-0');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }

            function updateCustomHTMLLegend() {
                const container1 = document.getElementById('customChartLegend');
                const container2 = document.getElementById('customBarChartLegend');
                if (!container1 && !container2) return;
                
                const activeDatasets = comparisonChart.data.datasets;
                if (activeDatasets.length === 0) {
                    const fallback = `<span class="text-xs font-semibold text-slate-400">No Reports Selected</span>`;
                    if (container1) container1.innerHTML = fallback;
                    if (container2) container2.innerHTML = fallback;
                    return;
                }
                
                const html = activeDatasets.map(ds => {
                    const color = ds.borderColor;
                    return `
                        <div class="flex items-center gap-2 cursor-pointer select-none transition-all hover:opacity-80" onclick="focusReportFromLegend('${ds.label}')">
                            <span class="inline-block rounded-full border-[3px] bg-[#e2e8f0] dark:bg-slate-700" style="border-color: ${color}; width: 18px; height: 18px; min-width: 18px; min-height: 18px;"></span>
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-bold tracking-wide">${ds.label}</span>
                        </div>
                    `;
                }).join('');

                if (container1) container1.innerHTML = html;
                if (container2) container2.innerHTML = html;
            }

            function focusReportFromLegend(label) {
                const reportIndex = Array.from(document.querySelectorAll('.report-link-item')).findIndex(item => {
                    return item.querySelector('.report-name').innerText === label;
                });
                if (reportIndex !== -1) {
                    const reportElement = document.querySelectorAll('.report-link-item')[reportIndex];
                    const tag = reportElement.querySelector('.report-tag-mini').innerText;
                    focusReport(label, tag, reportIndex);
                }
            }
        </script>
    @endpush