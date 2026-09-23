@extends('layouts.tenant')
@section('title', 'Purchase Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.purchase.index') }}" class="p-3 bg-white dark:bg-slate-800 rounded-2xl text-slate-400 hover:text-blue-600 shadow-sm transition-all">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Purchase Details</h3>
                <p class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $purchase->invoice_ref }}</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="window.location.href='{{ route('tenant.purchase.print', $purchase->id) }}'" class="px-6 py-3 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-95 transition-all">
                Generate Invoice
            </button>
            <a href="{{ route('tenant.purchase.edit', $purchase->id) }}" class="px-6 py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">
                Edit Order
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Order Items</h4>
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="pb-4">Product</th>
                            <th class="pb-4 text-center">Quantity</th>
                            <th class="pb-4 text-right">Price</th>
                            <th class="pb-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="py-4">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $item->product_name }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $item->brand }} | {{ $item->size }}</p>
                                </td>
                                <td class="py-4 text-xs font-black text-slate-900 dark:text-white text-center">{{ number_format($item->quantity) }}</td>
                                <td class="py-4 text-xs font-bold text-slate-500 text-right">₹{{ number_format($item->buy_price, 2) }}</td>
                                <td class="py-4 text-xs font-black text-slate-900 dark:text-white text-right">₹{{ number_format($item->grand_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td colspan="3" class="pt-6 text-[10px] font-black text-slate-400 uppercase text-right">Grand Total</td>
                            <td class="pt-6 text-xl font-black text-blue-600 dark:text-blue-400 text-right">₹{{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Vendor Info</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Vendor Name</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $purchase->vendor->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Phone</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $purchase->vendor->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Invoice Date</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

