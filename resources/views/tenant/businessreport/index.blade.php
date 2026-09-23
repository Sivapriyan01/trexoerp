@extends('layouts.tenant')

@section('title', 'Business Reports')
@section('page-title', 'Business Reports Hub')

@section('content')
<!-- Google Fonts & Font Awesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* Navigation Link styling overrides */
    .sub-nav-item {
        border-left-color: transparent;
        font-family: 'Sora', sans-serif;
    }
    .sub-nav-item:hover {
        background: rgba(156, 163, 175, 0.05);
    }
    .sub-nav-item.active {
        background: rgba(59, 130, 246, 0.08);
        color: #3b82f6 !important;
        border-left-color: #3b82f6;
    }
    .dark .sub-nav-item.active {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa !important;
        border-left-color: #60a5fa;
    }
    .filter-pill.active {
        background: rgba(59, 130, 246, 0.1) !important;
        border-color: rgba(59, 130, 246, 0.4) !important;
        color: #3b82f6 !important;
    }
    .dark .filter-pill.active {
        background: rgba(59, 130, 246, 0.15) !important;
        border-color: rgba(59, 130, 246, 0.4) !important;
        color: #60a5fa !important;
    }
    
    /* Animation definition */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    
    /* Custom mini scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.25);
        border-radius: 99px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.4);
    }

    .report-card {
        font-family: 'Sora', sans-serif;
    }
</style>

<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => request('category', 'all')])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-8 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/5">
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white" style="font-family:'Sora', sans-serif;">Reports & Analytics</h1>
                <p class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1" style="font-family:'Sora', sans-serif;">Dashboard &gt; Reports</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Hidden form that gets submitted with selected month/year --}}
                <form method="GET" action="{{ route('tenant.businessreport.index') }}" id="periodForm">
                    <input type="hidden" name="month" id="pickerMonth" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year"  id="pickerYear"  value="{{ $selectedYear }}">
                </form>

                {{-- Custom Period Picker --}}
                <div class="relative" id="periodPickerWrap">
                    <button type="button" id="periodPickerBtn" onclick="togglePeriodPicker()"
                            class="flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 shadow-sm hover:border-slate-300 dark:hover:border-slate-600 hover:shadow transition-all select-none">
                        <i class="fa-solid fa-calendar-days text-slate-400 dark:text-slate-500 text-[11px]"></i>
                        <span id="periodLabel">{{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }} {{ $selectedYear }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200" id="periodChevron"></i>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div id="periodDropdown"
                         class="hidden absolute right-0 top-full mt-2 z-50 w-72 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
                        {{-- Year navigation --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                            <button type="button" onclick="shiftYear(-1)"
                                    class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            <span id="pickerYearLabel" class="text-sm font-black text-slate-800 dark:text-white tracking-tight"></span>
                            <button type="button" onclick="shiftYear(1)"
                                    class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                        {{-- Month grid --}}
                        <div class="grid grid-cols-4 gap-1.5 p-3" id="monthGrid"></div>
                    </div>
                </div>

                <a href="{{ route('tenant.businessreport.analysis') }}" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-violet-500/20 hover:from-violet-700 hover:to-indigo-700 hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Analysis
                </a>
                <a href="#" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-500/10 hover:bg-blue-700 hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-download"></i>
                    Export All
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($stats as $index => $stat)
            @php
                $statColors = [
                    0 => ['color' => 'blue',   'icon' => 'fa-chart-line'],
                    1 => ['color' => 'emerald', 'icon' => 'fa-bag-shopping'],
                    2 => ['color' => 'amber',  'icon' => 'fa-boxes-stacked'],
                    3 => ['color' => 'rose',   'icon' => 'fa-rotate-left'],
                ];
                $sc = $statColors[$index] ?? ['color' => 'blue', 'icon' => 'fa-chart-line'];
            @endphp
            <div class="stat-card relative overflow-hidden bg-white/70 dark:bg-slate-900/50 border border-slate-200/55 dark:border-slate-800/50 p-6 rounded-2xl transition-all duration-300 hover:scale-[1.01] hover:border-slate-300 dark:hover:border-slate-700 shadow-sm">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-{{ $sc['color'] }}-500/5 dark:bg-{{ $sc['color'] }}-500/10 rounded-full"></div>
                <div class="w-10 h-10 rounded-xl bg-{{ $sc['color'] }}-500/10 text-{{ $sc['color'] }}-500 dark:text-{{ $sc['color'] }}-400 flex items-center justify-center text-sm mb-4">
                    <i class="fa-solid {{ $sc['icon'] }}"></i>
                </div>
                <div class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $stat['label'] }}</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 font-mono" style="font-family:'DM Mono', monospace;">{{ $stat['value'] }}</div>
                <div class="text-xs font-bold flex items-center gap-1 mt-2
                    {{ $stat['trend'] === 'up' ? 'text-emerald-500' : ($stat['trend'] === 'down' ? 'text-rose-500' : 'text-slate-400') }}">
                    @if ($stat['trend'] === 'up')
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    @elseif ($stat['trend'] === 'down')
                        <i class="fa-solid fa-arrow-trend-down"></i>
                    @endif
                    {{ $stat['change'] }}
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filter Pill Header -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 border-t border-slate-200/40 dark:border-slate-800/40 pt-6">
            <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white">
                    All Reports <span class="ml-1 text-xs font-bold text-slate-400" id="reportCount">({{ count($reports) }})</span>
                </h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Click any report card to view details</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full xl:w-auto overflow-hidden">
                <!-- Mobile-friendly Integrated Search Bar -->
                <div class="relative flex items-center group w-full sm:w-64 flex-shrink-0">
                    <i class="ti ti-search absolute left-3.5 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-600 transition-colors" style="font-size: 14px;"></i>
                    <input type="text" id="mainReportSearch" placeholder="Search 34 reports..." oninput="applyFilters()"
                           class="w-full pl-9 pr-4 py-2 bg-white/80 dark:bg-slate-900/60 hover:bg-white dark:hover:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-blue-500/50 rounded-xl text-xs font-bold outline-none transition-all dark:text-white shadow-sm">
                </div>

                <!-- Horizontal Scrollable Category Filter Pills -->
                <div class="flex gap-1.5 overflow-x-auto pb-1 max-w-full custom-scrollbar scroll-smooth whitespace-nowrap flex-1">
                    <span onclick="setPill('all', this)" class="filter-pill active px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">All</span>
                    <span onclick="setPill('ai', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">AI</span>
                    <span onclick="setPill('finance', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Financial</span>
                    <span onclick="setPill('tax', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Tax</span>
                    <span onclick="setPill('payment', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Payment</span>
                    <span onclick="setPill('sales', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Sales & Purchase</span>
                    <span onclick="setPill('inventory', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Inventory</span>
                    <span onclick="setPill('hr', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">HR & Customers</span>
                    <span onclick="setPill('analytics', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Analytics</span>
                    <span onclick="setPill('product', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Products</span>
                    <span onclick="setPill('branch', this)" class="filter-pill px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-bold cursor-pointer transition-all shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex-shrink-0">Branch</span>
                </div>
            </div>
        </div>

        <!-- Reports Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="reportsContainer">
            @foreach ($reports as $index => $report)
            @php
                $colorMap = [
                    'blue' => 'from-blue-500/10 to-blue-500/5 text-blue-500 dark:text-blue-400 border-blue-500/20',
                    'cyan' => 'from-cyan-500/10 to-cyan-500/5 text-cyan-500 dark:text-cyan-400 border-cyan-500/20',
                    'green' => 'from-emerald-500/10 to-emerald-500/5 text-emerald-500 dark:text-emerald-400 border-emerald-500/20',
                    'amber' => 'from-amber-500/10 to-amber-500/5 text-amber-500 dark:text-amber-400 border-amber-500/20',
                    'pink' => 'from-pink-500/10 to-pink-500/5 text-pink-500 dark:text-pink-400 border-pink-500/20',
                    'orange' => 'from-orange-500/10 to-orange-500/5 text-orange-500 dark:text-orange-400 border-orange-500/20',
                    'purple' => 'from-violet-500/10 to-violet-500/5 text-violet-500 dark:text-violet-400 border-violet-500/20',
                    'red' => 'from-rose-500/10 to-rose-500/5 text-rose-500 dark:text-rose-400 border-rose-500/20',
                ];
                $bgClass = $colorMap[$report['color']] ?? $colorMap['blue'];
                $isAi = !empty($report['ai']);
            @endphp
            <a href="{{ route($report['route']) }}"
               class="report-card cursor-pointer group flex flex-col justify-between p-5 rounded-2xl border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg
                      {{ $isAi 
                         ? 'bg-gradient-to-br from-violet-50/70 to-blue-50/70 dark:from-violet-950/10 dark:to-blue-950/10 border-violet-300 dark:border-violet-500/20 shadow-violet-50/30 dark:shadow-none' 
                         : 'bg-white/60 dark:bg-slate-900/40 backdrop-blur-md border-slate-200/50 dark:border-slate-800/50 hover:border-slate-300 dark:hover:border-slate-700' }}"
               data-categories='@json($report['cat'])'
               data-name="{{ strtolower($report['name']) }}"
               data-desc="{{ strtolower($report['desc']) }}"
               data-tag="{{ strtolower($report['tag']) }}"
               style="animation: fadeUp 0.35s ease both; animation-delay: {{ $index * 0.02 }}s;">

                <!-- Top Row (Icon & Link Arrow) -->
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $bgClass }} flex items-center justify-center text-base">
                        <i class="fa-solid {{ $report['icon'] }}"></i>
                    </div>
                    <span class="text-slate-400 dark:text-slate-600 text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>
                </div>

                <!-- Middle Content -->
                <div class="mt-4 flex-1">
                    <div class="text-[13px] font-black text-slate-950 dark:text-white tracking-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        {{ $report['name'] }}
                    </div>
                    <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
                        {{ $report['desc'] }}
                    </div>
                </div>

                <!-- Bottom Tag Row -->
                <div class="border-t border-slate-200/40 dark:border-slate-800/40 pt-3 mt-4 flex items-center justify-between">
                    <span class="text-[9.5px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-gradient-to-br {{ $bgClass }}">
                        {{ $report['tag'] }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
    window.currentCategory = 'all';

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        if (categoryParam) {
            // Find sidebar element that corresponds to the category
            const element = Array.from(document.querySelectorAll('.sub-nav-item')).find(item => {
                const clickAttr = item.getAttribute('onclick');
                return clickAttr && clickAttr.includes(`'${categoryParam}'`);
            });
            if (element) {
                setCategory(categoryParam, element);
            }
        }
    });

    function setCategory(category, element) {
        window.currentCategory = category;
        
        // Update active class in categories list
        document.querySelectorAll('.sub-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        if (element) {
            element.classList.add('active');
        }
        
        // Sync the corresponding pill active state
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.classList.remove('active');
            if (pill.getAttribute('onclick').includes(`'${category}'`)) {
                pill.classList.add('active');
            }
        });
        
        applyFilters();
    }

    function setPill(category, element) {
        window.currentCategory = category;
        
        // Update active class in pill list
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        if (element) {
            element.classList.add('active');
        }
        
        // Sync the corresponding sidebar category item
        document.querySelectorAll('.sub-nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('onclick').includes(`'${category}'`)) {
                item.classList.add('active');
            }
        });
        
        applyFilters();
    }

    function applyFilters() {
        const searchInput = document.getElementById('reportSearch');
        const mainSearchInput = document.getElementById('mainReportSearch');
        
        let searchVal = '';
        if (mainSearchInput && document.activeElement === mainSearchInput) {
            searchVal = mainSearchInput.value.toLowerCase().trim();
            if (searchInput) searchInput.value = mainSearchInput.value;
        } else if (searchInput && document.activeElement === searchInput) {
            searchVal = searchInput.value.toLowerCase().trim();
            if (mainSearchInput) mainSearchInput.value = searchInput.value;
        } else {
            // Initial load or programmatically triggered
            const val1 = mainSearchInput ? mainSearchInput.value.toLowerCase().trim() : '';
            const val2 = searchInput ? searchInput.value.toLowerCase().trim() : '';
            searchVal = val1 || val2;
            if (mainSearchInput) mainSearchInput.value = searchVal;
            if (searchInput) searchInput.value = searchVal;
        }

        const activeCategory = window.currentCategory || 'all';
        
        let visibleCount = 0;
        const cards = document.querySelectorAll('.report-card');
        
        cards.forEach((card, i) => {
            const categories = JSON.parse(card.getAttribute('data-categories'));
            const name = card.getAttribute('data-name');
            const desc = card.getAttribute('data-desc');
            const tag = card.getAttribute('data-tag');
            
            const matchesCategory = (activeCategory === 'all' || categories.includes(activeCategory));
            const matchesSearch = !searchVal || (name.includes(searchVal) || desc.includes(searchVal) || tag.includes(searchVal));
            
            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
                // Reset animation
                card.style.animation = 'none';
                card.offsetHeight; // Trigger reflow
                card.style.animation = 'fadeUp 0.3s ease both';
                card.style.animationDelay = `${(visibleCount * 0.02).toFixed(2)}s`;
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        const countBadge = document.getElementById('reportCount');
        if (countBadge) {
            countBadge.textContent = `(${visibleCount})`;
        }
    }

    // ── Period Picker ───────────────────────────────────────────────────────
    const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let _pickerOpen = false;
    let _pickerYear  = parseInt(document.getElementById('pickerYear').value);
    let _pickerMonth = parseInt(document.getElementById('pickerMonth').value);

    function renderPicker() {
        document.getElementById('pickerYearLabel').textContent = _pickerYear;

        const grid = document.getElementById('monthGrid');
        grid.innerHTML = '';
        MONTHS.forEach((m, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isActive = (i + 1 === _pickerMonth);
            btn.className = [
                'py-1.5 rounded-lg text-[11px] font-bold transition-all',
                isActive
                    ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
            ].join(' ');
            btn.textContent = m;
            btn.onclick = () => selectPeriod(i + 1);
            grid.appendChild(btn);
        });
    }

    function togglePeriodPicker() {
        _pickerOpen = !_pickerOpen;
        const dd = document.getElementById('periodDropdown');
        const ch = document.getElementById('periodChevron');
        dd.classList.toggle('hidden', !_pickerOpen);
        ch.style.transform = _pickerOpen ? 'rotate(180deg)' : '';
        if (_pickerOpen) renderPicker();
    }

    function shiftYear(delta) {
        const now = new Date().getFullYear();
        const next = _pickerYear + delta;
        if (next < now - 4 || next > now) return;
        _pickerYear = next;
        renderPicker();
    }

    function selectPeriod(month) {
        _pickerMonth = month;
        document.getElementById('pickerMonth').value = month;
        document.getElementById('pickerYear').value  = _pickerYear;
        document.getElementById('periodForm').submit();
    }

    // Close picker when clicking outside
    document.addEventListener('click', (e) => {
        if (_pickerOpen && !document.getElementById('periodPickerWrap').contains(e.target)) {
            _pickerOpen = false;
            document.getElementById('periodDropdown').classList.add('hidden');
            document.getElementById('periodChevron').style.transform = '';
        }
    });
</script>
@endsection