@extends('layouts.tenant')
@section('title', 'Stock History')
@section('page-title', 'Inventory Timeline')

@section('content')
<div class="space-y-6">
    {{-- Filters --}}
    <div class="glass-card p-6 rounded-[2rem] bg-white/50 border border-slate-100 shadow-sm">
        <form action="{{ route('tenant.inventory.history') }}" method="GET" class="flex flex-wrap items-end gap-6">
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Filter Product</label>
                <select name="product_id" class="block bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-black focus:ring-4 focus:ring-blue-50 outline-none transition-all w-64">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->product_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Movement Type</label>
                <select name="type" class="block bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-black focus:ring-4 focus:ring-blue-50 outline-none transition-all w-40">
                    <option value="">All Types</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In (Purchase)</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out (Sales)</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-100 hover:scale-[1.02] transition-all">Apply Filters</button>
            <a href="{{ route('tenant.inventory.history') }}" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">Reset</a>
        </form>
    </div>

    {{-- History Table --}}
    <div class="glass-card rounded-[2.5rem] border-white/40 shadow-xl overflow-hidden bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Product</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Event</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Qty Change</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Balance</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <p class="text-xs font-black text-slate-900">{{ $log->created_at->format('d M, Y') }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $log->created_at->format('h:i A') }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-900">{{ $log->product?->product_name ?: 'N/A' }}</p>
                                <p class="text-[9px] font-black text-blue-600 tracking-widest uppercase">{{ $log->product?->barcode }}</p>
                            </td>
                            <td class="px-8 py-5 text-center">
                                @php
                                    $style = [
                                        'in' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'out' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'adjustment' => 'bg-amber-50 text-amber-600 border-amber-100'
                                    ][$log->type] ?? 'bg-slate-50 text-slate-600';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $style }}">
                                    {{ $log->type }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-center font-black text-sm">
                                <span class="{{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $log->type == 'in' ? '+' : '-' }}{{ $log->quantity }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-black text-slate-900">{{ $log->new_stock }}</span>
                                    <span class="text-[9px] font-bold text-slate-400">From {{ $log->old_stock }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                @if($log->reference_id)
                                    <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest underline cursor-pointer">
                                        #{{ $log->reference_type == 'bill' ? 'INV' : 'PUR' }}-{{ $log->reference_id }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-300">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center opacity-30">
                                <p class="text-[10px] font-black uppercase tracking-[0.3em]">No movement records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-8 py-4 border-t border-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

