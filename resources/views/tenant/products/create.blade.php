@extends('layouts.tenant')
@section('title', 'Add Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="glass-card rounded-[2rem] border border-slate-100 dark:border-slate-800/80 overflow-hidden shadow-2xl shadow-blue-100 dark:shadow-none bg-white dark:bg-slate-900/40">
        <div class="p-8 bg-gradient-to-r from-blue-600 to-violet-700 text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black tracking-tight">Product Details</h3>
                <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest">Create a new SKU in master list</p>
            </div>
            <a href="{{ route('tenant.products.index') }}" class="p-2 bg-white/10 hover:bg-white/20 rounded-xl transition">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>

        <form action="{{ route('tenant.products.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6 bg-white/30 dark:bg-transparent">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Basic Info --}}
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/60 pb-2">Basic Identification</h4>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Product Name *</label>
                        <input type="text" name="product_name" required value="{{ old('product_name') }}"
                               class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                               placeholder="e.g. Cotton T-Shirt (Premium)">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Barcode / SKU</label>
                            <input type="text" name="barcode" value="{{ old('barcode') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                                   placeholder="Scan or Type">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Brand</label>
                            <input type="text" name="brand" value="{{ old('brand') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                                   placeholder="e.g. Nike">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Type / Category</label>
                            <input type="text" name="product_type" value="{{ old('product_type') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                                   placeholder="e.g. Apparel">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Size / Variant</label>
                            <input type="text" name="size" value="{{ old('size') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                                   placeholder="e.g. XL, 500ml">
                        </div>
                    </div>
                </div>

                {{-- Pricing & Stock --}}
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/60 pb-2">Pricing & Inventory</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">MRP (Sales Price) *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400 dark:text-slate-500 font-bold text-xs">₹</span>
                                <input type="number" step="0.01" name="mrp" required value="{{ old('mrp') }}"
                                       class="w-full pl-8 pr-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-black text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-50 dark:focus:ring-slate-800 outline-none transition-all">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Dealer/Buy Price</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400 dark:text-slate-500 font-bold text-xs">₹</span>
                                <input type="number" step="0.01" name="dealer_price" value="{{ old('dealer_price') }}"
                                       class="w-full pl-8 pr-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-black text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-50 dark:focus:ring-slate-800 outline-none transition-all">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Discount % (Optional)</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ old('discount_percent', 0) }}" placeholder="0 for no discount"
                                       class="w-full pl-4 pr-8 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-black text-slate-900 dark:text-white focus:ring-4 focus:ring-rose-50 dark:focus:ring-slate-800 outline-none transition-all">
                                <span class="absolute right-4 top-3.5 text-slate-400 dark:text-slate-500 font-bold text-xs">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">GST %</label>
                            <select name="gst" class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all appearance-none">
                                @foreach([0, 5, 12, 18, 28] as $rate)
                                    <option value="{{ $rate }}" {{ old('gst', 18) == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">HSN Code</label>
                            <input type="text" name="hsn" value="{{ old('hsn') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Initial Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-black text-blue-600 dark:text-blue-400 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Low Stock Alert</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 5) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-black text-rose-500 dark:text-rose-400 focus:ring-4 focus:ring-rose-50 dark:focus:ring-slate-800 outline-none transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase px-1">Expiry Date</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-3">
                        <input type="hidden" name="pre_order_available" value="0">
                        <input type="checkbox" name="pre_order_available" value="1" {{ old('pre_order_available') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-slate-350 dark:border-slate-750 rounded focus:ring-blue-500 bg-white dark:bg-slate-950">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Pre-Order Available</label>
                    </div>

                    <div class="flex items-center gap-2 pt-2 p-3 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
                        <input type="hidden" name="show_on_website" value="0">
                        <input type="checkbox" name="show_on_website" id="show_on_website" value="1" {{ old('show_on_website') ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-slate-350 dark:border-slate-750 rounded focus:ring-indigo-500 bg-white dark:bg-slate-950">
                        <label for="show_on_website" class="text-[10px] font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-widest flex items-center gap-1.5 cursor-pointer">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <span>Show on Website / Online Store</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Footer / Actions --}}
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                     <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="file" name="image" class="hidden" onchange="document.getElementById('img-label').innerText = 'Image Selected'">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800/80 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 dark:group-hover:bg-slate-800 group-hover:text-blue-600 transition">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span id="img-label" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Upload Image</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancel</button>
                    <button type="submit" class="px-10 py-3 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-200 dark:shadow-none hover:scale-[1.05] active:scale-95 transition-all">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
