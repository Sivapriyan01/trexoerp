@extends('layouts.tenant')

@section('title', 'Purchase Orders')

@section('content')
<div class="p-6 min-h-screen bg-[#F8FAFC] dark:bg-slate-950" x-data="{ 
    search: '{{ request('search') }}',
    status: '{{ request('status', 'all') }}',
    activeTab: '{{ request('view', 'purchase') }}',

    // Payment Modal State
    showPaymentModal: false,
    payment: {
        purchase_id: '',
        vendor_id: '',
        invoice_ref: '',
        amount: 0,
        balance: 0,
        payment_mode: 'Cash',
        document_number: '',
        date: '{{ date('Y-m-d') }}',
        description: ''
    },

    openPaymentModal(id, vendorId, balance, ref) {
        this.payment.purchase_id = id;
        this.payment.vendor_id = vendorId;
        this.payment.balance = balance;
        this.payment.amount = balance;
        this.payment.invoice_ref = ref;
        this.showPaymentModal = true;
    },

    async submitPayment() {
        if (this.payment.amount <= 0) return alert('Amount must be greater than 0');
        
        try {
            const response = await fetch('{{ route('tenant.purchase.settlement.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.payment)
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('An error occurred during payment.');
        }
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
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Purchase Orders</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage all your purchase orders</p>
        </div>
        <a href="{{ route('tenant.purchase.create') }}" 
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all shadow-lg shadow-blue-500/20 active:scale-95 group">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create PO
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
                       placeholder="Search by PO number, vendor name, or phone..." 
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm outline-none text-slate-900 dark:text-white">
            </div>

            <!-- Status Dropdown -->
            <div class="flex items-center gap-3">
                <select x-model="status" 
                        @change="applyFilters()"
                        class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none w-full transition-all text-slate-900 dark:text-white font-medium">
                    <option value="all">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
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
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">PO NUMBER</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">VENDOR</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">ORDER DATE</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">TOTAL AMOUNT</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">BALANCE AMOUNT</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $p->id }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-medium">Ref: {{ $p->invoice_ref ?: 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $p->vendor->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400">{{ $p->vendor->phone ?? 'No Phone' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ $p->invoice_date ? $p->invoice_date->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($p->total_amount, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold {{ $p->balance_amount > 0 ? 'text-red-500' : 'text-slate-500 dark:text-slate-400' }}">
                                ₹{{ number_format($p->balance_amount, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClasses = [
                                    'Pending' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                    'Completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                    'Cancelled' => 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
                                ]
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $statusClasses[$p->status] ?? 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                 <button type="button"
                                         @click="openPaymentModal({{ $p->id }}, {{ $p->vendor_id ?? 'null' }}, {{ $p->balance_amount }}, '{{ $p->invoice_ref }}')"
                                         class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                         title="Pay Now"
                                         {{ $p->balance_amount <= 0 ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                     </svg>
                                 </button>
                                <a href="{{ route('tenant.purchase.show', $p->id) }}" 
                                   class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                   title="View Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('tenant.purchase.edit', $p->id) }}" 
                                   class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"
                                   title="Edit Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button type="button"
                                        @click="if(confirm('Are you sure you want to delete this PO?')) { document.getElementById('delete-form-{{ $p->id }}').submit(); }"
                                        class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors"
                                        title="Delete Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <form id="delete-form-{{ $p->id }}" action="{{ route('tenant.purchase.destroy', $p->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
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
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Purchase Orders Found</h3>
                                <p class="text-slate-500 dark:text-slate-400 max-w-xs mx-auto mt-1">Try adjusting your filters or create a new purchase order to get started.</p>
                                <a href="{{ route('tenant.purchase.create') }}" class="mt-6 text-blue-600 font-bold hover:underline">Create your first PO →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($purchases->hasPages())
        <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
    <!-- 💳 Payment Modal -->
    <div x-show="showPaymentModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        
        <div class="glass-card w-full max-w-md overflow-hidden rounded-[2rem] shadow-2xl" @click.away="showPaymentModal = false">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white/50 dark:bg-slate-900/50">
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tighter">Record Payment</h3>
                <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 dark:bg-blue-500/10 p-4 rounded-2xl border border-blue-100 dark:border-blue-500/20">
                    <div class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Invoice Reference</div>
                    <div class="text-lg font-black text-blue-600 dark:text-blue-400" x-text="'#' + payment.invoice_ref"></div>
                    <div class="mt-2 text-xs text-blue-500/70">Remaining Balance: <span class="font-bold" x-text="'₹' + parseFloat(payment.balance).toLocaleString()"></span></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Payment Date</label>
                        <input type="date" x-model="payment.date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-900 dark:text-white">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Payment Mode</label>
                        <select x-model="payment.payment_mode" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-900 dark:text-white">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI / Digital</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Amount to Pay</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                        <input type="number" x-model.number="payment.amount" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl pl-8 pr-4 py-3 text-lg font-black outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ref Number / Note</label>
                    <input type="text" x-model="payment.document_number" placeholder="Transaction ID, Cheque No, etc." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-900 dark:text-white placeholder:text-slate-400">
                </div>
            </div>

            <div class="p-6 bg-slate-50 dark:bg-slate-900/50 flex gap-3">
                <button @click="showPaymentModal = false" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button @click="submitPayment()" class="flex-[2] bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl transition-all shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirm Payment
                </button>
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
