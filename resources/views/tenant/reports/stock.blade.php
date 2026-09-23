@extends('layouts.tenant')
@section('title', 'Stock Report & Valuation')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Premium visual overrides that blend perfectly with the layout's dark mode and standard look */
    .stock-page-wrap {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem 0;
    }

    /* ── Header ── */
    .page-header {
        display: flex; 
        align-items: center;
        justify-content: space-between; 
        margin-bottom: 1.5rem;
    }
    .page-header h1 { 
        font-size: 22px; 
        font-weight: 800; 
        color: #1e293b; 
        font-family: 'Sora', sans-serif;
    }
    .dark .page-header h1 {
        color: #f8fafc;
    }
    .breadcrumb { 
        font-size: 11px; 
        font-weight: 700;
        color: #64748b; 
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 2px; 
    }
    .breadcrumb a { 
        color: #3b82f6; 
        text-decoration: none; 
    }

    .btn {
        display: inline-flex; 
        align-items: center; 
        gap: 8px;
        padding: 10px 20px; 
        border-radius: 12px; 
        font-size: 13px;
        font-weight: 600; 
        cursor: pointer; 
        border: none;
        text-decoration: none; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Sora', sans-serif;
    }
    .btn:hover { 
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .btn:active {
        transform: translateY(0);
    }
    .btn-primary { 
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
        color: #fff; 
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
    }
    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    .btn-outline { 
        background: #fff; 
        color: #334155; 
        border: 1px solid #e2e8f0; 
    }
    .dark .btn-outline {
        background: #1e293b;
        color: #cbd5e1;
        border-color: #334155;
    }
    .dark .btn-outline:hover {
        background: #334155;
    }

    .btn-group { 
        display: flex; 
        gap: 10px; 
    }

    /* ── Filter Bar ── */
    .filter-bar {
        background: #fff; 
        border: 1px solid #e2e8f0;
        border-radius: 16px; 
        padding: 18px;
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .dark .filter-bar {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(51, 65, 85, 0.5);
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-bar label { 
        font-size: 10px; 
        color: #64748b; 
        font-weight: 700; 
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .filter-bar select, .filter-bar input {
        padding: 10px 14px; 
        border-radius: 10px; 
        border: 1px solid #cbd5e1; 
        font-size: 13px; 
        font-weight: 500;
        background-color: #fff;
        color: #334155;
        outline: none;
        transition: all 0.15s;
    }
    .dark .filter-bar select, .dark .filter-bar input {
        background-color: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }
    .filter-bar select:focus, .filter-bar input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* ── Premium Glass Cards ── */
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dark .glass-card {
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Custom scrollbar matching standard */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 100px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }

    /* ── Print Media Styles ── */
    @media print {
        aside, 
        header,
        .ai-chatbot-container,
        nav, 
        .btn-group, 
        .filter-bar, 
        button, 
        a.btn,
        .page-header .btn-group,
        footer,
        .blob {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
        }

        body, html {
            background: #fff !important;
            color: #000 !important;
            font-family: 'Segoe UI', sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            height: auto !important;
        }

        /* Standardize print container behavior */
        body > div.flex.h-screen,
        body > div.flex.h-screen > div.flex-1.flex.flex-col,
        body > div.flex.h-screen > div.flex-1.flex.flex-col > main,
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\],
        main > div.flex.h-\[calc\(100vh-3\.5rem\)\] > div.flex-1.overflow-y-auto,
        .stock-page-wrap {
            display: block !important;
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            transform: none !important;
            left: 0 !important;
            top: 0 !important;
        }

        .space-y-6 > * + * {
            margin-top: 15px !important;
        }

        .stat-card, .panel-box, .glass-card, .card {
            border: 1px solid #ddd !important;
            background: #fff !important;
            color: #000 !important;
            box-shadow: none !important;
            border-radius: 8px !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid !important;
        }

        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 15px !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border-bottom: 1px solid #eee !important;
            padding: 8px !important;
            color: #000 !important;
            font-size: 11px !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Google Fonts & Font Awesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="flex h-[calc(100vh-3.5rem)] -mx-4 md:-mx-6 -my-4 md:-my-6 overflow-hidden">
    @include('tenant.partials.reports_sidebar', ['active' => 'inventory'])

    <!-- Right Main Scrollable Content -->
    <div class="flex-1 overflow-y-auto h-full p-6 md:p-8 space-y-6 custom-scrollbar bg-slate-50/20 dark:bg-slate-950/20 stock-page-wrap">
        
        {{-- Page Header --}}
        <div class="page-header">
            <div>
                <p class="breadcrumb">
                    <a href="#">Dashboard</a> / Reports / <span class="text-slate-900 dark:text-white">Stock Valuation</span>
                </p>
                <h1>Stock Valuation & Assets</h1>
            </div>
            
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="ti ti-printer"></i> Print Report
                </button>
                <button onclick="exportStockTableToCSV('Stock_Valuation_Report.csv')" class="btn btn-primary">
                    <i class="ti ti-download"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Summary Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 stats-grid">
            
            {{-- Cost Value Card --}}
            <div class="glass-card p-6 rounded-[1.5rem] bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-xl shadow-blue-500/10 border-0 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-blue-100 uppercase tracking-widest">Cost Assets (Dealer Price)</span>
                        <div class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="ti ti-archive text-white text-lg"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-extrabold tracking-tight" id="stat-cost-assets">
                        ₹{{ number_format($products->sum(fn($p) => $p->stock * $p->dealer_price), 2) }}
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-white/10 flex justify-between text-[11px] font-semibold text-blue-100">
                    <span id="stat-total-skus">Total SKUs: {{ $products->count() }}</span>
                    <span id="stat-total-units">Total Units: {{ $products->sum('stock') }}</span>
                </div>
            </div>
            
            {{-- MRP expected sales Card --}}
            <div class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Expected Revenue (MRP Value)</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                            <i class="ti ti-currency-rupee text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="stat-mrp-assets">
                        ₹{{ number_format($products->sum(fn($p) => $p->stock * $p->mrp), 2) }}
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider" id="stat-potential-profit">
                        Potential Profit: ₹{{ number_format($products->sum(fn($p) => $p->stock * ($p->mrp - $p->dealer_price)), 2) }}
                    </span>
                </div>
            </div>

            {{-- Dead / Zero Stock Card --}}
            <div class="glass-card p-6 rounded-[1.5rem] bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-100 dark:shadow-none flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Inventory Status Alert</span>
                        <div class="h-8 w-8 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center">
                            <i class="ti ti-alert-triangle text-rose-500 dark:text-rose-400 text-lg"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 tracking-tight" id="stat-out-of-stock-count">
                        {{ $products->where('stock', '<=', 0)->count() }} SKUs
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    <span class="text-rose-500 font-black italic" id="stat-alert-desc">Zero stock items logged.</span>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="filter-bar">
            {{-- Search input --}}
            <div class="filter-group col-span-1 md:col-span-2">
                <label for="searchFilter">Search Products</label>
                <div class="relative">
                    <input type="text" id="searchFilter" placeholder="Search by name, brand, barcode..." oninput="applyAllFilters()" class="w-full pr-10">
                    <i class="ti ti-search absolute right-4 top-3.5 text-slate-400"></i>
                </div>
            </div>

            {{-- Brand filter --}}
            <div class="filter-group">
                <label for="brandFilter">Filter Brand</label>
                <select id="brandFilter" onchange="applyAllFilters()">
                    <option value="">All Brands</option>
                    @foreach ($products->pluck('brand')->unique()->filter()->sort() as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Stock Status Filter --}}
            <div class="filter-group">
                <label for="statusFilter">Stock Status</label>
                <select id="statusFilter" onchange="applyAllFilters()">
                    <option value="">All Statuses</option>
                    <option value="in_stock">In Stock (> 5 units)</option>
                    <option value="low_stock">Low Stock (1 - 5 units)</option>
                    <option value="out_stock">Out of Stock (0 units)</option>
                </select>
            </div>
        </div>

        {{-- Itemized Valuation Table panel --}}
        <div class="glass-card rounded-[1.5rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-xl shadow-slate-100/50 dark:shadow-none bg-white dark:bg-slate-900">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Itemized Valuation</h3>
                    <p class="text-xs text-slate-400 mt-0.5" id="records-counter">Showing {{ $products->count() }} of {{ $products->count() }} records</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="stock-valuation-table">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Product Details</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Status</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">In Stock</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Avg Cost</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Selling (MRP)</th>
                            <th class="px-8 py-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Asset Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="table-body">
                        @foreach ($products as $product)
                            @php
                                $stockStatus = 'in_stock';
                                if ($product->stock <= 0) {
                                    $stockStatus = 'out_stock';
                                } elseif ($product->stock <= 5) {
                                    $stockStatus = 'low_stock';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors product-row" 
                                data-name="{{ strtolower($product->product_name) }}" 
                                data-brand="{{ strtolower($product->brand) }}" 
                                data-barcode="{{ strtolower($product->barcode) }}"
                                data-status="{{ $stockStatus }}"
                                data-qty="{{ $product->stock }}"
                                data-cost="{{ $product->dealer_price }}"
                                data-mrp="{{ $product->mrp }}">
                                
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $product->product_name }}</p>
                                    <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 mt-1 tracking-wider uppercase">
                                        {{ $product->barcode ?: 'No Barcode' }} | {{ $product->brand ?: 'No Brand' }}
                                    </p>
                                </td>
                                
                                <td class="px-8 py-5 text-center">
                                    @if ($product->stock <= 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50">
                                            Out of Stock
                                        </span>
                                    @elseif ($product->stock <= 5)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">
                                            Healthy
                                        </span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-200">{{ number_format($product->stock) }}</span>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">₹{{ number_format($product->dealer_price, 2) }}</p>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100">₹{{ number_format($product->mrp, 2) }}</p>
                                </td>
                                
                                <td class="px-8 py-5 text-right">
                                    <p class="text-sm font-black text-blue-600 dark:text-blue-400">₹{{ number_format($product->stock * $product->dealer_price, 2) }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Premium instant client-side searching and filters
    function applyAllFilters() {
        const searchVal = document.getElementById('searchFilter').value.toLowerCase().trim();
        const brandVal = document.getElementById('brandFilter').value.toLowerCase();
        const statusVal = document.getElementById('statusFilter').value;

        const rows = document.querySelectorAll('.product-row');
        
        let visibleCount = 0;
        let totalCostValue = 0;
        let totalMrpValue = 0;
        let totalSKUs = 0;
        let totalUnits = 0;
        let totalOutOfStock = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const brand = row.getAttribute('data-brand');
            const barcode = row.getAttribute('data-barcode');
            const status = row.getAttribute('data-status');
            const qty = parseFloat(row.getAttribute('data-qty')) || 0;
            const cost = parseFloat(row.getAttribute('data-cost')) || 0;
            const mrp = parseFloat(row.getAttribute('data-mrp')) || 0;

            const matchesSearch = !searchVal || name.includes(searchVal) || brand.includes(searchVal) || barcode.includes(searchVal);
            const matchesBrand = !brandVal || brand === brandVal;
            const matchesStatus = !statusVal || status === statusVal;

            if (matchesSearch && matchesBrand && matchesStatus) {
                row.style.display = '';
                visibleCount++;
                
                totalCostValue += (qty * cost);
                totalMrpValue += (qty * mrp);
                totalSKUs++;
                totalUnits += qty;
                if (qty <= 0) {
                    totalOutOfStock++;
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Beautiful Live Recalculations on Stats cards!
        document.getElementById('stat-cost-assets').innerText = '₹' + totalCostValue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('stat-mrp-assets').innerText = '₹' + totalMrpValue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        const potentialProfit = Math.max(0, totalMrpValue - totalCostValue);
        document.getElementById('stat-potential-profit').innerText = 'Potential Profit: ₹' + potentialProfit.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        document.getElementById('stat-total-skus').innerText = 'Total SKUs: ' + totalSKUs;
        document.getElementById('stat-total-units').innerText = 'Total Units: ' + totalUnits.toLocaleString('en-IN');
        
        document.getElementById('stat-out-of-stock-count').innerText = totalOutOfStock + ' SKUs';
        if (totalOutOfStock > 0) {
            document.getElementById('stat-alert-desc').innerText = 'Zero stock items logged.';
            document.getElementById('stat-alert-desc').className = 'text-rose-500 font-black italic';
        } else {
            document.getElementById('stat-alert-desc').innerText = 'All items healthy.';
            document.getElementById('stat-alert-desc').className = 'text-emerald-500 font-black italic';
        }

        document.getElementById('records-counter').innerText = `Showing ${visibleCount} of ${rows.length} records`;
    }

    // Binary HTML5 CSV exporter
    function exportStockTableToCSV(filename) {
        const table = document.getElementById("stock-valuation-table");
        const rows = table.querySelectorAll("tr");
        let csv = [];
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.style.display === 'none') continue; // only export visible filtered rows!
            
            const cols = row.querySelectorAll('th, td');
            let rowData = [];
            
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.trim();
                text = text.replace(/[\n\r]+/g, ' '); // remove line breaks
                text = text.replace(/₹/g, ''); // remove currency symbol
                text = text.replace(/"/g, '""'); // escape double quotes
                
                if (text.includes(',') || text.includes(' ') || text.includes('"')) {
                    text = `"${text}"`;
                }
                rowData.push(text);
            }
            csv.push(rowData.join(','));
        }

        const csvContent = "\uFEFF" + csv.join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
@endsection
