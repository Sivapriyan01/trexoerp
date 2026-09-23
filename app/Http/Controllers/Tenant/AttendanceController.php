<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateStr);

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        // Get attendance records for this date
        $attendances = Attendance::whereDate('date', $dateStr)
            ->get()
            ->keyBy('employee_id');

        // Calculate summary stats
        $stats = [
            'total'     => $employees->count(),
            'present'   => $attendances->where('status', 'present')->count(),
            'absent'    => $attendances->where('status', 'absent')->count(),
            'half_day'  => $attendances->where('status', 'half_day')->count(),
            'late'      => $attendances->where('status', 'late')->count(),
        ];

        return view('tenant.attendance.index', compact('employees', 'attendances', 'dateStr', 'stats'));
    }

    public function store(Request $request)
    {
        try {
            $dateStr = $request->input('date', Carbon::today()->toDateString());
            
            $request->validate([
                'attendance' => 'required|array',
                'attendance.*.status' => 'required|in:present,absent,half_day,late',
                'attendance.*.check_in' => 'nullable|string',
                'attendance.*.check_out' => 'nullable|string',
                'attendance.*.notes' => 'nullable|string',
            ]);

            $attendanceData = $request->input('attendance', []);

            foreach ($attendanceData as $employeeId => $record) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date'        => $dateStr,
                    ],
                    [
                        'status'    => $record['status'],
                        'check_in'  => $record['check_in'] ?: null,
                        'check_out' => $record['check_out'] ?: null,
                        'notes'     => $record['notes'] ?: null,
                    ]
                );
            }

            return redirect()->route('tenant.attendance.index', ['date' => $dateStr])
                ->with('success', 'Attendance sheets saved successfully.');
        } catch (\Exception $e) {
            Log::error('Attendance bulk save failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save attendance sheets: ' . $e->getMessage());
        }
    }
}
