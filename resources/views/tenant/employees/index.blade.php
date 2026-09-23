@extends('layouts.tenant')

@section('title', 'Employee List')

@section('content')
<div class="h-full flex flex-col space-y-6" x-data="{ 
    showModal: false, 
    editingEmployee: null,
    search: '',
    employees: {{ $employees->toJson() }},
    branches: {{ $branches->toJson() }},
    filterOpen: false,
    selectedBranch: 'all',
    selectedStatus: 'all',
    
    newEmployee: {
        name: '',
        email: '',
        phone: '',
        role: 'staff',
        salary: '',
        joining_date: '',
        is_active: true,
        branch_id: ''
    },

    resetForm() {
        this.newEmployee = { 
            name: '', email: '', phone: '', role: 'staff', salary: '', 
            joining_date: '{{ now()->toDateString() }}', is_active: true, branch_id: '' 
        };
        this.editingEmployee = null;
    },

    editEmployee(employee) {
        this.editingEmployee = employee;
        this.newEmployee = { 
            ...employee, 
            joining_date: employee.joining_date ? employee.joining_date.substring(0, 10) : ''
        };
        this.showModal = true;
    }
}">
    <!-- Header Command Center -->
    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl">
        <div class="flex items-center gap-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">Employee Register</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Manage employee records, roles, and branch assignments</p>
            </div>
            
            <div class="h-10 w-px bg-slate-100 dark:bg-slate-800 mx-2"></div>

            <div class="flex items-center gap-8">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Total Employees</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white leading-none">{{ count($employees) }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Active staff</p>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <p class="text-lg font-black text-slate-900 dark:text-white leading-none">{{ $employees->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button @click="resetForm(); showModal = true" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-100 dark:shadow-none flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                Add New Employee
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="flex items-center justify-between">
        <div class="relative group w-96">
            <input type="text" x-model="search" placeholder="Search employee by name, email, phone or role..." 
                   class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl px-12 py-3.5 text-xs font-bold text-slate-600 dark:text-slate-300 shadow-sm focus:ring-2 focus:ring-blue-500 transition-all uppercase tracking-tight placeholder:text-slate-300">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex items-center gap-2 relative">
            <button @click="filterOpen = !filterOpen" :class="filterOpen || selectedBranch !== 'all' || selectedStatus !== 'all' ? 'text-blue-600 border-blue-500/30 bg-blue-50/50 dark:bg-blue-950/30' : 'text-slate-400 border-transparent bg-white dark:bg-slate-900'" class="p-3 rounded-xl transition-all shadow-sm border hover:text-blue-600 hover:border-slate-100 dark:hover:border-slate-800">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4.5h18M3 12h18M3 19.5h18"/></svg>
            </button>
            
            <!-- Filter Dropdown -->
            <div x-show="filterOpen" @click.away="filterOpen = false" x-cloak 
                 class="absolute right-0 top-14 w-64 bg-white/95 dark:bg-slate-950/95 border border-slate-100 dark:border-slate-800/80 rounded-[1.5rem] shadow-2xl p-5 z-50 backdrop-blur-md space-y-4">
                <div>
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Filter by Branch</p>
                    <select x-model="selectedBranch" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-700 dark:text-slate-300 focus:ring-1 focus:ring-blue-500 uppercase">
                        <option value="all">All Branches</option>
                        <template x-for="branch in branches">
                            <option :value="branch.id" x-text="branch.name"></option>
                        </template>
                    </select>
                </div>
                
                <div class="border-t border-slate-100 dark:border-slate-800/60 pt-3">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Filter by Status</p>
                    <div class="flex gap-1.5">
                        <button type="button" @click="selectedStatus = 'all'; filterOpen = false" :class="selectedStatus === 'all' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-2.5 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all">All</button>
                        <button type="button" @click="selectedStatus = 'active'; filterOpen = false" :class="selectedStatus === 'active' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-2.5 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all">Active</button>
                        <button type="button" @click="selectedStatus = 'inactive'; filterOpen = false" :class="selectedStatus === 'inactive' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-2.5 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all">Inactive</button>
                    </div>
                </div>
                
                <div x-show="selectedBranch !== 'all' || selectedStatus !== 'all'" class="border-t border-slate-100 dark:border-slate-800/60 pt-2 flex justify-end">
                    <button type="button" @click="selectedBranch = 'all'; selectedStatus = 'all'; filterOpen = false" class="text-[8px] font-black text-rose-500 hover:text-rose-600 uppercase tracking-widest transition-colors">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="flex-1 glass-card bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col">
        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md z-10">
                    <tr class="border-b border-slate-50 dark:border-slate-800">
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">S.No</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Role</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Branch</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Salary</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Joining Date</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50/50 dark:divide-slate-800/50">
                    <template x-for="(employee, index) in employees" :key="employee.id">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all cursor-default"
                            x-show="(search === '' || employee.name.toLowerCase().includes(search.toLowerCase()) || (employee.email && employee.email.toLowerCase().includes(search.toLowerCase())) || (employee.phone && employee.phone.toLowerCase().includes(search.toLowerCase())) || (employee.role && employee.role.toLowerCase().includes(search.toLowerCase()))) && (selectedBranch === 'all' || employee.branch_id == selectedBranch) && (selectedStatus === 'all' || (selectedStatus === 'active' && employee.is_active) || (selectedStatus === 'inactive' && !employee.is_active))">
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-black text-slate-400" x-text="index + 1"></span>
                            </td>
                            <td class="px-6 py-3">
                                <div>
                                    <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight" x-text="employee.name"></p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest" x-text="employee.email || 'NO EMAIL'"></p>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-100 dark:border-slate-700" x-text="employee.role || 'STAFF'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-tight" x-text="employee.phone || '---'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight" x-text="employee.branch ? employee.branch.name : 'All Branches'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-black text-slate-800 dark:text-slate-200 tracking-tight" x-text="employee.salary ? '₹' + parseFloat(employee.salary).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '---'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 tracking-tight" x-text="employee.joining_date ? new Date(employee.joining_date).toLocaleDateString('en-GB') : '---'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="employee.is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300'"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest" :class="employee.is_active ? 'text-emerald-600' : 'text-slate-400'" x-text="employee.is_active ? 'Active' : 'Inactive'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                    <button @click="editEmployee(employee)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form :action="'/employees/' + employee.id" method="POST" class="inline" @submit="return confirm('Are you sure you want to delete this employee record?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-xl transition-all">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="employees.length === 0">
                        <td colspan="9" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <span class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-full text-slate-400 dark:text-slate-500"><i class="fa-solid fa-user-slash text-2xl"></i></span>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No employee files registered</p>
                                <button @click="resetForm(); showModal = true" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-md">Add First Employee</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Employee Modal (Add/Edit) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-[3rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-white/20">
                
                <form :action="editingEmployee ? '/employees/' + editingEmployee.id : '{{ route('tenant.employees.store') }}'" method="POST" class="p-10 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    @csrf
                    <template x-if="editingEmployee">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight" x-text="editingEmployee ? 'Edit Employee File' : 'Register New Employee'"></h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Configure professional profile and branch binding</p>
                        </div>
                        <button type="button" @click="showModal = false" class="text-slate-300 hover:text-slate-500 transition-colors">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-10">
                        <!-- Left Column: Personal Info -->
                        <div class="space-y-6">
                            <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] border-b border-blue-50 dark:border-blue-900/30 pb-2">Personal Information</p>
                            
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                                <input type="text" name="name" x-model="newEmployee.name" placeholder="Enter Full Name" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                                <input type="email" name="email" x-model="newEmployee.email" placeholder="Enter Email Address" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Phone Number</label>
                                <input type="text" name="phone" x-model="newEmployee.phone" placeholder="Enter Phone Number" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Right Column: Employment details -->
                        <div class="space-y-6">
                            <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] border-b border-blue-50 dark:border-blue-900/30 pb-2">Employment Profile</p>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Branch / Outlet</label>
                                    <select name="branch_id" x-model="newEmployee.branch_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 uppercase tracking-widest">
                                        <option value="">All Branches</option>
                                        <template x-for="branch in branches" :key="branch.id">
                                            <option :value="branch.id" x-text="branch.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Designated Role</label>
                                    <input type="text" name="role" x-model="newEmployee.role" placeholder="e.g. Sales Executive" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Base Wage (Monthly)</label>
                                    <input type="number" step="0.01" name="salary" x-model="newEmployee.salary" placeholder="₹ Amount" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Joining Date</label>
                                    <input type="date" name="joining_date" x-model="newEmployee.joining_date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl">
                                <div>
                                    <p class="text-[10px] font-black text-emerald-900 dark:text-emerald-200 uppercase tracking-tight">Active Duty Status</p>
                                    <p class="text-[8px] font-bold text-emerald-400 uppercase tracking-widest">Enable or disable roster listing</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" x-model="newEmployee.is_active" class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex items-center gap-4 pt-6 border-t border-slate-50 dark:border-slate-800">
                        <button type="button" @click="showModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" class="flex-[2] px-8 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 dark:shadow-none" x-text="editingEmployee ? 'Update Profile details' : 'Register Employee'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
