<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    // GET /api/v1/employees
    public function index(Request $request)
    {
        $query = Employee::query()->latest();

        if ($request->filled('q')) {
            $query->where('name', 'ilike', '%' . $request->q . '%');
        }

        $employees = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $employees->items(),
            'meta'    => ['current_page' => $employees->currentPage(), 'last_page' => $employees->lastPage(), 'total' => $employees->total()],
        ]);
    }

    // POST /api/v1/employees
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'salary'      => 'nullable|numeric|min:0',
            'joined_at'   => 'nullable|date',
            'address'     => 'nullable|string',
        ]);

        $employee = Employee::create($data);

        return response()->json(['success' => true, 'data' => $employee], 201);
    }

    // GET /api/v1/employees/{id}
    public function show($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $employee]);
    }

    // PUT /api/v1/employees/{id}
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'designation' => 'sometimes|nullable|string|max:255',
            'phone'       => 'sometimes|nullable|string|max:20',
            'email'       => 'sometimes|nullable|email|max:255',
            'salary'      => 'sometimes|nullable|numeric|min:0',
            'joined_at'   => 'sometimes|nullable|date',
            'address'     => 'sometimes|nullable|string',
        ]);

        $employee->update($data);

        return response()->json(['success' => true, 'data' => $employee]);
    }

    // DELETE /api/v1/employees/{id}
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee deleted.']);
    }

    // GET /api/v1/employees/attendance
    public function attendance(Request $request)
    {
        $query = Attendance::with('employee')->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)->whereYear('date', $request->year);
        }

        $records = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => $records->items(),
            'meta'    => ['current_page' => $records->currentPage(), 'last_page' => $records->lastPage(), 'total' => $records->total()],
        ]);
    }

    // POST /api/v1/employees/attendance
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'date'                  => 'required|date',
            'records'               => 'required|array|min:1',
            'records.*.employee_id' => 'required|exists:employees,id',
            'records.*.status'      => 'required|in:present,absent,half_day,leave',
        ]);

        $saved = [];
        foreach ($request->records as $rec) {
            $saved[] = Attendance::updateOrCreate(
                ['employee_id' => $rec['employee_id'], 'date' => $request->date],
                ['status' => $rec['status'], 'notes' => $rec['notes'] ?? null]
            );
        }

        return response()->json(['success' => true, 'data' => $saved, 'message' => 'Attendance recorded.']);
    }
}
