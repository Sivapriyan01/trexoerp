@extends('layouts.tenant')
@section('title', 'Add Purchase')
@section('page-title', 'Purchase Inward')

@php
    $showBarcode = \App\Models\Setting::get('pur_show_barcode', '1') == '1';
    $showColor = \App\Models\Setting::get('pur_show_color', '1') == '1';
    $showImage = \App\Models\Setting::get('pur_show_image', '1') == '1';
    $showDealerPrice = \App\Models\Setting::get('pur_show_dealer_price', '1') == '1';
@endphp

@section('content')
<div class="space-y-6 w-full p-4 md:p-6 animate-in fade-in duration-700" 
     x-data="purchaseForm()" 
     x-init="init()"
     x-cloak>
    
    <!-- 📄 CSV / PDF Upload / Auto-Fill Section -->
    <div class="glass-card rounded-[2.5rem] p-6 relative"
         x-data="{ ocrFileName: '', ocrLoading: false, ocrMsg: '', ocrOk: null, showHint: false }">
        <input type="file" id="ocr_file_input" class="hidden" accept=".csv,.txt,text/plain,text/csv,.pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png"
               @change="
                   ocrFileName = $event.target.files[0] ? $event.target.files[0].name : '';
                   if ($event.target.files[0]) { uploadAndParse($event.target.files[0], $data); }
               ">

        <div class="flex items-center justify-between gap-6 flex-wrap">
            <div class="flex items-center gap-5 flex-1 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-violet-600 flex items-center justify-center text-white shadow-lg shadow-violet-200 dark:shadow-none flex-shrink-0">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Upload Inward Invoice (CSV/PDF)</h2>
                        <!-- Format hint tooltip -->
                        <button type="button" @click="showHint = !showHint"
                                class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-[10px] font-black flex items-center justify-center hover:bg-violet-100 transition-colors"
                                title="Show Format Rules">?</button>
                    </div>
                    <!-- Status line -->
                    <template x-if="ocrLoading">
                        <div class="flex items-center gap-2 mt-0.5">
                            <svg class="w-3 h-3 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/></svg>
                            <p class="text-[10px] font-bold text-violet-500 uppercase tracking-widest">Parsing file, please wait…</p>
                        </div>
                    </template>
                    <template x-if="!ocrLoading && ocrMsg">
                        <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5"
                           :class="ocrOk ? 'text-emerald-500' : 'text-rose-500'" x-text="ocrMsg"></p>
                    </template>
                    <template x-if="!ocrLoading && !ocrMsg">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="ocrFileName || 'Upload a CSV or PDF file to auto-fill product rows'"></p>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <!-- Upload button -->
                <button type="button"
                        onclick="document.getElementById('ocr_file_input').click()"
                        :disabled="ocrLoading"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg shadow-blue-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 flex-shrink-0">
                    <span x-text="ocrLoading ? 'Processing…' : 'Select File'"></span>
                </button>
            </div>
        </div>

        <!-- Format hint panel -->
        <div x-show="showHint" x-transition class="mt-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">📋 Expected Format (CSV/TXT):</p>
            <code class="text-[11px] font-mono text-violet-600 dark:text-violet-400 block">
                product_name, quantity, buy_price, product_type, brand, size, barcode, discount_percent
            </code>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 mb-2">Last 5 columns are optional. Columns without a header row → name, qty, price order.</p>
            
            <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">📄 Expected Format (PDF):</p>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">A standard invoice PDF. We will attempt to automatically extract lines containing Product Name, Quantity, and Price.</p>
        </div>
    </div>

    <!-- 🏢 Vendor & Header Section -->
    <div class="glass-card rounded-[2.5rem] p-8 relative z-20">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="flex-1 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200 dark:shadow-none">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Vendor Details</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identify the source of purchase</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Search Vendor -->
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Search and Select Vendor...</label>
                        <div class="relative group">
                            <input type="text" 
                                   x-model="vendorSearch" 
                                   @focus="showVendorResults = true"
                                   @click.away="showVendorResults = false"
                                   class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all pr-12 text-slate-900 dark:text-white"
                                   placeholder="Type vendor name...">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none opacity-40">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div x-show="showVendorResults && filteredVendors.length > 0" 
                                 class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto">
                                <template x-for="v in filteredVendors" :key="v.id">
                                    <div @click="selectVendor(v)" 
                                         class="px-5 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 cursor-pointer transition-colors border-b border-slate-100 dark:border-slate-700 last:border-0">
                                        <div class="font-bold text-slate-800 dark:text-white text-sm" x-text="v.name"></div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest" x-text="'Phone: ' + (v.phone || 'N/A')"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Date -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Purchase Date</label>
                        <input type="date" 
                               x-model="form.invoice_date"
                               class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white">
                    </div>

                    <!-- Invoice Number -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Invoice Number <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               x-model="form.invoice_ref"
                               class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white"
                               placeholder="Invoice No.">
                    </div>

                    <!-- Tax Type -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Tax Type</label>
                        <select x-model="form.tax_type" @change="updateGlobalTotals()" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white appearance-none cursor-pointer">
                            <option value="auto">Auto-Detect</option>
                            <option value="local">Local (CGST + SGST)</option>
                            <option value="interstate">Interstate (IGST)</option>
                        </select>
                    </div>
                </div>

                <!-- Selected Vendor Badge -->
                <div class="mt-4" x-show="selectedVendor">
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-2xl px-5 py-2.5 flex items-center justify-between w-full max-w-xs group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white">
                                <span class="text-xs font-black" x-text="selectedVendor ? selectedVendor.name.charAt(0) : ''"></span>
                            </div>
                            <div>
                                <div class="text-xs font-black text-blue-900 dark:text-blue-200" x-text="selectedVendor ? selectedVendor.name : ''"></div>
                                <div class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">Active Vendor</div>
                            </div>
                        </div>
                        <button @click="selectedVendor = null; form.vendor_id = ''" class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-400 hover:text-rose-500 ml-4">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" @click="showAddVendorModal = true" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg shadow-emerald-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    Add Vendor
                </button>
            </div>
        </div>
    </div>

    <!-- 📦 Items Section -->
    <div class="glass-card rounded-[2.5rem] overflow-hidden flex flex-col min-h-[500px]">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Items</h3>
                <div class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest" x-text="'Total Qty : ' + totalQuantity"></div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Columns Filter Dropdown -->
                <div class="relative" x-data="{ openCols: false }">
                    <button type="button" @click="openCols = !openCols" @click.away="openCols = false" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-sm flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Columns
                    </button>
                    <div x-show="openCols" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 p-2 overflow-hidden flex flex-col gap-1">
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showImage" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Image</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showColor" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Color</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showBarcode" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Barcode</span>
                        </label>
                        <div class="h-px bg-slate-100 dark:bg-slate-700 my-1"></div>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showProductType" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Product Type</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showSize" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Size</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showBrand" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Brand</span>
                        </label>
                        <div class="h-px bg-slate-100 dark:bg-slate-700 my-1"></div>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showDealerPrice" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Dealer Price</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showDiscount" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Discount Columns</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" x-model="showTax" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">GST Columns</span>
                        </label>
                        <template x-for="(col, i) in customColumns" :key="i">
                            <div class="flex items-center justify-between px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="col.show" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="col.name"></span>
                                </label>
                                <button type="button" @click.stop="deleteCustomColumn(i)" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Delete Column">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="button" @click="showAddCustomColumnModal = true" class="px-5 py-2.5 bg-violet-500 hover:bg-violet-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-md shadow-violet-100 dark:shadow-none">
                    Add Custom Col
                </button>
                <button type="button" @click="showAddChargesModal = true" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-md shadow-amber-100 dark:shadow-none">
                    Add Charges
                </button>
                <button type="button" @click="addItem()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-md shadow-blue-100 dark:shadow-none">
                    Add Product
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 w-16">S.NO</th>
                        <th x-show="showImage" class="px-4 py-4 w-16 text-center">IMAGE</th>
                        <th class="px-4 py-4 min-w-[250px]">PRODUCT NAME (VIEW)</th>
                        <th x-show="showBarcode" class="px-4 py-4 min-w-[120px]">BARCODE</th>
                        <th x-show="showProductType" class="px-4 py-4 min-w-[120px]">PRODUCT TYPE</th>
                        <th x-show="showSize" class="px-4 py-4 min-w-[100px]">SIZE</th>
                        <th x-show="showColor" class="px-4 py-4 min-w-[100px]">COLOR</th>
                        <th x-show="showBrand" class="px-4 py-4 min-w-[100px]">BRAND</th>
                        <th class="px-4 py-4 min-w-[120px]">QUANTITY</th>
                        <th x-show="showDealerPrice" class="px-4 py-4 min-w-[120px] text-right">DEALER PRICE</th>
                        <th class="px-4 py-4 min-w-[160px]">PER PIECE BUYING VALUE</th>
                        <th class="px-4 py-4 min-w-[140px]">TOTAL AMOUNT</th>
                        <th x-show="showTax" class="px-4 py-4 min-w-[100px]">GST %</th>
                        <th x-show="showTax" class="px-4 py-4 min-w-[120px]">GST AMT</th>
                        <th x-show="showDiscount" class="px-4 py-4 min-w-[100px]">DISCOUNT %</th>
                        <th x-show="showDiscount" class="px-4 py-4 min-w-[140px]">DISCOUNT AM</th>
                        <template x-for="(col, i) in customColumns" :key="i">
                            <th x-show="col.show" class="px-4 py-4 min-w-[120px] uppercase" x-text="col.name"></th>
                        </template>
                        <th class="px-4 py-4 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <template x-for="(item, index) in form.items" :key="index">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors group">
                            <td class="px-6 py-4 text-xs font-black text-slate-400" x-text="index + 1"></td>
                            <td x-show="showImage" class="px-4 py-4">
                                <div class="flex items-center justify-center">
                                    <template x-if="item.image">
                                        <img :src="'/storage/' + item.image" class="w-8 h-8 rounded-lg object-cover shadow-sm">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="relative group/search">
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               x-model="item.product_name"
                                               @input="searchProduct($event.target.value, index)"
                                               class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white"
                                               placeholder="Product Name">
                                        <button type="button" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                    <!-- Product Suggestions -->
                                    <div x-show="item.showSuggestions && filteredProducts[index]?.length > 0"
                                         class="absolute z-[60] w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-40 overflow-y-auto">
                                        <template x-for="p in filteredProducts[index]" :key="p.id">
                                            <div @click="selectProduct(p, index)"
                                                 class="px-4 py-2.5 hover:bg-blue-50 dark:hover:bg-slate-700 cursor-pointer transition-colors border-b border-slate-50 dark:border-slate-700 last:border-0">
                                                <div class="font-bold text-slate-800 dark:text-white text-[11px]" x-text="p.product_name"></div>
                                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest" x-text="p.brand + ' | ' + p.product_type"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td x-show="showBarcode" class="px-4 py-4">
                                <input type="text" x-model="item.barcode" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <td x-show="showProductType" class="px-4 py-4">
                                <input type="text" x-model="item.product_type" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td x-show="showSize" class="px-4 py-4">
                                <select x-model="item.size" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white appearance-none">
                                    <option value="">Size</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                </select>
                            </td>
                            <td x-show="showColor" class="px-4 py-4">
                                <input type="text" x-model="item.color" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <td x-show="showBrand" class="px-4 py-4">
                                <input type="text" x-model="item.brand" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" x-model.number="item.quantity" @input="calculateRow(index)" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td x-show="showDealerPrice" class="px-4 py-4">
                                <input type="text" :value="item.dealer_price ? '₹' + parseFloat(item.dealer_price).toFixed(2) : '—'" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" x-model.number="item.buy_price" @input="calculateRow(index)" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white" placeholder="0.00">
                            </td>
                            <td class="px-4 py-4">
                                <input type="text" :value="'₹' + item.total_amount.toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <td x-show="showTax" class="px-4 py-4">
                                <input type="number" x-model.number="item.tax_percent" @input="calculateRow(index)" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white" placeholder="0">
                            </td>
                            <td x-show="showTax" class="px-4 py-4">
                                <input type="text" :value="'₹' + item.tax_amount.toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <td x-show="showDiscount" class="px-4 py-4">
                                <input type="number" x-model.number="item.discount_percent" @input="calculateRow(index)" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td x-show="showDiscount" class="px-4 py-4">
                                <input type="text" :value="'₹' + item.discount_amount.toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                            </td>
                            <template x-for="(col, i) in customColumns" :key="i">
                                <td x-show="col.show" class="px-4 py-4">
                                    <template x-if="col.type === 'text'">
                                        <input type="text" x-model="item.custom_fields[col.name]" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                                    </template>
                                    <template x-if="col.type === 'number'">
                                        <input type="number" x-model="item.custom_fields[col.name]" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                                    </template>
                                    <template x-if="col.type === 'date'">
                                        <input type="date" x-model="item.custom_fields[col.name]" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                                    </template>
                                </td>
                            </template>
                            <td class="px-4 py-4">
                                <button @click="removeItem(index)" class="text-slate-300 hover:text-rose-500 transition-colors">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 📊 Footer & Summary Section -->
    <div class="glass-card rounded-[2.5rem] p-8">
        <div class="flex flex-col gap-8">
            <!-- Main Footer Row -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Remark</label>
                    <input type="text" x-model="form.remark" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white" placeholder="Remark">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Discount (%)</label>
                    <input type="number" x-model.number="form.discount_percent" @input="updateGlobalTotals" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Discount Amount</label>
                    <input type="text" :value="'₹' + (parseFloat(form.discount_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                </div>
                <!-- Tax Split -->
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Tax Split</label>
                    <div class="w-full bg-slate-100 dark:bg-slate-800/80 rounded-xl px-4 py-2.5 text-xs font-black text-slate-500 dark:text-slate-400">
                        <template x-if="form.tax_type !== 'interstate'">
                            <span>
                                CGST: ₹<span x-text="(form.cgst_amount || 0).toFixed(2)"></span>
                                &nbsp; SGST: ₹<span x-text="(form.sgst_amount || 0).toFixed(2)"></span>
                            </span>
                        </template>
                        <template x-if="form.tax_type === 'interstate'">
                            <span>IGST: ₹<span x-text="(form.gst_amount || 0).toFixed(2)"></span></span>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">GST Amount</label>
                    <input type="text" :value="'₹' + (parseFloat(form.gst_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Total</label>
                    <input type="text" :value="'₹' + (parseFloat(form.total_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-blue-600 dark:text-blue-400 outline-none">
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <button type="button" class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg shadow-orange-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        Previous Barcode
                    </button>
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">F8 : View Edit Purchase</span>
                    </div>
                </div>
                <button type="button" @click="submitForm" class="px-10 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-emerald-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    Generate Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- 🏷️ Add Vendor Modal -->
    <div x-show="showAddVendorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
        <div class="glass-card w-full max-w-md rounded-[2.5rem] overflow-hidden" @click.away="showAddVendorModal = false">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Add New Vendor</h3>
            </div>
            <div class="p-8 space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Vendor Name</label>
                    <input type="text" x-model="newVendor.name" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Phone</label>
                        <input type="text" x-model="newVendor.phone" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">GSTIN</label>
                        <input type="text" x-model="newVendor.gstin"
                               placeholder="22AAAAA0000A1Z5"
                               maxlength="15"
                               title="Valid 15-char GSTIN e.g. 22AAAAA0000A1Z5"
                               class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase tracking-widest">
                    </div>
                </div>
            </div>
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/30 flex justify-end gap-3">
                <button @click="showAddVendorModal = false" class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 uppercase tracking-widest">Cancel</button>
                <button @click="saveVendor" class="px-6 py-2 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-200 dark:shadow-none">Save Vendor</button>
            </div>
        </div>
    </div>

    <!-- ⚡ Add Charges Modal -->
    <div x-show="showAddChargesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
        <div class="glass-card w-full max-w-md rounded-[2.5rem] overflow-hidden" @click.away="showAddChargesModal = false">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Add Extra Charges</h3>
            </div>
            <div class="p-8 space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Charge Name</label>
                    <input type="text" x-model="newCharge.name" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" placeholder="Shipping, Loading, etc.">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Amount (₹)</label>
                    <input type="number" x-model.number="newCharge.amount" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/30 flex justify-end gap-3">
                <button @click="showAddChargesModal = false" class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 uppercase tracking-widest">Cancel</button>
                <button @click="saveCharge" class="px-6 py-2 bg-amber-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-amber-200 dark:shadow-none">Apply Charge</button>
            </div>
        </div>
    </div>

    <!-- ⚡ Add Custom Column Modal -->
    <div x-show="showAddCustomColumnModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
        <div class="glass-card w-full max-w-md rounded-[2.5rem] overflow-hidden" @click.away="showAddCustomColumnModal = false">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Add Custom Column</h3>
            </div>
            <div class="p-8 space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Column Name</label>
                    <input type="text" x-model="newCustomColumnName" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. HSN Code, Remarks...">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Input Method / Type</label>
                    <select x-model="newCustomColumnType" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="text">Text Input</option>
                        <option value="number">Number</option>
                        <option value="date">Date Picker</option>
                    </select>
                </div>
            </div>
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/30 flex justify-end gap-3">
                <button @click="showAddCustomColumnModal = false" class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 uppercase tracking-widest">Cancel</button>
                <button @click="saveCustomColumn" class="px-6 py-2 bg-violet-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-violet-200 dark:shadow-none">Add Column</button>
            </div>
        </div>
    </div>
