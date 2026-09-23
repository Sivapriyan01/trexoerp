@extends('layouts.tenant')
@section('title', 'Anniversary Reminders')
@section('page-title', 'Reminder Dashboard')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header with Stats -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Anniversary Reminders</h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Track and manage automated customer anniversary greetings</p>
        </div>
        
        <div class="grid grid-cols-3 gap-3">
            <div class="glass-card p-4 rounded-2xl border-blue-100 min-w-[120px]">
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Today</p>
                <p class="text-xl font-black text-blue-600">{{ $stats['today_count'] }}</p>
            </div>
            <div class="glass-card p-4 rounded-2xl border-emerald-100 min-w-[120px]">
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Next 7 Days</p>
                <p class="text-xl font-black text-emerald-600">{{ $stats['upcoming_count'] }}</p>
            </div>
            <div class="glass-card p-4 rounded-2xl border-slate-100 dark:border-slate-800 min-w-[120px]">
                <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Active</p>
                <p class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $stats['total_active'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Panel: Today's Reminders -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-[2.5rem] overflow-hidden bg-white/40 dark:bg-slate-900/40 border-white/40 dark:border-slate-800/60 shadow-2xl">
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800/60 flex items-center justify-between bg-white/20 dark:bg-slate-800/20">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Due Today</h3>
                        <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5">{{ now()->format('l, d F Y') }}</p>
                    </div>
                    @if($todayReminders->count() > 0)
                        <span class="px-3 py-1 bg-blue-600 text-white text-[8px] font-black uppercase rounded-full tracking-widest animate-pulse">Processing</span>
                    @endif
                </div>

                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @forelse($todayReminders as $bill)
                        <div class="px-8 py-6 flex items-center justify-between hover:bg-blue-50/50 dark:hover:bg-slate-800/50 transition-all group">
                            <div class="flex items-center gap-4">
                                @php
                                    $name = $bill->customer_name ?: ($bill->name ?? 'Unknown');
                                    $phone = $bill->customer_phone ?: ($bill->phone ?? '');
                                    $date = $bill->bill_date ?: ($bill->anniversary_date ?? now());
                                    $years = now()->year - $date->year;
                                    $type = ($bill instanceof \App\Models\Bill) ? 'Bill Anniversary' : 'Profile Anniversary';
                                @endphp
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ ($bill instanceof \App\Models\Bill) ? 'from-blue-600 to-indigo-600' : 'from-rose-500 to-fuchsia-600' }} text-white flex items-center justify-center text-xs font-black shadow-lg shadow-blue-200">
                                    {{ substr($name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase">{{ $name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ $phone }}</span>
                                        <span class="w-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></span>
                                        <span class="text-[10px] font-black {{ ($bill instanceof \App\Models\Bill) ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }} uppercase tracking-tighter">{{ $years }} Year {{ $type }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right mr-4">
                                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">{{ ($bill instanceof \App\Models\Bill) ? 'Invoice' : 'Relation' }}</p>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">#{{ $bill->invoice_no ?? 'Profile' }}</p>
                                </div>
                                <button onclick="sendManualReminder('{{ $bill->id }}', '{{ ($bill instanceof \App\Models\Bill) ? 'bill' : 'customer' }}')" class="p-3 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.284l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.768-5.764-5.768zm3.393 8.247c-.144.405-.833.778-1.162.827-.329.049-.652.072-1.611-.293-1.173-.446-1.926-1.637-1.983-1.714-.058-.076-.468-.621-.468-1.189 0-.568.298-.847.404-.961.107-.114.23-.143.308-.143h.221c.08 0 .188-.031.294.225.107.256.366.892.398.956.032.064.053.139.011.225-.042.085-.064.139-.127.213-.064.074-.134.165-.191.223-.064.064-.13.134-.056.262.074.128.33.543.707.879.485.431.892.565 1.02.629.128.064.202.053.277-.032.074-.085.319-.373.404-.5.085-.128.17-.107.287-.064.117.043.745.352.872.416.128.064.213.096.245.149.032.053.032.309-.112.714z"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-200">
                                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest">No reminders for today</h4>
                            <p class="text-[10px] font-bold text-slate-300 mt-2 uppercase tracking-tighter">Automated greetings will resume when anniversaries occur</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Side Panel: Upcoming Reminders -->
        <div class="space-y-6">
            <div class="glass-card p-8 rounded-[2.5rem] bg-white dark:bg-slate-900 shadow-xl border-white dark:border-slate-800">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Looking Ahead</h3>
                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Next 7 Days</span>
                </div>

                <div class="space-y-4">
                    @forelse($upcomingReminders as $bill)
                        <div class="p-4 bg-slate-50/50 dark:bg-slate-800/50 rounded-2xl border border-slate-50 dark:border-slate-800 flex items-center justify-between group hover:border-blue-100 dark:hover:border-blue-900 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center text-[10px] font-black text-blue-600 dark:text-blue-400">
                                    {{ substr($bill->customer_name ?: 'C', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-900 dark:text-slate-100 uppercase leading-none">{{ Str::limit($bill->customer_name ?: ($bill->name ?? 'Unknown'), 15) }}</p>
                                    <p class="text-[9px] font-bold text-blue-500 dark:text-blue-400 mt-1">{{ $bill->upcoming_date }} • {{ $bill->years }} Year {{ ($bill->type == 'Bill Anniversary') ? 'Bill' : 'Profile' }}</p>
                                </div>
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg width="14" height="14" class="text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    @empty
                        <p class="text-[10px] font-bold text-slate-300 text-center py-10 uppercase tracking-widest">No upcoming anniversaries</p>
                    @endforelse
                </div>

                <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800">
                    <a href="{{ route('tenant.setup.index') }}?tab=anniversary" class="flex items-center justify-between p-4 bg-slate-900 dark:bg-slate-800 text-white rounded-2xl group hover:bg-blue-600 dark:hover:bg-blue-600 transition-all">
                        <span class="text-[10px] font-black uppercase tracking-widest">Edit Message</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function sendManualReminder(id, type) {
        if(!confirm('Send this anniversary reminder manually now?')) return;
        
        try {
            const response = await fetch('{{ route("tenant.reminders.manual") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id, type })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Reminder sent successfully! ✅');
            } else {
                alert('Error: ' + (result.message || 'Failed to send reminder'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Something went wrong. Please check your internet connection.');
        }
    }
</script>

@endsection
