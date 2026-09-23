@extends('layouts.tenant')
@section('title', 'CRM Workflow')

@section('content')
<div class="flex flex-col lg:flex-row h-full bg-white dark:bg-slate-900 lg:overflow-hidden -m-4 md:-m-6 lg:rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-800" 
     x-data="{ 
        activeWorkflowId: {{ $activeWorkflowId ?? 'null' }},
        activeWorkspaceId: {{ $activeWorkspaceId ?? 'null' }},
        switchWorkflow(id) {
            const url = new URL(window.location.href);
            if (id) {
                url.searchParams.set('workflow', id);
                url.searchParams.delete('workspace');
            } else {
                url.searchParams.delete('workflow');
            }
            window.location.href = url.toString();
        },
        switchWorkspace(id) {
            const url = new URL(window.location.href);
            if (id) {
                url.searchParams.set('workspace', id);
                url.searchParams.delete('workflow');
            } else {
                url.searchParams.delete('workspace');
            }
            window.location.href = url.toString();
        },
        showAddModal: false,
        showImportModal: false,
        showLinkModal: false,
        linkStep: 1,
        linkConfig: {
            type: 'one-time',
            expiry: '',
            sectionHeader: 'LEAD INFORMATION',
            explanation: 'PLEASE FILL OUT THE FORM BELOW TO GET IN TOUCH WITH US.',
            mailNotification: true,
            fields: {},
            theme: {
                formName: 'External Lead Form',
                submitButtonName: 'Submit Request',
                primaryColor: '#4f46e5',
                secondaryColor: '#f8fafc',
                textColor: '#1e293b',
                backgroundColor: '#ffffff'
            },
            workflow_id: {{ $activeWorkflowId ?? $workflows->first()->id ?? 'null' }}
        },
        workflows: JSON.parse(atob('{{ base64_encode(json_encode($workflows)) }}')),
        leads: JSON.parse(atob('{{ base64_encode(json_encode(array_values($leads->items() ?? []))) }}')),
        showDetailsModal: false,
        showHistoryModal: false,
        activeLead: null,
        csrfToken: '{{ csrf_token() }}',
        openDetails(id) {
            this.activeLead = (this.leads || []).find(l => l.id == id);
            this.showDetailsModal = true;
        },
        openHistory(id) {
            this.activeLead = (this.leads || []).find(l => l.id == id);
            this.showHistoryModal = true;
        },
        editLead(id) {
            window.location.href = `/crm/lead/${id}/edit`;
        },
        deleteLead(id) {
            if (!confirm('Are you sure you want to delete this lead?')) return;
            fetch(`/crm/lead/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Delete failed');
                this.leads = this.leads.filter(l => l.id !== id);
            })
            .catch(error => {
                alert(error.message);
            });
        },
        newLead: {
            workflow_id: {{ $activeWorkflowId ?? $workflows->first()->id ?? 'null' }},
            data: {}
        },
        publicUrl: '',
        publicSlug: '',
        generating: false,
        publicForms: [],
        showManageLinksModal: false,
        loadingLinks: false,
        async openManageLinks() {
            this.showManageLinksModal = true;
            this.loadingLinks = true;
            try {
                const response = await fetch('{{ route('tenant.crm.settings.public-forms.index') }}');
                if(!response.ok) throw new Error('Failed to fetch links. Status: ' + response.status);
                this.publicForms = await response.json();
            } catch (e) {
                console.error(e);
                alert('Error loading links: ' + e.message);
            } finally {
                this.loadingLinks = false;
            }
        },
        async deletePublicForm(id) {
            if(!confirm('Are you sure you want to delete this link?')) return;
            try {
                const res = await fetch(`/crm/settings/public-forms/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                });
                if(!res.ok) throw new Error('Failed to delete');
                this.publicForms = this.publicForms.filter(f => f.id !== id);
                alert('Link deleted successfully');
            } catch (e) {
                alert('Delete failed: ' + e.message);
            }
        },
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                alert('Link copied to clipboard!');
            } catch (err) {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                alert('Link copied to clipboard!');
            }
        },
        async generatePublicLink() {
            this.generating = true;
            try {
                const response = await fetch('{{ route('tenant.crm.settings.public-forms.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        workspace_id: this.activeWorkspaceId || (this.workflows.find(w => w.id == this.linkConfig.workflow_id)?.workspace_id),
                        workflow_id: this.linkConfig.workflow_id,
                        name: this.linkConfig.theme.formName,
                        type: this.linkConfig.type,
                        title: this.linkConfig.sectionHeader,
                        description: this.linkConfig.explanation,
                        fields: this.linkConfig.fields,
                        settings: {
                            ...this.linkConfig.theme,
                            mailNotification: this.linkConfig.mailNotification
                        },
                        expiry: this.linkConfig.expiry
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    this.publicUrl = result.url;
                    this.publicSlug = result.slug;
                    this.linkStep = 5;
                } else {
                    alert('Failed to generate link. Please try again.');
                }
            } catch (error) {
                console.error('Error generating link:', error);
                alert('Connection error.');
            } finally {
                this.generating = false;
            }
        },
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('create_link') === 'true') {
                this.showLinkModal = true;
                this.linkStep = 1;
                // Remove the param from URL without reloading
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
     }">
    <!-- Sidebar: CRM Management -->
    <div class="w-full lg:w-72 flex flex-col lg:h-full border-b lg:border-b-0 lg:border-r border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-xl">
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
            <button @click="switchWorkflow(null)" 
                    :class="!activeWorkflowId && !activeWorkspaceId ? 'bg-blue-600 text-white shadow-xl shadow-blue-100' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800'"
                    class="w-full p-4 rounded-2xl flex items-center gap-3 transition-all duration-300 group">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-colors" :class="!activeWorkflowId && !activeWorkspaceId ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800 group-hover:bg-blue-50'">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">All Leads</span>
            </button>

            <!-- Workflows Section -->
            <div class="space-y-4">
                <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Workflows</p>
                <div class="space-y-1">
                    @foreach($workflows as $wf)
                    <button @click="switchWorkflow({{ $wf->id }})" 
                            :class="activeWorkflowId == {{ $wf->id }} ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/30'"
                            class="w-full p-3.5 rounded-2xl flex items-center justify-between transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $wf->color == 'blue' ? '#4f46e5' : ($wf->color == 'blue' ? '#3b82f6' : ($wf->color == 'emerald' ? '#10b981' : ($wf->color == 'violet' ? '#8b5cf6' : '#f59e0b'))) }}"></div>
                            <span class="text-[11px] font-bold uppercase tracking-tight">{{ $wf->name }}</span>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-lg bg-white dark:bg-slate-800 shadow-sm">{{ $wf->leads_count }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Workspaces Section -->
            <div class="space-y-4" x-data="{ open: true }">
                <button @click="open = !open" class="px-4 w-full flex items-center justify-between text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Workspaces
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? '' : '-rotate-90'" class="transition-transform"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="space-y-1">
                    @foreach($workspaces as $workspace)
                    <button @click="switchWorkspace({{ $workspace->id }})" 
                            :class="activeWorkspaceId == {{ $workspace->id }} ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/30'"
                            class="w-full p-3.5 rounded-2xl flex items-center gap-3 transition-all">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-white dark:bg-slate-800 shadow-sm">
                            @if($workspace->name == 'Marketing')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @else
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @endif
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-tight">{{ $workspace->name }}</span>
                    </button>
                    @endforeach
                    <button class="w-full p-2.5 text-slate-400 hover:text-blue-600 flex items-center gap-3 text-[10px] font-black uppercase tracking-widest mt-2">
                        <div class="w-8 h-8 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        Add Workspace
                    </button>
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

    <!-- Main Content: Lead List -->
    <div class="flex-1 flex flex-col bg-slate-50/50 dark:bg-slate-900 lg:overflow-hidden min-w-0">
        <!-- Stats & Action Row -->
        <div class="relative z-50 glass-card p-5 md:p-6 lg:rounded-[2.5rem] flex flex-col md:flex-row md:items-center justify-between bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-white dark:border-slate-700/50 shadow-xl shadow-slate-200/20 dark:shadow-none mb-4 md:mb-6 gap-4">
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lead Analysis</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ $leads->count() }}</span>
                        <span class="text-[11px] font-bold text-slate-400 uppercase leading-none">of {{ $leads->total() }} leads</span>
                    </div>
                </div>
                
                <div class="hidden md:block h-10 w-px bg-slate-100 dark:bg-slate-700/50"></div>
                
                <div class="flex flex-wrap items-center gap-2">
                    @if($activeWorkflowId && auth('tenant')->user()->hasCrmPermission($activeWorkflowId, 'create'))
                    <button @click="showAddModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-100 dark:shadow-none flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Pre Order
                    </button>
                    @endif
                    
                    @if($activeWorkflowId && auth('tenant')->user()->hasCrmPermission($activeWorkflowId, 'export'))
                    <a href="{{ route('tenant.crm.export') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                    </a>
                    @endif

                    @if($activeWorkflowId && auth('tenant')->user()->hasCrmPermission($activeWorkflowId, 'upload'))
                    <button @click="showImportModal = true" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Upload
                    </button>
                    @endif

                    @if($activeWorkflowId && auth('tenant')->user()->hasCrmPermission($activeWorkflowId, 'links'))
                    <button @click="showLinkModal = true; linkStep = 1" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Link
                    </button>
                    @endif

                    @if(auth('tenant')->user()->hasPermission('SetUp') || auth('tenant')->user()->hasPermission('CRM_Settings'))
                    <button @click="openManageLinks()" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-all shadow-sm" title="Manage Public Links">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                    @endif
                    <div class="relative" x-data="{ menuOpen: false }">
                        <button @click="menuOpen = !menuOpen" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            Actions
                        </button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" x-transition class="absolute right-0 mt-4 w-56 bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-700 z-[100] p-3">
                            <div class="px-4 py-2 border-b border-slate-50 dark:border-slate-700/50 mb-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Bulk Management</p>
                            </div>
                            <button @click="alert('Bulk Delete...'); menuOpen = false" class="w-full text-left px-4 py-3 text-[10px] font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition-colors flex items-center gap-3">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Bulk Delete
                            </button>
                            <button @click="alert('Changing Status...'); menuOpen = false" class="w-full text-left px-4 py-3 text-[10px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl transition-colors flex items-center gap-3">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Change Status
                            </button>
                            <button @click="alert('Assigning User...'); menuOpen = false" class="w-full text-left px-4 py-3 text-[10px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl transition-colors flex items-center gap-3">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Assign User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Row -->
        <div class="px-6 mb-4">
            <div class="relative group max-w-md" x-data="{ searchQuery: '{{ request('search') }}' }">
                <input type="text" 
                       x-model="searchQuery"
                       @keydown.enter="window.location.href = '{{ route('tenant.crm.index') }}?search=' + searchQuery"
                       placeholder="Search leads..." 
                       class="w-full bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl px-6 py-3.5 text-[11px] font-bold focus:ring-4 focus:ring-blue-500/10 outline-none dark:text-slate-200 transition-all group-hover:border-slate-200 shadow-sm">
                <div class="absolute right-4 top-3.5 flex items-center gap-2">
                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest hidden group-hover:block">Press Enter</span>
                    <svg class="text-slate-400" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Table Area -->
        <div class="glass-card flex-1 lg:rounded-[3rem] overflow-hidden flex flex-col">
            <div class="flex-1 overflow-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md z-10">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Assignee
                                </div>
                            </th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Status
                                </div>
                            </th>
                            @php $activeWf = $workflows->where('id', $activeWorkflowId)->first(); @endphp
                            @if($activeWf)
                                @foreach($activeWf->fields as $field)
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-2">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            {{ $field->label }}
                                        </div>
                                    </th>
                                @endforeach
                            @endif
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Updated
                                </div>
                            </th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-right w-32 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Action
                                </div>
                            </th>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center text-[10px] font-black">
                                        {{ substr($lead->assignee->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 uppercase">{{ $lead->assignee->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                    {{ $lead->status }}
                                </span>
                            </td>
                            @if($activeWf)
                                @foreach($activeWf->fields as $field)
                                    <td class="px-6 py-5">
                                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                            {{ $lead->data->where('field_id', $field->id)->first()?->value ?? '-' }}
                                        </span>
                                    </td>
                                @endforeach
                            @endif
                            <td class="px-6 py-5 text-right whitespace-nowrap">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $lead->updated_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-5 text-right w-40 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDetails({{ $lead->id }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="View Details">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button @click="openHistory({{ $lead->id }})" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-all" title="Lead History">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button @click="editLead({{ $lead->id }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="Edit Lead">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="deleteLead({{ $lead->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all" title="Delete Lead">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-20 text-center">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg width="24" height="24" class="text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No leads found in this workflow</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-8 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Show</span>
                        <select class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100">
                            <option>15</option>
                            <option>30</option>
                            <option>50</option>
                        </select>
                    </div>
                    <p class="text-[10px] font-bold text-slate-500">Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }} results</p>
                </div>
                <div>
                    {{ $leads->links('vendor.pagination.simple-tailwind') }}
                </div>
            </div>
        </div>
    </div>
    <!-- Add Lead Modal -->
    <div x-show="showAddModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showAddModal = false">
            <div class="p-8 border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">New CRM Lead</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pre-Order Configuration</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-colors text-slate-400">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('tenant.crm.store') }}" method="POST" class="p-8 space-y-6">
                @csrf
                <div class="space-y-6">
                    <div class="col-span-2 space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Workflow Target</label>
                        <select name="workflow_id" required x-model="activeWorkflowId" @change="activeWf = workflows.find(w => w.id == activeWorkflowId)" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <option value="">Select Workflow</option>
                            @foreach($workflows as $wf)
                                <option value="{{ $wf->id }}">{{ $wf->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-6" x-show="activeWf" x-transition>
                        <template x-for="field in activeWf?.fields || []" :key="field.id">
                            <div :class="field.type === 'textarea' ? 'col-span-2' : ''" class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2" x-text="field.label"></label>
                                
                                <template x-if="field.type === 'select'">
                                    <select :name="'fields[' + (field.name || field.label) + ']'" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                        <option value="">Select Option</option>
                                        <template x-for="opt in (field.options || [])" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </template>

                                <template x-if="field.type === 'textarea'">
                                    <textarea :name="'fields[' + (field.name || field.label) + ']'" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none" :placeholder="'Enter ' + field.label.toLowerCase() + '...'"></textarea>
                                </template>

                                <template x-if="field.type !== 'select' && field.type !== 'textarea'">
                                    <input :type="field.type === 'email' ? 'email' : 'text'" :name="'fields[' + (field.name || field.label) + ']'" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 transition-all" :placeholder="'Enter ' + field.label.toLowerCase() + '...'">
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="!activeWf" class="py-12 text-center bg-slate-50 dark:bg-slate-800/50 rounded-3xl border-2 border-dashed border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Select a workflow above to load fields</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="button" @click="showAddModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                    <button type="submit" class="flex-1 px-8 py-4 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-100 dark:shadow-none transition-all">Create Lead</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Import Modal -->
    <div x-show="showImportModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showImportModal = false">
            <div class="p-8 border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Bulk Import Leads</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">CSV / Excel Upload</p>
                    </div>
                </div>
                <button @click="showImportModal = false" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-colors text-slate-400">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 space-y-6">
                <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem] p-12 flex flex-col items-center justify-center text-center group hover:border-blue-500/50 transition-all cursor-pointer">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="text-slate-400 group-hover:text-blue-600 transition-colors" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Click to upload</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">or drag and drop CSV file</p>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 flex items-start gap-4">
                    <svg class="text-blue-600 mt-1" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-[10px] font-bold text-blue-900 dark:text-blue-300 uppercase tracking-widest leading-relaxed">Ensure your CSV follows the standard template format. You can download the sample template from the settings page.</p>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 border-t border-slate-50 dark:border-slate-800 flex items-center gap-3">
                <button @click="showImportModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                <button @click="alert('Import logic not implemented in demo'); showImportModal = false" class="flex-1 px-8 py-4 bg-blue-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 dark:shadow-none transition-all">Start Import</button>
            </div>
        </div>
    </div>
    <!-- Create External Lead Link Modal -->
    <div x-show="showLinkModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showLinkModal = false">
            <!-- Modal Header -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-slate-50 dark:border-slate-800">
                <h3 class="text-[15px] font-black text-slate-800 dark:text-white tracking-tight">Create External Lead Link</h3>
                <button @click="showLinkModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Steps Indicator -->
            <div class="px-6 py-5">
                <div class="flex items-center justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute top-4 left-0 w-full h-[2px] bg-slate-100 dark:bg-slate-800 -z-10"></div>
                    
                    @foreach(['Type', 'Fields', 'Calendar', 'Theme', 'Share'] as $index => $label)
                        <div class="flex flex-col items-center gap-2 relative bg-white dark:bg-slate-900 px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black border-2 transition-all duration-300"
                                 :class="{
                                    'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200': {{ $index + 1 }} == linkStep,
                                    'bg-blue-50 border-blue-100 text-blue-600': {{ $index + 1 }} < linkStep,
                                    'bg-white border-slate-200 text-slate-400': {{ $index + 1 }} > linkStep
                                 }">
                                {{ $index + 1 }}
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-widest transition-colors"
                                  :class="{{ $index + 1 }} == linkStep ? 'text-blue-600' : 'text-slate-400'">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Step Content -->
            <div class="px-6 py-4 min-h-[350px] overflow-y-auto custom-scrollbar" style="max-height: 50vh;">
                <!-- Step 1: Link Type -->
                <div x-show="linkStep === 1" x-transition class="space-y-8">
                    <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Select Link Type</h4>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div @click="linkConfig.type = 'one-time'" 
                             :class="linkConfig.type === 'one-time' ? 'border-blue-500 bg-blue-50/30 ring-1 ring-blue-500 shadow-lg' : 'border-slate-200 hover:border-blue-300 shadow-sm'"
                             class="p-8 border-2 rounded-[1.5rem] cursor-pointer transition-all">
                            <h5 class="text-md font-black text-slate-800 dark:text-white mb-2">One-time Link</h5>
                            <p class="text-[11px] font-bold text-slate-500 leading-relaxed">Expires after one submission. Best for individual use.</p>
                        </div>
                        
                        <div @click="linkConfig.type = 'permanent'" 
                             :class="linkConfig.type === 'permanent' ? 'border-blue-500 bg-blue-50/30 ring-1 ring-blue-500 shadow-lg' : 'border-slate-200 hover:border-blue-300 shadow-sm'"
                             class="p-8 border-2 rounded-[1.5rem] cursor-pointer transition-all">
                            <h5 class="text-md font-black text-slate-800 dark:text-white mb-2">Permanent Link</h5>
                            <p class="text-[11px] font-bold text-slate-500 leading-relaxed">Can be used multiple times. Great for websites or sharing publicly.</p>
                        </div>
                    </div>



                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Expiry Date & Time (optional)</label>
                        <div class="relative">
                            <input type="datetime-local" x-model="linkConfig.expiry" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Fields -->
                <div x-show="linkStep === 2" x-transition class="space-y-8">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Form Customization</h4>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="linkConfig.mailNotification" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest">Receive mail after submission</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Form Title (Public)</label>
                                <input type="text" x-model="linkConfig.sectionHeader" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 transition-all" placeholder="e.g. Schedule a Consultation">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Explanation / Description</label>
                                <textarea x-model="linkConfig.explanation" rows="4" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 transition-all resize-none" placeholder="Provide context for the user..."></textarea>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Field Configuration</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="field in workflows.find(w => w.id == linkConfig.workflow_id)?.fields || []" :key="field.id">
                                    <div class="bg-white dark:bg-slate-900 rounded-[1.5rem] p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                                </div>
                                                <span class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight" x-text="field.label"></span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" :checked="linkConfig.fields[field.name || field.label]?.enabled" @change="if($event.target.checked) { linkConfig.fields[field.name || field.label] = { enabled: true, required: true, hidden: false, disabled: false, defaultValue: '' } } else { delete linkConfig.fields[field.name || field.label] }" class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>

                                        <template x-if="linkConfig.fields[field.name || field.label]?.enabled">
                                            <div x-transition class="space-y-4 pt-4 border-t border-slate-50 dark:border-slate-800/50">
                                                <div class="flex items-center gap-4">
                                                    <label class="flex items-center gap-2 cursor-pointer group">
                                                        <input type="checkbox" x-model="linkConfig.fields[field.name || field.label].required" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-0">
                                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Required</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer group">
                                                        <input type="checkbox" x-model="linkConfig.fields[field.name || field.label].hidden" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-0">
                                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Hidden</span>
                                                    </label>
                                                </div>
                                                <div class="space-y-1">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1">Default Value</span>
                                                    <input type="text" x-model="linkConfig.fields[field.name || field.label].defaultValue" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-[10px] font-bold text-slate-900 dark:text-white" :placeholder="'Set default ' + field.label">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Calendar -->
                <div x-show="linkStep === 3" x-transition class="flex flex-col items-center justify-center h-full py-12 text-center space-y-8">
                    <div class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                        <svg class="text-blue-600" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="max-w-md space-y-4">
                        <h4 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Enable Calendar Scheduling</h4>
                        <p class="text-sm font-bold text-slate-500 leading-relaxed uppercase tracking-tight">Allow users to book meetings directly through the form. Great for consultation requests and service bookings.</p>
                        
                        <div class="pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                <span class="ml-4 text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Disabled for this workflow</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Theme -->
                <div x-show="linkStep === 4" x-transition class="space-y-8">
                    <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Appearance Config</h4>
                    
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Form Name (Internal)</label>
                                <input type="text" x-model="linkConfig.theme.formName" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Submit Button Name</label>
                                <input type="text" x-model="linkConfig.theme.submitButtonName" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Primary Color</label>
                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800 p-3 rounded-2xl">
                                    <input type="color" x-model="linkConfig.theme.primaryColor" class="w-10 h-10 border-none bg-transparent cursor-pointer rounded-lg">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase" x-text="linkConfig.theme.primaryColor"></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Text Color</label>
                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800 p-3 rounded-2xl">
                                    <input type="color" x-model="linkConfig.theme.textColor" class="w-10 h-10 border-none bg-transparent cursor-pointer rounded-lg">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase" x-text="linkConfig.theme.textColor"></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Secondary Color</label>
                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800 p-3 rounded-2xl">
                                    <input type="color" x-model="linkConfig.theme.secondaryColor" class="w-10 h-10 border-none bg-transparent cursor-pointer rounded-lg">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase" x-text="linkConfig.theme.secondaryColor"></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Background Color</label>
                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800 p-3 rounded-2xl">
                                    <input type="color" x-model="linkConfig.theme.backgroundColor" class="w-10 h-10 border-none bg-transparent cursor-pointer rounded-lg">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase" x-text="linkConfig.theme.backgroundColor"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Share -->
                <div x-show="linkStep === 5" x-transition class="space-y-8 py-4">
                    <div class="flex flex-col items-center text-center space-y-3">
                        <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-full flex items-center justify-center">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Your Link is Ready!</h4>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Share this link or embed it on your website</p>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Direct Form Link</label>
                            <div class="flex gap-2">
                                <input type="text" readonly id="share-link-input" :value="publicUrl" class="flex-1 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-bold text-blue-600 outline-none">
                                <button @click="
                                    const el = document.getElementById('share-link-input');
                                    el.select();
                                    document.execCommand('copy');
                                    alert('Link copied!');
                                " class="px-6 bg-slate-800 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all">Copy</button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">iFrame Embed Code</label>
                            <div class="flex gap-2">
                                <textarea readonly id="share-embed-input" class="flex-1 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-[10px] font-bold text-slate-500 outline-none resize-none h-20"><iframe :src="publicUrl" width="100%" height="600px" frameborder="0"></iframe></textarea>
                                <button @click="
                                    const el = document.getElementById('share-embed-input');
                                    el.select();
                                    document.execCommand('copy');
                                    alert('Embed code copied!');
                                " class="px-6 bg-slate-800 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-4 pt-4">
                        <button @click="window.open(publicUrl, '_blank')" class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all flex items-center gap-2">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview Form
                        </button>
                        <button @click="linkStep = 1" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                            Create Another Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800/50">
                <button @click="if(linkStep > 1) linkStep--;" 
                        x-show="linkStep > 1 && linkStep < 5"
                        x-transition
                        class="px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Previous</button>
                
                <button @click="if(linkStep < 4) linkStep++; else if(linkStep === 4) generatePublicLink();" 
                        x-show="linkStep < 5"
                        :disabled="generating"
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 dark:shadow-none transition-all min-w-[160px]">
                    <span x-show="!generating" x-text="linkStep === 1 ? 'Next: Fields' : (linkStep === 2 ? 'Next: Calendar' : (linkStep === 3 ? 'Next: Theme' : 'Generate Link'))"></span>
                    <span x-show="generating">Generating...</span>
                </button>

                <button @click="
                            const el = document.createElement('textarea');
                            el.value = 'http://demo.localhost:8000/public/leads/config_abc123';
                            document.body.appendChild(el);
                            el.select();
                            document.execCommand('copy');
                            document.body.removeChild(el);
                            alert('Link copied to clipboard!');
                            showLinkModal = false;
                        " 
                        x-show="linkStep === 5"
                        class="px-8 py-3 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all min-w-[160px]">
                    Finish & Copy Link
                </button>
            </div>
        </div>
    </div>

    <!-- Manage Public Links Modal -->
    <div x-show="showManageLinksModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition.opacity
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showManageLinksModal = false">
            <div class="px-8 py-6 flex items-center justify-between border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Manage Public Links</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Active External Form Links</p>
                    </div>
                </div>
                <button @click="showManageLinksModal = false" class="p-2 hover:bg-white dark:hover:bg-slate-800 rounded-xl transition-colors text-slate-400">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div x-show="loadingLinks" class="py-12 text-center">
                    <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-4"></div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Loading Links...</p>
                </div>

                <div x-show="!loadingLinks && publicForms.length === 0" class="py-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                        <svg width="24" height="24" class="text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No active public links found</p>
                    <button @click="showManageLinksModal = false; showLinkModal = true; linkStep = 1" class="mt-4 text-blue-600 text-[10px] font-black uppercase hover:underline">Create your first link</button>
                </div>

                <div class="space-y-4" x-show="!loadingLinks && publicForms.length > 0">
                    <template x-for="form in publicForms" :key="form.id">
                        <div class="p-5 rounded-[2rem] bg-slate-50/50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between group hover:bg-white dark:hover:bg-slate-800 transition-all shadow-sm hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full" :class="form.type === 'permanent' ? 'bg-emerald-500' : 'bg-amber-500'"></div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800 dark:text-white leading-none" x-text="form.name"></h4>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/40 uppercase tracking-widest" x-text="form.workflow?.name || 'Global'"></span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest" x-text="new Date(form.created_at).toLocaleDateString()"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button @click="copyToClipboard(window.location.origin + '/public/leads/' + form.slug)" class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-all" title="Copy Link">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                                <button @click="window.open('/public/leads/' + form.slug, '_blank')" class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition-all" title="Preview">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <div class="h-6 w-px bg-slate-100 dark:bg-slate-700 mx-1"></div>
                                <button @click="deletePublicForm(form.id)" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-all" title="Delete Link">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-8 py-6 border-t border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-end">
                <button @click="showManageLinksModal = false" class="px-8 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Close</button>
            </div>
        </div>
    </div>
    <div x-show="showDetailsModal" 
         class="fixed inset-0 z-[100] flex justify-end bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 shadow-2xl w-full max-w-md h-full flex flex-col border-l border-slate-100 dark:border-slate-800"
             @click.away="showDetailsModal = false"
             x-show="showDetailsModal"
             x-transition:enter="transition transform ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition transform ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            
            <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-full flex items-center justify-center font-black text-lg shadow-inner">
                        <span x-text="activeLead?.assignee?.name ? activeLead.assignee.name.substring(0,1) : '?'"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight" x-text="activeLead?.assignee?.name || 'Unassigned'"></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Lead Details</p>
                    </div>
                </div>
                <button @click="showDetailsModal = false" class="text-slate-400 hover:text-rose-500 transition-colors bg-white dark:bg-slate-800 p-2 rounded-full shadow-sm">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8 space-y-8 custom-scrollbar">
                <!-- Status & Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Status</p>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                              :class="activeLead?.status === 'new' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20'"
                              x-text="activeLead?.status || 'Unknown'"></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Created On</p>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="activeLead ? new Date(activeLead.created_at).toLocaleDateString() : ''"></p>
                    </div>
                </div>

                <!-- Custom Fields -->
                <div>
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                        <svg class="text-blue-500" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        Collected Data
                    </h4>
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl divide-y divide-slate-50 dark:divide-slate-800/50 shadow-sm">
                        <template x-for="data in activeLead?.data || []" :key="data.id">
                            <div class="p-4 flex items-start justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/3" x-text="data.field?.label || 'Unknown'"></span>
                                <span class="text-sm font-medium text-slate-800 dark:text-slate-200 text-right flex-1 break-words" x-text="data.value || '-'"></span>
                            </div>
                        </template>
                        <div x-show="!activeLead?.data?.length" class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                            No data collected yet
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-t border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex gap-3">
                <button @click="showDetailsModal = false" class="flex-1 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Close</button>
            </div>
        </div>
    </div>

    <!-- Lead History Modal -->
    <div x-show="showHistoryModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition.opacity
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showHistoryModal = false"
             x-show="showHistoryModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="px-8 py-6 flex items-center justify-between border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white tracking-tight">Activity Timeline</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Lead History</p>
                    </div>
                </div>
                <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div class="relative border-l-2 border-blue-100 dark:border-blue-900/50 ml-3 space-y-8">
                    <!-- Timeline Item 1 -->
                    <div class="relative pl-8">
                        <div class="absolute -left-[11px] top-1 w-5 h-5 rounded-full bg-blue-600 border-4 border-white dark:border-slate-900 shadow-sm"></div>
                        <p class="text-xs font-black text-slate-800 dark:text-white">Lead Created</p>
                        <p class="text-[10px] font-bold text-slate-500 mt-1" x-text="activeLead ? new Date(activeLead.created_at).toLocaleString() : ''"></p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">Lead entered the workflow automatically via web form.</p>
                    </div>
                    <!-- Timeline Item 2 -->
                    <div class="relative pl-8">
                        <div class="absolute -left-[11px] top-1 w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 shadow-sm"></div>
                        <p class="text-xs font-black text-slate-800 dark:text-white">Last Updated</p>
                        <p class="text-[10px] font-bold text-slate-500 mt-1" x-text="activeLead ? new Date(activeLead.updated_at).toLocaleString() : ''"></p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2" x-text="'Assigned to ' + (activeLead?.assignee?.name || 'Unknown')"></p>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-5 border-t border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-center">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">End of History</p>
            </div>
        </div>
    </div>
</div>
@endsection

