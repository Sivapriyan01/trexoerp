@extends('layouts.tenant')
@section('title', 'Pre Orders Dashboard')
@section('page-title', 'Pre Orders')

@section('content')
<div class="space-y-6" x-data="preOrderDashboard()">
    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
        {{-- Dynamic Tabs --}}
        <div class="flex overflow-x-auto gap-2 bg-slate-50 dark:bg-slate-800/50 p-1.5 rounded-[1.5rem] no-scrollbar max-w-full">
            <button @click="activeTab = 'All Orders'"
                    :class="activeTab === 'All Orders' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all">
                All Orders
            </button>
            
            @php
                // Build unique types from bills to ensure tabs always exist even if settings are empty
                $dbTypes = $drafts->pluck('bill_type')->unique()->toArray();
                $settingTypes = [];
                foreach($invoiceTypes as $type) {
                    $settingTypes[] = is_array($type) ? ($type['name'] ?? $type['type'] ?? 'Unknown') : $type;
                }
                $allTabs = array_unique(array_merge($settingTypes, $dbTypes));
            @endphp
            
            @foreach($allTabs as $typeName)
                @if(!empty($typeName))
                <button @click="activeTab = '{{ strtolower($typeName) }}'"
                        :class="activeTab === '{{ strtolower($typeName) }}' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all">
                    {{ $typeName }} Orders
                </button>
                @endif
            @endforeach
        </div>

        {{-- Create Button --}}
        <button @click="showCreateModal = true" class="shrink-0 px-6 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 hover:scale-105 transition-all">
            Create Pre Order
        </button>
    </div>

    {{-- Main Table Card --}}
    <div class="glass-card rounded-[1.5rem] overflow-hidden flex flex-col border border-slate-100 dark:border-slate-800">
        
        {{-- Search and Filters Toolbar --}}
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" placeholder="Search pre orders..." 
                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-[11px] font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
            </div>
            <div class="flex items-center gap-2 relative">
                {{-- Column Filter --}}
                <div class="relative">
                    <button @click="showColumnFilter = !showColumnFilter" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 transition-colors" title="Filter Columns">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                    <div x-show="showColumnFilter" @click.away="showColumnFilter = false" style="display: none;" class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-blue-500/10 border border-slate-100 dark:border-slate-700 z-50 p-2 py-3 origin-top-right" x-transition>
                        <p class="px-3 pb-2 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 dark:border-slate-700/50 mb-2">Visible Columns</p>
                        <template x-for="col in availableColumns" :key="col.id">
                            <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl cursor-pointer transition-colors">
                                <input type="checkbox" :value="col.id" x-model="visibleColumns" class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Download --}}
                <button @click="downloadCSV()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 hover:bg-emerald-100 transition-colors" title="Download Export">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </button>

                {{-- Refresh --}}
                <button @click="refreshData()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 transition-colors" title="Reset Filters & Refresh">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full whitespace-nowrap text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th x-show="visibleColumns.includes('sl_no')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16m-7 6h7"/></svg> SL.NO</div></th>
                        <th x-show="visibleColumns.includes('date')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> DATE</div></th>
                        <th x-show="visibleColumns.includes('invoice_no')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> INVOICE NO</div></th>
                        <th x-show="visibleColumns.includes('draft_no')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> DRAFT NO</div></th>
                        <th x-show="visibleColumns.includes('customer_name')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> CUSTOMER NAME</div></th>
                        <th x-show="visibleColumns.includes('customer_phone')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> CUSTOMER PHONE</div></th>
                        <th x-show="visibleColumns.includes('customer_address')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> CUSTOMER ADDRESS</div></th>
                        <th x-show="visibleColumns.includes('total')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">TOTAL</th>
                        <th x-show="visibleColumns.includes('order_with')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> ORDER WITH</div></th>
                        <th x-show="visibleColumns.includes('expected_delivery')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> EXPECTED DELIVERY</div></th>
                        <th x-show="visibleColumns.includes('status')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> STATUS</div></th>
                        <th x-show="visibleColumns.includes('actions')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"><div class="flex items-center gap-2"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16m-7 6h7"/></svg> ACTIONS</div></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse ($drafts as $index => $draft)
                        @php
                            $isRejected = strtolower($draft->status) === 'rejected' || strtolower($draft->status) === 'cancelled';
                            $searchString = strtolower(($draft->customer_name ?? '') . ($draft->customer_phone ?? '') . $draft->invoice_no);
                        @endphp
                        <tr x-show="isVisible('{{ strtolower($draft->bill_type) }}', {{ json_encode($searchString) }})"
                            x-transition
                            class="hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors {{ $isRejected ? 'bg-rose-50/30 dark:bg-rose-900/10' : '' }}">
                            
                            <td x-show="visibleColumns.includes('sl_no')" class="px-6 py-4 text-[11px] font-bold text-slate-500 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $index + 1 }}</td>
                            <td x-show="visibleColumns.includes('date')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $draft->created_at->format('Y-m-d') }}</td>
                            <td x-show="visibleColumns.includes('invoice_no')" class="px-6 py-4 text-[11px] font-black text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $draft->invoice_no }}</td>
                            <td x-show="visibleColumns.includes('draft_no')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $draft->id }}</td>
                            <td x-show="visibleColumns.includes('customer_name')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ strtoupper($draft->customer_name ?: 'N/A') }}</td>
                            <td x-show="visibleColumns.includes('customer_phone')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $draft->customer_phone ?: 'N/A' }}</td>
                            <td x-show="visibleColumns.includes('customer_address')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ strtoupper(Str::limit($draft->customer_address ?: '', 20)) }}</td>
                            <td x-show="visibleColumns.includes('total')" class="px-6 py-4 text-[11px] font-black text-slate-900 dark:text-white {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ number_format($draft->grand_total, 2) }}</td>
                            <td x-show="visibleColumns.includes('order_with')" class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase {{ $isRejected ? 'text-rose-500 line-through' : '' }}">{{ $draft->bill_type }}</td>
                            <td x-show="visibleColumns.includes('expected_delivery')" class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                <input type="date" 
                                       value="{{ $draft->expected_delivery_date ? $draft->expected_delivery_date->format('Y-m-d') : '' }}"
                                       @change="updateDeliveryDate({{ $draft->id }}, $event.target.value)"
                                       class="bg-transparent border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-[11px] font-bold outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            </td>
                            <td x-show="visibleColumns.includes('status')" class="px-6 py-4">
                                <div class="relative">
                                    <select @change="updateStatus({{ $draft->id }}, $event.target.value)"
                                            class="bg-transparent border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer shadow-sm
                                            {{ strtolower($draft->status) === 'completed' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200' : '' }}
                                            {{ strtolower($draft->status) === 'processing' ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-blue-200' : '' }}
                                            {{ strtolower($draft->status) === 'awaiting stock' ? 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 border-amber-200' : '' }}
                                            {{ strtolower($draft->status) === 'confirmed' ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200' : '' }}
                                            {{ $isRejected ? 'text-rose-600 bg-rose-50 dark:bg-rose-900/20 border-rose-200' : '' }}
                                            {{ !in_array(strtolower($draft->status), ['completed', 'processing', 'awaiting stock', 'confirmed', 'rejected', 'cancelled']) ? 'text-slate-600 dark:text-slate-300' : '' }}">
                                        
                                        {{-- Ensure current status is listed even if not in settings --}}
                                        <option value="{{ $draft->status }}" class="text-slate-900">{{ ucfirst($draft->status) }}</option>
                                        
                                        @php
                                            $allStatuses = ['draft', 'sample', 'processing', 'completed', 'Awaiting Stock', 'Confirmed', 'Rejected'];
                                            foreach($orderStatuses as $status) {
                                                $allStatuses[] = is_array($status) ? ($status['name'] ?? $status['status'] ?? 'Unknown') : $status;
                                            }
                                            $allStatuses = array_unique($allStatuses);
                                        @endphp

                                        @foreach($allStatuses as $statusName)
                                            @if(strtolower($statusName) !== strtolower($draft->status))
                                                <option value="{{ $statusName }}" class="text-slate-900">{{ ucfirst($statusName) }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td x-show="visibleColumns.includes('actions')" class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- View --}}
                                    <a href="{{ route('tenant.billing.invoice.view', $draft->id) }}" class="text-emerald-500 hover:text-emerald-600 transition-colors" title="View">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('tenant.billing.index', ['draft' => $draft->id]) }}" class="text-blue-500 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    {{-- Convert to Sales Order --}}
                                    @if(strtolower($draft->status) !== 'completed' && strtolower($draft->status) !== 'cancelled' && strtolower($draft->status) !== 'rejected')
                                        <button @click="openConvertModal({{ $draft->id }}, {{ $draft->grand_total - $draft->paid_amount }})" class="text-indigo-500 hover:text-indigo-600 transition-colors" title="Convert to Sales Order">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    {{-- Delete --}}
                                    <button @click="deleteOrder({{ $draft->id }})" class="text-rose-500 hover:text-rose-600 transition-colors" title="Delete">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mb-4 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-xs font-black uppercase tracking-widest">No orders found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Pre Order Modal --}}
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="showCreateModal" 
             x-transition.opacity 
             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
             @click="showCreateModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showCreateModal" 
             x-transition.scale.origin.bottom
             class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl overflow-hidden flex flex-col">
            
            <div class="p-8 text-center border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Create Pre Order</h2>
                <p class="text-[11px] font-bold text-slate-400 mt-2">Select the type of pre-order you want to create</p>
            </div>

            <div class="p-6 space-y-3 overflow-y-auto max-h-[60vh]">
                @foreach($invoiceTypes as $type)
                    @php 
                        $typeName = is_array($type) ? ($type['name'] ?? $type['type'] ?? 'Unknown') : $type; 
                        
                        // Default icons and styles
                        $icon = 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                        $iconClass = 'text-blue-500 bg-blue-50 dark:bg-blue-900/20';
                        $route = route('tenant.billing.index', ['type' => $typeName]);

                        // Custom matching based on name (like in screenshot)
                        if(stripos($typeName, 'Outward') !== false) {
                            $icon = 'M5 10l7-7m0 0l7 7m-7-7v18'; // Arrow up
                            $iconClass = 'text-rose-500 bg-rose-50 dark:bg-rose-900/20';
                            $route = route('tenant.billing.outward');
                        } elseif(stripos($typeName, 'Quat') !== false) {
                            $icon = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'; // Cube
                            $iconClass = 'text-fuchsia-500 bg-fuchsia-50 dark:bg-fuchsia-900/20';
                        } elseif(stripos($typeName, 'DC') !== false) {
                            $icon = 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'; // Stop / Ban
                            $iconClass = 'text-blue-300 bg-blue-50/50 dark:bg-blue-900/20';
                        } elseif(stripos($typeName, 'Sale') !== false) {
                            $icon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'; // Calendar
                            $iconClass = 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20';
                        }
                    @endphp
                    
                    <a href="{{ $route }}" class="group flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-2xl hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconClass }} transition-colors">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $typeName }} Pre-Order</h4>
                                <p class="text-[10px] font-bold text-slate-400 mt-0.5">Type: {{ $typeName }}</p>
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <div class="p-6 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                <button @click="showCreateModal = false" class="w-full py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Convert to Sales Order Modal --}}
    <div x-show="showConvertModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="showConvertModal" 
             x-transition.opacity 
             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
             @click="showConvertModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showConvertModal" 
             x-transition.scale.origin.bottom
             class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl overflow-hidden flex flex-col">
            
            <div class="p-8 text-center border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Convert to Sales Order</h2>
                <p class="text-[11px] font-bold text-slate-400 mt-2">Collect outstanding balance and finalize the order</p>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Remaining Balance</label>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">₹<span x-text="convertBalance.toFixed(2)"></span></div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Collect Amount</label>
                    <input type="number" step="0.01" x-model="collectAmount"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Payment Mode</label>
                    <select x-model="convertPaymentMode"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                        <option value="cash">Cash</option>
                        <option value="qr">QR Code</option>
                        <option value="card">Card</option>
                        <option value="credit">Credit / Pay Later</option>
                    </select>
                </div>
            </div>
            
            <div class="p-6 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex gap-3">
                <button @click="showConvertModal = false" class="flex-1 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                    Cancel
                </button>
                <button @click="submitConvert()" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20">
                    Convert
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('preOrderDashboard', () => ({
            activeTab: 'All Orders',
            search: '',
            showCreateModal: false,
            showColumnFilter: false,
            availableColumns: [
                { id: 'sl_no', label: 'Sl. No' },
                { id: 'date', label: 'Date' },
                { id: 'invoice_no', label: 'Invoice No' },
                { id: 'draft_no', label: 'Draft No' },
                { id: 'customer_name', label: 'Customer Name' },
                { id: 'customer_phone', label: 'Customer Phone' },
                { id: 'customer_address', label: 'Customer Address' },
                { id: 'total', label: 'Total' },
                { id: 'order_with', label: 'Order With' },
                { id: 'expected_delivery', label: 'Expected Delivery' },
                { id: 'status', label: 'Status' },
                { id: 'actions', label: 'Actions' }
            ],
            visibleColumns: ['sl_no', 'date', 'invoice_no', 'draft_no', 'customer_name', 'customer_phone', 'customer_address', 'total', 'order_with', 'expected_delivery', 'status', 'actions'],
            showConvertModal: false,
            convertBillId: null,
            convertBalance: 0,
            collectAmount: 0,
            convertPaymentMode: 'cash',

            openConvertModal(billId, balance) {
                this.convertBillId = billId;
                this.convertBalance = parseFloat(balance);
                this.collectAmount = this.convertBalance;
                this.showConvertModal = true;
            },

            async submitConvert() {
                try {
                    const response = await fetch(`/billing/pre-orders/${this.convertBillId}/convert`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            amount: this.collectAmount,
                            payment_mode: this.convertPaymentMode
                        })
                    });
                    
                    const data = await response.json();
                    
                    if(data.success) {
                        if (Alpine.store('setup')) {
                            Alpine.store('setup').addToast('Successfully converted to Sales Order', 'success');
                        } else {
                            alert('Successfully converted to Sales Order');
                        }
                        this.showConvertModal = false;
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        throw new Error(data.message || 'Conversion failed');
                    }
                } catch(e) {
                    console.error(e);
                    alert('Error converting: ' + e.message);
                }
            },

            async updateDeliveryDate(billId, newDate) {
                try {
                    const response = await fetch(`/billing/pre-orders/${billId}/delivery-date`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ expected_delivery_date: newDate })
                      });
                      
                      const data = await response.json();
                      if(data.success) {
                          if (Alpine.store('setup')) {
                              Alpine.store('setup').addToast('Expected delivery date updated', 'success');
                          } else {
                              alert('Expected delivery date updated');
                          }
                      } else {
                          throw new Error(data.message);
                      }
                  } catch(e) {
                      console.error(e);
                      alert('Error: ' + e.message);
                  }
              },
            
            refreshData() {
                this.search = '';
                this.activeTab = 'All Orders';
                this.visibleColumns = this.availableColumns.map(c => c.id);
            },
            
            downloadCSV() {
                let csvContent = "data:text/csv;charset=utf-8,";
                // Get visible headers
                const headers = Array.from(document.querySelectorAll('table thead th'))
                                     .filter(th => th.style.display !== 'none' && th.innerText !== 'ACTIONS')
                                     .map(th => `"${th.innerText.trim()}"`);
                csvContent += headers.join(",") + "\n";
                
                // Get visible rows
                const rows = Array.from(document.querySelectorAll('table tbody tr')).filter(tr => tr.style.display !== 'none');
                if (rows.length === 0) {
                    return Alpine.store('setup') ? Alpine.store('setup').addToast('No data available to download', 'error') : alert('No data available to download');
                }
                
                rows.forEach(row => {
                    const cols = Array.from(row.querySelectorAll('td'))
                                      .filter((td, index) => {
                                          const ths = Array.from(document.querySelectorAll('table thead th'));
                                          return td.style.display !== 'none' && ths[index] && ths[index].innerText !== 'ACTIONS';
                                      })
                                      .map(td => {
                                          let text = td.innerText.trim();
                                          if (td.querySelector('select')) {
                                              const select = td.querySelector('select');
                                              text = select.options[select.selectedIndex].text;
                                          }
                                          return `"${text.replace(/"/g, '""')}"`;
                                      });
                    if(cols.length > 0) csvContent += cols.join(",") + "\n";
                });
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `pre-orders-export-${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            
            isVisible(billType, searchStr) {
                const matchesTab = this.activeTab === 'All Orders' || billType === this.activeTab;
                const matchesSearch = this.search === '' || searchStr.includes(this.search.toLowerCase());
                return matchesTab && matchesSearch;
            },

            async updateStatus(billId, newStatus) {
                try {
                    const response = await fetch(`/billing/pre-orders/${billId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    
                    const data = await response.json();
                    
                    if(data.success) {
                        if (Alpine.store('setup')) {
                            Alpine.store('setup').addToast('Status Updated Successfully', 'success');
                        } else {
                            alert('Status Updated Successfully');
                        }
                        
                        // Success toast or reload if strikethrough logic needs to apply
                        if (newStatus.toLowerCase() === 'rejected' || newStatus.toLowerCase() === 'cancelled') {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        throw new Error(data.message || 'Failed to update');
                    }
                } catch(e) {
                    console.error(e);
                    if (Alpine.store('setup')) {
                        Alpine.store('setup').addToast('Error updating status: ' + e.message, 'error');
                    } else {
                        alert('Error updating status: ' + e.message);
                    }
                }
            },

            deleteOrder(billId) {
                if(confirm('Are you sure you want to cancel/delete this order?')) {
                    // Route to delete or set status to cancelled
                    this.updateStatus(billId, 'cancelled');
                }
            }
        }));
    });
</script>
@endpush
@endsection

