@extends('layouts.tenant')

@section('title', 'AI Smart Reports')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    :root {
        --ai-primary: #a855f7;
        --ai-secondary: #3b82f6;
        --ai-glow: rgba(168, 85, 247, 0.4);
    }

    .page-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;
    }
    .page-header h1 { font-size: 24px; font-weight: 800; color: #1a1a2e; display: flex; align-items: center; gap: 12px; }
    .dark .page-header h1 { color: #f8fafc; }
    .ai-icon { color: var(--ai-primary); filter: drop-shadow(0 0 8px var(--ai-glow)); animation: pulse 2s infinite; }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px; margin-bottom: 2rem;
    }
    .stat-card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 16px; padding: 24px;
        position: relative; overflow: hidden; transition: all 0.3s ease;
    }
    .dark .stat-card { background: rgba(15, 23, 42, 0.6); border-color: rgba(168, 85, 247, 0.2); backdrop-blur: 10px; }
    .stat-card:hover { transform: translateY(-5px); border-color: var(--ai-primary); box-shadow: 0 10px 30px -10px var(--ai-glow); }
    .stat-label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
    .stat-value { font-size: 28px; font-weight: 800; color: #1a1a2e; margin-top: 8px; }
    .dark .stat-value { color: #f8fafc; }

    .grid-layout { display: grid; grid-template-columns: 2fr 1.2fr; gap: 24px; }
    @media (max-width: 1024px) { .grid-layout { grid-template-columns: 1fr; } }

    .card {
        background: #fff; border: 1px solid #e8eaf0; border-radius: 20px; padding: 24px; margin-bottom: 24px;
    }
    .dark .card { background: rgba(15, 23, 42, 0.4); border-color: rgba(51, 65, 85, 0.5); }
    .card-title { font-size: 16px; font-weight: 800; color: #1a1a2e; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .dark .card-title { color: #f8fafc; }

    .insight-item {
        padding: 16px; border-radius: 14px; margin-bottom: 12px;
        display: flex; gap: 16px; transition: all 0.2s ease; border: 1px solid transparent;
    }
    .insight-item:hover { background: rgba(168, 85, 247, 0.05); border-color: rgba(168, 85, 247, 0.1); }
    .insight-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; shrink: 0; }
    .insight-title { font-size: 14px; font-weight: 700; color: #1a1a2e; }
    .dark .insight-title { color: #f8fafc; }
    .insight-desc { font-size: 12px; color: #64748b; margin-top: 4px; line-height: 1.5; }
    .priority-badge { font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }

    .chart-container { height: 350px; position: relative; }

    .anomaly-card {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px; background: #f8fafc; border-radius: 12px; margin-bottom: 8px;
    }
    .dark .anomaly-card { background: #1e293b; }
    .anomaly-date { font-size: 13px; font-weight: 700; }
    .anomaly-diff { font-size: 11px; font-weight: 700; }

    @media print {
        aside, header, nav, .btn, .filter-bar { display: none !important; }
        body { background: #fff !important; }
        .card, .stat-card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'ai'])

    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        
        <div class="page-header">
            <div>
                <h1><i class="ti ti-sparkles ai-icon"></i> AI Smart Reports</h1>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Virtual Analyst • Real-time Insights</p>
            </div>
            <div class="flex gap-3">
                <div class="flex items-center gap-2 px-4 py-2 bg-purple-500/10 text-purple-500 rounded-full text-xs font-bold border border-purple-500/20">
                    <span class="w-2 h-2 bg-purple-500 rounded-full animate-ping"></span>
                    AI Engine Online
                </div>
            </div>
        </div>

        <div class="stats-grid">
            @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="text-[11px] font-bold mt-2 text-slate-400 uppercase tracking-tighter">
                    {{ $stat['change'] }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid-layout">
            <!-- Forecast Chart -->
            <div class="card">
                <div class="card-title"><i class="ti ti-chart-arrows-vertical"></i> Predictive Sales Forecast (3 Months)</div>
                <div class="chart-container">
                    <canvas id="forecastChart"></canvas>
                </div>
                <p class="text-[11px] text-slate-400 mt-4 italic">* Forecast is based on linear projection of the last 6 months of historical data.</p>
            </div>

            <!-- Smart Insights Feed -->
            <div class="card">
                <div class="card-title"><i class="ti ti-bulb"></i> Intelligent Business Insights</div>
                <div class="space-y-4">
                    @forelse ($insights as $insight)
                    <div class="insight-item">
                        <div class="insight-icon bg-{{ $insight['color'] }}-500/10 text-{{ $insight['color'] }}-500">
                            <i class="ti {{ $insight['icon'] }}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="insight-title">{{ $insight['title'] }}</div>
                                <span class="priority-badge bg-{{ $insight['color'] }}-100 text-{{ $insight['color'] }}-600">{{ $insight['priority'] }}</span>
                            </div>
                            <div class="insight-desc">{{ $insight['desc'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10">
                        <i class="ti ti-checks text-4xl text-slate-200 mb-2"></i>
                        <p class="text-slate-400 text-sm">No critical risks identified. Everything looks optimal!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Anomaly Detection -->
        <div class="card">
            <div class="card-title"><i class="ti ti-activity-heartbeat"></i> Anomaly Detection (Last 30 Days)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse ($anomalies as $anomaly)
                <div class="anomaly-card border-l-4 border-{{ $anomaly['type'] == 'High' ? 'emerald' : 'rose' }}-500">
                    <div>
                        <div class="anomaly-date">{{ $anomaly['date'] }}</div>
                        <div class="text-[10px] text-slate-400 uppercase font-black">{{ $anomaly['type'] }} VOLUME</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold {{ $anomaly['type'] == 'High' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $anomaly['value'] }}</div>
                        <div class="anomaly-diff {{ $anomaly['type'] == 'High' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $anomaly['diff'] }} deviation</div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-6 text-slate-400 text-sm italic">
                    Stability check: No significant daily sales anomalies detected in the last 30 days.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
        const labelColor = isDark ? '#94a3b8' : '#888';

        new Chart(document.getElementById('forecastChart'), {
            type: 'line',
            data: {
                labels: @json($chartData['forecastLabels']),
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: @json($chartData['forecastActuals']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 4,
                        pointRadius: 5,
                        pointBackgroundColor: '#3b82f6',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'AI Projection',
                        data: @json($chartData['forecastProjections']),
                        borderColor: '#ad62f4ff',
                        backgroundColor: 'rgba(168, 85, 247, 0.05)',
                        borderWidth: 4,
                        borderDash: [5, 5],
                        pointRadius: 6,
                        pointStyle: 'star',
                        pointBackgroundColor: '#a855f7',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', labels: { color: labelColor, usePointStyle: true, font: { weight: 'bold' } } },
                    tooltip: { backgroundColor: isDark ? '#0f172a' : '#fff', titleColor: isDark ? '#fff' : '#0f172a', bodyColor: isDark ? '#cbd5e1' : '#64748b', borderColor: '#a855f7', borderWidth: 1 }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 'bold' } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: (v) => '₹' + v.toLocaleString() } }
                }
            }
        });
    });
</script>
@endpush
