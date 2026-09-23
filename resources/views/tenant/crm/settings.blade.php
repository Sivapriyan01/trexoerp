@extends('layouts.tenant')
@section('title', 'Workflow Architect')

@section('content')
<script>
function workflowArchitect() {
    return {
        workspaces: JSON.parse(atob('{{ base64_encode(json_encode($workspaces)) }}')),
        activeWorkspaceIndex: 0,
        activeWorkflowIndex: 0,
        activeTab: (new URLSearchParams(window.location.search)).get('tab') || 'architect',
        saving: false,
        publicForms: [],
        loadingLinks: false,

        init() {
            if (this.activeTab === 'public_forms') {
                this.loadPublicForms();
            }
        },

        async loadPublicForms() {
            this.loadingLinks = true;
            try {
                const response = await fetch('{{ route('tenant.crm.settings.public-forms.index') }}');
                if(!response.ok) throw new Error('Failed to fetch');
                this.publicForms = await response.json();
            } catch (e) {
                console.error(e);
                alert('Error loading public forms');
            } finally {
                this.loadingLinks = false;
            }
        },

        async deletePublicForm(id) {
            if(!confirm('Permanently delete this public link?')) return;
            try {
                const res = await fetch(`/crm/settings/public-forms/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if(!res.ok) throw new Error('Delete failed');
                this.publicForms = this.publicForms.filter(f => f.id !== id);
                alert('Public link removed');
            } catch (e) {
                alert('Error: ' + e.message);
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
        
        get totalWorkflows() {
            return this.workspaces.reduce((acc, ws) => acc + (ws.workflows?.length || 0), 0);
        },

        get totalFields() {
            return this.workspaces.reduce((acc, ws) => {
                return acc + (ws.workflows?.reduce((wfAcc, wf) => wfAcc + (wf.fields?.length || 0), 0) || 0);
            }, 0);
        },

        toggleMatrixTransition(wfFrom, toId) {
            if(!wfFrom.allowed_transitions) wfFrom.allowed_transitions = [];
            const index = wfFrom.allowed_transitions.indexOf(toId);
            if(index > -1) {
                wfFrom.allowed_transitions.splice(index, 1);
            } else {
                wfFrom.allowed_transitions.push(toId);
            }
        },

        toggleFieldVisibility(field, targetWf) {
            if(!field.visibility_rules) field.visibility_rules = {};
            const wfKey = targetWf.id || targetWf.name;
            if(field.visibility_rules[wfKey]) {
                delete field.visibility_rules[wfKey];
            } else {
                field.visibility_rules[wfKey] = { visible: true, mandatory: false };
            }
        },

        isFieldVisible(field, targetWf) {
            // Default: visible in its own stage
            const ownWf = this.workspaces[this.activeWorkspaceIndex]?.workflows[this.activeWorkflowIndex];
            if(targetWf === ownWf) return true;
            
            const wfKey = targetWf.id || targetWf.name;
            return field.visibility_rules?.[wfKey]?.visible || false;
        },

        toggleFieldMandatory(field, targetWf) {
            const wfKey = targetWf.id || targetWf.name;
            if(field.visibility_rules?.[wfKey]) {
                field.visibility_rules[wfKey].mandatory = !field.visibility_rules[wfKey].mandatory;
            }
        },

        isFieldMandatory(field, targetWf) {
            const wfKey = targetWf.id || targetWf.name;
            return field.visibility_rules?.[wfKey]?.mandatory || false;
        },

        get activeWf() {
            const ws = this.workspaces[this.activeWorkspaceIndex];
            return ws ? ws.workflows[this.activeWorkflowIndex] : null;
        },

        get allWorkflows() {
            let list = [];
            this.workspaces.forEach(ws => {
                ws.workflows?.forEach(wf => {
                    list.push({ ...wf, workspaceName: ws.name });
                });
            });
            return list;
        },

        addWorkspace() {
            this.workspaces.push({
                name: 'New Workspace',
                workflows: []
            });
            this.activeWorkspaceIndex = this.workspaces.length - 1;
            this.activeWorkflowIndex = 0;
        },

        addWorkflow(wsIndex) {
            this.workspaces[wsIndex].workflows.push({
                name: 'New Stage',
                icon: 'lightning-bolt',
                color: 'blue',
                fields: []
            });
            this.activeWorkspaceIndex = wsIndex;
            this.activeWorkflowIndex = this.workspaces[wsIndex].workflows.length - 1;
        },

        deleteWorkflow(wsIndex, wfIndex) {
            if(!confirm('Delete this stage? All fields and lead history will be affected.')) return;
            this.workspaces[wsIndex].workflows.splice(wfIndex, 1);
            if(this.activeWorkflowIndex >= this.workspaces[wsIndex].workflows.length) {
                this.activeWorkflowIndex = Math.max(0, this.workspaces[wsIndex].workflows.length - 1);
            }
        },

        addField() {
            if(!this.activeWf) return;
            if(!this.activeWf.fields) this.activeWf.fields = [];
            this.activeWf.fields.push({
                label: '',
                type: 'text',
                is_required: false
            });
        },

        removeField(index) {
            this.activeWf.fields.splice(index, 1);
        },

        async saveConfiguration() {
            this.saving = true;
            try {
                const response = await fetch('{{ route("tenant.crm.settings.sync") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        workspaces: this.workspaces
                    })
                });
                
                const result = await response.json();
                if(result.success) {
                    alert(result.message || 'CRM Architecture Deployed Successfully!');
                    // Optionally reload to get real IDs for new items
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Sync failed');
                }
            } catch (error) {
                console.error(error);
                alert('Sync failed: ' + error.message);
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>

<div class="h-full flex flex-col space-y-6" x-data="workflowArchitect()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.settings') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">CRM Settings</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Design Workspaces, Stages and Field Architecture</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div x-show="saving" x-transition class="flex items-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Syncing Changes...</span>
            </div>
            <button @click="saveConfiguration()" 
                    :disabled="saving"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none flex items-center gap-2">
                <svg x-show="!saving" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <svg x-show="saving" class="animate-spin" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Deploy Changes
            </button>
        </div>
    </div>

    <!-- Tabs Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 bg-slate-100/50 dark:bg-slate-800/50 p-1.5 rounded-2xl w-fit border border-slate-100 dark:border-slate-800">
            <button @click="activeTab = 'architect'" 
                    :class="activeTab === 'architect' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Architect View
            </button>
            <button @click="activeTab = 'list'" 
                    :class="activeTab === 'list' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                List View
            </button>
            <button @click="activeTab = 'transitions'" 
                    :class="activeTab === 'transitions' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Transition Rules
            </button>
            <button @click="activeTab = 'visibility'" 
                    :class="activeTab === 'visibility' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Field Visibility
            </button>
            <button @click="activeTab = 'public_forms'; loadPublicForms()" 
                    :class="activeTab === 'public_forms' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Public Forms
            </button>
        </div>

        <div class="flex items-center gap-8 pr-4">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Workflows</p>
                        <p class="text-lg font-black text-slate-900 dark:text-white leading-none mt-1" x-text="totalWorkflows"></p>
                    </div>
                </div>
                
                <div class="h-8 w-px bg-slate-100 dark:bg-slate-800"></div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Columns</p>
                        <p class="text-lg font-black text-slate-900 dark:text-white leading-none mt-1" x-text="totalFields"></p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Main Grid -->
    <div x-show="activeTab === 'architect'" x-transition class="flex-1 grid grid-cols-12 gap-6 overflow-hidden min-h-0">
        <!-- Sidebar: Workspaces & Workflows -->
        <div class="col-span-3 flex flex-col gap-6 overflow-hidden">
            <div class="glass-card p-6 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex-1 flex flex-col overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Workspace Hierarchy</h3>
                    <button @click="addWorkspace()" class="p-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 custom-scrollbar pr-2">
                    <template x-for="(ws, wsIndex) in workspaces" :key="ws.id || 'new-ws-' + wsIndex">
                        <div class="space-y-1">
                            <!-- Workspace Item -->
                            <div @click="activeWorkspaceIndex = wsIndex; activeWorkflowIndex = 0"
                                 :class="activeWorkspaceIndex === wsIndex ? 'bg-slate-50 dark:bg-slate-800' : ''"
                                 class="p-3 rounded-xl cursor-pointer group flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center text-blue-600">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <input type="text" x-model="ws.name" class="bg-transparent border-none p-0 text-[11px] font-black text-slate-800 dark:text-slate-200 focus:ring-0 uppercase tracking-tight">
                                </div>
                                <button @click.stop="addWorkflow(wsIndex)" class="opacity-0 group-hover:opacity-100 p-1.5 text-blue-400 hover:text-blue-600 transition-all">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            <!-- Workflow Sub-items -->
                            <div class="ml-11 space-y-1 border-l-2 border-slate-50 dark:border-slate-800/50 pl-2">
                                <template x-for="(wf, wfIndex) in ws.workflows" :key="wf.id || 'new-wf-' + wfIndex">
                                    <div @click="activeWorkspaceIndex = wsIndex; activeWorkflowIndex = wfIndex"
                                         :class="(activeWorkspaceIndex === wsIndex && activeWorkflowIndex === wfIndex) ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'"
                                         class="p-2.5 rounded-lg cursor-pointer transition-all flex items-center justify-between group">
                                        <div class="flex items-center gap-2">
                                            <div :class="(activeWorkspaceIndex === wsIndex && activeWorkflowIndex === wfIndex) ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800'" class="w-2 h-2 rounded-full"></div>
                                            <span x-text="wf.name" class="text-[10px] font-bold uppercase tracking-widest"></span>
                                        </div>
                                        <button @click.stop="deleteWorkflow(wsIndex, wfIndex)" class="opacity-0 group-hover:opacity-100 p-1 text-rose-300 hover:text-rose-500 transition-all">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Stage Editor -->
        <div class="col-span-9 flex flex-col gap-6 overflow-hidden">
            <template x-if="activeWf">
                <div class="glass-card flex-1 flex flex-col p-8 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden animate-in slide-in-from-right duration-500">
                    <!-- Stage Header -->
                    <div class="flex items-center justify-between mb-8 border-b border-slate-50 dark:border-slate-800 pb-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-[2rem] flex items-center justify-center shadow-inner">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <input type="text" x-model="activeWf.name" class="bg-transparent border-none p-0 text-2xl font-black text-slate-900 dark:text-white focus:ring-0 tracking-tight">
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Workflow Stage Identity</p>
                                    <div class="h-3 w-px bg-slate-100 dark:bg-slate-800"></div>
                                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest" x-text="(activeWf.fields?.length || 0) + ' Data Points'"></span>
                                </div>
                            </div>
                        </div>
                        <button @click="addField()" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-lg shadow-emerald-100 dark:shadow-none">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            New Field
                        </button>
                    </div>

                    <!-- Fields Editor -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar pr-4 pb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(field, fIndex) in activeWf.fields" :key="fIndex">
                                <div class="p-5 bg-slate-50/50 dark:bg-slate-800/30 rounded-[2.5rem] border border-slate-100 dark:border-slate-800/50 group hover:border-blue-500/20 transition-all flex flex-col gap-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center text-[10px] font-black text-slate-400" x-text="fIndex + 1"></div>
                                            <input type="text" x-model="field.label" placeholder="Field Label" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 dark:text-slate-200 focus:ring-0">
                                        </div>
                                        <button @click="removeField(fIndex)" class="p-2 text-rose-400 hover:bg-rose-50 rounded-xl transition-colors opacity-0 group-hover:opacity-100">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Type</label>
                                            <select x-model="field.type" class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-4 py-2.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 outline-none focus:ring-2 focus:ring-blue-500/10">
                                                <option value="text">Short Text</option>
                                                <option value="textarea">Paragraph</option>
                                                <option value="email">Email Addr</option>
                                                <option value="number">Numeric Val</option>
                                                <option value="date">Date Picker</option>
                                                <option value="select">Dropdown Menu</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end gap-3 pb-2.5">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" x-model="field.is_required" class="w-4 h-4 rounded border-slate-200 text-blue-600 focus:ring-blue-500/20">
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Required</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <template x-if="!activeWf.fields?.length">
                            <div class="h-64 flex flex-col items-center justify-center text-center space-y-4">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No data points defined for this stage</p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            
            <template x-if="!activeWf">
                <div class="glass-card flex-1 flex flex-col items-center justify-center text-center p-12 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xl">
                    <div class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-[2.5rem] flex items-center justify-center mb-6">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight uppercase">Select a Stage</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2 max-w-sm">Choose a workflow stage from the sidebar to configure its properties and custom fields.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Process Mapping Content (Matrix View) -->
    <div x-show="['transitions', 'visibility'].includes(activeTab)" x-transition class="flex-1 flex flex-col gap-6 overflow-hidden min-h-0">
        <div class="flex-1 flex flex-col gap-6 overflow-hidden">
            <!-- Transition Matrix Table -->
            <div x-show="activeTab === 'transitions'" class="glass-card flex-1 p-8 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden min-h-[400px]">
                <div class="mb-6">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Stage Transition Matrix</h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Rows: Current Stage | Columns: Target Stage</p>
                </div>
                <div class="flex-1 overflow-auto custom-scrollbar max-h-[60vh]">
                    <table class="w-full border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="sticky left-0 top-0 bg-white dark:bg-slate-900 z-30 p-4 border-b border-r border-slate-100 dark:border-slate-800 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">From \ To</th>
                                <template x-for="wf in allWorkflows" :key="'col-' + wf.id">
                                    <th class="sticky top-0 bg-white dark:bg-slate-900 z-20 p-4 border-b border-slate-100 dark:border-slate-800 text-center min-w-[120px]">
                                        <span class="text-[9px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight" x-text="wf.name"></span>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="wfFrom in allWorkflows" :key="'row-' + wfFrom.id">
                                <tr>
                                    <td class="sticky left-0 bg-white dark:bg-slate-900 z-20 p-4 border-r border-b border-slate-100 dark:border-slate-800 group hover:bg-slate-50 transition-colors whitespace-nowrap">
                                        <span class="text-[10px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight" x-text="wfFrom.name"></span>
                                    </td>
                                    <template x-for="wfTo in allWorkflows" :key="'cell-' + wfTo.id">
                                        <td class="p-4 border-b border-slate-50 dark:border-slate-800/50 text-center hover:bg-slate-50/50 transition-colors">
                                            <template x-if="wfFrom.id !== wfTo.id">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" 
                                                           :checked="wfFrom.allowed_transitions?.includes(wfTo.id || wfTo.name)"
                                                           @change="toggleMatrixTransition(wfFrom, wfTo.id || wfTo.name)"
                                                           class="sr-only peer">
                                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                                </label>
                                            </template>
                                            <template x-if="wfFrom.id === wfTo.id">
                                                <span class="w-2 h-2 rounded-full bg-slate-100 dark:bg-slate-800 inline-block"></span>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Visibility Matrix Table -->
            <div x-show="activeTab === 'visibility'" class="glass-card flex-1 p-8 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden min-h-[400px]">
                <div class="mb-6">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Column Visibility Matrix</h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Rows: Defined Columns | Columns: Stages where shown</p>
                </div>
                <div class="flex-1 overflow-auto custom-scrollbar max-h-[60vh]">
                    <table class="w-full border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="sticky left-0 top-0 bg-white dark:bg-slate-900 z-30 p-4 border-b border-r border-slate-100 dark:border-slate-800 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Field Name</th>
                                <template x-for="wf in allWorkflows" :key="'vis-col-' + wf.id">
                                    <th class="sticky top-0 bg-white dark:bg-slate-900 z-20 p-4 border-b border-slate-100 dark:border-slate-800 text-center min-w-[120px]">
                                        <span class="text-[9px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight" x-text="wf.name"></span>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="wfOrig in allWorkflows">
                                <template x-for="field in wfOrig.fields" :key="'vis-row-' + (field.id || field.label)">
                                    <tr>
                                        <td class="sticky left-0 bg-white dark:bg-slate-900 z-20 p-4 border-r border-b border-slate-100 dark:border-slate-800 group hover:bg-slate-50 transition-colors whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight" x-text="field.label"></span>
                                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="'From: ' + wfOrig.name"></span>
                                            </div>
                                        </td>
                                        <template x-for="wfShow in allWorkflows" :key="'vis-cell-' + wfShow.id">
                                            <td class="p-4 border-b border-slate-50 dark:border-slate-800/50 text-center hover:bg-slate-50/50 transition-colors">
                                                <div class="flex items-center justify-center gap-3">
                                                    <!-- Visibility Toggle -->
                                                    <button @click="toggleFieldVisibility(field, wfShow)" 
                                                            :class="isFieldVisible(field, wfShow) ? 'text-blue-600' : 'text-slate-300'"
                                                            class="p-1 hover:scale-110 transition-transform">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </button>
                                                    <!-- Mandatory Toggle -->
                                                    <button x-show="isFieldVisible(field, wfShow)"
                                                            @click="toggleFieldMandatory(field, wfShow)"
                                                            :class="isFieldMandatory(field, wfShow) ? 'text-rose-500' : 'text-slate-300'"
                                                            class="p-1 hover:scale-110 transition-transform">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Master List Mode -->
    <div x-show="activeTab === 'list'" x-transition class="flex-1 flex flex-col gap-6 overflow-hidden min-h-0">
        <div class="grid grid-cols-12 gap-6 flex-1 overflow-hidden">
            <!-- Workflow Groups Table -->
            <div class="col-span-4 glass-card p-8 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Workflow Groups</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Categorize your stages</p>
                    </div>
                    <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 transition-all flex items-center gap-2">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        New Group
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-white dark:bg-slate-900 z-10">
                            <tr class="border-b border-slate-50 dark:border-slate-800">
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Group Name</th>
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Workflows</th>
                                <th class="text-right py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="ws in workspaces" :key="ws.id">
                                <tr class="border-b border-slate-50/50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-4 text-[10px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight" x-text="ws.name"></td>
                                    <td class="py-4">
                                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg" x-text="ws.workflows?.length || 0"></span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!workspaces.length">
                                <tr>
                                    <td colspan="3" class="py-12 text-center">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No groups created yet</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- All Workflows Table -->
            <div class="col-span-8 glass-card p-8 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">All Workflows</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Complete list of all process stages</p>
                    </div>
                    <button @click="addWorkflow(activeWorkspaceIndex)" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 dark:shadow-none flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        Add Workflow
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-white dark:bg-slate-900 z-10">
                            <tr class="border-b border-slate-50 dark:border-slate-800">
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-16">Order</th>
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Workflow</th>
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                                <th class="text-left py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Group</th>
                                <th class="text-right py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(ws, wsIdx) in workspaces">
                                <template x-for="(wf, wfIdx) in ws.workflows">
                                    <tr class="border-b border-slate-50/50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                        <td class="py-4 text-[10px] font-black text-slate-400" x-text="wfIdx + 1"></td>
                                        <td class="py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-blue-600">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                </div>
                                                <span class="text-[11px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight" x-text="wf.name"></span>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Process Stage</span>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight" x-text="ws.name"></span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                                <button @click="activeWorkspaceIndex = wsIdx; activeWorkflowIndex = wfIdx; activeTab = 'architect'" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button @click="deleteWorkflow(wsIdx, wfIdx)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- Public Forms Management -->
    <div x-show="activeTab === 'public_forms'" x-transition class="flex-1 flex flex-col gap-6 overflow-hidden min-h-0">
        <div class="glass-card flex-1 p-12 rounded-[3.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Active Public Form Links</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Manage external lead intake channels</p>
                </div>
                <button @click="window.location.href = '{{ route('tenant.crm.index') }}?create_link=true'" class="px-6 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    Create New Link
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div x-show="loadingLinks" class="py-24 text-center">
                    <div class="animate-spin w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-4"></div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Fetching Link Data...</p>
                </div>

                <div x-show="!loadingLinks && publicForms.length === 0" class="py-24 text-center">
                    <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-[2rem] flex items-center justify-center mx-auto mb-6 opacity-50 shadow-inner">
                        <svg width="32" height="32" class="text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No public links have been generated yet</p>
                </div>

                <div class="grid grid-cols-1 gap-4" x-show="!loadingLinks && publicForms.length > 0">
                    <template x-for="form in publicForms" :key="form.id">
                        <div class="p-6 rounded-[2.5rem] bg-slate-50/50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/50 flex items-center justify-between group hover:bg-white dark:hover:bg-slate-800 transition-all shadow-sm hover:shadow-lg">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center">
                                    <div class="w-3 h-3 rounded-full" :class="form.type === 'permanent' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.4)]' : 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.4)]'"></div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-slate-800 dark:text-white leading-none" x-text="form.name"></h4>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-[10px] font-black px-2 py-1 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/40 uppercase tracking-widest" x-text="form.workflow?.name || 'Global'"></span>
                                        <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="'Created: ' + new Date(form.created_at).toLocaleDateString()"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <button @click="copyToClipboard(window.location.origin + '/public/leads/' + form.slug)" class="px-5 py-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    Copy Link
                                </button>
                                <button @click="window.open('/public/leads/' + form.slug, '_blank')" class="p-3 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition-all" title="Preview">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <div class="h-8 w-px bg-slate-100 dark:bg-slate-800 mx-2"></div>
                                <button @click="deletePublicForm(form.id)" class="p-3 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-all" title="Delete Link">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@endsection


