@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col items-center justify-center min-h-[70vh]">
    @if(session('error'))
        <div class="w-full max-w-4xl bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="w-full max-w-4xl bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm">
        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
    </div>
    <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter mb-2">Return & RMA Center</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 text-center max-w-md">Manage customer returns, product exchanges, warranty replacements, and generate RMAs from this dedicated dashboard.</p>
    
    <!-- KPI Section -->
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 w-full max-w-4xl">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-4 text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total RMAs</p>
            <p class="text-2xl font-black text-blue-600">{{ $kpi['total_rmas'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-4 text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Pending Actions</p>
            <p class="text-2xl font-black text-amber-500">{{ $kpi['pending_rmas'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-4 text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Warranty Replacements</p>
            <p class="text-2xl font-black text-violet-600">{{ $kpi['replacements'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-4 text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Processed</p>
            <p class="text-2xl font-black text-emerald-600">{{ $kpi['processed'] ?? 0 }}</p>
        </div>
    </div>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 w-full max-w-4xl">
        <!-- Return Option -->
        <button onclick="openReturnModal('return')" class="flex flex-col items-center p-6 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-all group">
            <div class="w-12 h-12 bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-1">Process Return</h3>
            <p class="text-[10px] text-slate-400 text-center uppercase">Refund to Customer</p>
        </button>

        <!-- Exchange Option -->
        <button onclick="openReturnModal('exchange')" class="flex flex-col items-center p-6 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-all group">
            <div class="w-12 h-12 bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full flex items-center justify-center mb-4 group-hover:bg-violet-600 group-hover:text-white transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-1">Process Exchange</h3>
            <p class="text-[10px] text-slate-400 text-center uppercase">Swap for another item</p>
        </button>

        <!-- Replacement Option -->
        <button onclick="openReturnModal('replacement')" class="flex flex-col items-center p-6 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-950/20 transition-all group">
            <div class="w-12 h-12 bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest mb-1">Replacement</h3>
            <p class="text-[10px] text-slate-400 text-center uppercase">Warranty / Defect claims</p>
        </button>
    </div>

    <!-- Existing RMAs Section -->
    <div class="w-full max-w-6xl mt-12">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Recent RMA Requests</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/50">
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">RMA Number</th>
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Original Invoice</th>
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Type</th>
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Status</th>
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Created At</th>
                            <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($rmas as $rma)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4">
                                <span class="text-sm font-black text-slate-800 dark:text-slate-200">{{ $rma->rma_number }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ $rma->bill->invoice_no ?? 'N/A' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $rma->type }}</span>
                            </td>
                            <td class="p-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'inspecting' => 'bg-purple-100 text-purple-700',
                                        'processed' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700'
                                    ];
                                    $color = $statusColors[$rma->status] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="px-3 py-1 {{ $color }} rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $rma->status }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $rma->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('tenant.returns.show', $rma->id) }}" class="px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all">Inspect / View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-sm font-bold text-slate-400">
                                No RMA requests found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Invoice Lookup -->
<div id="invoice_lookup_modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-200" id="invoice_modal_content">
        <div class="p-6 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 uppercase tracking-tighter" id="modal_title">Process Return</h3>
                <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase">Enter Invoice No. or Customer Phone</p>
            </div>
            <button onclick="closeReturnModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Invoice / Phone No.</label>
                <div class="relative">
                    <input type="text" id="invoice_number_input" placeholder="e.g. INV-23-001 or 9876543210" oninput="handleModalInput(this.value)"
                           class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none transition-all uppercase"/>
                    <div id="lookup_loading" class="hidden absolute right-3 top-3.5">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
            </div>
            
            <!-- Invoices List (Shown when phone number matches) -->
            <div id="customer_invoices_container" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer Invoices</p>
                    <input type="month" id="invoice_month_filter" onchange="handleModalInput(document.getElementById('invoice_number_input').value)" 
                           class="bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1 text-[10px] font-bold text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-blue-500 outline-none transition-all uppercase" />
                </div>
                <div id="customer_invoices_list" class="space-y-2 max-h-48 overflow-y-auto no-scrollbar pr-1">
                    <!-- Populated via JS -->
                </div>
            </div>
            
            <input type="hidden" id="process_type" value="return">
        </div>
        <div class="p-6 bg-slate-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <button onclick="closeReturnModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors uppercase tracking-widest">Cancel</button>
            <button onclick="proceedWithInvoice()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors uppercase tracking-widest shadow-lg shadow-blue-500/30">Proceed &rarr;</button>
        </div>
    </div>
</div>

<script>
    function openReturnModal(type) {
        document.getElementById('process_type').value = type;
        document.getElementById('invoice_lookup_modal').classList.remove('hidden');
        document.getElementById('customer_invoices_container').classList.add('hidden');
        document.getElementById('customer_invoices_list').innerHTML = '';
        
        let titles = {
            'return': 'Process Return',
            'exchange': 'Process Exchange',
            'replacement': 'Process Replacement'
        };
        
        document.getElementById('modal_title').innerText = titles[type];
        
        setTimeout(() => {
            document.getElementById('invoice_modal_content').classList.remove('scale-95', 'opacity-0');
            document.getElementById('invoice_number_input').focus();
        }, 10);
    }

    function closeReturnModal() {
        document.getElementById('invoice_modal_content').classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            document.getElementById('invoice_lookup_modal').classList.add('hidden');
            document.getElementById('invoice_number_input').value = '';
            document.getElementById('customer_invoices_container').classList.add('hidden');
        }, 200);
    }

    function proceedWithInvoice(invoiceNo = null) {
        const type = document.getElementById('process_type').value;
        const val = invoiceNo || document.getElementById('invoice_number_input').value.trim();
        
        if(!val) {
            alert('Please enter an Invoice Number');
            return;
        }
        
        window.location.href = `/returns/create?type=${type}&invoice_no=${encodeURIComponent(val)}`;
    }
    
    // Allow pressing Enter to proceed
    document.getElementById('invoice_number_input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            proceedWithInvoice();
        }
    });

    let lookupTimer = null;
    function handleModalInput(val) {
        val = val.trim();
        const container = document.getElementById('customer_invoices_container');
        const list = document.getElementById('customer_invoices_list');
        const loader = document.getElementById('lookup_loading');
        
        // If it looks like a phone number (10 digits)
        if (val.length === 10 && /^\d+$/.test(val)) {
            clearTimeout(lookupTimer);
            loader.classList.remove('hidden');
            
            const monthFilter = document.getElementById('invoice_month_filter').value;
            
            lookupTimer = setTimeout(async () => {
                try {
                    let url = `/returns/customer/invoices?phone=${val}`;
                    if (monthFilter) {
                        url += `&month=${monthFilter}`;
                    }
                    const res = await fetch(url);
                    const data = await res.json();
                    
                    if (data.invoices && data.invoices.length > 0) {
                        let html = '';
                        data.invoices.forEach(inv => {
                            // Format date safely
                            let dateStr = '';
                            if (inv.bill_date) {
                                const d = new Date(inv.bill_date);
                                dateStr = d.toLocaleDateString();
                            }
                            html += `
                                <div onclick="proceedWithInvoice('${inv.invoice_no}')" class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 hover:border-blue-500 rounded-xl cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-800 transition-all group">
                                    <div>
                                        <p class="text-xs font-black text-slate-800 dark:text-slate-100 group-hover:text-blue-600 transition-colors">${inv.invoice_no}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">${dateStr} • ₹${parseFloat(inv.grand_total).toFixed(2)}</p>
                                    </div>
                                    <div class="text-[10px] font-black text-blue-600 uppercase tracking-widest bg-blue-100 px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity">Select</div>
                                </div>
                            `;
                        });
                        list.innerHTML = html;
                        container.classList.remove('hidden');
                    } else {
                        list.innerHTML = `<div class="p-3 text-center text-xs font-bold text-slate-400">No invoices found for this number.</div>`;
                        container.classList.remove('hidden');
                    }
                } catch (e) {
                    console.error('Error fetching invoices', e);
                } finally {
                    loader.classList.add('hidden');
                }
            }, 300);
        } else {
            container.classList.add('hidden');
            loader.classList.add('hidden');
        }
    }
</script>
@endsection
