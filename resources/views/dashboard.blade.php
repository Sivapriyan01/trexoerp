<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <style>
            /* Premium Glassmorphic Styles */
            .superadmin-content {
                padding: 2rem;
                background: #f8fafc; /* Slate 50 */
                min-height: calc(100vh - 4rem);
            }
            .dark .superadmin-content {
                background: #0f172a; /* Slate 900 */
            }
            .page-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 2rem;
            }
            .page-header h1 {
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .dark .page-header h1 {
                color: #f8fafc;
            }
            .text-blue {
                color: #3b82f6;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .stat-card {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(226, 232, 240, 0.8);
                border-radius: 24px;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: all 0.3s ease;
            }
            .dark .stat-card {
                background: rgba(30, 41, 59, 0.5);
                border-color: rgba(51, 65, 85, 0.5);
            }
            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            .stat-label {
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0.5rem;
            }
            .stat-value {
                font-size: 32px;
                font-weight: 800;
                color: #0f172a;
            }
            .dark .stat-value {
                color: #f8fafc;
            }
            .stat-icon {
                float: right;
                font-size: 2rem;
                color: #64748b;
                opacity: 0.3;
            }
            .card {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(226, 232, 240, 0.8);
                border-radius: 24px;
                padding: 1.5rem;
                margin-bottom: 2rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }
            .dark .card {
                background: rgba(30, 41, 59, 0.5);
                border-color: rgba(51, 65, 85, 0.5);
            }
            .card-title {
                font-size: 18px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .dark .card-title {
                color: #f8fafc;
            }
            .chart-container {
                position: relative;
                height: 300px;
                width: 100%;
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
            .btn-primary {
                background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
                color: #fff;
                box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.5);
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.6);
            }
            .btn-outline {
                background: #fff;
                color: #475569;
                border: 1px solid #cbd5e1;
            }
            .dark .btn-outline {
                background: #1e293b;
                color: #cbd5e1;
                border-color: #475569;
            }
            .btn-outline:hover {
                background: #f8fafc;
                transform: translateY(-2px);
            }
            .dark .btn-outline:hover {
                background: #334155;
            }
        </style>
    @endpush

    <div class="superadmin-content">
        <div class="page-header">
            <h1><i class="ti ti-layout-dashboard"></i> Super Admin <span class="text-blue">Dashboard</span></h1>
            <div class="flex gap-3">
                <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-outline">
                    <i class="ti ti-users"></i> Manage Tenants
                </a>
                <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary">
                    <i class="ti ti-user-plus"></i> Add New Tenant
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="ti ti-building stat-icon"></i>
                <div class="stat-label">Total Tenants</div>
                <div class="stat-value">{{ \App\Models\Tenant::count() }}</div>
            </div>

            <div class="stat-card">
                <i class="ti ti-world stat-icon"></i>
                <div class="stat-label">Active Domains</div>
                <div class="stat-value">{{ \Stancl\Tenancy\Database\Models\Domain::count() }}</div>
            </div>

            <div class="stat-card">
                <i class="ti ti-shield-check stat-icon text-emerald-500" style="opacity: 0.8;"></i>
                <div class="stat-label">System Status</div>
                <div class="stat-value text-emerald-500">Operational</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart -->
            <div class="card lg:col-span-2">
                <div class="card-title"><i class="ti ti-chart-line"></i> Tenant Growth</div>
                <div class="chart-container">
                    <canvas id="tenantGrowthChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-title"><i class="ti ti-activity"></i> Recent Activity</div>
                <div class="space-y-4">
                    <div class="text-sm text-slate-500 italic">No recent central activity recorded.</div>
                    <!-- Future activity items can go here -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : '#f0f0f0';
                const labelColor = isDark ? '#94a3b8' : '#64748b';

                const ctx = document.getElementById('tenantGrowthChart').getContext('2d');
                
                // Create gradient
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                            label: 'Total Tenants',
                            data: @json($chartData['tenants']),
                            borderColor: '#3b82f6',
                            borderWidth: 3,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            x: { 
                                grid: { display: false }, 
                                ticks: { color: labelColor, font: { weight: 'bold' } } 
                            },
                            y: { 
                                grid: { color: gridColor }, 
                                ticks: { 
                                    color: labelColor,
                                    stepSize: 1,
                                    callback: (v) => Math.floor(v) === v ? v : ''
                                } 
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
