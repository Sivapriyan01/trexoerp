<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('branch')->latest()->get();
        $branches = Branch::all();
        return view('tenant.employees.index', compact('employees', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('tenant.employees.index', compact('branches'));
    }

    public function show($id)
    {
        $employee = Employee::with('branch')->findOrFail($id);
        $branches = Branch::all();
        return view('tenant.employees.index', compact('employee', 'branches'));
    }

    public function edit($id)
    {
        $employee = Employee::with('branch')->findOrFail($id);
        $branches = Branch::all();
        return view('tenant.employees.index', compact('employee', 'branches'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'nullable|email',
                'phone'        => 'nullable|string|max:20',
                'role'         => 'nullable|string|max:255',
                'salary'       => 'nullable|numeric|min:0',
                'joining_date' => 'nullable|date',
                'branch_id'    => 'nullable|exists:branches,id',
            ]);

            $data['is_active'] = $request->has('is_active') && in_array($request->input('is_active'), [true, 1, '1', 'true', 'on'], true);

            Employee::create($data);

            return redirect()->route('tenant.employees.index')->with('success', 'Employee registered successfully.');
        } catch (\Exception $e) {
            Log::error('Employee creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to register employee: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);

            $data = $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'nullable|email',
                'phone'        => 'nullable|string|max:20',
                'role'         => 'nullable|string|max:255',
                'salary'       => 'nullable|numeric|min:0',
                'joining_date' => 'nullable|date',
                'branch_id'    => 'nullable|exists:branches,id',
            ]);

            $data['is_active'] = $request->has('is_active') && in_array($request->input('is_active'), [true, 1, '1', 'true', 'on'], true);

            $employee->update($data);

            return redirect()->route('tenant.employees.index')->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            Log::error('Employee update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();
            return redirect()->route('tenant.employees.index')->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Employee deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }
}
