@extends('layouts.tenant')
@section('title', 'Website Templates')
@section('page-title', 'Website Templates')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-violet-600 text-white flex items-center justify-center shadow-lg shadow-violet-500/20">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Website Templates</h1>
                <p class="text-xs font-bold text-slate-400">Select and customize your storefront design</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/website-settings" class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 px-4 py-2.5 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all">
                ← Settings
            </a>
            <a href="/website-templates/configurator" class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-violet-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Customize Active
            </a>
        </div>
    </div>

    {{-- Success / Error --}}
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-black flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Active Template Banner --}}
    <div class="glass-card p-5 rounded-[2rem] flex items-center gap-4 border-2 border-violet-200 dark:border-violet-800">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
             style="background-color: {{ collect($templates)->firstWhere('id', $activeTemplate)['preview_color'] ?? '#10b981' }}22">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Currently Active</div>
            <div class="font-black text-slate-900 dark:text-white text-base">
                {{ collect($templates)->firstWhere('id', $activeTemplate)['name'] ?? $activeTemplate }}
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="/website-templates/configurator?template={{ $activeTemplate }}"
               class="px-4 py-2 bg-violet-600 text-white rounded-xl font-bold text-xs hover:bg-violet-700 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Customize
            </a>
            <a href="http://{{ request()->getHost() }}:5173" target="_blank"
               class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl font-bold text-xs hover:bg-emerald-100 transition-all flex items-center gap-1.5 border border-emerald-200 dark:border-emerald-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Preview Live
            </a>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="template-search" placeholder="Search templates…"
                class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium outline-none focus:border-violet-400 transition-colors text-slate-900 dark:text-white" />
        </div>
        <div class="flex gap-2 flex-wrap">
            <button onclick="filterTemplates('all')" data-filter="all" class="filter-btn active px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-violet-600 text-white">All (20)</button>
            <button onclick="filterTemplates('minimal')" data-filter="minimal" class="filter-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-violet-50">Minimalist</button>
            <button onclick="filterTemplates('luxury')" data-filter="luxury" class="filter-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-violet-50">Luxury & Fashion</button>
            <button onclick="filterTemplates('tech')" data-filter="tech" class="filter-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-violet-50">Tech & Retail</button>
            <button onclick="filterTemplates('corporate')" data-filter="corporate" class="filter-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-violet-50">Corporate & B2B</button>
        </div>
    </div>

    {{-- Templates Grid --}}
    <div id="templates-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($templates as $tpl)
        <div class="template-card group relative rounded-[1.5rem] overflow-hidden border-2 transition-all duration-300 cursor-pointer
                    {{ $tpl['id'] === $activeTemplate ? 'border-violet-500 ring-2 ring-violet-300 dark:ring-violet-800' : 'border-transparent hover:border-violet-300 dark:hover:border-violet-700' }}
                    bg-white dark:bg-slate-800 shadow-sm hover:shadow-xl"
             data-status="{{ $tpl['status'] }}"
             data-name="{{ strtolower($tpl['name']) }}"
             data-tags="{{ implode(' ', $tpl['tags']) }}">

            {{-- Preview Thumbnail --}}
            <div class="relative h-44 overflow-hidden" style="background-color: {{ $tpl['preview_bg'] }}">
                {{-- Mock browser chrome --}}
                <div class="absolute top-0 left-0 right-0 flex items-center gap-1.5 px-3 py-2 z-10" style="background: rgba(0,0,0,0.15); backdrop-filter: blur(4px)">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-400 opacity-80"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-400 opacity-80"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-400 opacity-80"></div>
                    <div class="flex-1 mx-2 py-0.5 px-2 rounded text-[8px] font-mono text-white opacity-60" style="background: rgba(255,255,255,0.1)">
                        yourstore.com
                    </div>
                </div>

                {{-- Template preview mockup — Distinct miniature UI for every template archetype --}}
                @switch($tpl['id'])
                    @case('modern_minimal')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-slate-50">
                            <div class="flex items-center justify-between pb-1 border-b border-slate-200">
                                <span class="text-[7px] font-black tracking-widest text-slate-800 uppercase">STUDIO</span>
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/20 flex items-center justify-center text-[5px] font-bold text-emerald-600">0</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 my-auto items-center">
                                <div class="space-y-1">
                                    <div class="w-10 h-1 rounded bg-emerald-500"></div>
                                    <div class="w-14 h-2 rounded bg-slate-900"></div>
                                    <div class="w-12 h-1.5 rounded bg-slate-400"></div>
                                    <div class="w-8 h-2 rounded-full bg-slate-900 mt-1"></div>
                                </div>
                                <div class="aspect-square rounded-lg bg-emerald-100/60 border border-emerald-200 flex items-center justify-center">
                                    <div class="w-6 h-6 rounded bg-emerald-500/30"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="bg-white p-1 rounded border border-slate-200 flex items-center gap-1">
                                    <div class="w-3.5 h-3.5 rounded bg-slate-100"></div>
                                    <div class="space-y-0.5"><div class="w-6 h-1 rounded bg-slate-700"></div><div class="w-4 h-1 rounded bg-emerald-500"></div></div>
                                </div>
                                <div class="bg-white p-1 rounded border border-slate-200 flex items-center gap-1">
                                    <div class="w-3.5 h-3.5 rounded bg-slate-100"></div>
                                    <div class="space-y-0.5"><div class="w-6 h-1 rounded bg-slate-700"></div><div class="w-4 h-1 rounded bg-emerald-500"></div></div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('premium_dark')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-slate-950 text-amber-400">
                            <div class="flex items-center justify-between pb-1 border-b border-amber-500/20">
                                <span class="text-[7px] font-serif font-black tracking-widest text-amber-300">ROYAL & CO</span>
                                <span class="text-[6px] text-amber-400/70 border border-amber-500/30 px-1 rounded-full">GOLD</span>
                            </div>
                            <div class="text-center my-auto space-y-1">
                                <div class="text-[6px] tracking-widest text-amber-500 font-mono">✦ HAUTE ATELIER ✦</div>
                                <div class="w-16 h-2 rounded bg-amber-400 mx-auto"></div>
                                <div class="w-10 h-1.5 rounded-full bg-amber-500/30 border border-amber-400/50 mx-auto"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="bg-slate-900/80 p-1.5 rounded-lg border border-amber-500/30 flex flex-col items-center shadow-[0_0_8px_rgba(245,158,11,0.15)]">
                                    <div class="w-5 h-5 rounded bg-gradient-to-tr from-amber-600 to-amber-300 mb-1"></div>
                                    <div class="w-8 h-1 rounded bg-amber-200"></div>
                                </div>
                                <div class="bg-slate-900/80 p-1.5 rounded-lg border border-amber-500/30 flex flex-col items-center shadow-[0_0_8px_rgba(245,158,11,0.15)]">
                                    <div class="w-5 h-5 rounded bg-gradient-to-tr from-amber-600 to-amber-300 mb-1"></div>
                                    <div class="w-8 h-1 rounded bg-amber-200"></div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('bold_commerce')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-indigo-50">
                            <div class="bg-gradient-to-r from-indigo-600 to-pink-600 text-white p-1 rounded flex items-center justify-between text-[6px] font-black">
                                <span>⚡ MEGA SALE</span>
                                <span class="bg-yellow-400 text-slate-950 px-1 rounded font-bold">50% OFF</span>
                            </div>
                            <div class="flex gap-1 py-0.5 overflow-hidden">
                                <span class="text-[5px] bg-indigo-600 text-white px-1.5 py-0.5 rounded-full font-bold">All</span>
                                <span class="text-[5px] bg-white text-indigo-700 border border-indigo-200 px-1.5 py-0.5 rounded-full">Deals</span>
                                <span class="text-[5px] bg-white text-indigo-700 border border-indigo-200 px-1.5 py-0.5 rounded-full">Top</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1">
                                @for($j = 0; $j < 4; $j++)
                                <div class="bg-white p-1 rounded-md border border-indigo-100 shadow-2xs relative">
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[4px] font-bold px-0.5 rounded">-{{ 20 + $j*10 }}%</span>
                                    <div class="w-full aspect-square rounded bg-indigo-100/70 mb-0.5"></div>
                                    <div class="w-full h-1 rounded bg-indigo-900"></div>
                                    <div class="w-1/2 h-1 rounded bg-pink-600 mt-0.5"></div>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @break

                    @case('clean_business')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-sky-50/50">
                            <div class="flex items-center justify-between pb-1 border-b border-sky-200">
                                <div class="flex items-center gap-1">
                                    <div class="w-2.5 h-2.5 rounded bg-sky-600"></div>
                                    <span class="text-[7px] font-bold text-sky-950">CORP ERP</span>
                                </div>
                                <div class="flex gap-1"><div class="w-4 h-1 rounded bg-sky-400"></div><div class="w-4 h-1 rounded bg-sky-400"></div></div>
                            </div>
                            <div class="flex items-center gap-2 my-auto">
                                <div class="flex-1 space-y-1">
                                    <div class="w-16 h-2 rounded bg-sky-900"></div>
                                    <div class="w-12 h-1.5 rounded bg-sky-600"></div>
                                    <div class="flex gap-1 mt-1">
                                        <span class="text-[5px] bg-sky-100 text-sky-700 px-1 py-0.5 rounded font-bold">99.9% Up</span>
                                        <span class="text-[5px] bg-emerald-100 text-emerald-700 px-1 py-0.5 rounded font-bold">ISO</span>
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-sky-200/70 border border-sky-300 flex items-center justify-center">
                                    <div class="w-6 h-6 rounded bg-sky-600/30"></div>
                                </div>
                            </div>
                            <div class="space-y-1 bg-white p-1 rounded-lg border border-sky-100">
                                <div class="flex justify-between items-center text-[5px] text-slate-500 font-mono">
                                    <span>Enterprise Suite</span><span class="text-sky-600 font-bold">Active</span>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('creative_commerce')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-pink-50 overflow-hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-[8px] font-black text-pink-600 italic">STUDIO.ART</span>
                                <div class="w-3 h-3 rounded-full bg-pink-500 text-white text-[5px] flex items-center justify-center font-black">★</div>
                            </div>
                            <div class="relative my-auto py-1">
                                <div class="w-20 h-2.5 rounded-full bg-gradient-to-r from-pink-500 to-purple-500 mb-1"></div>
                                <div class="flex gap-1.5 items-end">
                                    <div class="w-12 h-14 rounded-2xl bg-gradient-to-b from-pink-200 to-purple-200 border-2 border-pink-400 -rotate-3 p-1 flex flex-col justify-between">
                                        <span class="text-[4px] font-bold bg-pink-500 text-white rounded px-0.5">NEW</span>
                                        <div class="w-full h-1 rounded bg-purple-700"></div>
                                    </div>
                                    <div class="w-14 h-12 rounded-2xl bg-white border-2 border-purple-400 rotate-2 p-1 flex flex-col justify-between shadow-sm">
                                        <span class="text-[4px] font-bold bg-purple-500 text-white rounded px-0.5">LIMITED</span>
                                        <div class="w-full h-1 rounded bg-pink-700"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('luxury_store')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-[#1c1410] text-[#fdfbf7]">
                            <div class="text-center pb-1 border-b border-amber-900/60">
                                <span class="text-[7px] font-serif tracking-[0.2em] text-amber-300 uppercase">MAISON DE LUXE</span>
                            </div>
                            <div class="my-auto border border-amber-500/40 p-2 rounded text-center bg-gradient-to-b from-amber-950/40 to-black/60 shadow-[0_0_12px_rgba(217,119,6,0.15)]">
                                <div class="text-[5px] tracking-widest text-amber-400/80 mb-0.5">FINE JEWELLERY</div>
                                <div class="w-16 h-2 rounded bg-amber-200 mx-auto mb-1"></div>
                                <div class="w-8 h-1 bg-amber-500/80 mx-auto"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center text-[5px] text-amber-200/80">
                                <div class="border-t border-amber-500/30 pt-1">TIMEPIECES</div>
                                <div class="border-t border-amber-500/30 pt-1">DIAMONDS</div>
                            </div>
                        </div>
                        @break

                    @case('electronics_store')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-slate-900 text-cyan-400">
                            <div class="flex items-center justify-between pb-1 border-b border-cyan-500/30">
                                <span class="text-[7px] font-mono font-bold tracking-wider text-cyan-300">⚡ TECHNOVA</span>
                                <div class="w-12 h-2 rounded bg-slate-800 border border-cyan-500/40"></div>
                            </div>
                            <div class="my-auto bg-slate-800/80 p-1.5 rounded border border-cyan-500/30 space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-[6px] font-bold text-white">FLAGSHIP PRO</span>
                                    <span class="text-[5px] bg-cyan-500/20 text-cyan-300 px-1 rounded">GEN 5</span>
                                </div>
                                <div class="flex gap-1 text-[4px]">
                                    <span class="bg-slate-700 px-1 py-0.5 rounded text-cyan-200">128GB</span>
                                    <span class="bg-slate-700 px-1 py-0.5 rounded text-cyan-200">OLED</span>
                                    <span class="bg-cyan-500 text-slate-950 font-bold px-1 py-0.5 rounded">IN STOCK</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <div class="bg-slate-800 p-1 rounded border border-slate-700 text-[5px] text-slate-300 flex items-center gap-1">
                                    <div class="w-3 h-3 rounded bg-blue-500/30"></div>
                                    <span>Earbuds Pro</span>
                                </div>
                                <div class="bg-slate-800 p-1 rounded border border-slate-700 text-[5px] text-slate-300 flex items-center gap-1">
                                    <div class="w-3 h-3 rounded bg-cyan-500/30"></div>
                                    <span>Smartwatch</span>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('fashion_store')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-stone-50">
                            <div class="flex items-center justify-between pb-1 border-b border-rose-200">
                                <span class="text-[7px] font-serif font-black tracking-widest text-rose-900">VOGUE & CO</span>
                                <span class="text-[5px] tracking-widest text-rose-600 uppercase font-semibold">SS'26</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 my-auto items-center">
                                <div class="h-16 rounded-lg bg-rose-100/70 border border-rose-200 p-1 flex flex-col justify-between">
                                    <span class="text-[5px] font-serif text-rose-950 font-bold">RUNWAY EDIT</span>
                                    <div class="w-8 h-1.5 rounded-full bg-rose-700"></div>
                                </div>
                                <div class="space-y-1">
                                    <div class="w-12 h-2 rounded bg-rose-950"></div>
                                    <div class="w-10 h-1 rounded bg-rose-400"></div>
                                    <div class="h-8 rounded bg-rose-200/50 border border-rose-300"></div>
                                </div>
                            </div>
                            <div class="flex justify-around text-[5px] text-rose-900 font-serif border-t border-rose-200 pt-1">
                                <span>APPAREL</span><span>SILK</span><span>ACCESSORIES</span>
                            </div>
                        </div>
                        @break

                    @case('manufacturing')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-slate-100 font-mono">
                            <div class="flex items-center justify-between bg-slate-700 text-white px-1.5 py-1 rounded">
                                <span class="text-[6px] font-bold">FACTORY DIRECT B2B</span>
                                <span class="text-[5px] bg-slate-600 px-1 rounded">RFQ</span>
                            </div>
                            <div class="bg-white rounded border border-slate-300 p-1 space-y-0.5 text-[5px]">
                                <div class="flex justify-between border-b border-slate-200 pb-0.5 font-bold text-slate-700">
                                    <span>SKU</span><span>STOCK</span><span>TIER</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>#M-8492</span><span class="text-emerald-600 font-bold">1,200+</span><span>₹450/u</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>#M-9201</span><span class="text-emerald-600 font-bold">850+</span><span>₹780/u</span>
                                </div>
                            </div>
                            <div class="bg-slate-200 p-1 rounded text-[5px] text-slate-600 flex justify-between">
                                <span>ISO 9001:2015 CERTIFIED</span><span class="font-bold">BULK 500+</span>
                            </div>
                        </div>
                        @break

                    @case('industrial')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-zinc-900 text-orange-400">
                            <div class="h-1 w-full bg-[repeating-linear-gradient(45deg,#ea580c,#ea580c_6px,#18181b_6px,#18181b_12px)] rounded-full"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-[7px] font-black text-orange-500 uppercase">TITAN HEAVY</span>
                                <span class="text-[5px] bg-orange-500 text-black font-bold px-1 rounded">480V HEAVY</span>
                            </div>
                            <div class="my-auto bg-zinc-800 p-1.5 rounded border-l-2 border-orange-500 space-y-1">
                                <div class="w-14 h-2 rounded bg-orange-400"></div>
                                <div class="w-10 h-1 rounded bg-zinc-500"></div>
                                <div class="flex gap-1 text-[4px] font-bold">
                                    <span class="bg-zinc-700 text-orange-300 px-1 py-0.5 rounded">TORQUE 950Nm</span>
                                    <span class="bg-zinc-700 text-orange-300 px-1 py-0.5 rounded">IP67</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-[5px] text-zinc-300">
                                <div class="bg-zinc-800 p-1 rounded border border-zinc-700">PUMPS & VALVES</div>
                                <div class="bg-zinc-800 p-1 rounded border border-zinc-700">POWER TOOLS</div>
                            </div>
                        </div>
                        @break

                    @case('corporate')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-blue-50/50">
                            <div class="flex items-center justify-between pb-1 border-b border-blue-200">
                                <span class="text-[7px] font-bold text-blue-900">GLOBAL ENTERPRISE</span>
                                <div class="flex gap-1"><div class="w-3 h-1 bg-blue-600 rounded"></div><div class="w-3 h-1 bg-blue-300 rounded"></div></div>
                            </div>
                            <div class="my-auto text-center space-y-1">
                                <div class="w-20 h-2 rounded bg-blue-900 mx-auto"></div>
                                <div class="w-14 h-1.5 rounded bg-blue-600 mx-auto"></div>
                                <div class="w-10 h-2 rounded-full bg-blue-700 mx-auto"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <div class="bg-white p-1 rounded border border-blue-100 text-center text-[4px] font-bold text-blue-900">ENTERPRISE</div>
                                <div class="bg-white p-1 rounded border border-blue-100 text-center text-[4px] font-bold text-blue-900">LOGISTICS</div>
                                <div class="bg-white p-1 rounded border border-blue-100 text-center text-[4px] font-bold text-blue-900">SOLUTIONS</div>
                            </div>
                        </div>
                        @break

                    @case('bold_colorful')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-gradient-to-br from-violet-600 via-purple-600 to-pink-500 text-white">
                            <div class="flex items-center justify-between">
                                <span class="text-[8px] font-black tracking-wider">POP! SHOP</span>
                                <span class="text-[5px] bg-yellow-300 text-purple-900 px-1.5 py-0.5 rounded-full font-black">NEON</span>
                            </div>
                            <div class="my-auto bg-white/20 backdrop-blur-md p-2 rounded-xl border border-white/40 shadow-lg text-center space-y-1">
                                <div class="w-16 h-2 rounded-full bg-yellow-300 mx-auto"></div>
                                <div class="w-12 h-1.5 rounded-full bg-white mx-auto"></div>
                                <div class="w-8 h-2 rounded-full bg-pink-500 mx-auto"></div>
                            </div>
                            <div class="flex justify-between gap-1">
                                <div class="flex-1 bg-white/10 p-1 rounded-lg text-center text-[5px] font-bold">✨ TRENDING</div>
                                <div class="flex-1 bg-white/10 p-1 rounded-lg text-center text-[5px] font-bold">🔥 FRESH</div>
                            </div>
                        </div>
                        @break

                    @case('glassmorphism')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-[#080e1e] relative overflow-hidden">
                            <div class="absolute -top-4 -left-4 w-16 h-16 rounded-full bg-cyan-500/30 blur-md pointer-events-none"></div>
                            <div class="absolute -bottom-4 -right-4 w-16 h-16 rounded-full bg-blue-600/30 blur-md pointer-events-none"></div>
                            <div class="flex items-center justify-between relative z-10">
                                <span class="text-[7px] font-bold text-cyan-300">GLASSTECH</span>
                                <span class="text-[5px] bg-white/10 text-cyan-200 border border-white/20 px-1 rounded-full backdrop-blur-sm">FROST</span>
                            </div>
                            <div class="my-auto bg-white/10 backdrop-blur-md p-2 rounded-xl border border-white/25 shadow-[0_4px_16px_rgba(0,0,0,0.4)] relative z-10 space-y-1">
                                <div class="w-14 h-2 rounded bg-cyan-400"></div>
                                <div class="w-10 h-1.5 rounded bg-white/70"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 relative z-10">
                                <div class="bg-white/5 backdrop-blur-sm p-1 rounded-lg border border-white/15 text-[5px] text-cyan-200 text-center">TRANSLUCENT</div>
                                <div class="bg-white/5 backdrop-blur-sm p-1 rounded-lg border border-white/15 text-[5px] text-cyan-200 text-center">FROST UI</div>
                            </div>
                        </div>
                        @break

                    @case('classic_commerce')
                        <div class="absolute inset-x-0 top-7 bottom-0 flex flex-col bg-slate-50">
                            <div class="bg-emerald-600 text-white text-[5px] py-0.5 px-2 flex justify-between items-center font-medium">
                                <span>Free Shipping ₹999+</span><span>Help</span>
                            </div>
                            <div class="flex-1 flex gap-1 p-1.5">
                                <div class="w-10 bg-white rounded border border-slate-200 p-1 space-y-1 text-[4px] text-slate-600">
                                    <div class="font-bold text-emerald-700 border-b border-slate-100 pb-0.5">CATEGORIES</div>
                                    <div>Groceries</div><div>Personal</div><div>Home</div>
                                </div>
                                <div class="flex-1 grid grid-cols-2 gap-1">
                                    @for($k = 0; $k < 4; $k++)
                                    <div class="bg-white p-1 rounded border border-slate-200 flex flex-col justify-between">
                                        <div class="w-full aspect-square bg-emerald-50 rounded"></div>
                                        <div class="w-full h-1 bg-slate-700 mt-0.5"></div>
                                        <div class="w-1/2 h-1 bg-emerald-600 mt-0.5"></div>
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        @break

                    @case('modern_grid')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-[#fafaf9]">
                            <div class="flex items-center justify-between pb-1 border-b border-amber-200">
                                <span class="text-[7px] font-bold text-amber-900 uppercase">GRID EDITORIAL</span>
                                <div class="w-2.5 h-2.5 rounded bg-amber-600"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1 my-auto">
                                <div class="col-span-2 row-span-2 bg-amber-100/70 border border-amber-300 rounded-lg p-1.5 flex flex-col justify-between">
                                    <span class="text-[5px] font-bold text-amber-900">HERO FEATURE</span>
                                    <div class="w-full h-1.5 rounded bg-amber-800"></div>
                                </div>
                                <div class="bg-white border border-amber-200 rounded p-1 flex flex-col justify-between">
                                    <div class="w-full aspect-square bg-amber-50 rounded"></div>
                                    <div class="w-full h-1 bg-amber-900 mt-0.5"></div>
                                </div>
                                <div class="bg-white border border-amber-200 rounded p-1 flex flex-col justify-between">
                                    <div class="w-full aspect-square bg-amber-50 rounded"></div>
                                    <div class="w-full h-1 bg-amber-900 mt-0.5"></div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('product_focused')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-cyan-50">
                            <div class="flex items-center justify-between pb-1 border-b border-cyan-200">
                                <span class="text-[7px] font-black text-cyan-900">SOLO SPOTLIGHT</span>
                                <span class="text-[5px] bg-cyan-600 text-white font-bold px-1 rounded-full">FLAGSHIP</span>
                            </div>
                            <div class="my-auto relative flex items-center justify-center py-2">
                                <div class="w-12 h-12 rounded-full bg-cyan-200 border-2 border-cyan-400 flex items-center justify-center shadow-md">
                                    <div class="w-6 h-6 rounded-full bg-cyan-600"></div>
                                </div>
                                <span class="absolute top-0 left-2 text-[4px] bg-white border border-cyan-300 text-cyan-800 px-1 rounded shadow-2xs">ANC 40dB</span>
                                <span class="absolute bottom-0 right-2 text-[4px] bg-white border border-cyan-300 text-cyan-800 px-1 rounded shadow-2xs">40h Battery</span>
                            </div>
                            <div class="bg-cyan-700 text-white p-1 rounded-lg flex items-center justify-between text-[5px] font-bold">
                                <span>Instant ERP Order</span>
                                <span class="bg-white text-cyan-900 px-1.5 py-0.5 rounded">Buy Now</span>
                            </div>
                        </div>
                        @break

                    @case('pro_catalog')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-1.5 flex flex-col justify-between bg-slate-100 font-mono">
                            <div class="flex items-center justify-between bg-indigo-900 text-white px-1.5 py-0.5 rounded">
                                <span class="text-[6px] font-bold">PRO CATALOG</span>
                                <span class="text-[4px] bg-indigo-700 px-1 rounded">2,410 SKUs</span>
                            </div>
                            <div class="bg-white rounded border border-slate-300 divide-y divide-slate-100 text-[4px]">
                                <div class="p-0.5 flex justify-between items-center text-slate-800 font-sans">
                                    <span class="font-bold truncate">Wireless Scanner X</span>
                                    <span class="text-emerald-600 font-bold bg-emerald-50 px-0.5 rounded">In Stock</span>
                                    <span class="font-bold">₹3,499</span>
                                </div>
                                <div class="p-0.5 flex justify-between items-center text-slate-800 font-sans">
                                    <span class="font-bold truncate">Thermal Roll 80mm</span>
                                    <span class="text-emerald-600 font-bold bg-emerald-50 px-0.5 rounded">In Stock</span>
                                    <span class="font-bold">₹249</span>
                                </div>
                                <div class="p-0.5 flex justify-between items-center text-slate-800 font-sans">
                                    <span class="font-bold truncate">Cash Drawer POS</span>
                                    <span class="text-amber-600 font-bold bg-amber-50 px-0.5 rounded">Low Stock</span>
                                    <span class="font-bold">₹1,899</span>
                                </div>
                            </div>
                            <div class="text-[4px] text-slate-500 flex justify-between px-0.5">
                                <span>Batch SKU Search</span><span>Filter: Active</span>
                            </div>
                        </div>
                        @break

                    @case('elegant_white')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2.5 flex flex-col justify-between bg-white text-stone-800">
                            <div class="text-center pb-1 border-b border-stone-200">
                                <span class="text-[7px] font-serif tracking-[0.25em] text-stone-900">A T E L I E R</span>
                            </div>
                            <div class="text-center my-auto space-y-1">
                                <div class="text-[5px] font-serif italic text-stone-400">Curated Object Collection</div>
                                <div class="w-16 h-1.5 rounded bg-stone-800 mx-auto"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <div class="aspect-[3/4] bg-stone-50 border border-stone-200 rounded p-1 flex flex-col justify-end">
                                    <div class="w-full h-1 bg-stone-400"></div>
                                </div>
                                <div class="aspect-[3/4] bg-stone-50 border border-stone-200 rounded p-1 flex flex-col justify-end">
                                    <div class="w-full h-1 bg-stone-400"></div>
                                </div>
                                <div class="aspect-[3/4] bg-stone-50 border border-stone-200 rounded p-1 flex flex-col justify-end">
                                    <div class="w-full h-1 bg-stone-400"></div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('dark_industrial')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-[#18181b] text-red-500 font-mono">
                            <div class="flex items-center justify-between pb-1 border-b border-red-500/30">
                                <span class="text-[7px] font-black tracking-widest text-red-400">MACHINA // X</span>
                                <span class="text-[4px] bg-red-600 text-white font-bold px-1 rounded">MIL-SPEC</span>
                            </div>
                            <div class="my-auto bg-zinc-900 border border-red-500/40 p-1.5 rounded space-y-1">
                                <div class="flex justify-between items-center text-[5px]">
                                    <span class="font-bold text-white">INDUSTRIAL GRADE</span>
                                    <span class="text-red-400">HEAVY DUTY</span>
                                </div>
                                <div class="w-14 h-1.5 bg-red-600 rounded"></div>
                                <div class="w-8 h-1 bg-zinc-600 rounded"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-[4px] text-zinc-300">
                                <div class="bg-zinc-900 p-1 rounded border border-zinc-700">HEAVY IMPACT</div>
                                <div class="bg-zinc-900 p-1 rounded border border-zinc-700">HIGH TEMP 400°C</div>
                            </div>
                        </div>
                        @break

                    @case('modern_landing')
                        <div class="absolute inset-x-0 top-7 bottom-0 p-2 flex flex-col justify-between bg-emerald-50/50">
                            <div class="flex items-center justify-between pb-1 border-b border-emerald-200">
                                <span class="text-[7px] font-bold text-emerald-950">LAUNCHPAD</span>
                                <div class="w-8 h-2 rounded-full bg-emerald-600"></div>
                            </div>
                            <div class="my-auto text-center space-y-1">
                                <span class="inline-block text-[4px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded-full">⭐ 4.9/5 from 2,400+ Users</span>
                                <div class="w-18 h-2 rounded bg-emerald-950 mx-auto"></div>
                                <div class="w-12 h-1.5 rounded bg-emerald-600 mx-auto"></div>
                                <div class="w-10 h-2 rounded-full bg-emerald-700 mx-auto"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <div class="bg-white p-1 rounded-md border border-emerald-100 text-center text-[4px] font-bold text-emerald-900">Instant Sync</div>
                                <div class="bg-white p-1 rounded-md border border-emerald-100 text-center text-[4px] font-bold text-emerald-900">0% Fees</div>
                                <div class="bg-white p-1 rounded-md border border-emerald-100 text-center text-[4px] font-bold text-emerald-900">Live Support</div>
                            </div>
                        </div>
                        @break

                    @default
                        {{-- Generic Fallback --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center" style="background: {{ $tpl['preview_bg'] }}">
                            <div class="w-16 h-2 rounded bg-slate-400"></div>
                        </div>
                @endswitch

                {{-- Active badge --}}
                @if($tpl['id'] === $activeTemplate)
                <div class="absolute top-10 right-2 z-20">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-violet-600 text-white text-[9px] font-black rounded-full shadow-lg">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> LIVE
                    </span>
                </div>
                @endif

                {{-- Coming soon overlay --}}
                @if($tpl['status'] === 'coming_soon')
                <div class="absolute inset-0 flex items-center justify-center z-10" style="background: rgba(0,0,0,0.4); backdrop-filter: blur(2px)">
                    <span class="text-white font-black text-xs px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3)">Coming Soon</span>
                </div>
                @endif

                {{-- Hover overlay --}}
                @if($tpl['status'] === 'active')
                <div class="absolute inset-0 bg-violet-900/70 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 flex items-center justify-center gap-2">
                    <a href="/website-templates/configurator?template={{ $tpl['id'] }}"
                       class="px-3 py-1.5 bg-white text-violet-700 rounded-lg font-bold text-xs hover:bg-violet-50 transition-all">
                        ✏️ Customize
                    </a>
                    @if($tpl['id'] !== $activeTemplate)
                    <form method="POST" action="/website-templates/active">
                        @csrf
                        <input type="hidden" name="template" value="{{ $tpl['id'] }}">
                        <button type="submit" class="px-3 py-1.5 bg-violet-600 text-white rounded-lg font-bold text-xs hover:bg-violet-700 transition-all">
                            ⚡ Use This
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            {{-- Card Footer --}}
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-sm leading-tight">{{ $tpl['name'] }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">{{ $tpl['description'] }}</p>
                    </div>
                    @if($tpl['id'] === $activeTemplate)
                    <div class="shrink-0 w-6 h-6 rounded-full bg-violet-600 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @endif
                </div>

                {{-- Tags --}}
                <div class="flex gap-1 flex-wrap mt-2">
                    @foreach($tpl['tags'] as $tag)
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" style="background: {{ $tpl['preview_color'] }}20; color: {{ $tpl['preview_color'] }}">{{ $tag }}</span>
                    @endforeach
                </div>

                {{-- Actions --}}
                @if($tpl['status'] === 'active')
                <div class="flex gap-2 mt-3">
                    <a href="/website-templates/configurator?template={{ $tpl['id'] }}"
                       class="flex-1 text-center py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-violet-50 dark:hover:bg-violet-900/30 hover:text-violet-600 transition-all">
                        Customize
                    </a>
                    @if($tpl['id'] !== $activeTemplate)
                    <form method="POST" action="/website-templates/active" class="flex-1">
                        @csrf
                        <input type="hidden" name="template" value="{{ $tpl['id'] }}">
                        <button type="submit" class="w-full py-2 bg-violet-600 text-white rounded-xl text-xs font-black hover:bg-violet-700 transition-all">
                            Activate
                        </button>
                    </form>
                    @else
                    <div class="flex-1 py-2 bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400 rounded-xl text-xs font-black text-center border border-violet-200 dark:border-violet-800">
                        ✓ Active
                    </div>
                    @endif
                </div>
                @else
                <div class="mt-3 py-2 bg-slate-50 dark:bg-slate-900 text-slate-400 rounded-xl text-xs font-bold text-center border border-dashed border-slate-200 dark:border-slate-700">
                    Coming Soon
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>

<script>
// Search & filter
document.getElementById('template-search').addEventListener('input', function() {
    filterBySearch(this.value.toLowerCase());
});

function filterBySearch(query) {
    document.querySelectorAll('.template-card').forEach(card => {
        const name = card.dataset.name || '';
        const tags = card.dataset.tags || '';
        card.style.display = (name.includes(query) || tags.includes(query)) ? '' : 'none';
    });
}

function filterTemplates(filter) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-violet-600', 'text-white');
        b.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-400');
    });
    const active = document.querySelector(`[data-filter="${filter}"]`);
    if (active) {
        active.classList.add('bg-violet-600', 'text-white');
        active.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-400');
    }
    document.querySelectorAll('.template-card').forEach(card => {
        if (filter === 'all') {
            card.style.display = '';
            return;
        }
        const tags = (card.dataset.tags || '').toLowerCase();
        const name = (card.dataset.name || '').toLowerCase();
        const matchesTag = tags.includes(filter) || name.includes(filter);
        const matchesStatus = card.dataset.status === filter;
        card.style.display = (matchesTag || matchesStatus) ? '' : 'none';
    });
}
</script>
@endsection
