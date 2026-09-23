@extends('layouts.tenant')
@section('title', 'Website Configurator — ' . $currentTemplate['name'])
@section('page-title', 'Website Configurator')

@section('content')
<div class="animate-in fade-in duration-500">

    {{-- Top bar --}}
    <div class="flex items-center justify-between gap-4 mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.website-templates.index') }}"
               class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white leading-tight">Visual Configurator</h1>
                <p class="text-xs text-slate-400 font-bold">{{ $currentTemplate['name'] }} · Drag blocks & inline text editing</p>
            </div>
        </div>

        {{-- Template switcher (quick select dropdown) --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-bold hidden lg:block">Template:</span>
            <div class="relative">
                <select id="template-quick-switch"
                        onchange="switchTemplate(this.value)"
                        class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-black shadow-sm focus:outline-none focus:border-violet-500 cursor-pointer">
                    @foreach($templates as $tpl)
                    <option value="{{ $tpl['id'] }}" {{ $tpl['id'] === $templateId ? 'selected' : '' }}>
                        {{ $tpl['name'] }} · {{ ucfirst($tpl['tags'][0] ?? 'Theme') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div id="template-status-container">
                @if($templateId === $activeTemplate)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live on Store
                </span>
                @else
                <button type="button" onclick="activateTemplate('{{ $templateId }}')" id="btn-set-live"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5">
                    ⚡ Set as Live
                </button>
                @endif
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 shrink-0">
            <button onclick="resetSettings()" id="btn-reset"
                    class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-red-50 hover:text-red-600 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </button>
            <button onclick="saveDraft()" id="btn-draft"
                    class="flex items-center gap-1.5 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-xl text-xs font-bold hover:bg-amber-100 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save Draft
            </button>
            <button onclick="publishTemplate()" id="btn-publish"
                    class="flex items-center gap-1.5 px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-violet-500/25">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Publish Live
            </button>
        </div>
    </div>

    {{-- Toast notification --}}
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div class="px-5 py-3 rounded-2xl shadow-2xl text-sm font-bold flex items-center gap-2 max-w-sm" id="toast-inner">
        </div>
    </div>

    {{-- Main layout: sidebar + preview --}}
    <div class="flex gap-5 h-[calc(100vh-10rem)] min-h-[600px]">

        {{-- ── LEFT SIDEBAR: Settings ───────────────────── --}}
        <div class="w-80 shrink-0 flex flex-col gap-0 rounded-[2rem] overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">

            {{-- Tabs --}}
            <div class="flex overflow-x-auto border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 shrink-0">
                @foreach([
                    ['id' => 'sections',    'label' => 'Blocks',    'icon' => '🧱'],
                    ['id' => 'branding',    'label' => 'Brand',     'icon' => '🎨'],
                    ['id' => 'colors',      'label' => 'Colors',    'icon' => '🌈'],
                    ['id' => 'typography',  'label' => 'Type',      'icon' => 'Aa'],
                    ['id' => 'hero',        'label' => 'Hero',      'icon' => '🦸'],
                    ['id' => 'layout',      'label' => 'Layout',    'icon' => '⊞'],
                    ['id' => 'seo',         'label' => 'SEO',       'icon' => '🔍'],
                    ['id' => 'social',      'label' => 'Social',    'icon' => '📱'],
                ] as $tab)
                <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-{{ $tab['id'] }}"
                        class="tab-btn flex flex-col items-center gap-0.5 px-3 py-2.5 text-[10px] font-bold whitespace-nowrap transition-all border-b-2 border-transparent hover:text-violet-600
                               {{ $loop->first ? 'text-violet-600 border-violet-500' : 'text-slate-400' }}">
                    <span class="text-sm leading-none">{{ $tab['icon'] }}</span>
                    <span>{{ $tab['label'] }}</span>
                </button>
                @endforeach
            </div>

            {{-- Scrollable settings panel --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-5">

                {{-- ── BLOCKS / DRAG TAB ───────────────────────── --}}
                <div id="panel-sections" class="tab-panel space-y-4">
                    <div class="p-3 bg-violet-50 dark:bg-violet-950/30 border border-violet-200 dark:border-violet-800 rounded-2xl">
                        <div class="flex items-center gap-2 text-violet-700 dark:text-violet-300 text-xs font-black mb-1">
                            <span>✨ Drag & Drop Reorder</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Drag items or click <b>▲ / ▼</b> to reorder sections. Toggle the <b>👁</b> icon to show or hide blocks.
                        </p>
                    </div>

                    {{-- Dynamic Draggable Blocks List --}}
                    <div id="sections-list-container" class="space-y-2">
                        {{-- Rendered via JS --}}
                    </div>

                    {{-- Add Section Button & Dropdown --}}
                    <div class="relative pt-2">
                        <button type="button" id="btn-add-section" onclick="toggleAddSectionMenu(event)"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border-2 border-dashed border-violet-300 dark:border-violet-700/60 bg-violet-50/60 dark:bg-violet-950/30 text-violet-700 dark:text-violet-300 font-bold text-xs hover:bg-violet-100/80 dark:hover:bg-violet-900/40 hover:border-violet-400 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Section Block</span>
                        </button>

                        <div id="add-section-menu" class="hidden absolute left-0 right-0 top-full mt-2 z-30 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-2 space-y-1 backdrop-blur-md">
                            <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Available Sections</div>
                            <div id="available-sections-list" class="space-y-1 max-h-60 overflow-y-auto">
                                {{-- Rendered via JS --}}
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                            <p class="font-bold text-slate-700 dark:text-slate-300">✏️ Direct Text Editing</p>
                            <p>You can also click directly on any text inside the live preview window (titles, descriptions, badges, store name) to edit it directly in-place!</p>
                        </div>
                    </div>
                </div>

                {{-- ── BRANDING TAB ─────────────────────── --}}
                <div id="panel-branding" class="tab-panel hidden space-y-4">
                    <div class="setting-group">
                        <label class="setting-label">Store Name</label>
                        <input type="text" id="s_store_name" value="{{ $settings['store_name'] }}" oninput="previewUpdate()" class="setting-input" placeholder="My Store" />
                    </div>
                    {{-- Store Logo Upload --}}
                    <div class="setting-group">
                        <label class="setting-label flex items-center justify-between">
                            <span>Store Logo</span>
                            <span class="text-[10px] text-slate-400">PNG, JPG, SVG, WebP</span>
                        </label>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                                <div id="logo-preview-box" class="w-16 h-16 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center bg-white dark:bg-slate-800 overflow-hidden shrink-0">
                                    @if(!empty($settings['logo']))
                                        <img id="logo-preview-img" src="{{ $settings['logo'] }}" alt="Logo" class="w-full h-full object-contain p-1" />
                                    @else
                                        <img id="logo-preview-img" src="" alt="Logo" class="w-full h-full object-contain p-1 hidden" />
                                        <span id="logo-preview-placeholder" class="text-xs text-slate-400 font-bold">No Logo</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <label for="logo-file-input" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold cursor-pointer transition-all shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span id="logo-upload-btn-text">Upload Logo</span>
                                        </label>
                                        <input type="file" id="logo-file-input" accept="image/*" class="hidden" onchange="uploadImageFile(this, 'logo')" />
                                        <button type="button" onclick="removeImage('logo')" id="btn-remove-logo" class="px-2.5 py-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl text-xs font-bold transition-all {{ empty($settings['logo']) ? 'hidden' : '' }}">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">Appears on storefront navigation bar</div>
                                </div>
                            </div>
                            <input type="text" id="s_logo" value="{{ $settings['logo'] ?? '' }}" oninput="previewUpdate()" class="setting-input text-xs font-mono" placeholder="Or paste external logo image URL…" />
                        </div>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Announcement Bar</label>
                        <input type="text" id="s_announcement" value="{{ $settings['announcement'] }}" oninput="previewUpdate()" class="setting-input" placeholder="🎉 Free delivery on orders above ₹999!" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Support Phone</label>
                        <input type="text" id="s_support_phone" value="{{ $settings['support_phone'] }}" oninput="previewUpdate()" class="setting-input" placeholder="+91 98765 43210" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Support Email</label>
                        <input type="email" id="s_support_email" value="{{ $settings['support_email'] }}" oninput="previewUpdate()" class="setting-input" placeholder="support@store.com" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Business Type</label>
                        <input type="text" id="s_business_type" value="{{ $settings['business_type'] }}" oninput="previewUpdate()" class="setting-input" placeholder="Electronics Store, Fashion Boutique…" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Free Delivery Above (₹)</label>
                        <input type="number" id="s_free_delivery_min" value="{{ $settings['free_delivery_min'] }}" oninput="previewUpdate()" class="setting-input" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Standard Shipping Fee (₹)</label>
                        <input type="number" id="s_shipping_fee" value="{{ $settings['shipping_fee'] }}" oninput="previewUpdate()" class="setting-input" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Footer Text</label>
                        <input type="text" id="s_footer_text" value="{{ $settings['footer_text'] }}" oninput="previewUpdate()" class="setting-input" placeholder="© 2025 My Store. All rights reserved." />
                    </div>
                </div>

                {{-- ── COLORS TAB ────────────────────────── --}}
                <div id="panel-colors" class="tab-panel hidden space-y-4">
                    @foreach([
                        ['id' => 's_primary_color',   'label' => 'Primary Color',    'val' => $settings['primary_color']],
                        ['id' => 's_accent_color',    'label' => 'Accent Color',     'val' => $settings['accent_color']],
                        ['id' => 's_text_color',      'label' => 'Text Color',       'val' => $settings['text_color'] ?? '#0f172a'],
                        ['id' => 's_secondary_color', 'label' => 'Secondary / Card', 'val' => $settings['secondary_color']],
                        ['id' => 's_bg_color',        'label' => 'Background',       'val' => $settings['bg_color']],
                    ] as $color)
                    <div class="setting-group">
                        <label class="setting-label">{{ $color['label'] }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="{{ $color['id'] }}" value="{{ $color['val'] }}"
                                   class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5 shrink-0" />
                            <input type="text" id="{{ $color['id'] }}_hex" value="{{ $color['val'] }}"
                                   class="setting-input font-mono text-xs uppercase" maxlength="7" />
                        </div>
                    </div>
                    @endforeach

                    {{-- Preset palettes --}}
                    <div>
                        <label class="setting-label">Preset Palettes</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach([
                                ['Emerald', '#10b981', '#059669'],
                                ['Indigo',  '#6366f1', '#4f46e5'],
                                ['Amber',   '#f59e0b', '#d97706'],
                                ['Rose',    '#f43f5e', '#e11d48'],
                                ['Sky',     '#0ea5e9', '#0284c7'],
                                ['Violet',  '#8b5cf6', '#7c3aed'],
                            ] as [$pname, $p, $a])
                            <button onclick="applyPalette('{{ $p }}', '{{ $a }}')"
                                    class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-400 transition-all text-left">
                                <div class="w-4 h-4 rounded-full shrink-0" style="background: {{ $p }}"></div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $pname }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── TYPOGRAPHY TAB ─────────────────────── --}}
                <div id="panel-typography" class="tab-panel hidden space-y-4">
                    <div class="setting-group">
                        <label class="setting-label">Font Family</label>
                        <select id="s_font_family" onchange="previewUpdate()" class="setting-input">
                            @foreach([
                                'Inter'              => 'Inter (Clean & Modern)',
                                'Plus Jakarta Sans'  => 'Plus Jakarta Sans (Tech)',
                                'Outfit'             => 'Outfit (Geometric / Trendy)',
                                'Space Grotesk'      => 'Space Grotesk (Bold Tech)',
                                'Playfair Display'   => 'Playfair Display (Luxury Editorial)',
                                'DM Sans'            => 'DM Sans (Contemporary)',
                                'Manrope'            => 'Manrope (Corporate Clean)',
                                'Cabinet Grotesk'    => 'Cabinet Grotesk (Display)',
                            ] as $val => $label)
                            <option value="{{ $val }}" {{ $settings['font_family'] === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Corner Roundness</label>
                        <div class="grid grid-cols-3 gap-2 mt-1">
                            @foreach(['8px' => 'Sharp', '16px' => 'Smooth', '24px' => 'Pill'] as $r => $label)
                            <button onclick="setBorderRadius('{{ $r }}')"
                                    class="p-2 text-center rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-500 text-xs font-bold transition-all
                                           {{ $settings['border_radius'] === $r ? 'bg-violet-50 text-violet-600 border-violet-500' : '' }}">
                                {{ $label }}
                                <div class="text-[10px] text-slate-400">{{ $r }}</div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── HERO SECTION TAB ───────────────────── --}}
                <div id="panel-hero" class="tab-panel hidden space-y-4">
                    <div class="setting-group">
                        <label class="setting-label">Badge Tag</label>
                        <input type="text" id="s_hero_badge" value="{{ $settings['hero_badge'] }}" oninput="previewUpdate()" class="setting-input" placeholder="Official ERP Connected Store" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Main Title</label>
                        <textarea id="s_hero_title" rows="3" oninput="previewUpdate()" class="setting-input" placeholder="Catchy headline…">{{ $settings['hero_title'] }}</textarea>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Subtitle Description</label>
                        <textarea id="s_hero_subtitle" rows="3" oninput="previewUpdate()" class="setting-input" placeholder="Brief value proposition…">{{ $settings['hero_subtitle'] }}</textarea>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Primary CTA Button Text</label>
                        <input type="text" id="s_hero_cta_text" value="{{ $settings['hero_cta_text'] }}" oninput="previewUpdate()" class="setting-input" placeholder="Explore Catalog" />
                    </div>
                    {{-- Hero / Lookbook Banner Image --}}
                    <div class="setting-group">
                        <label class="setting-label flex items-center justify-between">
                            <span>Hero / Lookbook Image</span>
                            <span class="text-[10px] text-slate-400">JPG, PNG, WebP (Max 5MB)</span>
                        </label>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                                <div id="hero_image-preview-box" class="w-20 h-16 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center bg-white dark:bg-slate-800 overflow-hidden shrink-0">
                                    @if(!empty($settings['hero_image']))
                                        <img id="hero_image-preview-img" src="{{ $settings['hero_image'] }}" alt="Hero Image" class="w-full h-full object-cover" />
                                    @else
                                        <img id="hero_image-preview-img" src="" alt="Hero Image" class="w-full h-full object-cover hidden" />
                                        <span id="hero_image-preview-placeholder" class="text-[10px] text-slate-400 font-bold text-center px-1">Auto Photo</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <label for="hero_image-file-input" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold cursor-pointer transition-all shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span id="hero_image-upload-btn-text">Upload Photo</span>
                                        </label>
                                        <input type="file" id="hero_image-file-input" accept="image/*" class="hidden" onchange="uploadImageFile(this, 'hero_image')" />
                                        <button type="button" onclick="removeImage('hero_image')" id="btn-remove-hero_image" class="px-2.5 py-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl text-xs font-bold transition-all {{ empty($settings['hero_image']) ? 'hidden' : '' }}">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">Featured lookbook photo / spotlight banner</div>
                                </div>
                            </div>
                            <input type="text" id="s_hero_image" value="{{ $settings['hero_image'] ?? '' }}" oninput="previewUpdate()" class="setting-input text-xs font-mono" placeholder="Or paste image URL (Unsplash / CDN)…" />
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Products Catalog Section</span>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Products Section Title</label>
                        <input type="text" id="s_products_title" value="{{ $settings['products_title'] ?? 'All Products' }}" oninput="previewUpdate()" class="setting-input" placeholder="All Products" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Products Section Subtitle</label>
                        <input type="text" id="s_products_subtitle" value="{{ $settings['products_subtitle'] ?? '' }}" oninput="previewUpdate()" class="setting-input" placeholder="Showing 5 products available for immediate fulfillment" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-black uppercase tracking-wider text-rose-500">🏷️ Promotional Discount Banner</span>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Discount Badge</label>
                        <input type="text" id="s_promo_badge" value="{{ $settings['promo_badge'] ?? 'Limited Time Offer' }}" oninput="previewUpdate()" class="setting-input" placeholder="Limited Time Offer" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Discount Headline</label>
                        <input type="text" id="s_promo_title" value="{{ $settings['promo_title'] ?? 'Mega Festive Sale — Extra 20% Off Storewide!' }}" oninput="previewUpdate()" class="setting-input" placeholder="Mega Festive Sale — Extra 20% Off Storewide!" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Discount Description</label>
                        <textarea id="s_promo_desc" rows="2" oninput="previewUpdate()" class="setting-input" placeholder="Special discount description...">{{ $settings['promo_desc'] ?? 'Stock fulfilled live from certified warehouse with automated GST billing and priority dispatch.' }}</textarea>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Coupon / Promo Code</label>
                        <input type="text" id="s_promo_code" value="{{ $settings['promo_code'] ?? 'CODE: FESTIVE20' }}" oninput="previewUpdate()" class="setting-input font-mono font-bold" placeholder="CODE: FESTIVE20" />
                    </div>
                </div>

                {{-- ── LAYOUT TAB ─────────────────────────── --}}
                <div id="panel-layout" class="tab-panel hidden space-y-4">
                    <div class="setting-group">
                        <label class="setting-label">Header Navigation Style</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach(['regular' => 'Standard Sticky', 'floating' => 'Floating Glass'] as $h => $lbl)
                            <button onclick="setHeader('{{ $h }}')"
                                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-500 text-xs font-bold text-left transition-all">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Product Card Style</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach(['modern' => 'Modern Border', 'elevated' => 'Deep Shadow', 'glass' => 'Glass Frosted', 'minimal' => 'Flat Minimal'] as $c => $lbl)
                            <button onclick="setCardStyle('{{ $c }}')"
                                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-500 text-xs font-bold text-left transition-all">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── SEO TAB ────────────────────────────── --}}
                <div id="panel-seo" class="tab-panel hidden space-y-4">
                    <div class="setting-group">
                        <label class="setting-label">SEO Meta Title</label>
                        <input type="text" id="s_seo_title" value="{{ $settings['seo_title'] }}" oninput="previewUpdate()" class="setting-input" placeholder="Store Name — Quality Products" />
                    </div>
                    <div class="setting-group">
                        <label class="setting-label">Meta Description</label>
                        <textarea id="s_seo_description" rows="3" oninput="previewUpdate()" class="setting-input" placeholder="Shop authentic products online with instant delivery…">{{ $settings['seo_description'] }}</textarea>
                    </div>

                    {{-- Google search preview --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Search Engine Preview</div>
                        <div class="text-xs font-bold text-blue-600 truncate" id="seo-preview-title">{{ $settings['seo_title'] ?: 'Your Store — Online Store' }}</div>
                        <div class="text-[11px] text-emerald-700 truncate">https://yourstore.com</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2" id="seo-preview-desc">{{ $settings['seo_description'] ?: 'Shop authentic products with instant delivery and secure checkout.' }}</div>
                    </div>
                </div>

                {{-- ── SOCIAL TAB ─────────────────────────── --}}
                <div id="panel-social" class="tab-panel hidden space-y-4">
                    @foreach([
                        ['id' => 's_social_instagram', 'label' => 'Instagram Handle', 'placeholder' => '@mystore'],
                        ['id' => 's_social_facebook',  'label' => 'Facebook Page',    'placeholder' => 'fb.com/mystore'],
                        ['id' => 's_social_whatsapp',  'label' => 'WhatsApp Number',  'placeholder' => '+919876543210'],
                    ] as $soc)
                    <div class="setting-group">
                        <label class="setting-label">{{ $soc['label'] }}</label>
                        <input type="text" id="{{ $soc['id'] }}" value="{{ $settings[str_replace('s_', '', $soc['id'])] ?? '' }}" oninput="previewUpdate()" class="setting-input" placeholder="{{ $soc['placeholder'] }}" />
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- ── RIGHT PANEL: Device Bar & Live Iframe Preview ───────── --}}
        <div class="flex-1 flex flex-col gap-3 min-w-0">

            {{-- Device bar --}}
            <div class="flex items-center justify-between bg-white dark:bg-slate-800 rounded-2xl px-4 py-2 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5 bg-slate-100 dark:bg-slate-900 p-1 rounded-xl">
                        @foreach([['desktop','M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],['tablet','M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],['mobile','M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z']] as [$dev, $path])
                        <button onclick="setDevice('{{ $dev }}')" id="dev-{{ $dev }}"
                                class="device-btn w-8 h-8 rounded-lg flex items-center justify-center transition-all
                                       {{ $loop->first ? 'bg-white dark:bg-slate-800 text-violet-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                        </button>
                        @endforeach
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 ml-2">
                        <span>✨ Drag blocks & Click text to edit live</span>
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-400">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span>Live Sync</span>
                    </div>
                    <a href="http://{{ request()->getHost() }}:5173" target="_blank"
                       class="flex items-center gap-1 text-xs font-bold text-violet-600 hover:text-violet-700 bg-violet-50 dark:bg-violet-900/20 px-3 py-1.5 rounded-xl border border-violet-200 dark:border-violet-800">
                        <span>Storefront :5173</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>

            {{-- Preview frame container --}}
            <div id="preview-container" class="flex-1 flex items-start justify-center rounded-[2rem] overflow-hidden bg-slate-900 relative" style="min-height: 500px">
                <div id="iframe-wrapper" class="w-full h-full transition-all duration-300">
                    <iframe
                        id="preview-iframe"
                        src="http://{{ request()->getHost() }}:5173?edit=1&template={{ $templateId }}"
                        class="w-full h-full border-none"
                        style="min-height: 580px"
                        title="Live website preview">
                    </iframe>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.setting-label { display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 0.375rem; }
.setting-input { width: 100%; padding: 0.6rem 0.875rem; border: 1.5px solid; border-radius: 0.875rem; font-size: 0.85rem; outline: none; transition: border-color 0.15s; box-sizing: border-box; }
.dark .setting-input { background: #1e293b; border-color: #334155; color: #f1f5f9; }
.setting-input { background: #f8fafc; border-color: #e2e8f0; color: #0f172a; }
.setting-input:focus { border-color: #8b5cf6; }
.setting-group { display: flex; flex-direction: column; }
.block-item { transition: all 0.15s ease; cursor: grab; }
.block-item:active { cursor: grabbing; }
.block-item.dragging { opacity: 0.5; transform: scale(0.98); }
</style>

<script>
const CSRF = '{{ csrf_token() }}';
const DRAFT_URL    = '/website-templates/draft';
const PUBLISH_URL  = '/website-templates/publish';
const RESET_URL    = '/website-templates/reset';
const UPLOAD_URL   = '/website-templates/upload-image';
let TEMPLATE_ID    = '{{ $templateId }}';
let ACTIVE_TEMPLATE = '{{ $activeTemplate }}';
const ALL_TEMPLATE_DEFAULTS = {!! json_encode($allTemplateDefaults ?? []) !!};

function setInputValue(id, val) {
    const el = document.getElementById(id);
    if (el && val !== undefined) {
        el.value = val;
    }
}

function switchTemplate(newTplId) {
    if (!newTplId) return;
    TEMPLATE_ID = newTplId;

    // 1. Update browser URL history without page reload
    const newUrl = '/website-templates/configurator?template=' + newTplId;
    window.history.pushState({ template: newTplId }, '', newUrl);

    // 2. Update the "Set as Live" button / "Live on Store" status badge
    const isNowActive = newTplId === ACTIVE_TEMPLATE;
    const statusContainer = document.getElementById('template-status-container');
    if (statusContainer) {
        if (isNowActive) {
            statusContainer.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live on Store
                </span>
            `;
        } else {
            statusContainer.innerHTML = `
                <button type="button" onclick="activateTemplate('${newTplId}')" id="btn-set-live"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5">
                    ⚡ Set as Live
                </button>
            `;
        }
    }

    // 3. Apply template signature defaults to sidebar inputs
    const defaults = ALL_TEMPLATE_DEFAULTS[newTplId];
    if (defaults) {
        if (defaults.website_primary_color) {
            setInputValue('s_primary_color', defaults.website_primary_color);
            setInputValue('s_primary_color_hex', defaults.website_primary_color);
        }
        if (defaults.website_accent_color) {
            setInputValue('s_accent_color', defaults.website_accent_color);
            setInputValue('s_accent_color_hex', defaults.website_accent_color);
        }
        if (defaults.website_text_color) {
            setInputValue('s_text_color', defaults.website_text_color);
            setInputValue('s_text_color_hex', defaults.website_text_color);
        }
        if (defaults.website_font_family) {
            setInputValue('s_font_family', defaults.website_font_family);
        }
    }

    // 4. Update iframe directly with new template query param
    const iframe = document.getElementById('preview-iframe');
    if (iframe) {
        try {
            const parsed = new URL(iframe.src);
            parsed.searchParams.set('template', newTplId);
            parsed.searchParams.set('edit', '1');
            iframe.src = parsed.toString();
        } catch(e) {
            iframe.src = 'http://{{ request()->getHost() }}:5173?edit=1&template=' + newTplId;
        }
    }

    // 5. Post real-time message to iframe
    setTimeout(() => {
        previewUpdate();
    }, 250);

    const prettyName = newTplId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    showToast('success', '✨ Switched to ' + prettyName + ' template');
}

async function activateTemplate(id) {
    const targetId = id || TEMPLATE_ID;
    if (!confirm('Activate and set "' + targetId.replace(/_/g, ' ') + '" live on your storefront?')) return;

    const btn = document.getElementById('btn-set-live');
    if (btn) { btn.textContent = 'Activating…'; btn.disabled = true; }

    try {
        const res = await fetch('/website-templates/active', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ template: targetId }),
        });

        const data = await res.json();
        if (data.success) {
            ACTIVE_TEMPLATE = targetId;
            const statusContainer = document.getElementById('template-status-container');
            if (statusContainer) {
                statusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live on Store
                    </span>
                `;
            }
            previewUpdate();
            showToast('success', '🎉 ' + (data.message || 'Template is now live on your store!'));
        } else {
            showToast('error', data.message || 'Activation failed.');
            if (btn) { btn.textContent = '⚡ Set as Live'; btn.disabled = false; }
        }
    } catch(e) {
        // Fallback to regular form submission if network fails
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/website-templates/active';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = CSRF;
        form.appendChild(csrfInput);
        const tplInput = document.createElement('input');
        tplInput.type = 'hidden'; tplInput.name = 'template'; tplInput.value = targetId;
        form.appendChild(tplInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Initial section definitions
const ALL_SECTIONS = [
    { id: 'announcement', name: 'Announcement Bar', icon: '📢', desc: 'Top promo notice', core: true },
    { id: 'header',       name: 'Navigation Header', icon: '🧭', desc: 'Logo, search, cart', core: true },
    { id: 'hero',         name: 'Hero Section',     icon: '🦸', desc: 'Headline, CTA & images', core: true },
    { id: 'categories',   name: 'Category Filter',  icon: '🏷️', desc: 'Quick category pills', core: true },
    { id: 'products',     name: 'Products Catalog', icon: '🛍️', desc: 'Interactive item grid', core: true },
    { id: 'features',     name: 'Trust Badges',     icon: '🛡️', desc: 'Delivery, safety, billing', core: true },
    { id: 'testimonials', name: 'Customer Reviews', icon: '⭐', desc: 'Ratings & buyer quotes', core: false },
    { id: 'promo_banner', name: 'Special Offer Banner', icon: '🏷️', desc: 'Coupon code & promo CTA', core: false },
    { id: 'faq',          name: 'FAQ Accordion',     icon: '❓', desc: 'Questions & expandable answers', core: false },
    { id: 'newsletter',   name: 'VIP Newsletter',    icon: '📬', desc: 'Email subscription card', core: false },
    { id: 'footer',       name: 'Store Footer',     icon: '🦶', desc: 'Links, support & rights', core: true },
];

const CORE_SECTIONS = ['announcement','header','hero','categories','products','features','footer'];

let currentSectionsOrder = {!! json_encode(json_decode($settings['sections_order'] ?? '[]', true) ?: ['announcement','header','hero','categories','products','features','footer']) !!};
let currentSectionsVisibility = {!! json_encode(json_decode($settings['sections_visibility'] ?? '{}', true) ?: ['announcement'=>true,'header'=>true,'hero'=>true,'categories'=>true,'products'=>true,'features'=>true,'footer'=>true]) !!};

// Ensure all core sections exist in the order array
CORE_SECTIONS.forEach(sId => {
    if (!currentSectionsOrder.includes(sId)) currentSectionsOrder.push(sId);
    if (currentSectionsVisibility[sId] === undefined) currentSectionsVisibility[sId] = true;
});

// ── Render Draggable Sections List ──────────────────────────────
function renderSectionsList() {
    const container = document.getElementById('sections-list-container');
    if (!container) return;
    container.innerHTML = '';

    currentSectionsOrder.forEach((secId, idx) => {
        const sec = ALL_SECTIONS.find(s => s.id === secId) || { id: secId, name: secId, icon: '🧱', desc: '', core: false };
        const isVisible = currentSectionsVisibility[secId] !== false;
        const isCore = sec.core ?? CORE_SECTIONS.includes(secId);

        const card = document.createElement('div');
        card.className = `block-item flex items-center justify-between p-3 rounded-2xl border transition-all ${
            isVisible
                ? 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 shadow-sm'
                : 'bg-slate-50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 opacity-60'
        }`;
        card.draggable = true;
        card.dataset.id = secId;

        card.innerHTML = `
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="cursor-grab text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"/></svg>
                </div>
                <span class="text-base leading-none">${sec.icon}</span>
                <div class="truncate">
                    <div class="text-xs font-black text-slate-800 dark:text-slate-200 truncate flex items-center gap-1.5">
                        <span>${sec.name}</span>
                        ${!isCore ? '<span class="text-[9px] font-bold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-950/60 px-1.5 py-0.5 rounded-full uppercase tracking-wider">Custom</span>' : ''}
                    </div>
                    <div class="text-[10px] text-slate-400 truncate">${sec.desc}</div>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button type="button" onclick="moveBlock('${secId}', -1)" ${idx === 0 ? 'disabled' : ''}
                        class="w-6 h-6 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-25" title="Move Up">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" onclick="moveBlock('${secId}', 1)" ${idx === currentSectionsOrder.length - 1 ? 'disabled' : ''}
                        class="w-6 h-6 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-25" title="Move Down">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <button type="button" onclick="toggleVisibility('${secId}')"
                        class="w-6 h-6 rounded-lg flex items-center justify-center ${isVisible ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'}"
                        title="${isVisible ? 'Hide section' : 'Show section'}">
                    ${isVisible
                        ? '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                        : '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>'
                    }
                </button>
                ${!isCore ? `
                <button type="button" onclick="removeSection('${secId}')"
                        class="w-6 h-6 rounded-lg flex items-center justify-center text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40" title="Remove section">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                ` : ''}
            </div>
        `;

        // HTML5 drag handlers
        card.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', secId);
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
        });
        card.addEventListener('dragover', (e) => {
            e.preventDefault();
            card.style.borderTop = '2px solid #8b5cf6';
        });
        card.addEventListener('dragleave', () => {
            card.style.borderTop = '';
        });
        card.addEventListener('drop', (e) => {
            e.preventDefault();
            card.style.borderTop = '';
            const sourceId = e.dataTransfer.getData('text/plain');
            if (sourceId && sourceId !== secId) {
                const sIdx = currentSectionsOrder.indexOf(sourceId);
                const tIdx = currentSectionsOrder.indexOf(secId);
                if (sIdx >= 0 && tIdx >= 0) {
                    const [item] = currentSectionsOrder.splice(sIdx, 1);
                    currentSectionsOrder.splice(tIdx, 0, item);
                    renderSectionsList();
                    previewUpdate();
                }
            }
        });

        container.appendChild(card);
    });
}

function moveBlock(secId, delta) {
    const idx = currentSectionsOrder.indexOf(secId);
    const targetIdx = idx + delta;
    if (targetIdx < 0 || targetIdx >= currentSectionsOrder.length) return;
    const temp = currentSectionsOrder[idx];
    currentSectionsOrder[idx] = currentSectionsOrder[targetIdx];
    currentSectionsOrder[targetIdx] = temp;
    renderSectionsList();
    previewUpdate();
}

function toggleVisibility(secId) {
    currentSectionsVisibility[secId] = !(currentSectionsVisibility[secId] !== false);
    renderSectionsList();
    previewUpdate();
}

// ── Add / Remove Modular Sections ────────────────────────────────
function toggleAddSectionMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('add-section-menu');
    if (!menu) return;
    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        renderAddSectionMenu();
        menu.classList.remove('hidden');
    } else {
        menu.classList.add('hidden');
    }
}

function renderAddSectionMenu() {
    const list = document.getElementById('available-sections-list');
    if (!list) return;
    list.innerHTML = '';

    const available = ALL_SECTIONS.filter(s => !currentSectionsOrder.includes(s.id));
    if (available.length === 0) {
        list.innerHTML = '<div class="p-3 text-center text-xs text-slate-400 font-medium">All available sections have been added!</div>';
        return;
    }

    available.forEach(s => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'w-full flex items-center gap-2.5 p-2 rounded-xl text-left hover:bg-violet-50 dark:hover:bg-violet-950/40 border border-transparent hover:border-violet-200 dark:hover:border-violet-800 transition-all group';
        item.onclick = () => addSection(s.id);
        item.innerHTML = `
            <span class="text-xl leading-none">${s.icon}</span>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 truncate">${s.name}</div>
                <div class="text-[10px] text-slate-400 truncate">${s.desc}</div>
            </div>
            <span class="text-xs font-bold text-violet-600 dark:text-violet-400 opacity-0 group-hover:opacity-100 transition-opacity">+ Add</span>
        `;
        list.appendChild(item);
    });
}

function addSection(secId) {
    const menu = document.getElementById('add-section-menu');
    if (menu) menu.classList.add('hidden');

    if (!currentSectionsOrder.includes(secId)) {
        // Insert right before footer if footer exists, else append
        const footerIdx = currentSectionsOrder.indexOf('footer');
        if (footerIdx >= 0) {
            currentSectionsOrder.splice(footerIdx, 0, secId);
        } else {
            currentSectionsOrder.push(secId);
        }
        currentSectionsVisibility[secId] = true;
        renderSectionsList();
        previewUpdate();
        const sec = ALL_SECTIONS.find(s => s.id === secId);
        showToast('success', '✨ Added ' + (sec ? sec.name : 'section'));
    }
}

function removeSection(secId) {
    const idx = currentSectionsOrder.indexOf(secId);
    if (idx >= 0) {
        currentSectionsOrder.splice(idx, 1);
        delete currentSectionsVisibility[secId];
        renderSectionsList();
        previewUpdate();
        showToast('info', '🗑️ Removed section');
    }
}

// Close add-section menu on outside click
document.addEventListener('click', (e) => {
    const menu = document.getElementById('add-section-menu');
    const btn = document.getElementById('btn-add-section');
    if (menu && !menu.classList.contains('hidden')) {
        if (!menu.contains(e.target) && !btn?.contains(e.target)) {
            menu.classList.add('hidden');
        }
    }
});

// ── Tab switching ────────────────────────────────────────────────
function switchTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('text-violet-600', 'border-violet-500');
        b.classList.add('text-slate-400', 'border-transparent');
    });
    const panel = document.getElementById('panel-' + id);
    if (panel) panel.classList.remove('hidden');
    const btn = document.getElementById('tab-' + id);
    if (btn) { btn.classList.add('text-violet-600', 'border-violet-500'); btn.classList.remove('text-slate-400', 'border-transparent'); }
}

// ── Device preview modes ─────────────────────────────────────────
function setDevice(device) {
    const wrapper = document.getElementById('iframe-wrapper');
    document.querySelectorAll('.device-btn').forEach(b => {
        b.classList.remove('bg-white', 'dark:bg-slate-800', 'text-violet-600', 'shadow-sm');
        b.classList.add('text-slate-400');
    });
    document.getElementById('dev-' + device).classList.add('bg-white', 'dark:bg-slate-800', 'text-violet-600', 'shadow-sm');
    document.getElementById('dev-' + device).classList.remove('text-slate-400');

    if (device === 'mobile')  { wrapper.style.width = '390px'; wrapper.style.margin = 'auto'; wrapper.style.height = '844px'; wrapper.style.borderRadius = '2.5rem'; wrapper.style.overflow = 'hidden'; }
    else if (device === 'tablet') { wrapper.style.width = '768px'; wrapper.style.margin = 'auto'; wrapper.style.height = '100%'; wrapper.style.borderRadius = '1rem'; wrapper.style.overflow = 'hidden'; }
    else { wrapper.style.width = '100%'; wrapper.style.margin = ''; wrapper.style.height = '100%'; wrapper.style.borderRadius = ''; wrapper.style.overflow = ''; }
}

// ── Helper helpers ───────────────────────────────────────────────
function val(id) { const el = document.getElementById(id); if (!el) return null; if (el.type === 'checkbox') return el.checked ? '1' : '0'; return el.value || null; }
function applyPalette(p, a) {
    document.getElementById('s_primary_color').value = p;
    document.getElementById('s_primary_color_hex').value = p;
    document.getElementById('s_accent_color').value = a;
    document.getElementById('s_accent_color_hex').value = a;
    previewUpdate();
}
function setBorderRadius(r) { previewUpdate(); }
function setHeader(h) { previewUpdate(); }
function setCardStyle(c) { previewUpdate(); }

// Sync hex inputs with color pickers
['s_primary_color','s_accent_color','s_text_color','s_secondary_color','s_bg_color'].forEach(id => {
    const picker = document.getElementById(id);
    const hex    = document.getElementById(id + '_hex');
    if (picker && hex) {
        picker.addEventListener('input', () => { hex.value = picker.value; previewUpdate(); });
        hex.addEventListener('input',   () => { if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) { picker.value = hex.value; previewUpdate(); } });
    }
});

// SEO live preview
function updateSeoPreview() {
    const t = document.getElementById('s_seo_title');
    const d = document.getElementById('s_seo_description');
    const pt = document.getElementById('seo-preview-title');
    const pd = document.getElementById('seo-preview-desc');
    if (t && pt) pt.textContent = t.value || 'Your Store — Online Store';
    if (d && pd) pd.textContent = d.value || 'Shop authentic products…';
}
document.getElementById('s_seo_title')?.addEventListener('input', updateSeoPreview);
document.getElementById('s_seo_description')?.addEventListener('input', updateSeoPreview);

// ── Real-Time PostMessage Live Preview ─────────────────────────
function previewUpdate() {
    const settings = collectSettings();
    try {
        const iframe = document.getElementById('preview-iframe');
        if (iframe?.contentWindow) {
            iframe.contentWindow.postMessage({ type: 'TREXO_THEME_UPDATE', settings }, '*');
        }
    } catch(e) {}
}

const inlineTextOverrides = {};

function collectSettings() {
    return {
        template:         TEMPLATE_ID,
        store_name:       val('s_store_name'),
        logo:             val('s_logo'),
        hero_image:       val('s_hero_image'),
        announcement:     val('s_announcement'),
        primary_color:    val('s_primary_color'),
        accent_color:     val('s_accent_color'),
        text_color:       val('s_text_color'),
        secondary_color:  val('s_secondary_color'),
        bg_color:         val('s_bg_color'),
        font_family:      val('s_font_family'),
        hero_title:       val('s_hero_title'),
        hero_subtitle:    val('s_hero_subtitle'),
        hero_badge:       val('s_hero_badge'),
        hero_cta_text:    val('s_hero_cta_text'),
        products_title:   val('s_products_title') || inlineTextOverrides['products_title'] || '',
        products_subtitle: val('s_products_subtitle') || inlineTextOverrides['products_subtitle'] || '',
        promo_badge:      val('s_promo_badge') || inlineTextOverrides['promo_badge'] || '',
        promo_title:      val('s_promo_title') || inlineTextOverrides['promo_title'] || '',
        promo_desc:       val('s_promo_desc') || inlineTextOverrides['promo_desc'] || '',
        promo_code:       val('s_promo_code') || inlineTextOverrides['promo_code'] || '',
        support_phone:    val('s_support_phone'),
        support_email:    val('s_support_email'),
        free_delivery_min: val('s_free_delivery_min'),
        shipping_fee:     val('s_shipping_fee'),
        otp_required:     val('s_otp_required') === '1',
        seo_title:        val('s_seo_title'),
        seo_description:  val('s_seo_description'),
        social_instagram: val('s_social_instagram'),
        social_facebook:  val('s_social_facebook'),
        social_whatsapp:  val('s_social_whatsapp'),
        footer_text:      val('s_footer_text'),
        sections_order:   currentSectionsOrder,
        sections_visibility: currentSectionsVisibility,
        ...inlineTextOverrides,
    };
}

// ── Handle incoming messages from preview iframe (Direct Inline Text Edit) ──
window.addEventListener('message', (event) => {
    if (!event.data || typeof event.data !== 'object') return;

    if (event.data.type === 'TREXO_INLINE_TEXT_UPDATE') {
        const key = event.data.key;
        const val = event.data.value;
        inlineTextOverrides[key] = val;
        const input = document.getElementById('s_' + key);
        if (input) {
            input.value = val;
            // Visual pulse highlight
            input.style.borderColor = '#8b5cf6';
            input.style.boxShadow = '0 0 0 4px rgba(139, 92, 246, 0.25)';
            setTimeout(() => {
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }, 1200);
        }
        showToast('success', '✏️ Updated text in preview');
    } else if (event.data.type === 'TREXO_SECTIONS_ORDER_UPDATE') {
        if (Array.isArray(event.data.order)) {
            currentSectionsOrder = event.data.order;
            renderSectionsList();
            showToast('success', '↕️ Reordered blocks');
        }
    } else if (event.data.type === 'TREXO_SECTIONS_VISIBILITY_UPDATE') {
        if (event.data.visibility) {
            currentSectionsVisibility = event.data.visibility;
            renderSectionsList();
        }
    }
});

// ── Save Draft ───────────────────────────────────────────────────
async function saveDraft() {
    const btn = document.getElementById('btn-draft');
    btn.textContent = 'Saving…'; btn.disabled = true;
    const settings = collectSettings();
    const body = {};
    Object.entries(settings).forEach(([k, v]) => {
        if (k === 'sections_order' || k === 'sections_visibility') {
            body['website_' + k] = JSON.stringify(v);
        } else {
            body['website_' + k] = v;
        }
    });
    body['website_template_draft'] = TEMPLATE_ID;

    try {
        const res = await fetch(DRAFT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        showToast(data.success ? 'success' : 'error', data.message || 'Draft saved!');
    } catch(e) {
        showToast('error', 'Could not save draft. Please try again.');
    }
    btn.innerHTML = '<svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg> Save Draft';
    btn.disabled = false;
}

// ── Publish ──────────────────────────────────────────────────────
async function publishTemplate() {
    if (!confirm('Publish this template and customized blocks live to your storefront?')) return;
    const btn = document.getElementById('btn-publish');
    btn.textContent = 'Publishing…'; btn.disabled = true;

    // First save the current draft settings so everything is stored
    await saveDraft();

    try {
        const res = await fetch(PUBLISH_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ template: TEMPLATE_ID }),
        });
        const data = await res.json();
        if (data.success) {
            showToast('success', '🎉 ' + (data.message || 'Published successfully!'));
        } else {
            showToast('error', data.message || 'Publish failed.');
        }
    } catch(e) {
        showToast('error', 'Publish request failed.');
    }
    btn.innerHTML = '<svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Publish Live';
    btn.disabled = false;
}

