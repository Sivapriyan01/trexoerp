@extends('layouts.tenant')
@section('title', $customer->name . ' — Customer Profile')
@section('page-title', 'Customer Profile')

@section('content')
    <div class="space-y-6">

        {{-- Back Button --}}
        <a href="{{ route('tenant.customers.index') }}"
            class="inline-flex items-center gap-2 text-xs font-black text-slate-400 hover:text-blue-600 transition uppercase tracking-widest">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Customers
        </a>

        {{-- Profile Header --}}
        <div class="glass-card rounded-[2rem] p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center gap-6">
            <div
                class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-blue-200 shrink-0">
                {{ strtoupper(substr($customer->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $customer->name }}</h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                    <span class="text-sm font-bold text-slate-500 flex items-center gap-1">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $customer->phone }}
                    </span>
                    @if($customer->email)
                        <span class="text-sm font-bold text-slate-500 flex items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $customer->email }}
                        </span>
                    @endif
                    @if($customer->city || $customer->state)
                        <span class="text-sm font-bold text-slate-500 flex items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ collect([$customer->city, $customer->state])->filter()->implode(', ') }}
                        </span>
                    @endif
                </div>
                @if($customer->gstin)
                    <span
                        class="inline-block mt-2 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-black tracking-widest uppercase">GSTIN:
                        {{ $customer->gstin }}</span>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('tenant.customers.edit', $customer) }}"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Edit Profile
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="glass-card p-5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Bills</p>
                <p class="text-2xl font-black text-slate-900">
                    {{ $customer->bills->where('bill_type', '!=', 'credit_note')->count() }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Spent</p>
                <p class="text-2xl font-black text-emerald-600">₹{{ number_format($totalSpent, 0) }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg. Bill Value</p>
                <p class="text-2xl font-black text-blue-600">₹{{ number_format($averageBill, 0) }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Refunded</p>
                <p class="text-2xl font-black text-rose-500">₹{{ number_format($totalRefunded, 0) }}</p>
            </div>
            <div class="glass-card p-5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Loyalty Points</p>
                <p class="text-2xl font-black text-indigo-600">{{ number_format($customer->points, 0) }}</p>
            </div>
        </div>

        {{-- Info + Last Bill Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Customer Info Card --}}
            <div class="glass-card rounded-2xl p-6 space-y-4">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Profile Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Address</span>
                        <span
                            class="text-xs font-bold text-slate-700 text-right max-w-[60%]">{{ $customer->address ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pincode</span>
                        <span class="text-xs font-bold text-slate-700">{{ $customer->pincode ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Anniversary</span>
                        <span class="text-xs font-bold text-slate-700">
                            {{ $customer->anniversary_date ? $customer->anniversary_date->format('d M Y') : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reminders</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $customer->anniversary_reminder_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $customer->anniversary_reminder_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Member Since</span>
                        <span class="text-xs font-bold text-slate-700">{{ $customer->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Last Bill Summary --}}
            <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Last Bill Summary</h3>
                @if($lastBill)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Invoice No.</p>
                                <p class="text-sm font-black text-slate-900 mt-0.5">{{ $lastBill->invoice_no }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Date</p>
                                <p class="text-sm font-black text-slate-900 mt-0.5">{{ $lastBill->bill_date->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Amount</p>
                                <p class="text-sm font-black text-emerald-600 mt-0.5">
                                    ₹{{ number_format($lastBill->grand_total, 2) }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 bg-slate-50 rounded-xl text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Subtotal</p>
                                <p class="text-xs font-black text-slate-700 mt-1">
                                    ₹{{ number_format($lastBill->subtotal ?? 0, 2) }}</p>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Discount</p>
                                <p class="text-xs font-black text-rose-500 mt-1">
                                    -₹{{ number_format($lastBill->discount_amount ?? 0, 2) }}</p>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">GST</p>
                                <p class="text-xs font-black text-indigo-600 mt-1">
                                    ₹{{ number_format($lastBill->gst_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            class="mb-2 opacity-40">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs font-bold">No bills found for this customer.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Billing History --}}
        <div class="glass-card rounded-[2rem] overflow-hidden">
            <div class="p-5 md:p-6 border-b border-slate-100 bg-white/50">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Billing History (Last 20)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Invoice</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Date</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Items</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Discount</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                GST</th>
                            <th
                                class="px-6 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($customer->bills as $bill)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-black text-blue-600">{{ $bill->invoice_no }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-xs font-bold text-slate-700">{{ $bill->bill_date->format('d M Y') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-black">{{ $bill->items_count ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-xs font-bold text-rose-500">-₹{{ number_format($bill->discount_amount ?? 0, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-xs font-bold text-indigo-600">₹{{ number_format($bill->gst_amount ?? 0, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="text-xs font-black text-emerald-600">₹{{ number_format($bill->grand_total, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic text-xs">No billing history
                                    found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection