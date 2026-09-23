@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tighter uppercase">
                RMA Request: {{ $rma->rma_number }}
            </h1>
            <p class="text-sm font-medium text-slate-500 mt-1">
                Type: <span class="font-black text-blue-600 uppercase">{{ $rma->type }}</span> | 
                Original Invoice: <span class="font-black">{{ $rma->bill->invoice_no }}</span>
            </p>
        </div>
        <a href="{{ route('tenant.returns.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
            &larr; Back
        </a>
    </div>

    @if(session('success'))
        <div class="w-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-sm text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Inspection Items -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Items Inspection</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/50">
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Product</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800 text-center">Qty</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Condition</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Inspection Status</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($rma->items as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="p-4">
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $item->billItem?->product_name ?? 'Unknown' }}</p>
                                </td>
                                <td class="p-4 text-center text-xs font-bold text-slate-700 dark:text-slate-300">{{ $item->quantity }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded text-[10px] font-bold uppercase">{{ $item->condition }}</span>
                                </td>
                                <td class="p-4">
                                    @if($item->inspection_status === 'approved')
                                        <span class="text-green-600 font-bold text-xs uppercase tracking-wider">Approved</span>
                                    @elseif($item->inspection_status === 'rejected')
                                        <span class="text-red-600 font-bold text-xs uppercase tracking-wider">Rejected</span>
                                    @else
                                        <span class="text-yellow-600 font-bold text-xs uppercase tracking-wider">Pending</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($rma->status !== 'processed')
                                    <form action="{{ route('tenant.returns.update-item-inspection', [$rma->id, $item->id]) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <button type="submit" name="inspection_status" value="approved" class="px-2 py-1 bg-green-50 text-green-600 hover:bg-green-100 rounded text-[10px] font-black uppercase">Approve</button>
                                        <button type="submit" name="inspection_status" value="rejected" class="px-2 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded text-[10px] font-black uppercase">Reject</button>
                                    </form>
                                    @else
                                    <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- RMA Notes -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3">Notes & Audit Log</h3>
                <div class="bg-slate-50 dark:bg-slate-950/50 p-4 rounded-xl text-xs font-mono text-slate-700 dark:text-slate-300 whitespace-pre-wrap">
                    {{ $rma->notes ?: 'No notes available.' }}
                </div>
                
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3 mt-6">Customer Reason</h3>
                <div class="bg-slate-50 dark:bg-slate-950/50 p-4 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                    {{ $rma->reason ?: 'No reason provided.' }}
                </div>
            </div>
        </div>
        
        <div class="space-y-6">
            <!-- Overall Status Management -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3">Update RMA Status</h3>
                
                <div class="mb-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Status</p>
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'approved' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'inspecting' => 'bg-purple-100 text-purple-700 border-purple-200',
                            'processed' => 'bg-green-100 text-green-700 border-green-200',
                            'rejected' => 'bg-red-100 text-red-700 border-red-200'
                        ];
                        $color = $statusColors[$rma->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    @endphp
                    <span class="inline-block px-4 py-2 border {{ $color }} rounded-lg text-xs font-black uppercase tracking-widest">{{ $rma->status }}</span>
                </div>

                @if(!in_array($rma->status, ['processed', 'completed']))
                <form action="{{ route('tenant.returns.update-status', $rma->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Change Status To</label>
                        <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="pending" {{ $rma->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $rma->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="inspecting" {{ $rma->status == 'inspecting' ? 'selected' : '' }}>Inspecting</option>
                            <option value="rejected" {{ $rma->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="processed" {{ $rma->status == 'processed' ? 'selected' : '' }}>Processed (Close RMA)</option>
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Note: Setting status to "Processed" will update stock quantities for approved items.</p>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Add Note</label>
                        <textarea name="notes" rows="2" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Enter status update notes..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-3.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/50 transition-all">
                        Update Status
                    </button>
                </form>
                @else
                <div class="p-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-xl text-xs font-bold text-center">
                    This RMA has been {{ $rma->status }} and closed. Inventory has been updated.
                </div>
                
                @if($rma->type === 'exchange' && $rma->status !== 'completed')
                <div class="mt-4 text-center">
                    <a href="{{ route('tenant.billing.index', ['exchange_rma' => $rma->id]) }}" class="inline-block px-5 py-3 bg-violet-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-violet-700 transition-all shadow-lg shadow-violet-500/30">
                        Create Exchange Sales Order
                    </a>
                </div>
                @endif
                
                @endif
            </div>
            
            <!-- Customer Details -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3">Customer Info</h3>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $rma->customer?->name ?? $rma->bill->customer_name ?? 'Walk-in Customer' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $rma->customer?->phone ?? $rma->bill->customer_phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
