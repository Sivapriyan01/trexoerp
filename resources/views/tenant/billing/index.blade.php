{{-- resources/views/tenant/billing/index.blade.php --}}
@extends('layouts.tenant')
@section('title', isset($draftBill) ? strtoupper($draftBill->bill_type) . ' Edit' : (request('type') ? strtoupper(request('type')) : 'Billing'))
@section('page-title', isset($draftBill) ? strtoupper($draftBill->bill_type) . ' Edit' : (request('type') ? strtoupper(request('type')) : 'Billing'))

@push('styles')
    <style>
        @media (min-width: 1024px) {
            main {
                padding: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col h-full lg:overflow-hidden bg-slate-50 dark:bg-transparent">

        {{-- ── Tab Bar ──────────────────────────────────────────────────────── --}}
        <div
            class="flex items-center bg-white dark:bg-slate-900/85 border-b border-gray-200 dark:border-slate-800 px-4 gap-1 shrink-0">
            <div id="tab-bar" class="flex items-center gap-1 py-2 flex-1 overflow-x-auto">
                {{-- Tabs injected by JS --}}
            </div>
            <button onclick="addTab()"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shrink-0 ml-2 shadow-lg shadow-blue-500/10">
                <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        {{-- ── Main Content Row ─────────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row flex-1 min-h-0 lg:overflow-hidden">

            {{-- ── Left: Form Area ───────────────────────────────────────────── --}}
            <div class="flex-1 flex flex-col min-h-0 lg:overflow-hidden p-4 pb-0 gap-4">

                {{-- Exchange Info Banner --}}
                @if(isset($exchangeRma))
                <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-xl py-3 px-5 flex items-center justify-between shrink-0">
                    <div>
                        <h2 class="text-xs font-black text-violet-800 dark:text-violet-400 uppercase tracking-widest">Processing {{ $exchangeRma->type === 'replacement' ? 'Replacement' : 'Exchange' }}: {{ $exchangeRma->rma_number }}</h2>
                        <p class="text-[10px] text-violet-600 dark:text-violet-500 font-bold mt-0.5">Original Invoice: {{ $exchangeRma->bill->invoice_no }}</p>
                    </div>
                    @if(isset($creditNote))
                    <div class="text-right">
                        <p class="text-[10px] font-black text-violet-400 uppercase tracking-widest mb-0.5">Available Credit</p>
                        <p class="text-lg font-black text-violet-700 dark:text-violet-300">₹{{ number_format($creditNote->grand_total, 2) }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Customer Details --}}
                <div
                    class="bg-white dark:bg-slate-900/80 rounded-xl border border-gray-200 dark:border-slate-800 py-4 px-5 overflow-x-auto shrink-0 no-scrollbar">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-slate-100 mb-4 uppercase tracking-tighter">
                        Customer Details</h2>
                    <div class="flex flex-nowrap gap-6 items-end min-w-[1200px]">
                        <div class="w-48 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Customer
                                Phone</label>
                            <input id="customer_phone" type="tel" placeholder="Mobile No." class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-100
                                      focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                oninput="lookupCustomer(this.value)" />
                            <div id="credit_balance_badge"
                                class="hidden mt-1.5 flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-lg">
                                <svg width="10" height="10" class="w-2.5 h-2.5 text-rose-500 shrink-0" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                </svg>
                                <span
                                    class="text-[9px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">Credit
                                    Due:</span>
                                <span id="credit_balance_value"
                                    class="text-[9px] font-black text-rose-700 dark:text-rose-300">₹0.00</span>
                            </div>
                        </div>
                        <div class="w-72 shrink-0">
                            <div class="flex items-center justify-between mb-1.5 px-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">GSTIN
                                    (Optional)</label>
                                <span id="gst_status" class="hidden text-[8px] font-black uppercase tracking-widest"></span>
                            </div>
                            <div class="flex gap-2">
                                <input id="customer_gstin" type="text" placeholder="GSTIN" class="flex-1 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold uppercase text-slate-800 dark:text-slate-100
                                          focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                    oninput="if(this.value.length === 15) verifyBillingGstin()" />
                                <button onclick="verifyBillingGstin()" id="gst_verify_btn"
                                    class="px-4 bg-slate-900 dark:bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase hover:bg-slate-800 dark:hover:bg-blue-700 transition-all shrink-0">
                                    Verify
                                </button>
                            </div>
                        </div>
                        <div class="w-64 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Customer
                                Name</label>
                            <input id="customer_name" type="text" placeholder="Name" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-100
                                      focus:ring-2 focus:ring-blue-500 outline-none transition-all" />
                        </div>
                        <div class="w-80 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Address</label>
                            <textarea id="customer_address" rows="1" placeholder="Address"
                                class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-100
                                          focus:ring-2 focus:ring-blue-500 outline-none transition-all resize-none"></textarea>
                        </div>
                        <div class="w-32 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Reminder</label>
                            <label class="relative inline-flex items-center cursor-pointer mt-1 ml-2">
                                <input type="checkbox" id="reminder_enabled" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                                <span
                                    class="ml-2 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">1
                                    Year</span>
                            </label>
                        </div>
                        <div class="w-32 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Bill
                                Type</label>
                            <select id="bill_type_select"
                                class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                <option value="billing">Billing</option>
                                <option value="return">Return</option>
                                <option value="exchange">Exchange</option>
                                <option value="replacement">Replacement</option>
                            </select>
                        </div>
                        <div class="w-40 shrink-0">
                            <label
                                class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 px-1">Date</label>
                            <input id="bill_date" type="date" value="{{ $today }}" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-100
                                      focus:ring-2 focus:ring-blue-500 outline-none transition-all" />
                        </div>
                    </div>
                </div>

                {{-- Products Section --}}
                <div
                    class="bg-white dark:bg-slate-900/80 rounded-xl border border-gray-200 dark:border-slate-800 flex-[1_1_0%] flex flex-col min-h-[400px] lg:min-h-0">

                    {{-- Category filter tabs --}}
                    <div
                        class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 dark:border-slate-800/60 overflow-x-auto shrink-0">
                        <button onclick="filterCategory('All')" data-cat="All" class="cat-tab px-3 py-1 text-xs font-medium rounded-full bg-blue-600
                                   text-white whitespace-nowrap">All</button>
                        @foreach($productTypes as $type)
                            <button onclick="filterCategory('{{ $type }}')" data-cat="{{ $type }}"
                                class="cat-tab px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-slate-800
                                       text-gray-900 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700 whitespace-nowrap transition">
                                {{ $type }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Search + Scan bar --}}
                    <div
                        class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-slate-800/60 shrink-0">
                        <div class="flex items-center gap-2 flex-1 border border-gray-300 dark:border-slate-800 rounded-lg
                                px-3 py-2 focus-within:ring-2 focus-within:ring-blue-400 bg-white dark:bg-slate-950/50">
                            <svg width="16" height="16" class="w-4 h-4 text-gray-900 dark:text-slate-400 shrink-0"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <input id="scan_input" type="text" placeholder="Scan Code (Press Enter)"
                                class="flex-1 text-sm outline-none bg-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500"
                                onkeydown="handleScanEnter(event)" />
                        </div>
                        <input id="search_input" type="text" placeholder="Search by name, brand or barcode..." class="flex-1 border border-gray-300 dark:border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 bg-white dark:bg-slate-950/50
                                  focus:outline-none focus:ring-2 focus:ring-blue-400"
                            oninput="debounceSearch(this.value)" />
                        <span class="text-sm text-gray-900 dark:text-slate-300 font-medium whitespace-nowrap">
                            Total Qty: <span id="total_qty">0</span>
                        </span>
                        <button onclick="document.getElementById('custom_col_modal').classList.remove('hidden')"
                            class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition shadow-sm ml-2 shrink-0">
                            Add Custom Col
                        </button>
                        <div class="relative shrink-0 ml-2">
                            <button onclick="document.getElementById('col_filter_dropdown').classList.toggle('hidden')"
                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[10px] font-black uppercase tracking-widest transition shadow-sm flex items-center gap-2">
                                Filter Columns
                            </button>
                            <div id="col_filter_dropdown"
                                class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-xl z-[60] p-2 flex flex-col gap-1 max-h-64 overflow-y-auto custom-scrollbar">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>

                    {{-- Product Grid (Dropdown when searching) --}}
                    <div class="relative shrink-0">
                        <div id="product_grid" class="hidden absolute top-0 left-0 right-0 bg-white dark:bg-slate-950 shadow-2xl rounded-b-xl border border-gray-200 dark:border-slate-800 
                                grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3 p-4
                                max-h-96 overflow-y-auto z-[40] custom-scrollbar">
                        </div>
                    </div>

                    {{-- Product Table --}}
                    <div class="flex-1 overflow-auto custom-scrollbar">
                        <table class="w-full text-sm min-w-[1200px]">
                            <thead
                                class="sticky top-0 bg-gray-50 dark:bg-slate-950/80 border-b border-gray-200 dark:border-slate-800 z-10">
                                <tr id="table_headers_row">
                                    @foreach(['BARCODE', 'PROD NAME', 'BRAND', 'PROD TYPE', 'MODEL', 'SIZE', 'HSN', 'QUANTITY', 'TOTAL', 'ACTION'] as $col)
                                        <th class="text-left px-3 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-350 uppercase tracking-wide whitespace-nowrap"
                                            data-base-col="{{ $col }}">
                                            {{ $col }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="product_table_body">
                                {{-- Manual row always present --}}
                                <tr id="manual_row" class="border-b border-gray-50 dark:border-slate-800/80">
                                    <td class="px-3 py-2" data-cell-col="BARCODE"><input type="text" placeholder="Manual"
                                            class="w-20 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="PROD NAME">
                                        <div class="flex items-center gap-1">
                                            <input id="manual_name" type="text" placeholder="prod Name"
                                                class="w-28 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                            <button onclick="openProductPicker()"
                                                class="w-6 h-6 flex items-center justify-center text-blue-500 shrink-0
                                                       border border-blue-200 dark:border-blue-900/50 rounded hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all">
                                                <svg width="16" height="16" class="w-4 h-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="BRAND"><input type="text" placeholder="Brand..."
                                            class="w-20 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="PROD TYPE"><input type="text"
                                            placeholder="Prod Type"
                                            class="w-20 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="MODEL"><input type="text" placeholder="Model..."
                                            class="w-20 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="SIZE"><input type="text" placeholder="Size"
                                            class="w-14 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="HSN"><input type="text" placeholder="HSN"
                                            class="w-16 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2" data-cell-col="QUANTITY"><input type="number" value="1" min="1"
                                            class="w-14 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2 text-gray-900 dark:text-slate-300 font-medium"
                                        data-cell-col="TOTAL">0.00</td>
                                    <td class="px-3 py-2" data-cell-col="ACTION">
                                        <button
                                            class="w-6 h-6 flex items-center justify-center text-green-600
                                                   border border-green-200 dark:border-green-900/50 rounded hover:bg-green-50 dark:hover:bg-green-950/30 transition-all">
                                            <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                {{-- Scanned/selected product rows appended here by JS --}}
                            </tbody>
                        </table>

                        {{-- Empty state --}}
                        <div id="empty_state" class="py-12 text-center text-sm text-gray-900">
                            Scan a barcode or search to add products
                        </div>
                    </div>
                </div>


                {{-- Bottom Action Bar --}}
                <div class="shrink-0 hidden">
                    <div
                        class="bg-white dark:bg-slate-900/80 rounded-xl border border-gray-200 dark:border-slate-800 px-5 py-3 flex items-center justify-between shadow-sm">
                        <button onclick="loadPreviousBills()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm
                                   font-medium rounded-lg transition">
                            Previous Bill
                        </button>
                        <div class="text-xs text-gray-900 dark:text-slate-300 flex items-center gap-4">
                            <span>F5 : View Invoice</span>
                            <span>F10 : Return Invoice</span>
                            <span>F11 : Preview Profit Check</span>
                            <span>ctrl + enter : Focus Scan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick="clearBill()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm
                                       font-medium rounded-lg transition">
                                Clear
                            </button>
                            <button onclick="generateInvoice()" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm
                                       font-medium rounded-lg transition">
                                Generate Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Right: Summary Panel ────────────────────────────────────────── --}}
            <div
                class="w-full lg:w-44 bg-white dark:bg-slate-900/80 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-slate-800 flex flex-col shrink-0 p-3 gap-2.5 lg:overflow-y-auto">

                {{-- Discount --}}
                @if($settings['discount_enabled'])
                    <div>
                        <label
                            class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Discount
                            (%)</label>
                        <input id="discount_percent" type="number" min="0" max="100" step="0.1" placeholder="0" class="w-full border border-gray-300 dark:border-slate-800 bg-white dark:bg-slate-950/50 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-lg px-2.5 py-1 text-xs
                                  focus:outline-none focus:ring-2 focus:ring-blue-400" oninput="recalculate()" />
                    </div>
                    <div>
                        <label
                            class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Discount
                            Amt</label>
                        <input id="discount_amount" type="number" min="0" step="0.01" placeholder="0.00" class="w-full border border-gray-300 dark:border-slate-800 bg-white dark:bg-slate-950/50 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-lg px-2.5 py-1 text-xs
                                  focus:outline-none focus:ring-2 focus:ring-blue-400" oninput="recalculate()" />
                    </div>
                @else
                    <input id="discount_percent" type="hidden" value="0">
                    <input id="discount_amount" type="hidden" value="0">
                @endif

                {{-- GST --}}
                @if($settings['gst_enabled'])
                    <div>
                        <label
                            class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">GST
                            (%)</label>
                        <input id="gst_percent" type="number" value="{{ $settings['default_gst'] }}" min="0" class="w-full border border-gray-300 dark:border-slate-800 bg-white dark:bg-slate-950/50 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-lg px-2.5 py-1 text-xs
                                  focus:outline-none focus:ring-2 focus:ring-blue-400" oninput="recalculate()" />
                    </div>
                @else
                    <input id="gst_percent" type="hidden" value="0">
                @endif

                <div class="border-t border-gray-100 dark:border-slate-800 pt-2 space-y-1.5 text-[11px]">
                    <div class="flex justify-between text-gray-900 dark:text-slate-355">
                        <span>Total:</span>
                        <span id="disp_subtotal" class="font-bold text-slate-800 dark:text-slate-100">0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-900 dark:text-slate-355">
                        <span>After Disc:</span>
                        <span id="disp_after_disc" class="text-slate-800 dark:text-slate-100">0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-900 dark:text-slate-355">
                        <span id="label_disp_gst">GST Amt{{ ($settings['gst_calc_type'] ?? 'inclusive') === 'inclusive' ? ' (Incl)' : '' }}:</span>
                        <span id="disp_gst" class="text-slate-800 dark:text-slate-100">0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-900 dark:text-slate-355">
                        <span>Round Off:</span>
                        <span id="disp_round" class="text-slate-800 dark:text-slate-100">0.00</span>
                    </div>
                    <div
                        class="border-t border-gray-200 dark:border-slate-800 pt-2 flex justify-between text-gray-900 dark:text-slate-300">
                        <span class="font-black text-xs text-slate-900 dark:text-slate-100 uppercase tracking-tight">Grand
                            Total:</span>
                        <span id="disp_grand" class="font-black text-xs text-slate-900 dark:text-slate-100">0.00</span>
                    </div>
                </div>

                {{-- Wallet Option --}}
                <div id="wallet_panel" class="hidden bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-xl p-3 flex flex-col gap-2 my-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="use_wallet" onchange="toggleWalletInput()" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 border-gray-300">
                            <span class="text-[10px] font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-widest">Apply Wallet</span>
                        </div>
                        <span id="wallet_available_text" class="text-[10px] font-black text-emerald-600 dark:text-emerald-500">₹0.00 Available</span>
                    </div>
                    <div id="wallet_input_container" class="hidden flex gap-2 items-center">
                        <span class="text-[9px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest whitespace-nowrap">Amount:</span>
                        <div class="relative flex-1">
                            <span class="absolute left-2.5 top-1.5 text-xs font-black text-emerald-500">₹</span>
                            <input type="number" id="wallet_amount_used" min="0" oninput="validateWalletAmount()"
                                   class="w-full pl-6 pr-2 py-1 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800 rounded-md text-xs font-black text-emerald-900 dark:text-emerald-100 focus:ring-1 focus:ring-emerald-500 outline-none">
                        </div>
                        <button onclick="setMaxWallet()" class="px-2 py-1 bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-100 rounded-md text-[9px] font-black uppercase hover:bg-emerald-300 dark:hover:bg-emerald-700 transition-all">Max</button>
                    </div>
                </div>

                {{-- Payment Mode --}}
                <div>
                    <label
                        class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                        Payment Mode
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['cash' => 'Cash', 'qr' => 'QR', 'card' => 'Card', 'credit' => 'Credit', 'coupon' => 'Coupon'] as $val => $label)
                            @if($settings['payment_modes'][$val] ?? false)
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_mode" value="{{ $val }}" {{ $val === 'cash' ? 'checked' : '' }}
                                        class="sr-only peer" onchange="recalculate()">
                                    <div
                                        class="px-1.5 py-1 text-[10px] font-bold border border-gray-200 dark:border-slate-800 rounded-md
                                                text-center transition peer-checked:border-slate-900 peer-checked:dark:border-blue-500
                                                peer-checked:bg-slate-50 peer-checked:dark:bg-blue-950/20 peer-checked:text-slate-900 peer-checked:dark:text-blue-400
                                                text-slate-700 dark:text-slate-300 hover:border-gray-300 dark:hover:border-slate-700">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- GST Return Status Meter --}}
                <div id="gst_return_status_container"
                    class="hidden flex flex-col gap-1.5 my-2 p-2.5 border border-gray-100 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-950/50 shadow-sm">
                    <div class="flex justify-between items-center">
                        <label
                            class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Filing
                            Health</label>
                        <span id="gst_return_status_text"
                            class="text-[9px] font-bold text-slate-600 dark:text-slate-300">Checking...</span>
                    </div>

                    <div class="relative w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div id="gst_return_meter_bar"
                            class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-500 ease-out w-0"></div>
                    </div>

                    <div class="flex justify-between text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        <span>Poor</span>
                        <span>Excellent</span>
                    </div>
                </div>

                <button onclick="generateInvoice()" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white text-xs
                           font-black rounded-lg uppercase tracking-wider transition mt-auto mb-4">
                    Generate Invoice
                </button>
            </div>
        </div>
    </div>

    {{-- ── Previous Bills Modal ──────────────────────────────────────────── --}}
    <div id="prev_bills_modal"
        class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-slate-100">Previous Bills</h3>
                <button onclick="document.getElementById('prev_bills_modal').classList.add('hidden')"
                    class="text-slate-500 dark:text-slate-400 hover:text-gray-900">✕</button>
            </div>
            <div id="prev_bills_list" class="flex-1 overflow-y-auto p-4">
                <p class="text-sm text-gray-900 dark:text-slate-350 text-center py-8">Loading...</p>
            </div>
        </div>
    </div>

    {{-- ── Invoice Preview Modal ─────────────────────────────────────────── --}}
    <div id="invoice_modal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-800 shrink-0">
                <h3 class="font-semibold text-gray-800 dark:text-slate-100">Invoice Generated</h3>
                <button onclick="closeInvoiceModal()"
                    class="text-slate-500 dark:text-slate-400 hover:text-gray-900">✕</button>
            </div>
            <div id="invoice_content" class="flex-1 overflow-y-auto p-6 text-sm"></div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-800 flex gap-3 justify-end shrink-0">
                <button onclick="markForDelivery()"
                    class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm hover:bg-amber-700">
                    Mark for Delivery
                </button>
                @if($settings['inv_print_pos'] ?? true)
                    <button
                        onclick="window.open('/billing/print/' + document.getElementById('invoice_modal').dataset.billId, '_blank')"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700">
                        Print POS
                    </button>
                @endif
                @if($settings['inv_print_a4'] ?? true)
                    <button
                        onclick="window.open('/billing/invoice/' + document.getElementById('invoice_modal').dataset.billId, '_blank')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                        Print A4 Invoice
                    </button>
                @endif
                <button onclick="closeInvoiceModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-slate-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-slate-800">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ── EMI Setup Modal ──────────────────────────────────────────────── --}}
    <div id="emi_modal"
        class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-[2rem] shadow-2xl w-full max-w-lg transform transition-all overflow-hidden">
            <div class="p-8 pb-4 flex items-center justify-between border-b border-gray-50 dark:border-slate-850">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-slate-100 uppercase tracking-widest">Setup
                        Instalment Plan</h3>
                    <p class="text-[10px] font-bold text-gray-900 dark:text-slate-450 mt-1 uppercase">Convert credit sale to
                        EMI</p>
                </div>
                <div
                    class="w-10 h-10 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center shadow-sm">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <div
                    class="p-4 bg-gray-50 dark:bg-slate-950/50 rounded-2xl border border-gray-100 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <p class="text-[9px] font-black text-gray-900 dark:text-slate-400 uppercase mb-1">Total Credit
                            Amount</p>
                        <p class="text-xl font-black text-gray-900 dark:text-slate-100" id="emi_total_display">₹0.00</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black text-gray-900 dark:text-slate-400 uppercase mb-1">Customer</p>
                        <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase" id="emi_cust_name">
                            Walk-in</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-gray-900 dark:text-slate-400 uppercase px-1">No. of
                            Months</label>
                        <select id="emi_months" onchange="calcEMI()"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold outline-none focus:ring-4 focus:ring-blue-50 transition-all">
                            <option value="1">1 Month</option>
                            <option value="3" selected>3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                            <option value="24">24 Months</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-gray-900 dark:text-slate-400 uppercase px-1">First Due
                            Date</label>
                        <input type="date" id="emi_start_date" onchange="calcEMI()" value="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold outline-none focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>
                </div>

                <div class="p-5 bg-blue-600 rounded-2xl shadow-xl shadow-blue-100 text-center space-y-1">
                    <p class="text-[10px] font-black text-blue-100 uppercase tracking-widest">Monthly Instalment</p>
                    <p class="text-2xl font-black text-white" id="emi_amount_display">₹0.00</p>
                </div>

                <div class="flex gap-3">
                    <button onclick="closeEMI()"
                        class="flex-1 py-4 bg-gray-100 dark:bg-slate-800 text-gray-900 dark:text-slate-200 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-slate-700 transition-all">
                        Skip/Cancel
                    </button>
                    <button onclick="confirmEMI()"
                        class="flex-[2] py-4 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">
                        Confirm EMI Plan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Add Custom Column Modal ─────────────────────────────────────────── --}}
    <div id="custom_col_modal"
        class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-[2rem] shadow-2xl w-full max-w-sm transform transition-all overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-slate-850 flex items-center justify-between">
                <h3 class="text-sm font-black text-gray-900 dark:text-slate-100 uppercase tracking-widest">Add Custom Column
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Column Name</label>
                    <input type="text" id="new_custom_col_name"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g. Remarks">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Input Method /
                        Type</label>
                    <select id="new_custom_col_type"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="text">Text Input</option>
                        <option value="number">Number</option>
                        <option value="date">Date Picker</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button onclick="document.getElementById('custom_col_modal').classList.add('hidden')"
                        class="flex-1 py-3 bg-gray-100 dark:bg-slate-800 text-gray-900 dark:text-slate-200 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-slate-700 transition-all">
                        Cancel
                    </button>
                    <button onclick="saveCustomCol()"
                        class="flex-1 py-3 bg-violet-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-violet-700 transition-all">
                        Add
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ─────────────────────────────────────────────────────────────────────────────
        // RX TrexoERP Billing JS
        // ─────────────────────────────────────────────────────────────────────────────

        const ROUTES = {
            search: '{{ route("tenant.billing.products.search") }}',
            scan: '{{ route("tenant.billing.products.scan") }}',
            customer: '{{ route("tenant.billing.customer.lookup") }}',
            generate: '{{ route("tenant.billing.generate") }}',
            previous: '{{ route("tenant.billing.previous") }}',
            gst_validate: '{{ route("tenant.gst.validate") }}',
            gst_returns: '{{ route("tenant.gst.returns") }}',
        };
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // ── State ─────────────────────────────────────────────────────────────────
        let tabs = [{ id: 1, label: 'Billing 1', items: [] }];
        let active = 0;   // index into tabs[]
        let searchTimer = null;
        let activeCategory = 'All';
        let customColumns = []; // Array of { name: '..', type: '..' }
        let hiddenCols = new Set();
        const BASE_COLS = ['BARCODE', 'BRAND', 'PROD TYPE', 'MODEL', 'SIZE', 'HSN'];

        // Load settings from localStorage
        try {
            const savedCustomCols = localStorage.getItem('billing_custom_cols');
            if (savedCustomCols) customColumns = JSON.parse(savedCustomCols);

            const savedHiddenCols = localStorage.getItem('billing_hidden_cols');
            if (savedHiddenCols) hiddenCols = new Set(JSON.parse(savedHiddenCols));
        } catch (e) { }

        function saveColSettings() {
            localStorage.setItem('billing_custom_cols', JSON.stringify(customColumns));
            localStorage.setItem('billing_hidden_cols', JSON.stringify([...hiddenCols]));
        }

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            const d = document.getElementById('col_filter_dropdown');
            // If the target is no longer in the document (e.g. it was re-rendered), don't close
            if (!document.body.contains(e.target)) return;
            if (d && !d.classList.contains('hidden') && !e.target.closest('#col_filter_dropdown') && !e.target.closest('button[onclick*="col_filter_dropdown"]')) {
                d.classList.add('hidden');
            }
        });

        function toggleCol(name) {
            if (hiddenCols.has(name)) hiddenCols.delete(name);
            else hiddenCols.add(name);
            saveColSettings();
            renderColFilter();
            renderTableRows();
        }

        function renderColFilter() {
            const dropdown = document.getElementById('col_filter_dropdown');
            if (!dropdown) return;
            let html = '';
            const allCols = [...BASE_COLS.map(name => ({ name, custom: false })), ...customColumns.map(c => ({ name: c.name, custom: true }))];
            allCols.forEach(col => {
                const isChecked = !hiddenCols.has(col.name);
                const delBtn = col.custom ? `<button type="button" onclick="deleteCustomCol('${col.name}'); event.stopPropagation();" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Delete Column"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>` : '';

                html += `
                <div class="flex items-center justify-between px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition">
                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                        <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleCol('${col.name}')" class="w-3.5 h-3.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">${col.name}</span>
                    </label>
                    ${delBtn}
                </div>
            `;
            });
            dropdown.innerHTML = html;
        }

        // ── Custom Column Management ───────────────────────────────────────────────
        function saveCustomCol() {
            const nameInput = document.getElementById('new_custom_col_name');
            const typeInput = document.getElementById('new_custom_col_type');
            const colName = nameInput.value.trim();
            if (!colName) return alert('Column name required');
            if (customColumns.find(c => c.name.toLowerCase() === colName.toLowerCase())) {
                return alert('Column already exists!');
            }

            customColumns.push({ name: colName, type: typeInput.value });

            // Add custom field state to existing items
            tabs.forEach(tab => {
                tab.items.forEach(item => {
                    if (!item.custom_fields) item.custom_fields = {};
                    item.custom_fields[colName] = '';
                });
            });

            nameInput.value = '';
            typeInput.value = 'text';
            document.getElementById('custom_col_modal').classList.add('hidden');
            saveColSettings();
            renderColFilter();
            renderTableRows();
        }

        function deleteCustomCol(name) {
            if (!confirm('Are you sure you want to delete this column?')) return;
            customColumns = customColumns.filter(c => c.name !== name);
            hiddenCols.delete(name);

            tabs.forEach(tab => {
                tab.items.forEach(item => {
                    if (item.custom_fields && item.custom_fields[name] !== undefined) {
                        delete item.custom_fields[name];
                    }
                });
            });

            saveColSettings();
            renderColFilter();
            renderTableRows();
        }

        // ── Tab Management ────────────────────────────────────────────────────────
        function renderTabs() {
            const bar = document.getElementById('tab-bar');
            bar.innerHTML = tabs.map((t, i) => `
            <button onclick="switchTab(${i})"
                    class="flex items-center gap-2 px-4 py-1.5 text-xs rounded-lg border transition uppercase font-black tracking-wider
                           ${i === active
                    ? 'border-blue-500 dark:border-blue-500/80 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 font-black'
                    : 'border-gray-200 dark:border-slate-800/80 text-gray-600 dark:text-slate-400 hover:border-gray-300 dark:hover:border-slate-700 bg-transparent'}"
            >
                ${t.label}
                ${tabs.length > 1
                    ? `<span onclick="closeTab(event,${i})" class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 ml-1">✕</span>`
                    : ''}
            </button>`
            ).join('');
        }

        function addTab() {
            const n = tabs.length + 1;
            tabs.push({ id: n, label: `Billing ${n}`, items: [] });
            switchTab(tabs.length - 1);
        }

        function closeTab(e, i) {
            e.stopPropagation();
            tabs.splice(i, 1);
            active = Math.min(active, tabs.length - 1);
            renderTabs();
            renderTableRows();
            recalculate();
        }

        function switchTab(i) {
            active = i;
            renderTabs();
            renderTableRows();
            recalculate();
        }

        function currentItems() { return tabs[active].items; }

        // ── Category Filter ───────────────────────────────────────────────────────
        function filterCategory(cat) {
            activeCategory = cat;
            document.querySelectorAll('.cat-tab').forEach(b => {
                const selected = b.dataset.cat === cat;
                b.className = `cat-tab px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap transition ${selected ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                    }`;
            });
            const term = document.getElementById('search_input').value;
            if (term || cat !== 'All') fetchProducts(term);
        }

        // ── Search & Scan ─────────────────────────────────────────────────────────
        function debounceSearch(val) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fetchProducts(val), 300);
        }

        async function fetchProducts(term) {
            const url = new URL(ROUTES.search);
            url.searchParams.set('q', term);
            url.searchParams.set('type', activeCategory);

            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            const grid = document.getElementById('product_grid');
            if (!data.length) { grid.classList.add('hidden'); return; }

            grid.classList.remove('hidden');
            // Remove old grid classes that conflict, add proper display
            grid.style.display = 'grid';
            grid.innerHTML = data.map(p => `
            <div onclick='addProduct(${JSON.stringify(p)})'
                 class="border border-gray-200 dark:border-slate-800 rounded-xl p-3 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500
                        bg-white dark:bg-slate-950/80 hover:bg-blue-50/50 dark:hover:bg-slate-900/60 transition flex flex-col items-center gap-2">
                ${p.image
                    ? `<img src="/storage/${p.image}" class="w-10 h-10 object-cover rounded-lg"/>`
                    : `<div class="w-10 h-10 bg-gray-100 dark:bg-slate-900 rounded-lg flex items-center justify-center">
                       <svg width="20" height="20" class="w-5 h-5 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                               d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                       </svg>
                     </div>`}
                <div class="text-center">
                    <p class="text-xs font-medium text-slate-800 dark:text-slate-200 leading-tight">${p.product_name}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${p.brand || ''}</p>
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-0.5">₹${p.mrp}</p>
                    <p class="text-xs ${p.stock > 0 ? 'text-green-600' : 'text-red-500'}">
                        ${p.stock > 0 ? p.stock + ' avail.' : 'Out of stock'}
                    </p>
                </div>
            </div>`
            ).join('');
        }

        async function handleScanEnter(e) {
            if (e.key !== 'Enter') return;
            const barcode = e.target.value.trim();
            if (!barcode) return;

            const url = new URL(ROUTES.scan);
            url.searchParams.set('barcode', barcode);
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            if (data.found) {
                addProduct(data.product);
                e.target.value = '';
                showToast(`${data.product.product_name} added`, 'success');
            } else {
                showToast('Product not found', 'error');
            }
        }

        // ── Add Product to Current Tab ────────────────────────────────────────────
        function addProduct(p) {
            if (p.expiry_date) {
                const today = new Date().toISOString().split('T')[0];
                if (p.expiry_date < today) {
                    showToast(`⚠️ WARNING: ${p.product_name} has expired on ${p.expiry_date}!`, 'error');
                }
            }

            if (p.stock <= (p.low_stock_alert || 0)) {
                showToast(`⚠️ Low Stock Alert: ${p.product_name} (Only ${p.stock} left)`, 'warning');
            }

            const items = currentItems();
            const exist = items.find(i => i.category_id === p.id);

            if (exist) {
                exist.quantity++;
                exist.total = exist.mrp * exist.quantity;
            } else {
                items.push({
                    category_id: p.id,
                    barcode: p.barcode || '',
                    product_name: p.product_name,
                    brand: p.brand || '',
                    product_type: p.product_type || '',
                    model: p.model || '',
                    size: p.size || '',
                    hsn: p.hsn || '',
                    mrp: parseFloat(p.mrp),
                    quantity: 1,
                    total: parseFloat(p.mrp),
                    custom_fields: {},
                });

                // Init custom fields
                const newItem = items[items.length - 1];
                customColumns.forEach(c => newItem.custom_fields[c.name] = '');
            }

            document.getElementById('empty_state').classList.add('hidden');
            document.getElementById('product_grid').classList.add('hidden');
            document.getElementById('product_grid').style.display = 'none';
            document.getElementById('search_input').value = '';

            renderTableRows();
            recalculate();
        }

        // ── Render Table Rows ─────────────────────────────────────────────────────
        function renderTableRows() {
            const tbody = document.getElementById('product_table_body');
            const items = currentItems();

            // Keep manual row, replace the rest
            const manualRow = document.getElementById('manual_row');

            // Remove all rows except manual
            [...tbody.querySelectorAll('tr:not(#manual_row)')].forEach(r => r.remove());

            // Update Headers dynamically
            const trHead = document.getElementById('table_headers_row');
            [...trHead.querySelectorAll('th:not([data-base-col])')].forEach(th => th.remove());
            const actionTh = trHead.querySelector('th:last-child');

            // Hide/show base headers
            trHead.querySelectorAll('th[data-base-col]').forEach(th => {
                const cname = th.getAttribute('data-base-col');
                if (hiddenCols.has(cname)) th.classList.add('hidden');
                else th.classList.remove('hidden');
            });

            // Update Manual Row dynamically to maintain column alignment
            [...manualRow.querySelectorAll('td[data-custom-col="true"]')].forEach(td => td.remove());
            const actionTdManual = manualRow.querySelector('td:last-child');

            // Hide/show base manual row cells
            manualRow.querySelectorAll('td[data-cell-col]').forEach(td => {
                const cname = td.getAttribute('data-cell-col');
                if (hiddenCols.has(cname)) td.classList.add('hidden');
                else td.classList.remove('hidden');
            });

            customColumns.forEach(c => {
                const th = document.createElement('th');
                th.className = 'text-left px-3 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-350 uppercase tracking-wide whitespace-nowrap text-violet-600 dark:text-violet-400';
                th.textContent = c.name;
                if (hiddenCols.has(c.name)) th.classList.add('hidden');
                trHead.insertBefore(th, actionTh);

                const td = document.createElement('td');
                td.className = 'px-3 py-2';
                td.setAttribute('data-custom-col', 'true');
                if (hiddenCols.has(c.name)) td.classList.add('hidden');

                const inputType = c.type === 'date' ? 'date' : (c.type === 'number' ? 'number' : 'text');
                td.innerHTML = `<input type="${inputType}" placeholder="${c.name}" class="w-20 bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-violet-500">`;
                manualRow.insertBefore(td, actionTdManual);
            });

            items.forEach((item, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-50 dark:border-slate-800/80 hover:bg-gray-50 dark:hover:bg-slate-950/40 transition';
                tr.innerHTML = `
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('BARCODE') ? 'hidden' : ''}">${item.barcode || '—'}</td>
                <td class="px-3 py-2 text-xs font-bold text-slate-800 dark:text-slate-100 ${hiddenCols.has('PROD NAME') ? 'hidden' : ''}">${item.product_name}</td>
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('BRAND') ? 'hidden' : ''}">${item.brand}</td>
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('PROD TYPE') ? 'hidden' : ''}">${item.product_type}</td>
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('MODEL') ? 'hidden' : ''}">${item.model}</td>
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('SIZE') ? 'hidden' : ''}">${item.size}</td>
                <td class="px-3 py-2 text-xs text-slate-800 dark:text-slate-300 ${hiddenCols.has('HSN') ? 'hidden' : ''}">${item.hsn}</td>
                <td class="px-3 py-2 ${hiddenCols.has('QUANTITY') ? 'hidden' : ''}">
                    <div class="flex items-center gap-1">
                        <button onclick="changeQty(${idx},-1)"
                                class="w-5 h-5 flex items-center justify-center border border-gray-200 dark:border-slate-800
                                       rounded text-slate-800 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 text-xs">−</button>
                        <span class="w-8 text-center text-xs font-medium text-slate-800 dark:text-slate-200">${item.quantity}</span>
                        <button onclick="changeQty(${idx},1)"
                                class="w-5 h-5 flex items-center justify-center border border-gray-200 dark:border-slate-800
                                       rounded text-slate-800 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 text-xs">+</button>
                    </div>
                </td>
                <td class="px-3 py-2 text-xs font-bold text-slate-850 dark:text-slate-100 ${hiddenCols.has('TOTAL') ? 'hidden' : ''}">
                    ₹${item.total.toFixed(2)}
                </td>`;

                customColumns.forEach(c => {
                    const inputType = c.type === 'date' ? 'date' : (c.type === 'number' ? 'number' : 'text');
                    const val = (item.custom_fields && item.custom_fields[c.name]) ? item.custom_fields[c.name] : '';
                    const hideCls = hiddenCols.has(c.name) ? 'hidden' : '';
                    tr.innerHTML += `<td class="px-3 py-2 ${hideCls}"><input type="${inputType}" value="${val}" oninput="updateCustomField(${idx}, '${c.name}', this.value)" class="w-full bg-white dark:bg-slate-950/50 border border-gray-200 dark:border-slate-800 rounded px-2 py-1 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-violet-500"></td>`;
                });

                tr.innerHTML += `
                <td class="px-3 py-2 ${hiddenCols.has('ACTION') ? 'hidden' : ''}">
                    <button onclick="removeItem(${idx})"
                            class="w-6 h-6 flex items-center justify-center text-red-400 dark:text-red-500
                                   hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded transition">
                        <svg width="14" height="14" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>`;
                tbody.insertBefore(tr, manualRow);
            });

            document.getElementById('total_qty').textContent = items.reduce((s, i) => s + i.quantity, 0);
            document.getElementById('empty_state').classList.toggle('hidden', items.length > 0);
        }

        function changeQty(idx, delta) {
            const items = currentItems();
            items[idx].quantity = Math.max(1, items[idx].quantity + delta);
            items[idx].total = items[idx].mrp * items[idx].quantity;
            renderTableRows();
            recalculate();
        }

        function updateCustomField(idx, colName, val) {
            const items = currentItems();
            if (!items[idx].custom_fields) items[idx].custom_fields = {};
            items[idx].custom_fields[colName] = val;
        }

        function removeItem(idx) {
            currentItems().splice(idx, 1);
            renderTableRows();
            recalculate();
        }

        // ── Customer Lookup ───────────────────────────────────────────────────────
        let customerTimer = null;
        async function lookupCustomer(phone) {
            clearTimeout(customerTimer);

            // Hide credit badge when field is cleared
            if (phone.length < 10) {
                document.getElementById('credit_balance_badge').classList.add('hidden');
                return;
            }

            customerTimer = setTimeout(async () => {
                const url = new URL(ROUTES.customer);
                url.searchParams.set('phone', phone);
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.found) {
                    document.getElementById('customer_name').value = data.customer.name || '';
                    document.getElementById('customer_address').value = data.customer.address || '';
                    if (data.customer.gstin) {
                        document.getElementById('customer_gstin').value = data.customer.gstin;
                    }

                    // ── Show / hide credit balance badge ──────────────────────────
                    const creditBadge = document.getElementById('credit_balance_badge');
                    const creditValue = document.getElementById('credit_balance_value');
                    const balance = parseFloat(data.credit_balance) || 0;
                    if (balance > 0) {
                        creditValue.textContent = '₹' + balance.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        creditBadge.classList.remove('hidden');
                    } else {
                        creditBadge.classList.add('hidden');
                    }

                    let message = data.was_created ? 'New customer created!' : ('Customer found: ' + (data.customer.name || ''));
                    let msgType = 'success';
                    
                    if (data.was_created) {
                        setTimeout(() => document.getElementById('customer_name').focus(), 100);
                    }

                    // Check for active membership discount
                    if (data.customer.active_membership && data.customer.active_membership.plan) {
                        const plan = data.customer.active_membership.plan;
                        if (plan.discount_percent > 0) {
                            document.getElementById('discount_percent').value = plan.discount_percent;
                            message = `Customer found! Applying ${plan.name} discount: ${plan.discount_percent}%`;
                            recalculate();
                        } else {
                            message = `Customer found! Tier: ${plan.name}`;
                        }
                    }

                    // Append credit note to toast message
                    if (balance > 0) {
                        message += ` · Credit Due: ₹${balance.toFixed(2)}`;
                        msgType = 'warning';
                    }

                    showToast(message, msgType);
                    
                    // Show wallet panel if they have a balance
                    const walletBal = parseFloat(data.customer.wallet_balance) || 0;
                    window.customerWalletBalance = walletBal;
                    const walletPanel = document.getElementById('wallet_panel');
                    if (walletBal > 0) {
                        document.getElementById('wallet_available_text').textContent = `₹${walletBal.toFixed(2)} Available`;
                        walletPanel.classList.remove('hidden');
                    } else {
                        walletPanel.classList.add('hidden');
                        document.getElementById('use_wallet').checked = false;
                        document.getElementById('wallet_input_container').classList.add('hidden');
                        document.getElementById('wallet_amount_used').value = '';
                    }

                } else {
                    // Customer not found — clear badge
                    document.getElementById('credit_balance_badge').classList.add('hidden');
                    document.getElementById('wallet_panel').classList.add('hidden');
                    document.getElementById('use_wallet').checked = false;
                    document.getElementById('wallet_input_container').classList.add('hidden');
                    document.getElementById('wallet_amount_used').value = '';
                    window.customerWalletBalance = 0;
                }
            }, 500);
        }

        // Wallet Logic
        window.customerWalletBalance = 0;
        function toggleWalletInput() {
            const isChecked = document.getElementById('use_wallet').checked;
            const container = document.getElementById('wallet_input_container');
            if (isChecked) {
                container.classList.remove('hidden');
                setMaxWallet();
            } else {
                container.classList.add('hidden');
                document.getElementById('wallet_amount_used').value = '';
            }
        }

        function setMaxWallet() {
            const maxVal = Math.min(window.customerWalletBalance, parseFloat(document.getElementById('disp_grand').innerText) || 0);
            document.getElementById('wallet_amount_used').value = maxVal.toFixed(2);
        }

        function validateWalletAmount() {
            const input = document.getElementById('wallet_amount_used');
            let val = parseFloat(input.value) || 0;
            const maxVal = Math.min(window.customerWalletBalance, parseFloat(document.getElementById('disp_grand').innerText) || 0);
            if (val > maxVal) {
                input.value = maxVal.toFixed(2);
            }
        }

        async function verifyBillingGstin() {
            const gstin = document.getElementById('customer_gstin').value.trim();
            if (gstin.length !== 15) return;

            const btn = document.getElementById('gst_verify_btn');
            const status = document.getElementById('gst_status');

            btn.disabled = true;
            btn.innerText = '...';
            status.classList.remove('hidden');
            status.innerText = 'Fetching...';
            status.className = 'text-[8px] font-black text-blue-500 uppercase tracking-widest';

            try {
                const response = await fetch(ROUTES.gst_validate, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ gstin: gstin })
                });

                const result = await response.json();
                if (result.success && result.data) {
                    document.getElementById('customer_name').value = result.data.legal_name || document.getElementById('customer_name').value;
                    document.getElementById('customer_address').value = result.data.address || document.getElementById('customer_address').value;

                    status.innerText = 'Verified';
                    status.className = 'text-[8px] font-black text-green-500 uppercase tracking-widest';
                    showToast('GST Verified!', 'success');

                    // Show meter in "loading" state immediately
                    const retContainer = document.getElementById('gst_return_status_container');
                    const retText = document.getElementById('gst_return_status_text');
                    const retBar = document.getElementById('gst_return_meter_bar');
                    retContainer.classList.remove('hidden');
                    retText.className = 'text-[9px] font-bold text-slate-400 dark:text-slate-500';
                    retText.innerText = 'Loading...';
                    retBar.className = 'absolute top-0 left-0 h-full bg-slate-300 dark:bg-slate-700 transition-all duration-300 ease-out animate-pulse';
                    retBar.style.width = '30%';

                    // Async: fetch returns in background without blocking UI
                    const targetMonths = result.data.target_months || 8;
                    fetchGstReturns(gstin, targetMonths, retContainer, retText, retBar);

                } else {
                    status.innerText = result.message || 'Not Found';
                    status.className = 'text-[8px] font-black text-rose-500 uppercase tracking-widest';
                }
            } catch (e) {
                status.innerText = 'Error';
                status.className = 'text-[8px] font-black text-rose-500 uppercase tracking-widest';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Verify';
            }
        }

        async function fetchGstReturns(gstin, targetMonths, retContainer, retText, retBar) {
            try {
                const res = await fetch(ROUTES.gst_returns, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ gstin: gstin })
                });
                const data = await res.json();

                if (!data.success || !data.returns) {
                    retText.className = 'text-[9px] font-bold text-slate-400';
                    retText.innerText = 'No Returns Data';
                    retBar.style.width = '0%';
                    retBar.className = 'absolute top-0 left-0 h-full bg-slate-300 transition-all duration-300';
                    return;
                }

                const returnsBody = data.returns;
                let eFiledList = [];
                if (returnsBody.data && Array.isArray(returnsBody.data)) eFiledList = returnsBody.data;
                else if (returnsBody.EFiledList && Array.isArray(returnsBody.EFiledList)) eFiledList = returnsBody.EFiledList;
                else if (returnsBody.data && returnsBody.data.EFiledList) eFiledList = returnsBody.data.EFiledList;
                else if (Array.isArray(returnsBody)) eFiledList = returnsBody;

                if (eFiledList.length > 0) {
                    let periods = new Set(eFiledList.map(r => r.return_period || r.ret_prd));
                    let filedCount = periods.size;
                    let tm = data.target_months || targetMonths || 8;
                    let healthPercent = Math.min(100, Math.max(0, (filedCount / tm) * 100));
                    let rounded = Math.round(healthPercent);

                    if (healthPercent >= 80) {
                        retBar.className = 'absolute top-0 left-0 h-full bg-green-500 transition-all duration-500 ease-out';
                        retText.className = 'text-[9px] font-bold text-green-600 dark:text-green-500';
                        retText.innerText = 'Excellent (' + rounded + '%)';
                    } else if (healthPercent >= 40) {
                        retBar.className = 'absolute top-0 left-0 h-full bg-amber-500 transition-all duration-500 ease-out';
                        retText.className = 'text-[9px] font-bold text-amber-600 dark:text-amber-500';
                        retText.innerText = 'Average (' + rounded + '%)';
                    } else {
                        retBar.className = 'absolute top-0 left-0 h-full bg-rose-500 transition-all duration-500 ease-out';
                        retText.className = 'text-[9px] font-bold text-rose-600 dark:text-rose-500';
                        retText.innerText = 'Poor (' + rounded + '%)';
                    }
                    retBar.style.width = healthPercent + '%';
                } else {
                    retText.innerText = 'No Returns Found';
                    retBar.style.width = '0%';
                    retBar.className = 'absolute top-0 left-0 h-full bg-slate-300 transition-all duration-300';
                }
            } catch (e) {
                retText.innerText = 'Unavailable';
                retBar.style.width = '0%';
            }
        }

        // ── Recalculate Totals ────────────────────────────────────────────────────
        function recalculate() {
            const items = currentItems();
            const subtotal = items.reduce((s, i) => s + i.total, 0);
            const discPct = parseFloat(document.getElementById('discount_percent').value) || 0;
            const discAmt = parseFloat(document.getElementById('discount_amount').value) || 0;
            const gstPct = parseFloat(document.getElementById('gst_percent').value) || 0;

            const effectiveDisc = discPct > 0 ? subtotal * discPct / 100 : discAmt;
            const afterDiscount = Math.max(0, subtotal - effectiveDisc);

            let gstAmt, raw;
            const gstCalcType = '{{ $settings['gst_calc_type'] ?? 'exclusive' }}';

            if (gstCalcType === 'inclusive') {
                const rate = gstPct / 100;
                gstAmt = afterDiscount * rate / (1 + rate);
                raw = afterDiscount;
            } else {
                gstAmt = afterDiscount * gstPct / 100;
                raw = afterDiscount + gstAmt;
            }

            const rounded = Math.round(raw);
            const roundOff = rounded - raw;
            let grandTotal = rounded;
            
            // Exchange Credit Logic
            let exchangeCreditAmount = {{ isset($creditNote) ? $creditNote->grand_total : 0 }};
            let appliedExchangeCredit = 0;
            
            if (exchangeCreditAmount > 0) {
                appliedExchangeCredit = Math.min(grandTotal, exchangeCreditAmount);
                grandTotal -= appliedExchangeCredit;
                
                // Show exchange credit in UI
                let dispExchangeCredit = document.getElementById('disp_exchange_credit_row');
                if (!dispExchangeCredit) {
                    const rowHtml = `
                    <div id="disp_exchange_credit_row" class="flex justify-between text-violet-600 dark:text-violet-400 font-bold">
                        <span>Exchange Credit:</span>
                        <span id="disp_exchange_credit_val">0.00</span>
                    </div>`;
                    document.getElementById('disp_round').parentElement.insertAdjacentHTML('afterend', rowHtml);
                    dispExchangeCredit = document.getElementById('disp_exchange_credit_row');
                }
                document.getElementById('disp_exchange_credit_val').textContent = '-₹' + appliedExchangeCredit.toFixed(2);
                dispExchangeCredit.classList.remove('hidden');
                
                // Update label to show NET Payable instead of just Grand Total
                const grandTotalLabel = document.getElementById('disp_grand').previousElementSibling;
                if(grandTotalLabel) grandTotalLabel.textContent = 'NET PAYABLE:';
            }

            document.getElementById('disp_subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('disp_after_disc').textContent = afterDiscount.toFixed(2);
            document.getElementById('disp_gst').textContent = gstAmt.toFixed(2);
            document.getElementById('disp_round').textContent = roundOff.toFixed(2);
            document.getElementById('disp_grand').textContent = grandTotal.toFixed(2);
            
            // Trigger wallet logic to cap amount
            if (document.getElementById('use_wallet').checked) {
                const billType = document.getElementById('bill_type_select') ? document.getElementById('bill_type_select').value : 'billing';
                if (billType === 'exchange') {
                    setMaxWallet();
                } else {
                    validateWalletAmount();
                }
            }
        }

        // ── Generate Invoice ──────────────────────────────────────────────────────
        async function generateInvoice() {
            const btn = document.querySelector('button[onclick="generateInvoice()"]');
            if (btn.disabled) return;

            const items = currentItems();
            if (!items.length) { showToast('Add at least one product', 'error'); return; }

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-pulse">Generating...</span>';

            const urlParams = new URLSearchParams(window.location.search);
            const draftBillType = @json(isset($draftBill) ? $draftBill->bill_type : null);
            const uiBillType = document.getElementById('bill_type_select') ? document.getElementById('bill_type_select').value : null;
            const billType = draftBillType || urlParams.get('type') || uiBillType || 'billing';

            // Calculate wallet usage and amount paid
            let walletUsed = 0;
            if (document.getElementById('use_wallet')?.checked) {
                walletUsed = parseFloat(document.getElementById('wallet_amount_used').value) || 0;
            }
            
            let exchangeCreditAmount = {{ isset($creditNote) ? $creditNote->grand_total : 0 }};
            let exchangeCreditUsed = 0;
            if (exchangeCreditAmount > 0) {
                // Determine how much exchange credit was applied during recalculate
                let rawTotal = items.reduce((s, i) => s + i.total, 0);
                const discPct = parseFloat(document.getElementById('discount_percent').value) || 0;
                const discAmt = parseFloat(document.getElementById('discount_amount').value) || 0;
                const effectiveDisc = discPct > 0 ? rawTotal * discPct / 100 : discAmt;
                rawTotal = Math.max(0, rawTotal - effectiveDisc);
                
                const gstPct = parseFloat(document.getElementById('gst_percent').value) || 0;
                const gstCalcType = '{{ $settings['gst_calc_type'] ?? 'exclusive' }}';
                let gstAmt = 0;
                if (gstCalcType === 'inclusive') {
                    const rate = gstPct / 100;
                    gstAmt = rawTotal * rate / (1 + rate);
                } else {
                    gstAmt = rawTotal * gstPct / 100;
                    rawTotal += gstAmt;
                }
                
                let grandTotalBeforeExchange = Math.round(rawTotal);
                exchangeCreditUsed = Math.min(grandTotalBeforeExchange, exchangeCreditAmount);
            }
            
            const dispGrand = parseFloat(document.getElementById('disp_grand').textContent) || 0;
            const amountPaid = Math.max(0, dispGrand - walletUsed);

            const payload = {
                customer_phone: document.getElementById('customer_phone').value,
                customer_gstin: document.getElementById('customer_gstin').value,
                customer_name: document.getElementById('customer_name').value,
                customer_address: document.getElementById('customer_address').value,
                bill_date: document.getElementById('bill_date').value,
                discount_percent: document.getElementById('discount_percent').value || 0,
                discount_amount: document.getElementById('discount_amount').value || 0,
                gst_percent: document.getElementById('gst_percent').value || {{ $settings['default_gst'] ?? 0 }},
                payment_mode: document.querySelector('input[name="payment_mode"]:checked')?.value || 'cash',
                bill_type: billType,
                wallet_used: walletUsed,
                exchange_credit_total: exchangeCreditAmount,
                exchange_credit_used: exchangeCreditUsed,
                exchange_rma_id: urlParams.get('exchange_rma') || null,
                amount_paid: amountPaid,
                items: items.map(i => ({
                    id: i.category_id,
                    qty: i.quantity,
                    price: i.mrp
                }))
            };

            try {
                const res = await fetch(ROUTES.generate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    if (payload.payment_mode === 'credit') {
                        showEMIModal(data);
                    } else {
                        showInvoiceModal(data);
                    }
                } else {
                    showToast(data.message || 'Error generating invoice', 'error');
                }
            } catch (err) {
                showToast('Network error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Generate Invoice';
            }
        }

        // ── Invoice Modal ─────────────────────────────────────────────────────────
        function showInvoiceModal(data) {
            document.getElementById('invoice_content').innerHTML = `
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-950/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg width="24" height="24" class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Invoice Generated!</h3>
                <p class="text-sm text-gray-900 dark:text-slate-400 mt-1">${data.invoice_no}</p>
            </div>
            <div class="bg-gray-50 dark:bg-slate-950/50 border dark:border-slate-800/80 rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-900 dark:text-slate-400">Invoice No</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">${data.invoice_no}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-900 dark:text-slate-400">Grand Total</span>
                    <span class="font-bold text-green-700 dark:text-green-400 text-base">₹${parseFloat(data.grand_total).toFixed(2)}</span>
                </div>
            </div>`;
            const modal = document.getElementById('invoice_modal');
            modal.dataset.billId = data.bill_id;
            modal.classList.remove('hidden');

            if (@json($settings['inv_auto_print'] ?? false)) {
                if (@json($settings['inv_print_pos'] ?? true)) {
                    window.open('/billing/print/' + data.bill_id, '_blank');
                }
                if (@json($settings['inv_print_a4'] ?? true)) {
                    window.open('/billing/invoice/' + data.bill_id, '_blank');
                }
            }
        }

        function closeInvoiceModal() {
            document.getElementById('invoice_modal').classList.add('hidden');
            clearBillItems();
            
            // Redirect if it's draft or exchange mode to avoid generating again
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') || @json(isset($draftBill)) || @json(isset($exchangeRma))) {
                window.location.href = "{{ route('tenant.billing.index') }}";
            }
        }

        async function markForDelivery() {
            const billId = document.getElementById('invoice_modal').dataset.billId;
            try {
                const response = await fetch(`/billing/pre-orders/${billId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'Delivery' })
                });
                const data = await response.json();
                if (data.success) {
                    showToast('Order marked for delivery', 'success');
                    closeInvoiceModal();
                } else {
                    showToast('Failed to update status', 'error');
                }
            } catch (e) {
                showToast('Error updating status', 'error');
            }
        }

        // ── EMI Logic ─────────────────────────────────────────────────────────────
        function showEMIModal(data) {
            document.getElementById('emi_total_display').innerText = '₹' + parseFloat(data.grand_total).toFixed(2);
            document.getElementById('emi_cust_name').innerText = document.getElementById('customer_name').value || 'Walk-in';
            document.getElementById('emi_modal').dataset.billId = data.bill_id;
            document.getElementById('emi_modal').dataset.invoiceData = JSON.stringify(data);
            document.getElementById('emi_modal').classList.remove('hidden');
            calcEMI();
        }

        function calcEMI() {
            const totalText = document.getElementById('emi_total_display').innerText.replace('₹', '');
            const total = parseFloat(totalText);
            const months = parseInt(document.getElementById('emi_months').value);
            const emi = total / months;
            document.getElementById('emi_amount_display').innerText = '₹' + emi.toFixed(2);
        }

        function closeEMI() {
            const modal = document.getElementById('emi_modal');
            modal.classList.add('hidden');
            showToast('Sale saved as credit.', 'success');

            // Show original invoice modal after closing EMI
            const dataStr = modal.dataset.invoiceData;
            if (dataStr) {
                showInvoiceModal(JSON.parse(dataStr));
            }
            clearBillItems();
        }

        async function confirmEMI() {
            const billId = document.getElementById('emi_modal').dataset.billId;
            const months = document.getElementById('emi_months').value;
            const startDate = document.getElementById('emi_start_date').value;
            const total = parseFloat(document.getElementById('emi_total_display').innerText.replace('₹', ''));

            const btn = document.querySelector('button[onclick="confirmEMI()"]');
            btn.disabled = true;
            btn.innerText = 'Creating...';

            try {
                const res = await fetch('{{ route("tenant.instalments.pay") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        bill_id: billId,
                        total_amount: total,
                        months: months,
                        start_date: startDate,
                        type: 'create_plan'
                    })
                });

                const result = await res.json();
                if (result.success) {
                    showToast('EMI Plan created successfully!', 'success');
                    const modal = document.getElementById('emi_modal');
                    modal.classList.add('hidden');

                    // Show invoice modal and clear instead of reload
                    const dataStr = modal.dataset.invoiceData;
                    if (dataStr) {
                        showInvoiceModal(JSON.parse(dataStr));
                    }
                    clearBillItems();

                    // Reset button
                    btn.disabled = false;
                    btn.innerText = 'Confirm EMI Plan';
                } else {
                    showToast(result.message || 'Error creating EMI plan', 'error');
                    btn.disabled = false;
                    btn.innerText = 'Confirm EMI Plan';
                }
            } catch (e) {
                showToast('Error creating EMI plan', 'error');
                btn.disabled = false;
                btn.innerText = 'Confirm EMI Plan';
            }
        }

        // ── Previous Bills ─────────────────────────────────────────────────────────
        async function loadPreviousBills() {
            document.getElementById('prev_bills_modal').classList.remove('hidden');
            const res = await fetch(ROUTES.previous, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            const list = document.getElementById('prev_bills_list');
            if (!data.data?.length) {
                list.innerHTML = '<p class="text-center text-sm text-gray-900 py-8">No previous bills</p>';
                return;
            }

            list.innerHTML = data.data.map(b => `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-slate-800/60 group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all px-2 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg ${b.reminder_enabled ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500'} flex items-center justify-center">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter">${b.invoice_no}</p>
                            ${b.reminder_enabled ? '<span class="text-[8px] font-black bg-blue-600 text-white px-1.5 py-0.5 rounded uppercase tracking-widest">Reminder On</span>' : ''}
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">${b.customer_name || 'Walk-in'} · ${new Date(b.bill_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' })}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black text-slate-900 dark:text-slate-100">₹${parseFloat(b.grand_total).toFixed(2)}</p>
                    <span class="text-[9px] font-black uppercase tracking-widest ${b.status === 'completed' ? 'text-green-500' : 'text-slate-400 dark:text-slate-500'}">
                        ${b.status}
                    </span>
                </div>
            </div>`).join('');
        }

        // ── Utilities ─────────────────────────────────────────────────────────────
        function clearBill() {
            if (!confirm('Clear all items?')) return;
            clearBillItems();
        }

        function clearBillItems() {
            tabs[active].items = [];
            document.getElementById('customer_phone').value = '';
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('discount_percent').value = '';
            document.getElementById('discount_amount').value = '';

            // Clear credit badge
            const creditBadge = document.getElementById('credit_balance_badge');
            if (creditBadge) creditBadge.classList.add('hidden');

            // Clear wallet panel
            const walletPanel = document.getElementById('wallet_panel');
            if (walletPanel) {
                walletPanel.classList.add('hidden');
                document.getElementById('wallet_balance_display').textContent = '₹0.00';
                document.getElementById('wallet_amount_used').value = '';
                const cb = document.getElementById('use_wallet');
                if (cb) cb.checked = false;
                window.customerWalletBalance = 0;
            }

            renderTableRows();
            recalculate();
        }

        function showToast(msg, type = 'info') {
            const t = document.createElement('div');
            t.className = `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition
                       ${type === 'success' ? 'bg-green-600 text-white' :
                    type === 'error' ? 'bg-red-600 text-white' :
                        type === 'warning' ? 'bg-amber-500 text-white' :
                            'bg-gray-800 text-white'}`;
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 4000);
        }

        // ── Keyboard Shortcuts ─────────────────────────────────────────────────────
        document.addEventListener('keydown', e => {
            if (e.key === 'F5') {
                e.preventDefault();
                loadPreviousBills();
            }
            if (e.key === 'F9') {
                e.preventDefault();
                generateInvoice();
            }
            if (e.key === 'F10') {
                e.preventDefault();
                showToast('Return Invoice coming soon', 'info');
            }
            if (e.ctrlKey && e.key === 'Enter') { document.getElementById('scan_input').focus(); }
        });

        // ── Init ───────────────────────────────────────────────────────────────────
        renderTabs();
        renderColFilter();
        renderTableRows();
        recalculate();
        document.getElementById('scan_input').focus();

        @if(isset($exchangeRma))
            if (document.getElementById('customer_phone')) document.getElementById('customer_phone').value = @json($exchangeRma->bill->customer_phone ?? '');
            if (document.getElementById('customer_name')) document.getElementById('customer_name').value = @json($exchangeRma->bill->customer_name ?? '');
            if (document.getElementById('customer_address')) document.getElementById('customer_address').value = @json($exchangeRma->bill->customer_address ?? '');
            if (document.getElementById('customer_gstin')) document.getElementById('customer_gstin').value = @json($exchangeRma->bill->customer_gstin ?? '');
            
            // Set Bill Type to exchange
            if (document.getElementById('bill_type_select')) document.getElementById('bill_type_select').value = 'exchange';
            
            @if(isset($creditNote))
            // Apply credit note as wallet balance instead of discount
            window.customerWalletBalance = parseFloat(@json($creditNote->grand_total ?? 0));
            const wp = document.getElementById('wallet_panel');
            if (wp) {
                wp.classList.remove('hidden');
                document.getElementById('wallet_balance_display').textContent = '₹' + window.customerWalletBalance.toFixed(2);
                document.getElementById('use_wallet').checked = true;
                toggleWalletInput();
            }
            if (document.getElementById('remarks')) document.getElementById('remarks').value = "Exchange against RMA: {{ $exchangeRma->rma_number }}\nCredit Note Applied: {{ $creditNote->invoice_no }}";
            @else
            if (document.getElementById('remarks')) document.getElementById('remarks').value = "Exchange against RMA: {{ $exchangeRma->rma_number }}";
            @endif
            
            recalculate();
        @endif

        @if(isset($draftBill))
            document.getElementById('customer_phone').value = @json($draftBill->customer_phone ?? '');
            document.getElementById('customer_name').value = @json($draftBill->customer_name ?? '');
            document.getElementById('customer_address').value = @json($draftBill->customer_address ?? '');
            document.getElementById('customer_gstin').value = @json($draftBill->customer_gstin ?? '');
            document.getElementById('discount_percent').value = @json($draftBill->discount_percent ?? '');
            document.getElementById('discount_amount').value = @json($draftBill->discount_amount ?? '');

            // Set payment mode
            const paymentMode = @json($draftBill->payment_mode ?? 'cash');
            const paymentRadio = document.querySelector(`input[name="payment_mode"][value="${paymentMode}"]`);
            if (paymentRadio) paymentRadio.checked = true;

            // Load Items
            const draftItems = @json($draftBill->items ?? []);
            tabs[active].items = draftItems.map(item => ({
                category_id: item.category_id,
                barcode: item.barcode || '',
                product_name: item.product_name || '',
                brand: item.brand || '',
                product_type: item.category ? item.category.product_type : '',
                model: item.category ? item.category.model : '',
                size: item.size || '',
                hsn: item.category ? item.category.hsn : '',
                mrp: parseFloat(item.mrp || 0),
                quantity: parseFloat(item.quantity || 1),
                total: parseFloat(item.mrp || 0) * parseFloat(item.quantity || 1)
            }));

            renderTableRows();
            recalculate();
        @endif
    </script>
@endpush