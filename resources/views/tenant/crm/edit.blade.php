@extends('layouts.tenant')

@section('content')
<div class="h-full flex flex-col max-w-4xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.index') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Edit Lead #{{ $lead->id }}</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Modify lead data and status</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="glass-card p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 shadow-xl border border-slate-100 dark:border-slate-800 flex-1 overflow-auto custom-scrollbar">
        <form method="POST" action="{{ route('tenant.crm.lead.update', $lead) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Core Info -->
            <div>
                <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="text-blue-500" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Core Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lead Status</label>
                        <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                            <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                            <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Workflow</label>
                        <input type="text" disabled value="{{ $lead->workflow->name ?? 'N/A' }}" class="w-full bg-slate-100 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-bold text-slate-500 dark:text-slate-400 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Custom Data -->
            <div>
                <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="text-blue-500" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Collected Data
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 dark:bg-slate-800/30 p-6 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                    @foreach($lead->workflow->fields as $field)
                        @php 
                            $data = $lead->data->where('field_id', $field->id)->first();
                            $val = $data ? $data->value : '';
                        @endphp
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ $field->label }}</label>
                            @if($field->type == 'select')
                                <select name="data[{{ $field->id }}]" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500/50 transition-all shadow-sm">
                                    <option value="">Select {{ $field->label }}</option>
                                    @foreach($field->options ?? [] as $option)
                                        <option value="{{ $option }}" {{ $val == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif($field->type == 'textarea')
                                <textarea name="data[{{ $field->id }}]" rows="3" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500/50 transition-all shadow-sm">{{ old('data.'.$field->id, $val) }}</textarea>
                            @else
                                <input type="{{ strtolower($field->type ?? 'text') === 'email' ? 'email' : 'text' }}" 
                                       name="data[{{ $field->id }}]" 
                                       value="{{ old('data.'.$field->id, $val) }}" 
                                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500/50 transition-all shadow-sm">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('tenant.crm.index') }}" class="px-8 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</a>
                <button type="submit" class="px-10 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

