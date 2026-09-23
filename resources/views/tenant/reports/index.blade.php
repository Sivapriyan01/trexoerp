@extends('layouts.tenant')
@section('title', 'Business Reports')
@section('page-title', 'Reports Center')

@section('content')
<div class="space-y-6">
    <!-- Tabs Navigation -->
    <div class="flex items-center gap-1 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md p-1.5 rounded-2xl border border-white/20 dark:border-slate-800 shadow-sm w-fit">
        @php
            $tabs = [
                ['id' => 'invoice', 'label' => 'Sales (Invoice)', 'icon' => 'currency-dollar'],
                ['id' => 'purchase', 'label' => 'Purchase', 'icon' => 'shopping-bag'],
                ['id' => 'product', 'label' => 'Product Wise', 'icon' => 'cube'],
                ['id' => 'gst', 'label' => 'GST Report', 'icon' => 'receipt-tax'],
            ];
        @endphp

        @foreach($tabs as $tab)
            <a href="{{ route('tenant.reports.index', ['type' => $tab['id'], 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
               {{ $type == $tab['id'] ? 'bg-blue-600 text-white shadow-lg shadow-blue-100 dark:shadow-none' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-800/50' }}">
               {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <!-- Filters & Actions -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('tenant.reports.index') }}" method="GET" class="flex items-center gap-3 bg-white dark:bg-slate-900/60 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="flex items-center gap-2 px-3">
                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">From</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-[11px] font-black outline-none border-none focus:ring-0 bg-transparent text-slate-800 dark:text-slate-200 w-28 cursor-pointer">
            </div>
            <div class="w-px h-6 bg-slate-100 dark:bg-slate-800"></div>
            <div class="flex items-center gap-2 px-3">
                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">To</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-[11px] font-black outline-none border-none focus:ring-0 bg-transparent text-slate-800 dark:text-slate-200 w-28 cursor-pointer">
            </div>
            <button type="submit" class="p-2 bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white dark:hover:text-white transition-all">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>

        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('download_modal').classList.remove('hidden')" class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white dark:bg-slate-800 dark:border dark:border-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-slate-200 dark:shadow-none">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Report
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 {{ count($stats) === 4 ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-4">
        @foreach($stats as $stat)
            <div class="glass-card p-5 rounded-3xl group hover:-translate-y-1 transition-all duration-300">
                <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">{{ $stat['label'] }}</h4>
                <p class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $stat['value'] }}</p>
                <div class="mt-3 w-full h-1 bg-{{ $stat['color'] }}-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-{{ $stat['color'] }}-500 w-1/2"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Report Table -->
    <div class="glass-card rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden bg-white dark:bg-slate-900/40">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800/60 flex items-center justify-between bg-slate-50/30 dark:bg-slate-900/30">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-200 uppercase tracking-widest">{{ ucfirst($type) }} Details</h3>
            <div class="flex gap-2">
                <input type="text" id="report-search" oninput="filterReportTable()" placeholder="Search..." class="px-4 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-600 w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/10 dark:bg-slate-900/10">
                        @if($type == 'invoice')
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Date</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Bill #</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Customer</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">GST</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Status</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Actions</th>
                        @elseif($type == 'purchase')
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Date</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Invoice Ref</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Vendor</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Items</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Total Amount</th>
                        @elseif($type == 'product')
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Product Name</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Qty Sold</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Revenue</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Net Profit</th>
                        @elseif($type == 'gst')
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Date</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Invoice</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Taxable Val</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">GST (9+9%)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($data as $item)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-800/30 transition-colors group">
                            @if($type == 'invoice')
                                <td class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $item->bill_date->format('d M, Y') }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-900 dark:text-slate-100">#{{ $item->invoice_no }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-600 dark:text-slate-300">{{ $item->customer_name }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-blue-600 dark:text-blue-400">₹{{ number_format($item->grand_total, 2) }}</td>
                                <td class="px-4 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400">₹{{ number_format($item->gst_amount, 2) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-[8px] font-black uppercase tracking-widest border border-emerald-100/10">Paid</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button onclick="sendInvoiceEmail({{ $item->id }}, '{{ $item->customer_email }}')" class="p-1.5 text-blue-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="Email Invoice">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </button>
                                </td>
                            @elseif($type == 'purchase')
                                <td class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $item->invoice_date->format('d M, Y') }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-900 dark:text-slate-100">{{ $item->invoice_ref }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-600 dark:text-slate-300">{{ $item->vendor->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-900 dark:text-slate-100 text-center">{{ $item->items_count }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-emerald-600 dark:text-emerald-400 text-right">₹{{ number_format($item->total_amount, 2) }}</td>
                            @elseif($type == 'product')
                                <td class="px-6 py-4">
                                    <p class="text-[11px] font-black text-slate-900 dark:text-slate-100 uppercase leading-tight">{{ $item->product_name }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 dark:text-slate-500">{{ $item->product_type }}</p>
                                </td>
                                <td class="px-4 py-4 text-[11px] font-black text-blue-600 dark:text-blue-400 text-center bg-blue-50/20 dark:bg-blue-950/10 rounded-xl">{{ $item->total_qty }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-900 dark:text-slate-100 text-right">₹{{ number_format($item->total_sales, 2) }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-emerald-600 dark:text-emerald-400 text-right">₹{{ number_format($item->total_profit, 2) }}</td>
                            @elseif($type == 'gst')
                                <td class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $item->bill_date->format('d M, Y') }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-900 dark:text-slate-100">#{{ $item->invoice_no }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-slate-600 dark:text-slate-300">₹{{ number_format($item->subtotal - $item->discount_amount, 2) }}</td>
                                <td class="px-4 py-4 text-[11px] font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($item->gst_amount, 2) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="10" class="px-6 py-20 text-center opacity-30">
                                <svg width="48" height="48" class="mx-auto mb-4 text-blue-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600 dark:text-slate-400">No data found for this period</p>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="js-empty-row" style="display: none;">
                        <td colspan="10" class="px-6 py-20 text-center opacity-30">
                            <svg width="48" height="48" class="mx-auto mb-4 text-blue-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600 dark:text-slate-400">No matching records found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
            @if(is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->all())->links() }}
            @endif
        </div>
    </div>
</div>

{{-- Download Modal --}}
<div id="download_modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-2xl w-full max-w-md transform transition-all overflow-hidden">
        <div class="p-8 pb-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Download Report</h3>
            <button onclick="document.getElementById('download_modal').classList.add('hidden')" class="p-2 text-slate-300 hover:text-slate-600 transition-colors">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form action="{{ route('tenant.reports.download') }}" method="POST" class="p-8 pt-4 space-y-4">
            @csrf
            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">Report Category</label>
                <select name="type" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all">
                    @foreach($tabs as $tab)
                        <option value="{{ $tab['id'] }}" {{ $type == $tab['id'] ? 'selected' : '' }}>{{ $tab['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-black text-slate-800 dark:text-slate-200 outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-black text-slate-800 dark:text-slate-200 outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">Format</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="p-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center gap-3 cursor-pointer hover:bg-white dark:hover:bg-slate-900 transition-all">
                        <input type="radio" name="format" value="csv" checked class="text-blue-600">
                        <span class="text-xs font-black uppercase text-slate-600 dark:text-slate-300">CSV/Excel</span>
                    </label>
                    <label class="p-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center gap-3 cursor-pointer hover:bg-white dark:hover:bg-slate-900 transition-all">
                        <input type="radio" name="format" value="pdf" class="text-rose-600">
                        <span class="text-xs font-black uppercase text-slate-600 dark:text-slate-300">PDF Document</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-[1.5rem] text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-100/10 hover:scale-[1.02] transition-all mt-4">
                Generate & Download
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function sendInvoiceEmail(billId, defaultEmail) {
    let email = prompt("Enter customer email address:", defaultEmail || "");
    
    if (email === null) return; // Cancelled
    if (email.trim() === "") {
        alert("Email address is required.");
        return;
    }

    // Basic email validation
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }

    // Show loading state (could be improved)
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
    btn.disabled = true;

    fetch(`/billing/email/${billId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An unexpected error occurred.");
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function filterReportTable() {
    const query = document.getElementById('report-search').value.toLowerCase().trim();
    const rows = document.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.id === 'empty-row' || row.id === 'js-empty-row') return;
        
        const text = row.textContent.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const jsEmptyRow = document.getElementById('js-empty-row');
    if (jsEmptyRow) {
        if (visibleCount === 0) {
            jsEmptyRow.style.display = '';
        } else {
            jsEmptyRow.style.display = 'none';
        }
    }
}
</script>
@endpush



