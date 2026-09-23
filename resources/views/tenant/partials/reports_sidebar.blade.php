@php
    $isIndex = request()->routeIs('tenant.businessreport.index');
    // If not on index, clicking categories should link to the index page with ?category=...
@endphp

<!-- Ensure Tabler Icons stylesheet is loaded -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<!-- Left Categories Sub-Sidebar -->
<aside class="hidden lg:flex w-64 flex-shrink-0 bg-slate-50/50 dark:bg-slate-900/40 backdrop-blur-md border-r border-slate-200/40 dark:border-slate-800/50 flex flex-col h-full z-10">
    <!-- Sub-sidebar search -->
    <div class="p-4 border-b border-slate-200/40 dark:border-slate-800/50">
        <div class="relative flex items-center group">
            <i class="ti ti-search absolute left-3.5 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-600 transition-colors" style="font-size: 16px;"></i>
            <input type="text" id="reportSearch" placeholder="Search reports..." 
                   @if($isIndex)
                       oninput="applyFilters()"
                   @else
                       onfocus="window.location.href='{{ route('tenant.businessreport.index') }}'"
                   @endif
                   class="w-full pl-9 pr-4 py-2 bg-white/70 dark:bg-slate-900/60 hover:bg-white dark:hover:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-blue-500/50 rounded-xl text-xs font-bold outline-none transition-all dark:text-white">
        </div>
    </div>

    <!-- Sidebar Navigation Items -->
    <div class="flex-1 overflow-y-auto py-4 space-y-4 custom-scrollbar">
        <!-- Group Overview -->
        <div class="space-y-1">
            <p class="px-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Overview</p>
            <a @if($isIndex) onclick="setCategory('all', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'all']) }}" @endif 
               class="sub-nav-item @if($active == 'all') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center"><i class="ti ti-layout-grid text-base"></i></span>
                All Reports
                <span class="ml-auto px-2 py-0.5 text-[10px] bg-blue-100/55 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full font-black">34</span>
            </a>
            <a @if($isIndex) onclick="setCategory('ai', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'ai']) }}" @endif 
               class="sub-nav-item @if($active == 'ai') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-500 flex items-center justify-center"><i class="ti ti-sparkles text-base"></i></span>
                AI Smart Reports
                <span class="ml-auto px-2 py-0.5 text-[10px] bg-gradient-to-r from-violet-500/20 to-blue-500/20 text-violet-600 dark:text-violet-400 rounded-full font-black">AI</span>
            </a>
        </div>

        <!-- Group Financial -->
        <div class="space-y-1">
            <p class="px-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Financial</p>
            <a @if($isIndex) onclick="setCategory('finance', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'finance']) }}" @endif 
               class="sub-nav-item @if($active == 'finance') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center"><i class="ti ti-coin text-base"></i></span>
                Financial Reports
            </a>
            <a @if($isIndex) onclick="setCategory('tax', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'tax']) }}" @endif 
               class="sub-nav-item @if($active == 'tax') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center"><i class="ti ti-receipt text-base"></i></span>
                Tax Reports
            </a>
            <a @if($isIndex) onclick="setCategory('payment', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'payment']) }}" @endif 
               class="sub-nav-item @if($active == 'payment') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center"><i class="ti ti-credit-card text-base"></i></span>
                Payment Reports
            </a>
        </div>

        <!-- Group Operations -->
        <div class="space-y-1">
            <p class="px-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Operations</p>
            <a @if($isIndex) onclick="setCategory('sales', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'sales']) }}" @endif 
               class="sub-nav-item @if($active == 'sales') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center"><i class="ti ti-chart-line text-base"></i></span>
                Sales & Purchase
            </a>
            <a @if($isIndex) onclick="setCategory('inventory', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'inventory']) }}" @endif 
               class="sub-nav-item @if($active == 'inventory') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-500 flex items-center justify-center"><i class="ti ti-box text-base"></i></span>
                Inventory & Stock
            </a>
            <a @if($isIndex) onclick="setCategory('hr', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'hr']) }}" @endif 
               class="sub-nav-item @if($active == 'hr') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center"><i class="ti ti-users text-base"></i></span>
                HR & Customers
            </a>
        </div>

        <!-- Group Analytics -->
        <div class="space-y-1">
            <p class="px-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Analytics</p>
            <a @if($isIndex) onclick="setCategory('analytics', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'analytics']) }}" @endif 
               class="sub-nav-item @if($active == 'analytics') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-500 flex items-center justify-center"><i class="ti ti-presentation-analytics text-base"></i></span>
                Dashboard Analytics
                <span class="ml-auto px-2 py-0.5 text-[8px] bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-md font-black">NEW</span>
            </a>
            <a @if($isIndex) onclick="setCategory('product', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'product']) }}" @endif 
               class="sub-nav-item @if($active == 'product') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-500 flex items-center justify-center"><i class="ti ti-barcode text-base"></i></span>
                Product Reports
            </a>
            <a @if($isIndex) onclick="setCategory('branch', this)" @else href="{{ route('tenant.businessreport.index', ['category' => 'branch']) }}" @endif 
               class="sub-nav-item @if($active == 'branch') active @endif flex items-center gap-3 px-5 py-2.5 text-xs font-bold transition-all cursor-pointer border-l-2 text-slate-600 dark:text-slate-400">
                <span class="w-7 h-7 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center"><i class="ti ti-building-store text-base"></i></span>
                Branch Reports
            </a>
        </div>
    </div>
</aside>
