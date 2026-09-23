@extends('layouts.tenant')

@section('title', 'Employee Attendance')

@section('content')
<div class="h-full flex flex-col space-y-6" x-data="{
    date: '{{ $dateStr }}',
    
    submitForm() {
        document.getElementById('attendance-form').submit();
    },

    changeDate(newDate) {
        this.date = newDate;
        window.location.href = '{{ route('tenant.attendance.index') }}?date=' + newDate;
    },

    markAll(status) {
        document.querySelectorAll('.attendance-status-select').forEach(select => {
            select.value = status;
            // Dispatch change event to let Alpine/HTML know if needed
            select.dispatchEvent(new Event('change'));
        });
        
        // Also update Alpine-driven status visuals if needed
        // Since we are using standard selects for maximum form submission safety, we'll let users toggle statuses.
    }
}">
    <!-- Session Alerts -->
    <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[200] w-[400px] space-y-3">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="bg-emerald-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center justify-between border border-emerald-400/20 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-xs font-black uppercase tracking-widest">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="opacity-50 hover:opacity-100"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" 
                 class="bg-rose-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center justify-between border border-rose-400/20 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-xs font-black uppercase tracking-widest">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="opacity-50 hover:opacity-100"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
    </div>

    <!-- Header Command Center -->
    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl">
        <div class="flex items-center gap-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">Daily Attendance</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Log daily attendance, punch times, and shift comments</p>
            </div>
            
            <div class="h-10 w-px bg-slate-100 dark:bg-slate-800 mx-2"></div>

            <div class="flex items-center gap-4">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select Date</label>
                <input type="date" :value="date" @change="changeDate($event.target.value)" 
                       class="bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3 text-xs font-black text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 uppercase">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="markAll('present')" class="px-5 py-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                Mark All Present
            </button>
            <button type="button" @click="markAll('absent')" class="px-5 py-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-500 dark:text-rose-400 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                Mark All Absent
            </button>
        </div>
    </div>

    <!-- Attendance Summary cards -->
    <div class="grid grid-cols-5 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-md">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Roster Count</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-emerald-50/50 dark:bg-emerald-950/10 p-6 rounded-[2rem] border border-emerald-100/50 dark:border-emerald-900/20 shadow-md">
            <p class="text-[9px] font-black text-emerald-600/80 dark:text-emerald-400/80 uppercase tracking-widest mb-1">Present Today</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $stats['present'] }}</p>
        </div>
        <div class="bg-rose-50/50 dark:bg-rose-950/10 p-6 rounded-[2rem] border border-rose-100/50 dark:border-rose-900/20 shadow-md">
            <p class="text-[9px] font-black text-rose-500/80 dark:text-rose-400/80 uppercase tracking-widest mb-1">Absent Today</p>
            <p class="text-2xl font-black text-rose-500 dark:text-rose-400 leading-none">{{ $stats['absent'] }}</p>
        </div>
        <div class="bg-amber-50/50 dark:bg-amber-950/10 p-6 rounded-[2rem] border border-amber-100/50 dark:border-amber-900/20 shadow-md">
            <p class="text-[9px] font-black text-amber-600/80 dark:text-amber-400/80 uppercase tracking-widest mb-1">Half Day Today</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400 leading-none">{{ $stats['half_day'] }}</p>
        </div>
        <div class="bg-sky-50/50 dark:bg-sky-950/10 p-6 rounded-[2rem] border border-sky-100/50 dark:border-sky-900/20 shadow-md">
            <p class="text-[9px] font-black text-sky-600/80 dark:text-sky-400/80 uppercase tracking-widest mb-1">Late Today</p>
            <p class="text-2xl font-black text-sky-600 dark:text-sky-400 leading-none">{{ $stats['late'] }}</p>
        </div>
    </div>

    <!-- Attendance Form sheet -->
    <div class="flex-1 glass-card bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col">
        <form id="attendance-form" action="{{ route('tenant.attendance.store') }}" method="POST" class="flex-1 flex flex-col">
            @csrf
            <input type="hidden" name="date" :value="date">

            <div class="overflow-x-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md z-10">
                        <tr class="border-b border-slate-50 dark:border-slate-800">
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">S.No</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Employee Name</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Branch</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Check In</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Check Out</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Notes / Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50/50 dark:divide-slate-800/50">
                        @foreach($employees as $index => $employee)
                        @php
                            $attendance = $attendances->get($employee->id);
                            $currentStatus = $attendance ? $attendance->status : 'present';
                            $checkInVal = $attendance && $attendance->check_in ? substr($attendance->check_in, 0, 5) : '';
                            $checkOutVal = $attendance && $attendance->check_out ? substr($attendance->check_out, 0, 5) : '';
                            $notesVal = $attendance ? $attendance->notes : '';
                        @endphp
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all cursor-default">
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-black text-slate-400">{{ $index + 1 }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div>
                                    <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $employee->name }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $employee->role ?? 'STAFF' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight">{{ $employee->branch ? $employee->branch->name : 'All Branches' }}</span>
                            </td>
                            <td class="px-6 py-3" x-data="{ status: '{{ $currentStatus }}' }">
                                <select name="attendance[{{ $employee->id }}][status]" 
                                        x-model="status"
                                        class="attendance-status-select text-[10px] font-black uppercase tracking-wider bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                        :class="{
                                            'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20': status === 'present',
                                            'text-rose-500 bg-rose-50 dark:bg-rose-950/20': status === 'absent',
                                            'text-amber-600 bg-amber-50 dark:bg-amber-950/20': status === 'half_day',
                                            'text-sky-600 bg-sky-50 dark:bg-sky-950/20': status === 'late',
                                        }">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="late">Late Arrival</option>
                                </select>
                            </td>
                            <td class="px-6 py-3">
                                <input type="time" name="attendance[{{ $employee->id }}][check_in]" value="{{ $checkInVal }}" 
                                       class="text-[10px] font-black bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-3">
                                <input type="time" name="attendance[{{ $employee->id }}][check_out]" value="{{ $checkOutVal }}" 
                                       class="text-[10px] font-black bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-3">
                                <input type="text" name="attendance[{{ $employee->id }}][notes]" value="{{ $notesVal }}" placeholder="e.g. Approved causal leave"
                                       class="w-full text-[10px] font-bold bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            </td>
                        </tr>
                        @endforeach
                        @if(count($employees) === 0)
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <span class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-full text-slate-400 dark:text-slate-500"><i class="fa-solid fa-users-slash text-2xl"></i></span>
                                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No active employees to record attendance</p>
                                    <a href="{{ route('tenant.employees.index') }}" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-md">Go to Employee Register</a>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Sticky Save Footer -->
            @if(count($employees) > 0)
            <div class="p-6 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center rounded-b-[3rem]">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">Make sure to review all records before deployment</p>
                <button type="submit" class="px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-blue-100 dark:shadow-none">
                    Save Attendance Sheets
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