// ── Reset ────────────────────────────────────────────────────────
async function resetSettings() {
    if (!confirm('Reset this template to its default settings?')) return;
    try {
        const res = await fetch(RESET_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ template: TEMPLATE_ID }),
        });
        const data = await res.json();
        if (data.success && data.defaults) {
            Object.entries(data.defaults).forEach(([key, value]) => {
                const field = key.replace('website_', 's_');
                const el = document.getElementById(field);
                if (el) { el.value = value; }
                const hex = document.getElementById(field + '_hex');
                if (hex) { hex.value = value; }
            });
            currentSectionsOrder = ['announcement','header','hero','categories','products','features','footer'];
            currentSectionsVisibility = {'announcement':true,'header':true,'hero':true,'categories':true,'products':true,'features':true,'footer':true};
            renderSectionsList();
            previewUpdate();
            showToast('success', 'Settings reset to defaults.');
        }
    } catch(e) {
        showToast('error', 'Reset failed.');
    }
}

// ── Image Upload Handling ─────────────────────────────────────────
async function uploadImageFile(inputEl, type) {
    const file = inputEl.files?.[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        showToast('error', 'Image size must be less than 5MB.');
        inputEl.value = '';
        return;
    }

    const btnText = document.getElementById(`${type}-upload-btn-text`);
    const origText = btnText ? btnText.textContent : 'Upload';
    if (btnText) btnText.textContent = 'Uploading…';

    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', type);

    try {
        const res = await fetch(UPLOAD_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await res.json();
        if (data.success && data.url) {
            const inputField = document.getElementById(`s_${type}`);
            if (inputField) inputField.value = data.url;

            const previewImg = document.getElementById(`${type}-preview-img`);
            const placeholder = document.getElementById(`${type}-preview-placeholder`);
            const removeBtn = document.getElementById(`btn-remove-${type}`);

            if (previewImg) {
                previewImg.src = data.url;
                previewImg.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
            if (removeBtn) removeBtn.classList.remove('hidden');

            previewUpdate();
            showToast('success', '✨ Image uploaded successfully!');
        } else {
            showToast('error', data.message || 'Image upload failed.');
        }
    } catch (e) {
        showToast('error', 'Upload error. Please try again.');
    } finally {
        if (btnText) btnText.textContent = origText;
        inputEl.value = '';
    }
}

function removeImage(type) {
    const inputField = document.getElementById(`s_${type}`);
    if (inputField) inputField.value = '';

    const previewImg = document.getElementById(`${type}-preview-img`);
    const placeholder = document.getElementById(`${type}-preview-placeholder`);
    const removeBtn = document.getElementById(`btn-remove-${type}`);
    const fileInput = document.getElementById(`${type}-file-input`);

    if (previewImg) {
        previewImg.src = '';
        previewImg.classList.add('hidden');
    }
    if (placeholder) placeholder.classList.remove('hidden');
    if (removeBtn) removeBtn.classList.add('hidden');
    if (fileInput) fileInput.value = '';

    previewUpdate();
    showToast('info', 'Image removed.');
}

// Sync typed/pasted image URLs with preview box
['logo', 'hero_image'].forEach(type => {
    const input = document.getElementById('s_' + type);
    if (!input) return;
    input.addEventListener('input', () => {
        const url = input.value.trim();
        const previewImg = document.getElementById(`${type}-preview-img`);
        const placeholder = document.getElementById(`${type}-preview-placeholder`);
        const removeBtn = document.getElementById(`btn-remove-${type}`);
        if (url) {
            if (previewImg) {
                previewImg.src = url;
                previewImg.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
            if (removeBtn) removeBtn.classList.remove('hidden');
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            if (placeholder) placeholder.classList.remove('hidden');
            if (removeBtn) removeBtn.classList.add('hidden');
        }
    });
});

// ── Toast notification ───────────────────────────────────────────
function showToast(type, msg) {
    const toast = document.getElementById('toast');
    const inner = document.getElementById('toast-inner');
    const isSuccess = type === 'success';
    inner.className = `px-5 py-3 rounded-2xl shadow-2xl text-sm font-bold flex items-center gap-2 max-w-sm ${isSuccess ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'}`;
    inner.textContent = msg;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

// Initial render
document.addEventListener('DOMContentLoaded', () => {
    renderSectionsList();
});
renderSectionsList();
</script>
@endsection
