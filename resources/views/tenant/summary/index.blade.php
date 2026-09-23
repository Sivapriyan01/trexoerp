@extends('layouts.tenant')
@section('title', 'Financial Summary')

@section('content')
<div class="space-y-6 max-w-[1400px] mx-auto animate-in fade-in duration-700" x-data="summaryDashboard()">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h2 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Financial Summary <span class="text-slate-400 font-bold text-lg md:text-xl">({{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }})</span>
        </h2>
        
        <div class="flex items-center gap-3">
            @php
                $todayAnniversaries = \App\Models\Bill::where('reminder_enabled', true)
                    ->whereMonth('bill_date', now()->month)
                    ->whereDay('bill_date', now()->day)
                    ->whereYear('bill_date', '<', now()->year)
                    ->count();
            @endphp
            @if($todayAnniversaries > 0)
                <a href="{{ route('tenant.reminders.index') }}" class="flex items-center gap-2 px-6 py-3 bg-rose-50 dark:bg-rose-900/20 text-rose-600 rounded-xl text-sm font-black transition-all border border-rose-100 dark:border-rose-800 animate-pulse">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a2.98 2.98 0 0 0 2.818-2H9.182A2.98 2.98 0 0 0 12 22zm7-7.414V11a7 7 0 1 0-14 0v3.586l-2 2V18h18v-.414l-2-2z"/></svg>
                    {{ $todayAnniversaries }} Anniversaries Today
                </a>
            @endif

            <button class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-600/20">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Day Close
            </button>
            
            <form action="{{ route('tenant.summary.index') }}" method="GET" x-ref="filterForm" class="flex items-center gap-2">
                <template x-if="filter === 'custom'">
                    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 shadow-sm">
                        <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="text-[13px] font-bold bg-transparent border-none outline-none text-slate-700 dark:text-slate-200 p-1" required>
                        <span class="text-slate-400 text-[10px] uppercase font-black tracking-widest">to</span>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="text-[13px] font-bold bg-transparent border-none outline-none text-slate-700 dark:text-slate-200 p-1" required>
                        <button type="submit" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all ml-1">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </template>
                
                <select name="filter" x-model="filter" @change="if(filter !== 'custom') $refs.filterForm.submit()" class="px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
        <div class="flex items-center gap-2 mb-6">
            <svg width="20" height="20" class="text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-lg font-black text-slate-800 dark:text-white">Sales Summary</h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-100/50 dark:border-blue-800/30">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Total Invoices</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalInvoices }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-100/50 dark:border-emerald-800/30">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Cash Sales</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($cashSales, 0) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-fuchsia-50/80 dark:bg-fuchsia-900/20 border border-fuchsia-100/50 dark:border-fuchsia-800/30">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">QR Payments</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($qrSales, 0) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-900/20 border border-amber-100/50 dark:border-amber-800/30">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Card Payments</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($cardSales, 0) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-rose-50/80 dark:bg-rose-900/20 border border-rose-100/50 dark:border-rose-800/30">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Credit Sales</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($creditSales, 0) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-100/50 dark:border-blue-800/30">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Total Sales</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($totalSales, 0) }}</p>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="w-full h-[300px]">
            <canvas id="salesSummaryChart"></canvas>
        </div>
    </div>

    <!-- Middle Section: Expenses & Purchases -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Expenses -->
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-6">
                <svg width="20" height="20" class="text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <h3 class="text-lg font-black text-slate-800 dark:text-white">Expenses</h3>
            </div>
            
            <div class="p-5 rounded-2xl bg-rose-50/80 dark:bg-rose-900/20 border border-rose-100/50 dark:border-rose-800/30 mb-6">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Total Expenses</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white">₹{{ number_format($totalExpenses, 0) }}</p>
            </div>

            <!-- Expense Chart Placeholder -->
            <div class="w-full h-[200px] flex items-center justify-center border-2 border-dashed border-slate-100 dark:border-slate-700 rounded-2xl">
                <p class="text-xs font-bold text-slate-400">No expense data available</p>
            </div>
        </div>

        <!-- Purchases -->
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-6">
                <svg width="20" height="20" class="text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <h3 class="text-lg font-black text-slate-800 dark:text-white">Purchases</h3>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-100/50 dark:border-emerald-800/30">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Total Purchases Count</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalPurchasesCount }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-100/50 dark:border-blue-800/30">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Total Purchases</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($totalPurchases, 0) }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-100/50 dark:border-emerald-800/30">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Amount Settled</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($amountSettled, 0) }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-orange-50/80 dark:bg-orange-900/20 border border-orange-100/50 dark:border-orange-800/30">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Pending Payment</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($pendingPayment, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Cash Movement -->
    <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
        <div class="flex items-center gap-2 mb-6">
            <svg width="20" height="20" class="text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <h3 class="text-lg font-black text-slate-800 dark:text-white">Cash Movement</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-slate-700 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Opening Cash</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($openingCash, 0) }}</p>
            </div>
            
            <div class="p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-100/50 dark:border-blue-800/30">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Bank Deposits</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($bankDeposits, 0) }}</p>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-2 mb-1">
                    <svg width="14" height="14" class="text-slate-700 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Closing Cash (Calculated)</p>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($closingCash, 0) }}</p>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('summaryDashboard', () => ({
            filter: '{{ $filter }}',
            init() {
                this.initChart();
            },
            initChart() {
                const ctx = document.getElementById('salesSummaryChart').getContext('2d');
                
                // Colors matching the cards
                const colors = {
                    cash: '#10b981',   // Emerald
                    qr: '#d946ef',     // Fuchsia
                    card: '#f59e0b',   // Amber
                    credit: '#f43f5e'  // Rose
                };

                const chartLabels = @json($chartLabels);
                const chartData = @json($chartData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Cash Sales',
                                data: chartData.cash,
                                backgroundColor: colors.cash,
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'QR Sales',
                                data: chartData.qr,
                                backgroundColor: colors.qr,
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Card Sales',
                                data: chartData.card,
                                backgroundColor: colors.card,
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Credit Sales',
                                data: chartData.credit,
                                backgroundColor: colors.credit,
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 11,
                                        weight: 'bold'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { size: 13, family: "'Inter', sans-serif" },
                                bodyFont: { size: 12, family: "'Inter', sans-serif" },
                                padding: 12,
                                cornerRadius: 8,
                            }
                        },
                        scales: {
                            y: {
                                stacked: true,
                                border: { display: false },
                                grid: {
                                    color: 'rgba(226, 232, 240, 0.5)',
                                    drawTicks: false
                                },
                                ticks: {
                                    font: { family: "'Inter', sans-serif", size: 10 },
                                    color: '#64748b'
                                }
                            },
                            x: {
                                stacked: true,
                                border: { display: false },
                                grid: { display: false },
                                ticks: {
                                    font: { family: "'Inter', sans-serif", size: 10 },
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                });
            }
        }))
    });
</script>
@endpush

