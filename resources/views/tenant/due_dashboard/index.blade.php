@extends('layouts.tenant')
@section('title', 'Due Date Dashboard')
@section('page-title', 'Due Date Dashboard')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <!-- TOP STATS ROW -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        @php
            $statCards = [
                ['label' => 'Total Due', 'value' => '₹' . number_format($stats['total_due'], 2), 'color' => 'blue', 'icon' => 'currency-rupee', 'filter' => 'pending'],
                ['label' => 'Overdue', 'value' => '₹' . number_format($stats['overdue'], 2), 'color' => 'rose', 'icon' => 'exclamation-circle', 'filter' => 'overdue'],
                ['label' => 'Due Today', 'value' => '₹' . number_format($stats['due_today'], 2), 'color' => 'amber', 'icon' => 'clock', 'filter' => 'due_today'],
                ['label' => 'Due Soon', 'value' => '₹' . number_format($stats['due_soon'], 2), 'color' => 'sky', 'icon' => 'calendar', 'filter' => 'due_soon'],
                ['label' => 'Total Paid', 'value' => '₹' . number_format($stats['total_paid'], 2), 'color' => 'emerald', 'icon' => 'check-circle', 'filter' => 'paid'],
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ route('tenant.due-dashboard.index', ['status' => $card['filter']]) }}" class="glass-card p-6 rounded-[2.5rem] border-white/20 hover:scale-[1.02] transition-all group {{ request('status') == $card['filter'] ? 'ring-2 ring-'.$card['color'].'-500' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-900/20 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                        @if($card['icon'] === 'currency-rupee')
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($card['icon'] === 'exclamation-circle')
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($card['icon'] === 'clock')
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($card['icon'] === 'calendar')
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @else
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $card['label'] }}</p>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- FILTERS & DATA TABLE -->
    <div class="glass-card rounded-[3rem] overflow-hidden">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800/60">
            <form action="{{ route('tenant.due-dashboard.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[200px]">
                    <svg width="14" height="14" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer or invoice..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white">
                </div>
                
                <select name="status" class="bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold px-6 py-2.5 outline-none dark:text-white min-w-[150px]">
                    <option value="">All Pending</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Total Unpaid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="due_today" {{ request('status') == 'due_today' ? 'selected' : '' }}>Due Today</option>
                    <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid History</option>
                </select>

                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold px-4 py-2 outline-none dark:text-white">
                    <span class="text-slate-400 text-xs">to</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold px-4 py-2 outline-none dark:text-white">
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 dark:shadow-none">
                    Filter
                </button>
                
                <a href="{{ route('tenant.due-dashboard.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-6 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Reset
                </a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Sl No</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Invoice No</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Customer</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Phone</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Due Date</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Remaining</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($dueItems as $item)
                        @php
                            $dueDate = \Carbon\Carbon::parse($item->due_date);
                            $daysRemaining = \Carbon\Carbon::today()->diffInDays($dueDate, false);
                            $isOverdue = $daysRemaining < 0;
                            $isDueSoon = $daysRemaining >= 0 && $daysRemaining <= 7;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-8 py-5 text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ ($dueItems->currentPage()-1) * $dueItems->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-black text-blue-600 dark:text-blue-400 uppercase">#{{ $item->instalment->bill->invoice_no ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-black text-slate-900 dark:text-slate-200 uppercase">{{ $item->instalment->customer->name ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Customer ID: #{{ $item->instalment->customer->id ?? '0' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $item->instalment->customer->phone ?? 'N/A' }}</td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase">{{ $dueDate->format('d M, Y') }}</span>
                            </td>
                            <td class="px-6 py-5">
                                @if($isOverdue)
                                    <span class="px-2 py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[9px] font-black rounded-lg uppercase">
                                        {{ abs($daysRemaining) }} Days Overdue
                                    </span>
                                @elseif($daysRemaining == 0)
                                    <span class="px-2 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[9px] font-black rounded-lg uppercase">
                                        Due Today
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-black rounded-lg uppercase">
                                        {{ $daysRemaining }} Days Left
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[12px] font-black text-slate-900 dark:text-white tracking-tight">₹{{ number_format($item->amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($item->status === 'paid')
                                    <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-black rounded-lg uppercase">
                                        Paid
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[9px] font-black rounded-lg uppercase">
                                        Unpaid
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if($item->status !== 'paid')
                                        <!-- WhatsApp Reminder -->
                                        <a href="{{ $item->instalment->getReminderLink($item->instalment_no, $item->amount, $item->due_date) }}" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="WhatsApp Reminder">
                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.7 17.7 68.9 27.1 106.1 27.1h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.2-3.2-5.6-.3-8.6 2.5-11.3 2.5-2.5 5.6-6.5 8.3-9.8 2.8-3.3 3.7-5.6 5.6-9.3 1.9-3.7.9-6.9-.5-9.8-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.5 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                                        </a>
                                    @endif
                                    <!-- View Invoice -->
                                    <a href="{{ route('tenant.billing.invoice.view', $item->instalment->bill_id) }}" class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="View Invoice">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if($item->status !== 'paid')
                                        <!-- Pay Now -->
                                        <button onclick="openPaymentModal({{ $item->id }}, {{ $item->amount }})" class="w-8 h-8 rounded-xl bg-slate-900 dark:bg-blue-600 text-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg" title="Pay Now">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No pending dues found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dueItems->hasPages())
            <div class="p-8 border-t border-slate-100 dark:border-slate-800/60">
                {{ $dueItems->links() }}
            </div>
        @endif
    </div>
</div>

<!-- PAYMENT MODAL -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closePaymentModal()"></div>
    <div class="relative w-full max-w-md glass-card rounded-[2.5rem] overflow-hidden animate-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800/60">
            <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight uppercase">Record Payment</h3>
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Settle pending instalment amount</p>
        </div>
        <form id="paymentForm" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="schedule_id" id="modal_schedule_id">
            
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Amount to Pay</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">₹</span>
                    <input type="number" name="amount" id="modal_amount" step="0.01" class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-base font-black outline-none dark:text-white" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Payment Method</label>
                <select name="payment_method" class="w-full px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white">
                    <option value="cash">Cash</option>
                    <option value="upi">UPI / Online</option>
                    <option value="card">Card</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Remarks (Optional)</label>
                <textarea name="remark" rows="2" class="w-full px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white resize-none" placeholder="Add a note..."></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closePaymentModal()" class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 dark:shadow-none">
                    Submit Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPaymentModal(id, amount) {
        document.getElementById('modal_schedule_id').value = id;
        document.getElementById('modal_amount').value = amount;
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('paymentModal').classList.add('flex');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentModal').classList.remove('flex');
    }

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch("{{ route('tenant.instalments.pay') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Payment failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    });
</script>
@endpush
@endsection

