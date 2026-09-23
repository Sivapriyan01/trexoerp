@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tighter uppercase">
                Create {{ ucfirst($type) }} Request (RMA)
            </h1>
            <p class="text-sm font-medium text-slate-500 mt-1">
                Original Invoice: <span class="font-black text-blue-600">{{ $bill->invoice_no }}</span>
            </p>
        </div>
        <a href="{{ route('tenant.returns.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
            &larr; Back
        </a>
    </div>

    <form action="{{ route('tenant.returns.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="bill_id" value="{{ $bill->id }}">
        <input type="hidden" name="type" value="{{ $type }}">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <!-- Items Table -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Select Items to {{ ucfirst($type) }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/50">
                                    <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Select</th>
                                    <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Product</th>
                                    <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Purchased Qty</th>
                                    <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">{{ ucfirst($type) }} Qty</th>
                                    <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">Condition</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($bill->items as $item)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors {{ $item->available_qty == 0 ? 'opacity-50 pointer-events-none' : '' }}">
                                    <td class="p-4">
                                        <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300" {{ $item->available_qty == 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $item->product_name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $item->brand }} | {{ $item->category?->category_name }}</p>
                                    </td>
                                    <td class="p-4 text-xs font-bold text-slate-700 dark:text-slate-300">
                                        {{ $item->available_qty }} 
                                        @if($item->available_qty < $item->quantity)
                                            <span class="text-[9px] text-slate-400 font-normal ml-1">(of {{ $item->quantity }})</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <input type="number" name="items[{{ $item->id }}][quantity]" min="1" max="{{ $item->available_qty }}" value="{{ min(1, $item->available_qty) }}" class="w-20 px-3 py-2 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-lg text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all" {{ $item->available_qty == 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td class="p-4">
                                        <select name="items[{{ $item->id }}][condition]" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-lg text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all" {{ $item->available_qty == 0 ? 'disabled' : '' }}>
                                            <option value="new">Unused / New</option>
                                            <option value="defective">Defective</option>
                                            <option value="damaged">Damaged in transit</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Customer Details -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3">Customer Details</h3>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $bill->customer_name ?: 'Walk-in Customer' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $bill->customer_phone ?: 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- RMA Details -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-3">Request Details</h3>
                    
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason for {{ ucfirst($type) }}</label>
                        <textarea name="reason" rows="2" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Enter detailed reason..."></textarea>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Internal Notes</label>
                        <textarea name="notes" rows="2" class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Any internal comments?"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-3.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/50 transition-all">
                        Generate RMA
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
