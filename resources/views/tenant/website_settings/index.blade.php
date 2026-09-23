@extends('layouts.tenant')
@section('title', 'Website & Store Settings')
@section('page-title', 'Website Settings')

@section('content')
<div class="space-y-8 animate-in fade-in duration-500">

    {{-- Top Header / Action --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Website & Store Settings</h1>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">Configure MSG91 OTP gateways, customer support info, delivery rules, and storefront announcements</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- Hidden: Website Templates button
            <a href="{{ route('tenant.website-templates.index') }}" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                <span>Website Templates (20)</span>
            </a>
            --}}
            <a href="http://{{ request()->getHost() }}:5173" target="_blank" class="flex items-center gap-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 text-blue-600 dark:text-blue-400 px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-sm">
                <span>View Storefront :5173</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-black flex items-center gap-3 animate-in fade-in">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Main Form --}}
    <form method="POST" action="{{ route('tenant.website-settings.update') }}" class="space-y-8">
        @csrf

        {{-- STOREFRONT RULES & OTP GATEWAYS (THE 4 CARDS) --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Storefront & Gateway Configuration</span>
                <span class="text-xs text-slate-400 font-bold">Configure client verification, support details, and shipping fees</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- Card 1: MSG91 OTP WIDGET --}}
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">MSG91 OTP Widget</h3>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Active
                        </span>
                    </div>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="website_otp_required" value="0">
                                <input type="checkbox" name="website_otp_required" value="1" {{ ($settings['website_otp_required'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Require OTP Verification</span>
                        </label>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Widget ID</label>
                            <input type="text" name="msg91_widget_id" value="{{ old('msg91_widget_id', $settings['msg91_widget_id'] ?? '') }}" placeholder="e.g. 36697164476b323432353839" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white" required>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Token Auth</label>
                            <input type="text" name="msg91_token_auth" value="{{ old('msg91_token_auth', $settings['msg91_token_auth'] ?? '') }}" placeholder="e.g. 572040TDOpHdLN6aab6e64P1" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white" required>
                        </div>
                    </div>
                </div>

                {{-- Card 2: SMS GATEWAY (API) --}}
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6 shadow-sm">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">SMS Gateway (API)</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">MSG91 Auth Key</label>
                            <input type="text" name="msg91_auth_key" value="{{ old('msg91_auth_key', $settings['msg91_auth_key'] ?? '') }}" placeholder="Main Auth Key (optional)" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Flow / Template ID</label>
                            <input type="text" name="msg91_template_id" value="{{ old('msg91_template_id', $settings['msg91_template_id'] ?? '') }}" placeholder="DLT Template ID (optional)" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-[10px] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                            Used for direct backend SMS dispatches, delivery alerts, and order confirmation SMS.
                        </div>
                    </div>
                </div>

                {{-- Card 3: STOREFRONT IDENTITY --}}
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6 shadow-sm">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Storefront Identity</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Store Name</label>
                            <input type="text" name="website_store_name" value="{{ old('website_store_name', $settings['website_store_name'] ?? 'Square Store') }}" placeholder="Store Name" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Support Phone / WA</label>
                            <input type="text" name="website_support_phone" value="{{ old('website_support_phone', $settings['website_support_phone'] ?? '+91 98765 43210') }}" placeholder="+91 98765 43210" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Support Email</label>
                            <input type="email" name="website_support_email" value="{{ old('website_support_email', $settings['website_support_email'] ?? 'support@store.com') }}" placeholder="support@store.com" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Card 4: SHIPPING & NOTICE --}}
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6 lg:col-span-1 shadow-sm">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Shipping & Notice</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Free Above (₹)</label>
                                <input type="number" name="website_free_delivery_min" value="{{ old('website_free_delivery_min', $settings['website_free_delivery_min'] ?? '499') }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Shipping (₹)</label>
                                <input type="number" name="website_shipping_fee" value="{{ old('website_shipping_fee', $settings['website_shipping_fee'] ?? '40') }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between ml-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Storewide Discount (%)</label>
                                <span class="text-[9px] text-slate-400 font-bold">0 = no discount</span>
                            </div>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" max="100" name="website_discount_percent" value="{{ old('website_discount_percent', $settings['website_discount_percent'] ?? '0') }}" placeholder="0" class="w-full pl-4 pr-8 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                                <span class="absolute right-3.5 top-2 text-slate-400 font-bold text-xs">%</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Announcement Banner</label>
                            <input type="text" name="website_announcement" value="{{ old('website_announcement', $settings['website_announcement'] ?? 'Free delivery on orders over ₹499!') }}" placeholder="Banner text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>

                        <div class="pt-1">
                            <a href="http://{{ request()->getHost() }}:5173" target="_blank" class="flex items-center justify-center gap-2 w-full py-2.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                <span>Open Storefront :5173</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 glass-card rounded-[2rem] shadow-sm sticky bottom-6 z-20 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">Settings will instantly sync with the storefront at :5173</span>
            </div>
            <button type="submit" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-blue-500/20 hover:scale-[1.02]">
                Save Website Settings
            </button>
        </div>
    </form>
</div>
@endsection
