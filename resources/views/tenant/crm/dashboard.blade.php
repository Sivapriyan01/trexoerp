@extends('layouts.tenant')
@section('title', 'CRM Dashboard')

@section('content')
<div class="flex h-full bg-white dark:bg-slate-900 overflow-hidden -m-6 rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-800">
    <!-- Sidebar: CRM Management -->
    <div class="w-72 flex flex-col h-full border-r border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-xl">
        <!-- CRM Header -->
        <div class="p-8 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4 group cursor-pointer">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-none group-hover:scale-110 transition-transform duration-300">
                    <span class="text-white text-xl font-black">C</span>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">CRM</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Management</p>
                </div>
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="ml-auto text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-8 custom-scrollbar">


            <!-- All Leads -->
            <a href="{{ route('tenant.crm.index') }}" 
               class="w-full p-4 rounded-2xl flex items-center gap-3 text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 transition-all duration-300 group">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 dark:bg-slate-800 group-hover:bg-blue-50 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">All Leads</span>
            </a>

            <!-- Workflows Section -->
            <div class="space-y-4">
                <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Workflows</p>
                <div class="space-y-1">
                    @foreach($workflows as $wf)
                    <a href="{{ route('tenant.crm.index', ['workflow' => $wf->id]) }}" 
                       class="w-full p-3.5 rounded-2xl flex items-center justify-between transition-all group text-slate-600 dark:text-slate-400 hover:bg-slate-100/30">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $wf->color == 'blue' ? '#4f46e5' : ($wf->color == 'blue' ? '#3b82f6' : ($wf->color == 'emerald' ? '#10b981' : ($wf->color == 'violet' ? '#8b5cf6' : '#f59e0b'))) }}"></div>
                            <span class="text-[11px] font-bold uppercase tracking-tight">{{ $wf->name }}</span>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-lg bg-white dark:bg-slate-800 shadow-sm">{{ $wf->leads_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Workspaces Section -->
            <div class="space-y-4">
                <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Workspaces</p>
                <div class="space-y-1">
                    @foreach($workspaces as $workspace)
                    <a href="{{ route('tenant.crm.index', ['workspace' => $workspace->id]) }}" 
                       class="w-full p-3.5 rounded-2xl flex items-center gap-3 transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100/30">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-white dark:bg-slate-800 shadow-sm">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-tight">{{ $workspace->name }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            @if(auth('tenant')->user()->hasPermission('SetUp') || auth('tenant')->user()->hasPermission('CRM_Settings'))
            <a href="{{ route('tenant.crm.settings') }}" class="w-full p-4 rounded-2xl flex items-center gap-3 text-slate-500 hover:bg-white dark:hover:bg-slate-800 hover:shadow-lg transition-all mt-4">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">Settings</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Main Content: Dashboard Grid -->
    <div x-data="{ selectedDashboardId: {{ $dashboards->first()->id ?? 'null' }} }" class="flex-1 flex flex-col bg-slate-50/50 dark:bg-slate-900 overflow-hidden">
        <div class="p-12 overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">CRM Dashboard</h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">View your custom analytics and workflows</p>
                </div>
                
                @if($dashboards->count() > 1)
                <select x-model.number="selectedDashboardId" class="bg-white dark:bg-slate-800 border-none rounded-2xl px-6 py-3 text-xs font-black uppercase tracking-widest shadow-sm focus:ring-2 focus:ring-blue-500 transition-all">
                    @foreach($dashboards as $db)
                        <option value="{{ $db->id }}">{{ $db->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            @forelse($dashboards as $db)
                <div x-show="selectedDashboardId === {{ $db->id }}" class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-700" style="{{ $dashboards->count() == 1 ? 'display:block;' : '' }}">
                    <!-- Widgets Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @forelse($db->widgets as $widget)
                            <div class="glass-card p-8 rounded-[2.5rem] bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl flex flex-col items-center justify-center text-center group hover:scale-[1.02] transition-all duration-500">
                                @if($widget->type == 'number')
                                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-500">
                                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </div>
                                    <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
                                        {{ $widget->workflow ? $widget->workflow->leads()->count() : \App\Models\CrmLead::count() }}
                                    </div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">{{ $widget->title }}</p>
                                @else
                                    <!-- Chart Placeholder -->
                                    <div class="w-full h-32 flex flex-col items-center justify-center">
                                        <svg class="w-full h-full text-blue-100 dark:text-blue-900/40" viewBox="0 0 100 40" preserveAspectRatio="none">
                                            <path d="M0,40 L10,25 L20,35 L30,15 L40,30 L50,10 L60,25 L70,5 L80,20 L90,10 L100,30 L100,40 L0,40 Z" fill="currentColor" />
                                            <path d="M0,40 L10,25 L20,35 L30,15 L40,30 L50,10 L60,25 L70,5 L80,20 L90,10 L100,30" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600" />
                                        </svg>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-4">{{ $widget->title }}</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full h-[30vh] flex flex-col items-center justify-center text-center space-y-4">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-300">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-500 uppercase tracking-widest">No Widgets Yet</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1">Configure widgets in dashboard settings.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="h-[50vh] flex flex-col items-center justify-center text-center space-y-6">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-[2.5rem] flex items-center justify-center text-slate-200">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-300 uppercase tracking-tight">No Custom Dashboards</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 mb-8">Create your first dashboard in settings to see analytics here</p>
                        @if(auth('tenant')->user()->hasPermission('SetUp'))
                        <a href="{{ route('tenant.crm.settings.dashboards') }}" class="px-8 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                            Configure Dashboard
                        </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

