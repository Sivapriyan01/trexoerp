@extends('layouts.tenant')
@section('title', 'Business Calendar')
@section('page-title', 'Scheduler')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-160px)]">
    
    {{-- Left: The Calendar --}}
    <div class="lg:col-span-8 flex flex-col gap-6 h-full min-h-0">
        <div class="glass-card flex-1 p-8 rounded-[2.5rem] bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 shadow-xl flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">
                        @if($view === 'month') {{ $date->format('F Y') }}
                        @elseif($view === 'week') Week of {{ $date->startOfWeek()->format('d M') }} - {{ $date->endOfWeek()->format('d M') }}
                        @else {{ $date->format('d F, Y') }}
                        @endif
                    </h2>
                    <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-[0.2em]">{{ ucfirst($view) }}ly Activity Timeline</p>
                </div>
                
                <div class="flex items-center gap-4">
                    {{-- View Switcher --}}
                    <div class="flex bg-slate-50 dark:bg-slate-950/50 p-1 rounded-xl border border-slate-100 dark:border-slate-800/60">
                        @foreach(['day', 'week', 'month', 'year'] as $v)
                            <a href="{{ route('tenant.calendar.index', ['view' => $v, 'date' => $date->format('Y-m-d')]) }}" 
                               class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase transition-all {{ $view === $v ? 'bg-white dark:bg-slate-900 shadow-sm text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}">
                                {{ $v }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex gap-1">
                        @php
                            $prevDate = match($view) {
                                'year' => $date->copy()->subYear(),
                                'month' => $date->copy()->subMonth(),
                                'week' => $date->copy()->subWeek(),
                                default => $date->copy()->subDay()
                            };
                            $nextDate = match($view) {
                                'year' => $date->copy()->addYear(),
                                'month' => $date->copy()->addMonth(),
                                'week' => $date->copy()->addWeek(),
                                default => $date->copy()->addDay()
                            };
                        @endphp
                        <a href="{{ route('tenant.calendar.index', ['view' => $view, 'date' => $prevDate->format('Y-m-d')]) }}" class="w-10 h-10 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition shadow-sm">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <a href="{{ route('tenant.calendar.index', ['view' => $view, 'date' => $nextDate->format('Y-m-d')]) }}" class="w-10 h-10 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition shadow-sm">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto pr-2 scrollbar-hide">
                @if($view === 'year')
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                        @for($m=1; $m<=12; $m++)
                            @php
                                $monthDate = $date->copy()->month($m);
                                $monthSales = $sales->filter(fn($s) => $s->bill_date->month == $m);
                            @endphp
                            <div onclick="window.location.href='{{ route('tenant.calendar.index', ['view' => 'month', 'date' => $monthDate->format('Y-m-d')]) }}'" 
                                 class="p-6 rounded-[2rem] bg-slate-50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/60 text-slate-800 dark:text-slate-200 hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white dark:hover:text-white hover:scale-105 transition-all group cursor-pointer shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-60">{{ $monthDate->format('M') }}</p>
                                <h4 class="text-lg font-black tracking-tighter mb-2 text-slate-900 dark:text-white group-hover:text-white">{{ $monthDate->format('F') }}</h4>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-bold opacity-80 uppercase">Revenue</p>
                                    <p class="text-xs font-black text-slate-900 dark:text-white group-hover:text-white">₹{{ number_format($monthSales->sum('grand_total'), 0) }}</p>
                                    <p class="text-[8px] font-bold opacity-60">{{ $monthSales->count() }} Invoices</p>
                                </div>
                            </div>
                        @endfor
                    </div>
                @elseif($view === 'month')
                    <div class="grid grid-cols-7 gap-4">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                            <div class="text-center text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-4">{{ $dayName }}</div>
                        @endforeach

                        @php
                            $daysInMonth = $date->daysInMonth;
                            $startDay = $date->copy()->startOfMonth()->dayOfWeek;
                        @endphp

                        @for ($i = 0; $i < $startDay; $i++)
                            <div class="aspect-square opacity-20"></div>
                        @endfor

                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $currDate = $date->copy()->day($day);
                                $isToday = $currDate->isToday();
                                $hasSales = $sales->contains(fn($s) => $s->bill_date->format('Y-m-d') == $currDate->format('Y-m-d'));
                                $hasPurchases = $purchases->contains(fn($p) => $p->invoice_date->format('Y-m-d') == $currDate->format('Y-m-d'));
                            @endphp
                            <div onclick="window.location.href='{{ route('tenant.calendar.index', ['view' => 'day', 'date' => $currDate->format('Y-m-d')]) }}'" 
                                 class="aspect-square rounded-2xl border {{ $isToday ? 'bg-blue-600 border-blue-600 shadow-xl shadow-blue-100 dark:shadow-none text-white' : 'bg-slate-50/50 dark:bg-slate-950/30 border-slate-100 dark:border-slate-800/60 text-slate-800 dark:text-slate-200' }} p-3 relative group hover:scale-105 transition-all cursor-pointer">
                                <span class="text-sm font-black">{{ $day }}</span>
                                <div class="absolute bottom-3 left-3 flex gap-1">
                                    @if($hasSales) <div class="w-1.5 h-1.5 rounded-full {{ $isToday ? 'bg-white' : 'bg-emerald-500' }}"></div> @endif
                                    @if($hasPurchases) <div class="w-1.5 h-1.5 rounded-full {{ $isToday ? 'bg-blue-200' : 'bg-amber-500' }}"></div> @endif
                                </div>
                            </div>
                        @endfor
                    </div>

                @elseif($view === 'week')
                    <div class="space-y-4">
                        @php $weekStart = $date->copy()->startOfWeek(); @endphp
                        @for($i=0; $i<7; $i++)
                            @php 
                                $currDate = $weekStart->copy()->addDays($i);
                                $isToday = $currDate->isToday();
                                $daySales = $sales->filter(fn($s) => $s->bill_date->format('Y-m-d') == $currDate->format('Y-m-d'));
                                $dayPurchases = $purchases->filter(fn($p) => $p->invoice_date->format('Y-m-d') == $currDate->format('Y-m-d'));
                            @endphp
                            <div class="flex items-center gap-6 p-4 rounded-3xl border {{ $isToday ? 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-900/60' : 'bg-white dark:bg-slate-900/40 border-slate-100 dark:border-slate-800' }}">
                                <div class="w-16 text-center">
                                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $currDate->format('D') }}</p>
                                    <p class="text-lg font-black text-slate-900 dark:text-white">{{ $currDate->day }}</p>
                                </div>
                                <div class="flex-1 flex gap-4">
                                    @if($daySales->isNotEmpty())
                                        <div class="flex-1 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl p-3 border border-emerald-100 dark:border-emerald-900/40">
                                            <p class="text-[8px] font-black text-emerald-600 dark:text-emerald-400 uppercase mb-1">Sales ({{ $daySales->count() }})</p>
                                            <p class="text-xs font-black text-slate-900 dark:text-white">₹{{ number_format($daySales->sum('grand_total'), 2) }}</p>
                                        </div>
                                    @endif
                                    @if($dayPurchases->isNotEmpty())
                                        <div class="flex-1 bg-amber-50 dark:bg-amber-950/30 rounded-2xl p-3 border border-amber-100 dark:border-amber-900/40">
                                            <p class="text-[8px] font-black text-amber-600 dark:text-amber-400 uppercase mb-1">Inward ({{ $dayPurchases->count() }})</p>
                                            <p class="text-xs font-black text-slate-900 dark:text-white">₹{{ number_format($dayPurchases->sum('total_amount'), 2) }}</p>
                                        </div>
                                    @endif
                                    @if($daySales->isEmpty() && $dayPurchases->isEmpty())
                                        <div class="flex-1 flex items-center justify-center py-2 opacity-30 italic text-[10px] text-slate-500">No Activity</div>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>

                @else {{-- Day View --}}
                    <div class="space-y-8">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="glass-card p-6 rounded-3xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                <p class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Daily Revenue</p>
                                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($sales->sum('grand_total'), 2) }}</p>
                            </div>
                            <div class="glass-card p-6 rounded-3xl bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40">
                                <p class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest mb-1">Daily Expenses</p>
                                <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($purchases->sum('total_amount'), 2) }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-2">Transactions</h3>
                            @foreach($sales as $s)
                                <div class="flex items-center justify-between p-4 bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 rounded-3xl hover:shadow-lg transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900 dark:text-slate-200">{{ $s->invoice_no }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ $s->customer_name ?: 'Walk-in' }}</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">+₹{{ number_format($s->grand_total, 2) }}</p>
                                </div>
                            @endforeach
                            @foreach($purchases as $p)
                                <div class="flex items-center justify-between p-4 bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 rounded-3xl hover:shadow-lg transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900 dark:text-slate-200">PO #{{ $p->invoice_ref }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Vendor Ref</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-black text-rose-600 dark:text-rose-400">-₹{{ number_format($p->total_amount, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Quick Stats --}}
    <div class="lg:col-span-4 flex flex-col gap-6 h-full min-h-0">
        <div class="glass-card p-8 rounded-[2.5rem] bg-blue-600 text-white shadow-2xl shadow-blue-100 dark:shadow-none flex flex-col justify-center gap-2">
            <p class="text-[10px] font-black text-blue-200 uppercase tracking-[0.2em] mb-2">Total Turn-over</p>
            <h3 class="text-4xl font-black tracking-tighter">₹{{ number_format($sales->sum('grand_total'), 2) }}</h3>
            <p class="text-[10px] font-bold text-blue-200">Across {{ $sales->count() }} transactions in this {{ $view }}</p>
        </div>

        <div class="glass-card flex-1 p-6 rounded-[2.5rem] bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 shadow-xl overflow-hidden flex flex-col">
            <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 px-2">Top Customers</h3>
            <div class="flex-1 overflow-y-auto space-y-4 scrollbar-hide">
                @foreach($sales->groupBy('customer_name')->take(5) as $name => $custSales)
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-[10px]">
                                {{ substr($name ?: 'W', 0, 1) }}
                            </div>
                            <p class="text-[11px] font-black text-slate-800 dark:text-slate-200 uppercase">{{ $name ?: 'Walk-in' }}</p>
                        </div>
                        <p class="text-[10px] font-black text-slate-900 dark:text-slate-300">₹{{ number_format($custSales->sum('grand_total'), 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
