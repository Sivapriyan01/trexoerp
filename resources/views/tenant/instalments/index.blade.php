@extends('layouts.tenant')
@section('title', 'Instalments Ledger')
@section('page-title', 'Instalments Command Center')

@section('content')
<div class="space-y-6">
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statCards = [
                ['label' => 'Pending Loans', 'value' => $stats['total_pending'], 'color' => 'amber', 'icon' => 'clock', 'filter' => 'pending'],
                ['label' => 'Total Overdue', 'value' => $stats['total_overdue'], 'color' => 'rose', 'icon' => 'exclamation', 'filter' => 'overdue'],
                ['label' => 'Completed', 'value' => $stats['total_completed'], 'color' => 'emerald', 'icon' => 'check-circle', 'filter' => 'completed'],
                ['label' => 'Total Outstanding', 'value' => '₹' . number_format($stats['total_due_amount'], 0), 'color' => 'blue', 'icon' => 'currency-dollar', 'filter' => ''],
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ route('tenant.instalments.index', $card['filter'] ? ['status' => $card['filter']] : []) }}" class="glass-card rounded-3xl p-5 border border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 flex flex-col gap-3 group hover:-translate-y-1 transition-all duration-300 {{ request('status') === $card['filter'] ? 'ring-2 ring-'.$card['color'].'-500 shadow-lg shadow-'.$card['color'].'-100 dark:shadow-none' : '' }}">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 rounded-xl bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-950/40 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 flex items-center justify-center shadow-sm">
                        @if($card['icon'] == 'clock')
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($card['icon'] == 'exclamation')
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($card['icon'] == 'check-circle')
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">{{ $card['label'] }}</h4>
                    <p class="text-xl font-black text-slate-900 dark:text-white">{{ $card['value'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Main List -->
    <div class="glass-card rounded-[2.5rem] border border-slate-100 dark:border-slate-800/80 shadow-xl overflow-hidden bg-white dark:bg-slate-900/40">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/60 flex items-center justify-between bg-slate-50/30 dark:bg-slate-950/30">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest">Active Instalment Accounts</h3>
            <div class="flex gap-2">
                <input type="text" id="instalment_search" placeholder="Search customer or bill..." class="px-4 py-2 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold text-slate-800 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-50 dark:focus:ring-slate-800 w-64 placeholder:text-slate-400 dark:placeholder:text-slate-600">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800/60 bg-slate-50/10 dark:bg-slate-950/20">
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Bill Info</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Value</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Paid</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Balance</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Next Due</th>
                        <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40" id="instalment_table_body">
                    @forelse($instalments as $ins)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-950/20 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-xs font-black text-slate-900 dark:text-slate-200 uppercase leading-tight">{{ $ins->customer->name }}</p>
                                <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500">{{ $ins->customer->phone }}</p>
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">#{{ $ins->bill->invoice_no }}</td>
                            <td class="px-4 py-4 text-xs font-black text-slate-900 dark:text-slate-100">₹{{ number_format($ins->total_amount, 2) }}</td>
                            <td class="px-4 py-4 text-xs font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($ins->paid_amount, 2) }}</td>
                            <td class="px-4 py-4 text-xs font-black text-rose-600 dark:text-rose-400">₹{{ number_format($ins->due_amount, 2) }}</td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 dark:text-slate-300">
                                {{ $ins->next_due_date ? date('d M, Y', strtotime($ins->next_due_date)) : 'N/A' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter 
                                    {{ $ins->status == 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : ($ins->status == 'overdue' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400') }}">
                                    {{ $ins->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="viewSchedule({{ $ins->id }})" class="px-4 py-1.5 bg-white dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 text-blue-600 dark:text-blue-400 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-50 dark:hover:bg-slate-900 transition-all shadow-sm">
                                    View Schedule
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="8" class="px-6 py-20 text-center opacity-30 dark:opacity-20">
                                <svg width="48" height="48" class="mx-auto mb-4 text-blue-200 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">No active instalment accounts found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/10 dark:bg-slate-950/20">
            {{ $instalments->links() }}
        </div>
    </div>
</div>

{{-- Schedule Modal --}}
<div id="schedule_modal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[3rem] shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/30">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest">Payment Schedule</h3>
                <p id="modal_customer_info" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1"></p>
            </div>
            <button onclick="closeModal()" class="p-2 text-slate-300 hover:text-slate-600 transition-colors">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
            <div id="schedule_grid" class="space-y-3">
                {{-- Schedule items will be loaded here --}}
            </div>
        </div>

        <div class="p-8 border-t border-slate-50 dark:border-slate-800 flex justify-between items-center bg-slate-50/30 dark:bg-slate-950/30">
            <div class="flex gap-4">
                <div>
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">Paid</p>
                    <p id="modal_paid_amt" class="text-sm font-black text-emerald-600 dark:text-emerald-400"></p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">Balance</p>
                    <p id="modal_due_amt" class="text-sm font-black text-rose-600 dark:text-rose-400"></p>
                </div>
            </div>
            <button onclick="closeModal()" class="px-8 py-3 bg-slate-800 dark:bg-slate-950 border border-slate-700/60 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 dark:shadow-none">
                Close View
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    async function viewSchedule(id) {
        const modal = document.getElementById('schedule_modal');
        const grid = document.getElementById('schedule_grid');
        grid.innerHTML = '<div class="text-center py-10"><span class="animate-pulse font-black text-blue-600 dark:text-blue-400">Loading...</span></div>';
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`{{ url('instalments') }}/${id}`);
            const data = await res.json();
            
            document.getElementById('modal_customer_info').textContent = `${data.customer.name} | Bill #${data.bill.invoice_no}`;
            document.getElementById('modal_paid_amt').textContent = `₹${parseFloat(data.paid_amount).toLocaleString()}`;
            document.getElementById('modal_due_amt').textContent = `₹${parseFloat(data.due_amount).toLocaleString()}`;

            grid.innerHTML = data.schedules.map(s => `
                <div class="flex items-center justify-between p-4 rounded-2xl border ${s.status === 'paid' ? 'border-emerald-100 dark:border-emerald-950/40 bg-emerald-50/30 dark:bg-emerald-950/10' : (s.status === 'overdue' ? 'border-rose-100 dark:border-rose-950/40 bg-rose-50/30 dark:bg-rose-950/10' : 'border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30')}">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg ${s.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400'} flex items-center justify-center text-[10px] font-black">
                            ${s.instalment_no}
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-900 dark:text-slate-200 uppercase">Due: ${new Date(s.due_date).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}</p>
                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tighter">Amount: ₹${parseFloat(s.amount).toLocaleString()}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[9px] font-black uppercase ${s.status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : (s.status === 'overdue' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-500 dark:text-amber-400')}">
                            ${s.status}
                        </span>
                        
                        <div class="flex gap-2">
                            ${s.status !== 'paid' ? `
                                <button onclick="sendReminder('${data.customer.phone}', '${data.customer.name}', '${s.instalment_no}', '${s.amount}', '${s.due_date}')" class="p-1.5 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-600 hover:text-white transition-all shadow-sm" title="Send WhatsApp Reminder">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.7 17.7 68.9 27.1 106.1 27.1h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.2-3.2-5.6-.3-8.6 2.5-11.3 2.5-2.5 5.6-6.5 8.3-9.8 2.8-3.3 3.7-5.6 5.6-9.3 1.9-3.7.9-6.9-.5-9.8-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.5 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                                </button>
                                <button onclick="processPayment(${s.id}, ${s.amount})" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-md">
                                    Pay Now
                                </button>
                            ` : `
                                <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500">${new Date(s.paid_at).toLocaleDateString()}</span>
                            `}
                        </div>
                    </div>
                </div>
            `).join('');

        } catch (e) {
            grid.innerHTML = '<p class="text-center text-rose-500">Error loading data</p>';
        }
    }

    async function sendReminder(phone, name, scheduleNo, amount, dueDate) {
        @php
            $shopName = function_exists('tenant') && tenant('name') ? tenant('name') : 'TrexoERP Store';
            $search = ['.localhost', 'demo.', 'demo ', 'rx ', 'trexoerp', 'http://', 'https://'];
            $shopName = str_ireplace($search, '', $shopName);
            $shopName = trim($shopName);
            if (strtolower($shopName) === 'laravel' || empty($shopName)) {
                $shopName = 'TrexoERP Store';
            }
            if (!str_contains(strtolower($shopName), 'store')) {
                $shopName .= ' Store';
            }
            $shopName = ucwords($shopName);
        @endphp
        const shopName = "{{ $shopName }}";
        const msg = `🔔 *Payment Reminder from ${shopName}* 🔔\n\nHello *${name}*,\nThis is a friendly reminder that your Instalment *#${scheduleNo}* for ₹${parseFloat(amount).toLocaleString()} is due on *${new Date(dueDate).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}*.\n\nKindly process the payment to avoid any late fees. Thank you! 🙏`;
        
        try {
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btn.classList.add('bg-green-600', 'text-white');

            const res = await fetch(`{{ route('tenant.whatsapp.send') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone: phone,
                    message: msg
                })
            });
            
            const result = await res.json();
            if (result.success) {
                alert('✅ Reminder sent via WhatsApp!');
            } else {
                alert('❌ API Error: ' + result.message);
                // Fallback to manual link if API fails
                const cleanPhone = phone.replace(/[^0-9]/g, '');
                const finalPhone = cleanPhone.length === 10 ? '91' + cleanPhone : cleanPhone;
                window.open(`https://wa.me/${finalPhone}?text=${encodeURIComponent(msg)}`, '_blank');
            }
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-green-600', 'text-white');
        } catch (e) {
            alert('Connection error');
        }
    }

    async function processPayment(scheduleId, amount) {
        if (!confirm(`Process payment of ₹${amount}?`)) return;

        try {
            const res = await fetch(`{{ route('tenant.instalments.pay') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    schedule_id: scheduleId,
                    amount: amount,
                    payment_method: 'Cash'
                })
            });
            
            const result = await res.json();
            if (result.success) {
                alert('✅ Payment successful!');
                window.location.reload();
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (e) {
            alert('Network error');
        }
    }

    function closeModal() {
        document.getElementById('schedule_modal').classList.add('hidden');
    }

    // Live Search
    document.getElementById('instalment_search').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#instalment_table_body tr');
        rows.forEach(row => {
            if (row.id === 'empty-row') return;
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    @media (prefers-color-scheme: dark) {
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
        }
    }
</style>
