@extends('layouts.tenant')

@section('title', 'Sales History')

@section('content')
<div class="p-6 min-h-screen bg-[#F8FAFC] dark:bg-slate-950" x-data="{ 
    search: '{{ request('search') }}',
    status: '{{ request('status', 'all') }}',
    showModal: false,
    selectedBill: null,

    viewDetails(billData) {
        this.selectedBill = billData;
        this.showModal = true;
    },

    closeModal() {
        this.showModal = false;
        setTimeout(() => this.selectedBill = null, 300);
    },

    applyFilters() {
        let url = new URL(window.location.href);
        if (this.search) url.searchParams.set('search', this.search);
        else url.searchParams.delete('search');
        
        if (this.status !== 'all') url.searchParams.set('status', this.status);
        else url.searchParams.delete('status');
        
        window.location.href = url.toString();
    }
}">


    <!-- Top Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Sales History</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage all your sales invoices</p>
        </div>
        <a href="{{ route('tenant.billing.index') }}" 
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all shadow-lg shadow-blue-500/20 active:scale-95 group">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Bill
        </a>
    </div>

    <!-- Filters & Search Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
            <!-- Search Bar -->
            <div class="relative lg:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       x-model="search"
                       @keydown.enter="applyFilters()"
                       placeholder="Search by invoice number, customer name, or phone..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm outline-none text-slate-900 dark:text-white">
            </div>

            <!-- Status Dropdown -->
            <div class="flex items-center gap-3">
                <select x-model="status" 
                        @change="applyFilters()"
                        class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none w-full transition-all text-slate-900 dark:text-white font-medium">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xl shadow-slate-200/50 dark:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">INVOICE NO</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">CUSTOMER</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">DATE</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">TOTAL AMOUNT</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">PAID AMOUNT</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sales as $bill)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $bill->invoice_no }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-medium">{{ ucfirst($bill->bill_type) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $bill->customer_name ?: 'Walk-in Customer' }}</div>
                            <div class="text-xs text-slate-400">{{ $bill->customer_phone ?: 'No Phone' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($bill->grand_total, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-500 dark:text-slate-400">
                                ₹{{ number_format($bill->paid_amount, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                    'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                    'cancelled' => 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
                                ]
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $statusClasses[strtolower($bill->status)] ?? 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @click="viewDetails(JSON.parse(atob('{{ base64_encode(json_encode($bill)) }}')))"
                                        class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                        title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Sales Invoices Found</h3>
                                <p class="text-slate-500 dark:text-slate-400 max-w-xs mx-auto mt-1">Try adjusting your filters or create a new bill to get started.</p>
                                <a href="{{ route('tenant.billing.index') }}" class="mt-6 text-blue-600 font-bold hover:underline">Create your first Bill →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($sales->hasPages())
        <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

    <!-- View Details Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" 
         style="display: none;">
        <div @click.away="closeModal()" 
             x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl p-6 md:p-8 max-w-2xl w-full mx-4 border border-slate-100 dark:border-slate-800 max-h-[90vh] flex flex-col">
            
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Bill Details</h3>
                    <p class="text-sm font-medium text-slate-500" x-text="selectedBill ? selectedBill.invoice_no : ''"></p>
                </div>
                <button @click="closeModal()" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl transition text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="overflow-y-auto flex-1 pr-2 space-y-6">
                <!-- Customer & Bill Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer</p>
                        <p class="font-bold text-slate-900 dark:text-white" x-text="selectedBill?.customer_name || 'Walk-in'"></p>
                        <p class="text-xs text-slate-500" x-text="selectedBill?.customer_phone || ''"></p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Date</p>
                        <p class="font-bold text-slate-900 dark:text-white" x-text="selectedBill ? new Date(selectedBill.bill_date).toLocaleDateString() : ''"></p>
                        <p class="text-xs font-bold" :class="{'text-emerald-500': selectedBill?.status === 'completed', 'text-amber-500': selectedBill?.status === 'pending', 'text-rose-500': selectedBill?.status === 'cancelled'}" x-text="selectedBill?.status ? selectedBill.status.toUpperCase() : ''"></p>
                    </div>
                </div>

                <!-- Items Table -->
                <div>
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Items</h4>
                    <div class="border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="p-3 font-semibold text-slate-600 dark:text-slate-300">Product</th>
                                    <th class="p-3 font-semibold text-slate-600 dark:text-slate-300 text-center">Qty</th>
                                    <th class="p-3 font-semibold text-slate-600 dark:text-slate-300 text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="item in (selectedBill?.items || [])" :key="item.id">
                                    <tr>
                                        <td class="p-3 text-slate-900 dark:text-white font-medium" x-text="item.product_name"></td>
                                        <td class="p-3 text-center text-slate-600 dark:text-slate-400" x-text="item.quantity"></td>
                                        <td class="p-3 text-right font-medium text-slate-900 dark:text-white" x-text="'₹' + parseFloat(item.mrp || 0).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-full max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-medium" x-text="selectedBill ? '₹' + parseFloat(selectedBill.subtotal || 0).toFixed(2) : '₹0.00'"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span>Discount</span>
                            <span class="font-medium text-rose-500" x-text="selectedBill ? '-₹' + parseFloat(selectedBill.discount_amount || 0).toFixed(2) : '₹0.00'"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span>Tax (GST)</span>
                            <span class="font-medium" x-text="selectedBill ? '₹' + parseFloat(selectedBill.gst_amount || 0).toFixed(2) : '₹0.00'"></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-slate-100 dark:border-slate-800 text-lg font-bold">
                            <span class="text-slate-900 dark:text-white">Grand Total</span>
                            <span class="text-emerald-600" x-text="selectedBill ? '₹' + parseFloat(selectedBill.grand_total || 0).toFixed(2) : '₹0.00'"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <a :href="selectedBill ? '/billing/invoice/' + selectedBill.id : '#'" target="_blank" class="px-5 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 rounded-xl font-bold transition">A4 Invoice</a>
                <button @click="closeModal()" class="px-5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white rounded-xl font-bold transition">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar for better aesthetics */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #E2E8F0;
        border-radius: 10px;
    }
    .dark .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #1E293B;
    }
</style>
@endsection
