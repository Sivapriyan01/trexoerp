@extends('layouts.tenant')
@section('title', 'Purchase Order Edit')
@section('page-title', 'Purchase Order Edit')

@php
    $showBarcode = \App\Models\Setting::get('pur_show_barcode', '1') == '1';
    $showColor = \App\Models\Setting::get('pur_show_color', '1') == '1';
    $showImage = \App\Models\Setting::get('pur_show_image', '1') == '1';
    $showDealerPrice = \App\Models\Setting::get('pur_show_dealer_price', '1') == '1';
@endphp

@section('content')
<div class="space-y-6 max-w-[1600px] mx-auto animate-in fade-in duration-700" 
     x-data="purchaseForm()" 
     x-init="init()"
     x-cloak>
    
    <!-- 🏢 Vendor & Header Section -->
    <div class="glass-card rounded-[2.5rem] p-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="flex-1 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200 dark:shadow-none">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Purchase Order Edit</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Update existing purchase order details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Search Vendor -->
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Search and select vendor...</label>
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
                            
                            <!-- Vendor Results Dropdown -->
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

                    <!-- Date Selection -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Purchase Date</label>
                        <input type="date" 
                               x-model="form.invoice_date"
                               class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white">
                    </div>

                    <!-- Selected Vendor Badge -->
                    <div class="flex items-end pb-1" x-show="selectedVendor">
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-2xl px-5 py-2.5 flex items-center justify-between w-full group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white">
                                    <span class="text-xs font-black" x-text="selectedVendor.name.charAt(0)"></span>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-blue-900 dark:text-blue-200" x-text="selectedVendor.name"></div>
                                    <div class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">Active Vendor</div>
                                </div>
                            </div>
                            <button @click="selectedVendor = null; form.vendor_id = ''" class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-400 hover:text-rose-500">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
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
                        <th class="px-4 py-4 min-w-[120px]">PRODUCT TYPE</th>
                        <th class="px-4 py-4 min-w-[100px]">SIZE</th>
                        <th x-show="showColor" class="px-4 py-4 min-w-[100px]">COLOR</th>
                        <th class="px-4 py-4 min-w-[100px]">BRAND</th>
                        <th class="px-4 py-4 w-28">QUANTITY</th>
                        <th x-show="showDealerPrice" class="px-4 py-4 min-w-[120px] text-right">DEALER PRICE</th>
                        <th class="px-4 py-4 min-w-[160px]">PER PIECE BUYING VALUE</th>
                        <th class="px-4 py-4 min-w-[140px]">TOTAL AMOUNT</th>
                        <th class="px-4 py-4 w-24">DISCOUNT %</th>
                        <th class="px-4 py-4 min-w-[140px]">DISCOUNT AM</th>
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
                            <td class="px-4 py-4">
                                <input type="text" x-model="item.product_type" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td class="px-4 py-4">
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
                            <td class="px-4 py-4">
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
                            <td class="px-4 py-4">
                                <input type="number" x-model.number="item.discount_percent" @input="calculateRow(index)" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                            </td>
                            <td class="px-4 py-4">
                                <input type="text" :value="'₹' + item.discount_amount.toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                            </td>
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
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 items-end">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Vendor Invoice Ref <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.invoice_ref" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white" placeholder="Invoice Ref">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Invoice Date</label>
                    <input type="date" x-model="form.invoice_date" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-900 dark:text-white">
                </div>
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
                    <input type="text" :value="(parseFloat(form.discount_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">GST (%)</label>
                    <input type="number" x-model.number="form.gst_percent" @input="updateGlobalTotals" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-black text-center focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">GST Amount</label>
                    <input type="text" :value="(parseFloat(form.gst_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-slate-500 dark:text-slate-400 outline-none">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Total</label>
                    <input type="text" :value="(parseFloat(form.total_amount) || 0).toFixed(2)" readonly class="w-full bg-slate-100 dark:bg-slate-800/80 border-0 rounded-xl px-4 py-2.5 text-xs font-black text-right text-blue-600 dark:text-blue-400 outline-none">
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <a href="{{ route('tenant.purchase.index') }}" class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        Cancel
                    </a>
                </div>
                <button type="button" @click="submitForm" class="px-10 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-blue-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    Update Purchase Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function purchaseForm() {
    return {
        init() {
            this.updateGlobalTotals();
        },
        vendors: @json($vendors),
        products: @json($products),
        vendorSearch: '{{ $purchase->vendor->name ?? '' }}',
        showVendorResults: false,
        selectedVendor: @json($purchase->vendor),
        filteredProducts: [],
        
        // Setup table dynamic visibility settings
        showBarcode: {{ $showBarcode ? 'true' : 'false' }},
        showColor: {{ $showColor ? 'true' : 'false' }},
        showImage: {{ $showImage ? 'true' : 'false' }},
        showDealerPrice: {{ $showDealerPrice ? 'true' : 'false' }},
        
        // Modals
        showAddVendorModal: false,
        showAddChargesModal: false,
        newVendor: { name: '', phone: '', gstin: '' },
        newCharge: { name: '', amount: 0 },

        form: {
            id: '{{ $purchase->id }}',
            vendor_id: '{{ $purchase->vendor_id }}',
            invoice_ref: '{{ $purchase->invoice_ref }}',
            invoice_date: '{{ $purchase->invoice_date->format('Y-m-d') }}',
            remark: '{{ $purchase->remark }}',
            discount_percent: {{ $purchase->discount_percent ?? 0 }},
            discount_amount: {{ $purchase->discount_amount ?? 0 }},
            gst_percent: {{ $purchase->gst_percent ?? 0 }},
            gst_amount: {{ $purchase->gst_amount ?? 0 }},
            total_amount: {{ $purchase->total_amount ?? 0 }},
            items: @json($items),
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

        addItem() {
            this.form.items.push({
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
                barcode: '',
                color: '',
                image: '',
                dealer_price: 0,
                showSuggestions: false
            });
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
            
            item.showSuggestions = false;
            this.calculateRow(index);
        },

        calculateRow(index) {
            let item = this.form.items[index];
            let subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.buy_price) || 0);
            item.discount_amount = subtotal * ((parseFloat(item.discount_percent) || 0) / 100);
            item.total_amount = subtotal - item.discount_amount;
            this.updateGlobalTotals();
        },

        updateGlobalTotals() {
            let sub = this.subtotal;
            this.form.discount_amount = sub * ((parseFloat(this.form.discount_percent) || 0) / 100);
            let taxable = sub - this.form.discount_amount;
            this.form.gst_amount = taxable * ((parseFloat(this.form.gst_percent) || 0) / 100);
            this.form.total_amount = taxable + this.form.gst_amount;
        },

        async submitForm() {
            if (!this.form.vendor_id) return alert('Please select a vendor');
            if (!this.form.invoice_ref) return alert('Please enter invoice reference');
            
            try {
                const response = await fetch('{{ route('tenant.purchase.update', $purchase->id) }}', {
                    method: 'PUT',
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
                    alert('Error: ' + (data.message || 'Update failed'));
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

