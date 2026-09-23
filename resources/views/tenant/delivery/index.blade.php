@extends('layouts.tenant')
@section('title', 'Delivery Management')
@section('page-title', 'Deliveries')

@section('content')
<div class="space-y-6" x-data="deliveryDashboard()">
    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Delivery Orders</h3>
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Manage orders marked for delivery</p>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="glass-card rounded-[1.5rem] overflow-hidden flex flex-col border border-slate-100 dark:border-slate-800">
        
        {{-- Search and Filters Toolbar --}}
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" placeholder="Search deliveries..." 
                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-[11px] font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
            </div>
        </div>

        {{-- Data Table --}}
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full whitespace-nowrap text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">SL.NO</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">DATE</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">INVOICE NO</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">CUSTOMER NAME</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">PHONE</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ADDRESS</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">TOTAL</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">STATUS</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse ($deliveries as $index => $delivery)
                        @php
                            $searchString = strtolower(($delivery->customer_name ?? '') . ($delivery->customer_phone ?? '') . $delivery->invoice_no);
                        @endphp
                        <tr x-show="isVisible({{ json_encode($searchString) }})"
                            x-transition
                            class="hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors">
                            
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $delivery->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-[11px] font-black text-slate-700 dark:text-slate-300">{{ $delivery->invoice_no }}</td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ strtoupper($delivery->customer_name ?: 'N/A') }}</td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $delivery->customer_phone ?: 'N/A' }}</td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ strtoupper(Str::limit($delivery->customer_address ?: '', 30)) }}</td>
                            <td class="px-6 py-4 text-[11px] font-black text-slate-900 dark:text-white">{{ number_format($delivery->grand_total, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <select @change="updateStatus({{ $delivery->id }}, $event.target.value)"
                                            class="bg-transparent border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer shadow-sm
                                            {{ strtolower($delivery->status) === 'delivered' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200' : '' }}
                                            {{ strtolower($delivery->status) === 'delivery' ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-blue-200' : '' }}
                                            text-slate-600 dark:text-slate-300">
                                        
                                        <option value="Delivery" {{ $delivery->status === 'Delivery' ? 'selected' : '' }}>Pending Delivery</option>
                                        <option value="In Transit" {{ $delivery->status === 'In Transit' ? 'selected' : '' }}>In Transit</option>
                                        <option value="Delivered" {{ $delivery->status === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="Cancelled" {{ $delivery->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- View --}}
                                    <a href="{{ route('tenant.billing.invoice.view', $delivery->id) }}" class="text-emerald-500 hover:text-emerald-600 transition-colors" title="View">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mb-4 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-xs font-black uppercase tracking-widest">No deliveries found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>

<script>
function deliveryDashboard() {
    return {
        search: '',
        isVisible(searchStr) {
            if (!this.search) return true;
            return searchStr.includes(this.search.toLowerCase());
        },
        async updateStatus(id, status) {
            try {
                const response = await fetch(`/delivery/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                });
                const data = await response.json();
                if (data.success) {
                    showToast('Status updated successfully', 'success');
                    // Optional: reload or update UI
                    window.location.reload();
                } else {
                    showToast('Failed to update status', 'error');
                }
            } catch (e) {
                showToast('Error updating status', 'error');
            }
        }
    }
}
</script>
@endsection
