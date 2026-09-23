@extends('layouts.tenant')
@section('title', 'CRM Settings')

@section('content')
<div class="h-full flex flex-col space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.crm.index') }}" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">CRM Settings</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Control center for your lead management engine</p>
            </div>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- User Access -->
        <a href="{{ route('tenant.crm.settings.permissions') }}" class="glass-card group p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all cursor-pointer">
            <div class="flex flex-col gap-5">
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">User Access</h3>
                    <p class="text-xs font-bold text-slate-400 leading-relaxed uppercase tracking-tight">Manage user roles and permissions for CRM access.</p>
                </div>
            </div>
        </a>

        <!-- Edit Settings (Workflow Architect) -->
        <a href="{{ route('tenant.crm.settings.workflow') }}" class="glass-card group p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all cursor-pointer">
            <div class="flex flex-col gap-5">
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Edit Settings</h3>
                    <p class="text-xs font-bold text-slate-400 leading-relaxed uppercase tracking-tight">Configure workflows, columns, and mapping rules.</p>
                </div>
            </div>
        </a>

        <!-- API Config -->
        <a href="{{ route('tenant.crm.settings.apis') }}" class="glass-card group p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all cursor-pointer">
            <div class="flex flex-col gap-5">
                <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">API Config</h3>
                    <p class="text-xs font-bold text-slate-400 leading-relaxed uppercase tracking-tight">Manage API integrations and external sources.</p>
                </div>
            </div>
        </a>



        <!-- Link Management -->
        <a href="{{ route('tenant.crm.settings.workflow') }}?tab=public_forms" class="glass-card group p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all cursor-pointer">
            <div class="flex flex-col gap-5">
                <div class="w-14 h-14 bg-orange-50 dark:bg-orange-900/20 text-orange-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Link Management</h3>
                    <p class="text-xs font-bold text-slate-400 leading-relaxed uppercase tracking-tight">Manage and organize external links and resources.</p>
                </div>
            </div>
        </a>

        <!-- Document Management -->
        <div class="glass-card group p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all cursor-pointer">
            <div class="flex flex-col gap-5">
                <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Document Management</h3>
                    <p class="text-xs font-bold text-slate-400 leading-relaxed uppercase tracking-tight">Upload, organize, and manage important documents and files securely.</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

