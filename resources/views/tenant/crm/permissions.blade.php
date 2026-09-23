@extends('layouts.tenant')
@section('title', 'User Permissions - CRM')

@section('content')
<div class="h-full flex flex-col space-y-8 animate-in fade-in duration-700" 
     x-data="{
        users: {{ json_encode($users) }},
        workspaces: {{ json_encode($workspaces) }},
        allWorkflows: {{ json_encode($workflows) }},
        selectedUserId: null,
        selectedUser: null,
        searchQuery: '',
        dropdownOpen: false,
        loading: false,
        message: '',
        
        // Form Data
        selectedWorkflows: [],
        selectedDashboards: [],
        settingsAccess: false,
        detailsOpen: true,
        workflowActions: {}, // {wf_id: {edit: true, create: false, ...}}
        
        availableActions: [
            { id: 'edit', label: 'Allow Edit' },
            { id: 'create', label: 'Lead Create' },
            { id: 'calendar', label: 'Calendar' },
            { id: 'read_unread', label: 'Read & UnRead' },
            { id: 'bulk_edit', label: 'Bulk Edit' },
            { id: 'history', label: 'Show History' },
            { id: 'links', label: 'External Links' },
            { id: 'whatsapp', label: 'Whatsapp' },
            { id: 'mail', label: 'Mail' },
            { id: 'upload', label: 'Upload' },
            { id: 'export', label: 'Export' }
        ],

        get filteredUsers() {
            if (!this.searchQuery || this.selectedUser?.name === this.searchQuery) return this.users;
            return this.users.filter(u => u.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
        },
        
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },

        selectUser(user) {
            this.selectedUserId = user.id;
            this.selectedUser = user;
            this.searchQuery = user.name;
            this.dropdownOpen = false;
            this.loadUserPermissions();
        },
        
        loadUserPermissions() {
            if (!this.selectedUserId) return;
            this.loading = true;
            fetch(`/crm/settings/permissions/${this.selectedUserId}/get`)
                .then(r => r.json())
                .then(data => {
                    const perms = data.permissions || [];
                    
                    // Reset
                    this.selectedWorkflows = [];
                    this.selectedDashboards = [];
                    this.settingsAccess = perms.includes('CRM_Settings');
                    this.workflowActions = {};

                    // Load Workspaces (we call them workflows in the UI as per screenshot)
                    this.allWorkflows.forEach(wf => {
                        if (perms.some(p => p === 'CRM_WF:' + wf.id + ':edit' || p.startsWith('CRM_WF:' + wf.id + ':'))) {
                            this.selectedWorkflows.push(wf.id);
                        }
                        
                        // Load granular actions
                        this.workflowActions[wf.id] = {};
                        this.availableActions.forEach(action => {
                            this.workflowActions[wf.id][action.id] = perms.includes(`CRM_WF:${wf.id}:${action.id}`);
                        });
                    });

                    // Dashboards
                    this.selectedDashboards = perms.filter(p => p.startsWith('CRM_DB:')).map(p => p.replace('CRM_DB:', ''));
                })
                .finally(() => this.loading = false);
        },
        
        savePermissions() {
            this.loading = true;
            
            // Format workflow actions for backend
            const formattedActions = {};
            const workspaceIds = new Set();
            
            this.selectedWorkflows.forEach(wfId => {
                const workflow = this.allWorkflows.find(w => w.id == wfId);
                if (workflow && workflow.workspace_id) {
                    workspaceIds.add(workflow.workspace_id);
                }
                
                formattedActions[wfId] = Object.keys(this.workflowActions[wfId] || {})
                    .filter(action => this.workflowActions[wfId][action]);
            });

            fetch(`/crm/settings/permissions/${this.selectedUserId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    workspaces: Array.from(workspaceIds),
                    dashboards: this.selectedDashboards,
                    settings_access: this.settingsAccess,
                    workflow_actions: formattedActions
                })
            })
            .then(r => r.json())
            .then(data => {
                this.message = data.message;
                setTimeout(() => this.message = '', 3000);
            })
            .finally(() => this.loading = false);
        }
     }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.settings') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400 shadow-sm">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">User Permissions</h1>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Workspace Access Control</p>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="flex flex-col gap-8 flex-1">
        
        <!-- User Selection Section -->
        <div class="glass-card bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight uppercase">User Access Management</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">workspace permissions</p>
                </div>
            </div>

            <div class="max-w-xl relative">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block px-1">Select User to Manage Permissions</label>
                <div class="relative cursor-pointer" @click="toggleDropdown()">
                    <input type="text" 
                           x-model="searchQuery"
                           @click.stop="dropdownOpen = true"
                           @input="dropdownOpen = true"
                           placeholder="Search and select user..." 
                           class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-2xl px-6 py-4 text-xs font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                    <div class="absolute right-5 top-4 text-slate-400 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute z-[100] top-full mt-2 w-full bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 max-h-64 overflow-y-auto custom-scrollbar">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <button @click="selectUser(user)" class="w-full text-left px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors flex items-center justify-between group">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase" x-text="user.name"></span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest" x-text="user.role + ' • ' + (user.branch?.name || 'N/A')"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <template x-if="selectedUser">
            <div class="space-y-8 animate-in slide-in-from-bottom-8 duration-700">
                
                <!-- User Detail Info -->
                <div class="grid grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-md">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Name</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedUser.name"></p>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-md">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Role</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white uppercase" x-text="selectedUser.role"></p>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-md">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Branch</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedUser.branch?.name || 'N/A'"></p>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-md">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <p class="text-sm font-black text-emerald-600 uppercase">Active</p>
                        </div>
                    </div>
                </div>

                <!-- Dashboard & Details Access -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden">
                    <button @click="detailsOpen = !detailsOpen" class="w-full p-8 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Details</h3>
                        </div>
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="detailsOpen ? '' : '-rotate-180'" class="transition-transform text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <div x-show="detailsOpen" x-transition>
                        <div class="p-8 pt-0 space-y-3">
                            <!-- Daily Expense -->
                            <div @click="selectedDashboards.includes('Daily Expense') ? selectedDashboards = selectedDashboards.filter(d => d !== 'Daily Expense') : selectedDashboards.push('Daily Expense')"
                                 class="p-5 rounded-3xl border transition-all cursor-pointer flex items-center gap-6 group"
                                 :class="selectedDashboards.includes('Daily Expense') ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-900/20' : 'border-slate-50 dark:border-slate-800 bg-slate-50/50 hover:bg-slate-100/50 dark:bg-slate-800/30'">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                     :class="selectedDashboards.includes('Daily Expense') ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm'">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Daily Expense</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Manage and track daily expenses</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                     :class="selectedDashboards.includes('Daily Expense') ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700'">
                                    <svg x-show="selectedDashboards.includes('Daily Expense')" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                            <!-- Employee Attendance -->
                            <div @click="selectedDashboards.includes('Employee Attendance') ? selectedDashboards = selectedDashboards.filter(d => d !== 'Employee Attendance') : selectedDashboards.push('Employee Attendance')"
                                 class="p-5 rounded-3xl border transition-all cursor-pointer flex items-center gap-6 group"
                                 :class="selectedDashboards.includes('Employee Attendance') ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-900/20' : 'border-slate-50 dark:border-slate-800 bg-slate-50/50 hover:bg-slate-100/50 dark:bg-slate-800/30'">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                     :class="selectedDashboards.includes('Employee Attendance') ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm'">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Employee Attendance</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Keeps track of your employees</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                     :class="selectedDashboards.includes('Employee Attendance') ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700'">
                                    <svg x-show="selectedDashboards.includes('Employee Attendance')" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                            <!-- Employee List -->
                            <div @click="selectedDashboards.includes('Employee List') ? selectedDashboards = selectedDashboards.filter(d => d !== 'Employee List') : selectedDashboards.push('Employee List')"
                                 class="p-5 rounded-3xl border transition-all cursor-pointer flex items-center gap-6 group"
                                 :class="selectedDashboards.includes('Employee List') ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-900/20' : 'border-slate-50 dark:border-slate-800 bg-slate-50/50 hover:bg-slate-100/50 dark:bg-slate-800/30'">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                     :class="selectedDashboards.includes('Employee List') ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm'">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Employee List</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">View and manage employee records</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                     :class="selectedDashboards.includes('Employee List') ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700'">
                                    <svg x-show="selectedDashboards.includes('Employee List')" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                            <!-- Purchase Settlement -->
                            <div @click="selectedDashboards.includes('Purchase Settlement') ? selectedDashboards = selectedDashboards.filter(d => d !== 'Purchase Settlement') : selectedDashboards.push('Purchase Settlement')"
                                 class="p-5 rounded-3xl border transition-all cursor-pointer flex items-center gap-6 group"
                                 :class="selectedDashboards.includes('Purchase Settlement') ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-900/20' : 'border-slate-50 dark:border-slate-800 bg-slate-50/50 hover:bg-slate-100/50 dark:bg-slate-800/30'">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                     :class="selectedDashboards.includes('Purchase Settlement') ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm'">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Purchase Settlement</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Settle purchases and manage vendor payments</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                     :class="selectedDashboards.includes('Purchase Settlement') ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700'">
                                    <svg x-show="selectedDashboards.includes('Purchase Settlement')" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div @click="selectedDashboards.includes('Summary') ? selectedDashboards = selectedDashboards.filter(d => d !== 'Summary') : selectedDashboards.push('Summary')"
                                 class="p-5 rounded-3xl border transition-all cursor-pointer flex items-center gap-6 group"
                                 :class="selectedDashboards.includes('Summary') ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-900/20' : 'border-slate-50 dark:border-slate-800 bg-slate-50/50 hover:bg-slate-100/50 dark:bg-slate-800/30'">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                     :class="selectedDashboards.includes('Summary') ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm'">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Summary</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">View todays sales, expenses, and vendor settlements</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                     :class="selectedDashboards.includes('Summary') ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700'">
                                    <svg x-show="selectedDashboards.includes('Summary')" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Access -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-800/30">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">CRM Workflow Access</h3>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Select workflows and configure permissions</p>
                            </div>
                        </div>
                        <button class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline">Hide Options</button>
                    </div>
                    <div class="p-8">
                        <div class="flex flex-wrap gap-4">
                            <template x-for="wf in allWorkflows" :key="wf.id">
                                <label class="relative flex items-center gap-3 p-4 rounded-2xl border transition-all cursor-pointer group"
                                       :class="selectedWorkflows.includes(wf.id) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-200'">
                                    <input type="checkbox" :value="wf.id" x-model="selectedWorkflows" class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight" x-text="wf.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Settings Access Control -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Configuration Settings</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Allow user to manage CRM workflows and global fields</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settingsAccess" class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-orange-600 shadow-inner"></div>
                        <span class="ml-4 text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest" x-text="settingsAccess ? 'Enabled' : 'Disabled'"></span>
                    </label>
                </div>

                <!-- Workflow Details Permissions -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Workflow Permissions</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Configure detailed access controls for selected workflows</p>
                        </div>
                        <div class="px-4 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-[10px] font-black uppercase" x-text="selectedWorkflows.length + ' workflows'"></div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="wfId in selectedWorkflows" :key="wfId">
                            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-lg overflow-hidden group">
                                <div class="p-6 border-b border-slate-50 dark:border-slate-800 bg-slate-50/30 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-blue-600 bg-blue-50">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </div>
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight" x-text="allWorkflows.find(w => w.id == wfId)?.name"></h4>
                                    </div>
                                    <button @click="selectedWorkflows = selectedWorkflows.filter(id => id != wfId)" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="p-8 grid grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-12">
                                    <template x-for="action in availableActions" :key="action.id">
                                        <label class="flex items-center gap-3 cursor-pointer group/label">
                                            <div class="relative">
                                                <input type="checkbox" x-model="workflowActions[wfId][action.id]" class="w-5 h-5 rounded-md border-slate-200 text-blue-600 focus:ring-blue-500/20 transition-all">
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight group-hover/label:text-slate-900 transition-colors" x-text="action.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-12 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between mt-8">
                    <a href="{{ route('tenant.crm.settings') }}" 
                       class="px-10 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all shadow-inner">
                        Close
                    </a>
                    <div class="flex items-center gap-3">
                        <button @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="p-4 bg-slate-50 dark:bg-slate-800 text-slate-400 rounded-2xl hover:bg-slate-100 transition-all shadow-sm">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button @click="savePermissions()" :disabled="loading" 
                                class="px-12 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-blue-700 shadow-2xl shadow-blue-200 transition-all flex items-center gap-3 min-w-[200px] justify-center">
                            <template x-if="!loading"><span>Deploy Changes</span></template>
                            <template x-if="loading"><span>Processing...</span></template>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="!selectedUser">
            <div class="h-[60vh] flex flex-col items-center justify-center text-center space-y-6">
                <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center text-slate-200">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-300 uppercase tracking-tight">No User Selected</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Search and select a user above to begin configuration</p>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