</div>

<script>
function purchaseForm() {
    return {
        init() {
            // Load saved Custom Columns
            const savedCustomCols = localStorage.getItem('purchase_custom_cols');
            if (savedCustomCols) {
                try {
                    this.customColumns = JSON.parse(savedCustomCols);
                    this.form.items.forEach(item => {
                        if (!item.custom_fields) item.custom_fields = {};
                        this.customColumns.forEach(c => {
                            if (item.custom_fields[c.name] === undefined) item.custom_fields[c.name] = '';
                        });
                    });
                } catch(e) {}
            }
            
            // Load saved visibility
            const savedVis = localStorage.getItem('purchase_col_vis');
            if (savedVis) {
                try {
                    const vis = JSON.parse(savedVis);
                    ['showBarcode', 'showColor', 'showImage', 'showDealerPrice', 'showProductType', 'showSize', 'showBrand', 'showDiscount', 'showTax'].forEach(c => {
                        if (vis[c] !== undefined) this[c] = vis[c];
                    });
                } catch(e) {}
            }
            
            this.updateGlobalTotals();

            // Watch for changes to auto-save
            this.$watch('customColumns', (val) => {
                localStorage.setItem('purchase_custom_cols', JSON.stringify(val));
            });

            const watchCols = ['showBarcode', 'showColor', 'showImage', 'showDealerPrice', 'showProductType', 'showSize', 'showBrand', 'showDiscount', 'showTax'];
            watchCols.forEach(col => {
                this.$watch(col, () => {
                    const vis = {};
                    watchCols.forEach(c => vis[c] = this[c]);
                    localStorage.setItem('purchase_col_vis', JSON.stringify(vis));
                });
            });
        },
        vendors: @json($vendors),
        products: @json($products),
        vendorSearch: '',
        showVendorResults: false,
        selectedVendor: null,
        filteredProducts: [],
        
        // Setup table dynamic visibility settings
        showBarcode: {{ $showBarcode ? 'true' : 'false' }},
        showColor: {{ $showColor ? 'true' : 'false' }},
        showImage: {{ $showImage ? 'true' : 'false' }},
        showDealerPrice: {{ $showDealerPrice ? 'true' : 'false' }},
        showProductType: false,
        showSize: false,
        showBrand: false,
        showDiscount: false,
        showTax: false,
        customColumns: [],
        
        // Modals
        showAddVendorModal: false,
        showAddChargesModal: false,
        showAddCustomColumnModal: false,
        newVendor: { name: '', phone: '', gstin: '' },
        newCharge: { name: '', amount: 0 },
        newCustomColumnName: '',
        newCustomColumnType: 'text',

        form: {
            vendor_id: '',
            invoice_ref: '',
            invoice_date: '{{ date('Y-m-d') }}',
            tax_type: 'auto',
            remark: '',
            discount_percent: 0,
            discount_amount: 0,
            gst_percent: 18,
            gst_amount: 0,
            cgst_amount: 0,
            sgst_amount: 0,
            total_amount: 0,
            items: [
                { 
                    product_id: '',
                    product_name: '', 
                    product_type: 'General', 
                    size: '', 
                    brand: 'Default', 
                    quantity: 1, 
                    buy_price: 0, 
                    total_amount: 0, 
                    discount_percent: 0, 
                    discount_amount: 0,
                    tax_percent: 0,
                    tax_amount: 0,
                    barcode: '',
                    color: '',
                    image: '',
                    dealer_price: 0,
                    custom_fields: {},
                    showSuggestions: false
                }
            ],
            charges: []
        },

        get filteredVendors() {
            if (this.vendorSearch.length < 1) return [];
            return this.vendors.filter(v => 
                v.name.toLowerCase().includes(this.vendorSearch.toLowerCase())
            );
        },

        get totalQuantity() {
            return this.form.items.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0);
        },

        get subtotal() {
            let itemSub = this.form.items.reduce((sum, item) => sum + (parseFloat(item.total_amount) || 0), 0);
            let chargeSub = this.form.charges.reduce((sum, c) => sum + (parseFloat(c.amount) || 0), 0);
            return itemSub + chargeSub;
        },

        selectVendor(vendor) {
            this.selectedVendor = vendor;
            this.form.vendor_id = vendor.id;
            this.vendorSearch = vendor.name;
            this.showVendorResults = false;
        },

        async saveVendor() {
            if (!this.newVendor.name) return alert('Name is required');
            
            // Validate GSTIN format if provided
            if (this.newVendor.gstin) {
                const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i;
                if (!gstinRegex.test(this.newVendor.gstin.trim())) {
                    alert('Please enter a valid 15-character GSTIN number (e.g. 22AAAAA0000A1Z5).');
                    return;
                }
            }

            let res, data;
            try {
                res = await fetch('{{ route('tenant.suppliers.store') }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newVendor)
                });
                data = await res.json();
            } catch (e) {
                alert('Network error: could not reach the server. Please try again.');
                return;
            }

            if (data.success) {
                this.vendors.push(data.supplier);
                this.selectVendor(data.supplier);
                this.showAddVendorModal = false;
                this.newVendor = { name: '', phone: '', gstin: '' };
            } else if (data.errors) {
                // Laravel validation errors
                const msgs = Object.values(data.errors).flat().join('\n');
                alert(msgs);
            } else {
                alert(data.message || 'Failed to save vendor. Please check the details and try again.');
            }
        },

        saveCharge() {
            if (!this.newCharge.name || !this.newCharge.amount) return alert('Details required');
            this.form.charges.push({ ...this.newCharge });
            this.updateGlobalTotals();
            
            this.addItem(); 
            let lastIdx = this.form.items.length - 1;
            this.form.items[lastIdx].product_name = 'CHARGES: ' + this.newCharge.name;
            this.form.items[lastIdx].buy_price = this.newCharge.amount;
            this.form.items[lastIdx].quantity = 1;
            this.calculateRow(lastIdx);
            
            this.showAddChargesModal = false;
            this.newCharge = { name: '', amount: 0 };
        },

        saveCustomColumn() {
            if (!this.newCustomColumnName) return alert('Column name required');
            let colName = this.newCustomColumnName.trim();
            // check if exists
            if (this.customColumns.find(c => c.name.toLowerCase() === colName.toLowerCase())) {
                alert('Column already exists!');
                return;
            }
            this.customColumns = [...this.customColumns, { name: colName, type: this.newCustomColumnType, show: true }];
            // Initialize this field for all existing rows
            this.form.items.forEach(item => {
                if (!item.custom_fields || typeof item.custom_fields !== 'object') {
                    item.custom_fields = {};
                }
                item.custom_fields[colName] = '';
            });
            this.showAddCustomColumnModal = false;
            this.newCustomColumnName = '';
            this.newCustomColumnType = 'text';
        },

        deleteCustomColumn(index) {
            if(!confirm('Are you sure you want to delete this column?')) return;
            const colName = this.customColumns[index].name;
            this.customColumns = this.customColumns.filter((_, idx) => idx !== index);
            
            this.form.items.forEach(item => {
                if (item.custom_fields && item.custom_fields[colName] !== undefined) {
                    delete item.custom_fields[colName];
                }
            });
        },

        addItem() {
            const newItem = {
                product_id: '',
                product_name: '', 
                product_type: 'General', 
                size: '', 
                brand: 'Default', 
                quantity: 1, 
                buy_price: 0, 
                total_amount: 0, 
                discount_percent: 0, 
                discount_amount: 0,
                tax_percent: 0,
                tax_amount: 0,
                barcode: '',
                color: '',
                image: '',
                dealer_price: 0,
                custom_fields: {},
                showSuggestions: false
            };
            this.customColumns.forEach(c => newItem.custom_fields[c.name] = '');
            this.form.items.push(newItem);
        },

        removeItem(index) {
            if (this.form.items.length > 1) {
                this.form.items.splice(index, 1);
                this.updateGlobalTotals();
            }
        },

        searchProduct(query, index) {
            if (!this.filteredProducts[index]) this.filteredProducts[index] = [];
            
            if (query.length < 2) {
                this.form.items[index].showSuggestions = false;
                return;
            }

            this.filteredProducts[index] = this.products.filter(p => 
                p.product_name.toLowerCase().includes(query.toLowerCase()) ||
                (p.barcode && p.barcode.toLowerCase().includes(query.toLowerCase()))
            ).slice(0, 5);
            
            this.form.items[index].showSuggestions = true;
        },

        selectProduct(product, index) {
            let item = this.form.items[index];
            item.product_id = product.id;
            item.product_name = product.product_name;
            item.product_type = product.product_type;
            item.brand = product.brand;
            item.size = product.size;
            item.buy_price = parseFloat(product.dealer_price) || 0;
            
            item.barcode = product.barcode || '';
            item.color = product.color || '';
            item.image = product.image || '';
            item.dealer_price = product.dealer_price || 0;
            item.tax_percent = parseFloat(product.gst) || 0;
            
            item.showSuggestions = false;
            this.calculateRow(index);
        },

        calculateRow(index) {
            let item = this.form.items[index];
            let subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.buy_price) || 0);
            item.discount_amount = subtotal * ((parseFloat(item.discount_percent) || 0) / 100);
            let taxable = subtotal - item.discount_amount;
            item.tax_amount = taxable * ((parseFloat(item.tax_percent) || 0) / 100);
            item.total_amount = taxable + item.tax_amount;
            this.updateGlobalTotals();
        },

        updateGlobalTotals() {
            let sub = this.subtotal;
            this.form.discount_amount = sub * ((parseFloat(this.form.discount_percent) || 0) / 100);
            let taxable = sub - this.form.discount_amount;
            this.form.gst_amount = taxable * ((parseFloat(this.form.gst_percent) || 0) / 100);
            // CGST / SGST / IGST split
            if (this.form.tax_type === 'interstate') {
                this.form.cgst_amount = 0;
                this.form.sgst_amount = 0;
            } else {
                this.form.cgst_amount = this.form.gst_amount / 2;
                this.form.sgst_amount = this.form.gst_amount / 2;
            }
            this.form.total_amount = taxable + this.form.gst_amount;
        },

        async uploadAndParse(file, $data) {
            $data.ocrLoading = true;
            $data.ocrMsg = '';
            $data.ocrOk  = null;

            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', '{{ csrf_token() }}');

            try {
                const res  = await fetch('{{ route('tenant.purchase.parse-upload') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();

                if (data.success && data.items && data.items.length > 0) {
                    // Replace form items with the parsed rows, applying fallback tax
                    data.items.forEach(item => {
                        if (item.tax_percent === 0) {
                            item.tax_percent = parseFloat(this.form.gst_percent) || 18;
                            let sub = (parseFloat(item.quantity) || 0) * (parseFloat(item.buy_price) || 0);
                            let disc = sub * ((parseFloat(item.discount_percent) || 0) / 100);
                            let taxable = sub - disc;
                            item.tax_amount = taxable * (item.tax_percent / 100);
                            item.total_amount = taxable + item.tax_amount;
                        }
                    });
                    this.form.items = data.items;
                    this.updateGlobalTotals();

                    // Auto-fill Extracted Data (Vendor & Invoice Ref)
                    if (data.extracted) {
                        if (data.extracted.invoice_ref) {
                            this.form.invoice_ref = data.extracted.invoice_ref;
                        }
                        if (data.extracted.vendor_id) {
                            let matchedVendor = this.vendors.find(v => v.id == data.extracted.vendor_id);
                            if (matchedVendor) {
                                this.selectVendor(matchedVendor);
                            }
                        }
                    }

                    $data.ocrMsg = `✅ ${data.count} product${data.count > 1 ? 's' : ''} loaded from file!`;
                    $data.ocrOk  = true;
                } else {
                    $data.ocrMsg = data.message || 'No rows found in file.';
                    $data.ocrOk  = false;
                }
            } catch (e) {
                $data.ocrMsg = 'Network error – could not parse file.';
                $data.ocrOk  = false;
            } finally {
                $data.ocrLoading = false;
                // Reset the file input so the same file can be re-uploaded
                document.getElementById('ocr_file_input').value = '';
            }
        },

        async submitForm() {
            if (!this.form.vendor_id) return alert('Please select a vendor');
            if (!this.form.invoice_ref) return alert('Please enter invoice reference');
            
            try {
                const response = await fetch('{{ route('tenant.purchase.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                });

                if (response.ok) {
                    const data = await response.json();
                    window.location.href = data.redirect || '{{ route('tenant.purchase.index') }}';
                } else {
                    const data = await response.json();
                    alert('Error: ' + (data.message || 'Submission failed'));
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                alert('An error occurred during submission.');
            }
        }
    }
}
</script>
@endsection
