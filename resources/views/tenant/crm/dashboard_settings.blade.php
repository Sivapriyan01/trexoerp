@extends('layouts.tenant')
@section('title', 'CRM Dashboard Configuration')

@section('content')
<div x-data="dashboardManager()" class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.settings') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Dashboard Configuration</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Design and customize visual analytics for your CRM</p>
            </div>
        </div>
        <button @click="showAddDashboardModal = true" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
            Add New Dashboard
        </button>
    </div>

    <!-- Dashboard Selection & Widget Add -->
    <div class="glass-card p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl space-y-8">
        <div class="flex flex-wrap items-end gap-6">
            <div class="flex-1 space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Dashboard</label>
                <div class="flex items-center gap-4">
                    <select x-model="selectedDashboardId" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                        <option value="">Select Dashboard</option>
                        @foreach($dashboards as $db)
                            <option value="{{ $db->id }}">{{ $db->name }}</option>
                        @endforeach
                    </select>
                    <button x-show="selectedDashboardId" @click="deleteDashboard()" class="p-4 text-rose-500 bg-rose-50 dark:bg-rose-900/20 rounded-2xl hover:bg-rose-100 transition-all">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-shrink-0">
                <button @click="openAddWidgetModal()" :disabled="!selectedDashboardId" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-100 disabled:text-slate-400 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    Add New Widget
                </button>
            </div>
        </div>

        <div x-show="!selectedDashboardId" class="py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
            Select a dashboard to manage widgets
        </div>

        <!-- Widget Grid -->
        <div x-show="selectedDashboardId" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 min-h-[300px]">
            <template x-for="widget in currentWidgets" :key="widget.id">
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 space-y-4 relative group">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white truncate pr-8" x-text="widget.title"></h4>
                        <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                            <button class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click="deleteWidget(widget.id)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="h-40 flex flex-col items-center justify-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4">
                        <template x-if="widget.type === 'number'">
                            <div class="text-center">
                                <div class="text-4xl font-black text-blue-600 dark:text-blue-400 tracking-tight">754</div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1" x-text="widget.title"></div>
                            </div>
                        </template>
                        <template x-if="widget.type.includes('chart')">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-full h-24 text-blue-200 dark:text-blue-900" viewBox="0 0 100 40">
                                    <path d="M5,35 L15,20 L25,30 L35,10 L45,25 L55,15 L65,30 L75,10 L85,20 L95,5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                </svg>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <button @click="openAddWidgetModal()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2rem] p-8 flex flex-col items-center justify-center gap-3 text-slate-400 hover:border-blue-400 hover:text-blue-400 transition-all group">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-xs font-black uppercase tracking-widest">New Widget</span>
            </button>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Dashboard Modal -->
    <div x-show="showAddDashboardModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showAddDashboardModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] w-full max-w-md overflow-hidden shadow-2xl">
            <div class="p-8 space-y-6">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">New Dashboard</h3>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dashboard Name</label>
                        <input type="text" x-model="newDashboard.name" placeholder="e.g. Sales Overview" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Attach to Workspace</label>
                        <select x-model="newDashboard.workspace_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold">
                            <option value="">Tenant Wide (All Workspaces)</option>
                            @foreach($workspaces as $ws)
                                <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button @click="showAddDashboardModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest">Cancel</button>
                    <button @click="saveDashboard()" class="flex-2 px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-100" :disabled="!newDashboard.name">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Widget Modal -->
    <div x-show="showAddWidgetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showAddWidgetModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] w-full max-w-lg overflow-hidden shadow-2xl">
            <div class="p-8 space-y-6">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Configure Widget</h3>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Widget Title</label>
                        <input type="text" x-model="newWidget.title" placeholder="e.g. Total Leads" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Widget Type</label>
                            <select x-model="newWidget.type" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold">
                                <option value="number">Stat Number</option>
                                <option value="bar_chart">Bar Chart</option>
                                <option value="line_chart">Line Chart</option>
                                <option value="pie_chart">Pie Chart</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Workflow Source</label>
                            <select x-model="newWidget.workflow_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold">
                                <option value="">Global (All Workflows)</option>
                                @foreach($workflows as $wf)
                                    <option value="{{ $wf->id }}">{{ $wf->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Group By Field (For Charts)</label>
                        <select x-model="newWidget.field_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold" :disabled="newWidget.type === 'number'">
                            <option value="">Default (Status/Date)</option>
                            <!-- Fields will be populated via AJAX based on workflow -->
                        </select>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button @click="showAddWidgetModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest">Cancel</button>
                    <button @click="saveWidget()" class="flex-2 px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest" :disabled="!newWidget.title">Deploy Widget</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardManager() {
    return {
        selectedDashboardId: '',
        showAddDashboardModal: false,
        showAddWidgetModal: false,
        newDashboard: { name: '', workspace_id: '' },
        newWidget: { title: '', type: 'number', workflow_id: '', field_id: '' },
        dashboards: @json($dashboards),
        currentWidgets: [],

        init() {
            this.$watch('selectedDashboardId', (val) => {
                if (val) {
                    const db = this.dashboards.find(d => d.id == val);
                    this.currentWidgets = db ? db.widgets : [];
                } else {
                    this.currentWidgets = [];
                }
            });
        },

        saveDashboard() {
            fetch("{{ route('tenant.crm.settings.dashboards.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.newDashboard)
            }).then(r => r.json()).then(data => {
                if (data.success) { window.location.reload(); }
            });
        },

        openAddWidgetModal() {
            this.newWidget = { title: '', type: 'number', workflow_id: '', field_id: '' };
            this.showAddWidgetModal = true;
        },

        saveWidget() {
            fetch("{{ route('tenant.crm.settings.widgets.store', ['dashboard' => ':id']) }}".replace(':id', this.selectedDashboardId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.newWidget)
            }).then(r => r.json()).then(data => {
                if (data.success) { window.location.reload(); }
            });
        },

        deleteDashboard() {
            if (!confirm('Are you sure you want to delete this entire dashboard and all its widgets?')) return;
            fetch("{{ route('tenant.crm.settings.dashboards.delete', ['dashboard' => ':id']) }}".replace(':id', this.selectedDashboardId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) { window.location.reload(); }
            });
        },

        deleteWidget(id) {
            if (!confirm('Delete this widget?')) return;
            fetch("{{ route('tenant.crm.settings.widgets.delete', ['widget' => ':id']) }}".replace(':id', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) { window.location.reload(); }
            });
        }
    }
}
</script>
@endsection

