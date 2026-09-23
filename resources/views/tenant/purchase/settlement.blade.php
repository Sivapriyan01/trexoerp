@extends('layouts.tenant')

@section('title', 'Purchase Settlement')

@section('content')
<script>
    function settlementData() {
        return {
            search: {!! json_encode(request('search') ?? '') !!},
            showAddModal: false,
            vendors: {!! $vendors->map(function($v) {
                $due = $v->pending_due ?? 0;
                $advance = $v->advance_balance ?? 0;
                return ["id" => $v->id, "name" => $v->name, "balance" => $due - $advance];
            })->toJson() !!},
            form: {
                posting_date: '{{ date('Y-m-d') }}',
                type: 'Opening',
                vendor_id: '',
                document_number: '',
                description: '',
                payment_mode: 'Cash',
                amount: 0
            },
            get selectedVendor() {
                return this.vendors.find(v => v.id == this.form.vendor_id) || { balance: 0 };
            },
            get newBalance() {
                return (parseFloat(this.selectedVendor.balance) || 0) - (parseFloat(this.form.amount) || 0);
            },
            number_format(number, decimals = 2) {
                return parseFloat(number).toLocaleString('en-IN', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            },
            applyFilters() {
                let url = new URL(window.location.href);
                if (this.search) url.searchParams.set('search', this.search);
                else url.searchParams.delete('search');
                window.location.href = url.toString();
            },
            submitSettlement() {
                if (!this.form.vendor_id || !this.form.amount) {
                    alert('Please select a vendor and enter an amount.');
                    return;
                }
                document.getElementById('settlementForm').submit();
            }
        };
    }
</script>
<div class="p-6 min-h-screen bg-[#F8FAFC] dark:bg-slate-950" x-data="settlementData()">
    
    <!-- 📑 Tabs Section -->
    <div class="flex items-center gap-6 border-b border-slate-200 dark:border-slate-800 mb-6 px-4">
        <a href="{{ route('tenant.purchase.settlement', ['view' => 'purchase']) }}" 
           class="pb-3 text-xs {{ $view === 'purchase' ? 'font-black text-blue-600 border-b-2 border-blue-600' : 'font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }} transition-all">Purchase</a>
        <a href="{{ route('tenant.purchase.settlement', ['view' => 'settlements']) }}" 
           class="pb-3 text-xs {{ $view === 'settlements' ? 'font-black text-blue-600 border-b-2 border-blue-600' : 'font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }} transition-all tracking-tight">Settlements</a>
        <a href="{{ route('tenant.purchase.settlement', ['view' => 'completed']) }}" 
           class="pb-3 text-xs {{ $view === 'completed' ? 'font-black text-blue-600 border-b-2 border-blue-600' : 'font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }} transition-all">Completed</a>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter uppercase leading-none">
                @if($view === 'purchase') Purchase Settlements @elseif($view === 'settlements') Settlements @else Completed Purchases @endif
            </h1>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1 uppercase tracking-wider">Manage and track vendor payments</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showAddModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-4 py-2 rounded-xl transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2 text-[10px] uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Settlement
            </button>
        </div>
    </div>

    <!-- 🏗️ Add Settlement Modal -->
    <div x-show="showAddModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-[2px]"
         style="display: none;">
        
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Add Settlement</h2>
                <button @click="showAddModal = false" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-colors text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="settlementForm" action="{{ route('tenant.purchase.settlement.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <!-- Posting Date -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Posting Date</label>
                        <input type="date" name="date" x-model="form.posting_date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white">
                    </div>

                    <!-- Type -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</label>
                        <select name="type" x-model="form.type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white">
                            <option value="Opening">Opening</option>
                            <option value="Payment">Payment</option>
                        </select>
                    </div>
                </div>

                <!-- Vendor Selection -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Vendor</label>
                        <span class="text-[9px] font-black text-blue-500 uppercase">Bal: ₹<span x-text="number_format(selectedVendor.balance, 2)"></span></span>
                    </div>
                    <select name="vendor_id" x-model="form.vendor_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white">
                        <option value="">Select Vendor</option>
                        <template x-for="v in vendors" :key="v.id">
                            <option :value="v.id" x-text="v.name"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Document Number -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Doc Number</label>
                        <input type="text" name="document_number" x-model="form.document_number" placeholder="Enter DOC #" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white">
                    </div>

                    <!-- Payment Mode -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Payment Mode</label>
                        <select name="payment_mode" x-model="form.payment_mode" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white">
                            <option value="Cash">Cash</option>
                            <option value="GPay">GPay</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Description</label>
                    <textarea name="description" x-model="form.description" rows="1" placeholder="Details..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500/10 outline-none text-[11px] font-bold text-slate-700 dark:text-white"></textarea>
                </div>

                <!-- Debit Amount -->
                <div class="bg-blue-50 dark:bg-blue-500/5 p-4 rounded-xl border border-blue-100 dark:border-blue-500/10">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tight">Amount (₹)</label>
                            <input type="number" name="amount" x-model="form.amount" step="0.01" class="w-24 px-2 py-1.5 bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-500/20 rounded-lg outline-none text-right font-black text-[11px] text-blue-600 dark:text-blue-400">
                        </div>
                        <div class="flex flex-col gap-0.5 border-t border-blue-200/30 dark:border-blue-500/10 pt-3">
                            <div class="flex justify-between text-[9px] font-bold text-slate-500">
                                <span>Current:</span>
                                <span>₹<span x-text="number_format(selectedVendor.balance, 2)"></span></span>
                            </div>
                            <div class="flex justify-between text-[10px] font-black text-blue-600">
                                <span>New:</span>
                                <span>₹<span x-text="number_format(newBalance, 2)"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" @click="submitSettlement()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl shadow-lg shadow-blue-500/20 transition-all active:scale-95 uppercase tracking-widest text-[10px]">
                    Post Settlement
                </button>
            </form>
        </div>
    </div>

    <!-- 🔍 Filters Section -->
    <div class="glass-card mb-6 p-3 flex flex-col md:flex-row gap-3 items-center">
        <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   x-model="search"
                   @keyup.enter="applyFilters()"
                   placeholder="Search..." 
                   class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm outline-none text-slate-900 dark:text-white">
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="applyFilters()" class="p-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors" title="Apply Filter"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg></button>
            <button @click="window.location.href = '{{ route('tenant.purchase.settlement.export') }}?view={{ $view }}&search=' + encodeURIComponent(search)" class="p-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors" title="Download Report"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg></button>
            <button @click="window.location.reload()" class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors" title="Refresh Data"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></button>
        </div>
    </div>

    <!-- 📊 Table Section -->
    <div class="glass-card overflow-hidden rounded-[2.5rem] shadow-xl border border-white/20 dark:border-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 uppercase">
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16"></path></svg>SL.NO</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>DATE</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>VENDOR</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01M19 7h.01M19 11h.01M19 15h.01M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"></path></svg>TYPE</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>DOC NO</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16m-7 6h7"></path></svg>DESCRIPTION</div></th>
                        
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1 uppercase"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>CREDIT</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1 uppercase"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 0 -2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>DEBIT</div></th>
                        <th class="px-6 py-5"><div class="text-[10px] font-black text-slate-400 tracking-widest flex items-center gap-1 uppercase text-rose-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>BALANCE</div></th>
                        
                        <th class="px-6 py-5 text-center"><div class="text-[10px] font-black text-slate-400 tracking-widest">ACTIONS</div></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($data as $index => $item)
                    <tr class="border-b border-slate-100 dark:border-slate-800/50 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors group">
                        <td class="px-6 py-4"><span class="text-xs font-bold text-slate-400 group-hover:text-blue-500 transition-colors">{{ $data->firstItem() + $index }}</span></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 group-hover:bg-blue-500 transition-colors"></div>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($item->date ?? $item->invoice_date)->format('d-m-Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4"><div class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $item->vendor->name ?? 'N/A' }}</div></td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 uppercase font-bold">
                            @if($view === 'purchase' || $view === 'completed') Purchase @else {{ $item->entry_type ?? 'payment' }} @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-slate-600 dark:text-slate-400">
                            @if($view === 'completed' || $view === 'purchase') {{ $item->invoice_number ?? $item->id }} @else {{ $item->document_number ?: '-' }} @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 italic">
                            @if($view === 'completed') {{ $item->remark ?: 'no' }} @elseif($view === 'purchase') {{ $item->remark ?: 'Purchase Order' }} @else {{ $item->description ?: ($item->entry_type === 'payment' ? 'Payment' : 'no') }} @endif
                        </td>
                        
                        <!-- Financial Ledger -->
                        <td class="px-6 py-4 text-xs font-black text-emerald-600 dark:text-emerald-400">
                            ₹{{ number_format($view === 'settlements' ? ($item->purchase->total_amount ?? 0) : $item->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-blue-600 dark:text-blue-400">
                            ₹{{ number_format($view === 'settlements' ? $item->amount : ($item->paid_amount ?? ($item->total_amount - $item->balance_amount)), 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-rose-600 dark:text-rose-400">
                            ₹{{ number_format($view === 'settlements' ? ($item->purchase->balance_amount ?? 0) : $item->balance_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($view !== 'completed')
                                @if($view === 'settlements' && empty($item->purchase_id))
                                    <button @click="alert('This payment is not tied to a specific purchase order.')" class="p-2 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors" title="No Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </button>
                                @else
                                    <a href="{{ route('tenant.purchase.show', $view === 'settlements' ? $item->purchase_id : $item->id) }}" class="p-2 inline-block text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-10 h-10 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="text-lg font-bold">No Data Found</h3>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
        <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800">
            {{ $data->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

