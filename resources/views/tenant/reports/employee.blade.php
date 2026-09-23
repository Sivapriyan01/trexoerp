@extends('layouts.tenant')

@section('title', 'Employee Analytics & Payroll')

@section('content')
<div class="h-full flex flex-col space-y-6" x-data="{
    year: '{{ $year }}',
    month: '{{ $month }}',
    
    changePeriod() {
        window.location.href = '{{ route('tenant.businessreport.employee') }}?year=' + this.year + '&month=' + this.month;
    }
}">
    <!-- Header Command Center -->
    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl">
        <div class="flex items-center gap-6">
            <a href="{{ route('tenant.businessreport.index') }}" class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-750 transition-colors">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 dark:bg-blue-950/30 px-2.5 py-1 rounded-lg border border-blue-100/50 dark:border-blue-900/30">HR & Payroll</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase mt-2">Employee Report</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Monthly attendance metrics, roster audits, and payroll calculations</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex gap-2">
                <select x-model="month" @change="changePeriod()" class="bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3 text-xs font-black text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 uppercase">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>

                <select x-model="year" @change="changePeriod()" class="bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3 text-xs font-black text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 uppercase">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-md flex items-center justify-between">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Payroll Expense</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">₹{{ number_format($stats['total_payroll'], 2) }}</p>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2">Pro-rata payout for {{ \Carbon\Carbon::create($year, $month, 1)->format('M Y') }}</p>
            </div>
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-md flex items-center justify-between">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Attendance Rate</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ number_format($stats['avg_attendance'], 1) }}%</p>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2">Average across active staff</p>
            </div>
            <div class="p-4 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-2xl">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-md flex items-center justify-between">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Late Clock-ins</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ $stats['total_late_arrivals'] }}</p>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2">Total occurrences this month</p>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-2xl">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-md flex items-center justify-between">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Active staff</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ $stats['active_staff'] }}</p>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2">Staff count on active roster</p>
            </div>
            <div class="p-4 bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 rounded-2xl">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Details Table -->
    <div class="flex-1 glass-card bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Payroll & Roster Overview</h3>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Calculated using actual attendance records over {{ $daysInMonth }} calendar days</p>
            </div>
            
            <a href="{{ route('tenant.attendance.index') }}" class="px-5 py-2.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/20 dark:hover:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                Go to Daily Attendance
            </a>
        </div>

        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white/85 dark:bg-slate-900/85 backdrop-blur-md z-10">
                    <tr class="border-b border-slate-50 dark:border-slate-800">
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">S.No</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Employee Name</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Base Salary</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Present</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Absent</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Half Day</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Late</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Attendance Rate</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Calculated Payout</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50/50 dark:divide-slate-800/50">
                    @foreach($employeeReports as $index => $rep)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all cursor-default">
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-black text-slate-400">{{ $index + 1 }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $rep['employee']->name }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $rep['employee']->role ?? 'STAFF' }} • {{ $rep['employee']->branch ? $rep['employee']->branch->name : 'All Branches' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">₹{{ number_format($rep['employee']->salary ?: 0, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-1 rounded-lg">{{ $rep['present'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-black text-rose-500 bg-rose-50 dark:bg-rose-950/20 px-2.5 py-1 rounded-lg">{{ $rep['absent'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-black text-amber-600 bg-amber-50 dark:bg-amber-950/20 px-2.5 py-1 rounded-lg">{{ $rep['half_day'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-black text-sky-600 bg-sky-50 dark:bg-sky-950/20 px-2.5 py-1 rounded-lg">{{ $rep['late'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-12 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full bg-blue-600" style="width: {{ $rep['attendance_rate'] }}%"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-800 dark:text-slate-200">{{ number_format($rep['attendance_rate'], 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-[10px] font-black text-slate-950 dark:text-white">₹{{ number_format($rep['calculated_salary'], 2) }}</span>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($employeeReports) === 0)
                    <tr>
                        <td colspan="9" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <span class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-full text-slate-400 dark:text-slate-500"><i class="fa-solid fa-user-tie text-2xl"></i></span>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No active employees to generate reports</p>
                                <a href="{{ route('tenant.employees.index') }}" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-md">Go to Employee Register</a>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
