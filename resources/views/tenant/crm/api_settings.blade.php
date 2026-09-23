@extends('layouts.tenant')
@section('title', 'CRM API Management')

@section('content')
<div x-data="apiManager()" class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.settings') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">API Management</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Connect your CRM with external lead sources</p>
            </div>
        </div>
        <button @click="openNewApiModal()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
            New API
        </button>
    </div>

    <!-- API List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($apis as $api)
            <div class="glass-card p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-xl flex items-center justify-center font-black">
                            {{ substr($api->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $api->name }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Workspace: {{ $api->workspace->name }} • Workflow: {{ $api->workflow->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="deleteApi({{ $api->id }})" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">API Key</label>
                        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <code class="text-sm font-bold text-blue-600 dark:text-blue-400 break-all">{{ $api->api_key }}</code>
                            <button @click="copyToClipboard('{{ $api->api_key }}')" class="ml-auto p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">API URL</label>
                        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <code class="text-sm font-bold text-slate-600 dark:text-slate-300 break-all">{{ route('tenant.crm.index') }}/api/lead/create?api_key={{ $api->api_key }}</code>
                            <button @click="copyToClipboard('{{ route('tenant.crm.index') }}/api/lead/create?api_key={{ $api->api_key }}')" class="ml-auto p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Required Fields:</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($api->workflow->fields as $field)
                            <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-bold border border-emerald-100 dark:border-emerald-800/30">
                                {{ $field->label }} <span class="text-[10px] opacity-60 uppercase ml-1">({{ $field->type }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-50 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Data Format Guidelines:</p>
                    <ul class="mt-2 space-y-1 text-xs text-slate-500 font-medium">
                        <li>• text/textarea: Any string value</li>
                        <li>• number/currency: Numeric values (123, 45.67)</li>
                        <li>• date: YYYY-MM-DD format (2024-12-31)</li>
                        <li>• email: Valid email format</li>
                    </ul>
                </div>
            </div>
        @empty
            <div class="glass-card p-20 rounded-[3rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 text-slate-300 rounded-[2rem] flex items-center justify-center mb-6">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">No APIs Configured</h3>
                <p class="text-sm font-bold text-slate-400 mt-2">Generate an API key to start receiving leads from external sources.</p>
                <button @click="openNewApiModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">Generate First API</button>
            </div>
        @endforelse
    </div>

    <!-- New API Modal -->
    <div x-show="showNewApiModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showNewApiModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] w-full max-w-lg overflow-hidden shadow-2xl animate-in zoom-in duration-300">
            <div class="p-8 space-y-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Generate New API</h3>
                    <button @click="showNewApiModal = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">API Name</label>
                        <input type="text" x-model="newApi.name" placeholder="e.g. Website Contact Form" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Workspace</label>
                        <select x-model="newApi.workspace_id" @change="onWorkspaceChange()" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                            <option value="">Select Workspace</option>
                            @foreach($workspaces as $ws)
                                <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Workflow</label>
                        <select x-model="newApi.workflow_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all dark:text-white" :disabled="!availableWorkflows.length">
                            <option value="">Select Workflow</option>
                            <template x-for="wf in availableWorkflows" :key="wf.id">
                                <option :value="wf.id" x-text="wf.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button @click="showNewApiModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</button>
                    <button @click="saveNewApi()" class="flex-2 px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 dark:shadow-none" :disabled="isSaving || !newApi.name || !newApi.workflow_id">
                        <span x-show="!isSaving">Generate API Key</span>
                        <span x-show="isSaving">Generating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function apiManager() {
    return {
        showNewApiModal: false,
        isSaving: false,
        newApi: {
            name: '',
            workspace_id: '',
            workflow_id: ''
        },
        workspaces: @json($workspaces),
        availableWorkflows: [],

        openNewApiModal() {
            this.newApi = { name: '', workspace_id: '', workflow_id: '' };
            this.availableWorkflows = [];
            this.showNewApiModal = true;
        },

        onWorkspaceChange() {
            const ws = this.workspaces.find(w => w.id == this.newApi.workspace_id);
            this.availableWorkflows = ws ? ws.workflows : [];
            this.newApi.workflow_id = '';
        },

        saveNewApi() {
            this.isSaving = true;
            fetch("{{ route('tenant.crm.settings.apis.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.newApi)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error saving API');
                }
            })
            .finally(() => this.isSaving = false);
        },

        deleteApi(id) {
            if (!confirm('Are you sure you want to delete this API configuration?')) return;
            
            fetch("{{ route('tenant.crm.settings.apis.delete', ['api' => ':id']) }}".replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }
    }
}
</script>
@endsection

