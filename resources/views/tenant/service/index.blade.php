@extends('layouts.tenant')
@section('title', 'Service Management')
@section('page-title', 'Service Claims')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
    /* Modal styles */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); 
        display: none; align-items: center; justify-content: center; z-index: 50; backdrop-filter: blur(4px);
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
        background: #fff; width: 100%; max-width: 500px; padding: 24px;
        border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        max-height: 90vh; overflow-y: auto;
    }
    .dark .modal-content { background: #1e293b; color: #f8fafc; border: 1px solid #334155; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; tracking-wider: 0.05em; }
    .dark .form-group label { color: #94a3b8; }
    .form-control {
        width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px;
        font-size: 13px; outline: none; transition: border-color 0.2s;
    }
    .dark .form-control { background: #0f172a; border-color: #334155; color: #f8fafc; }
    .form-control:focus { border-color: #3b82f6; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-title { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; }
    .dark .close-btn { color: #cbd5e1; }
    .close-btn:hover { color: #ef4444; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="serviceDashboard()">
    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Service & Warranty Claims</h3>
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Manage and track customer service claims</p>
        </div>
        <div>
            <button @click="openModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider transition-all duration-300 shadow-lg shadow-blue-500/20">
                <i class="ti ti-plus text-sm"></i> New Service Claim
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-[1.5rem] border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Claims</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="ti ti-checklist text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[1.5rem] border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Resolved Claims</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['resolved'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i class="ti ti-circle-check text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[1.5rem] border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">In Progress</p>
                    <h3 class="text-2xl font-black text-amber-500 mt-1">{{ $stats['in_progress'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center text-amber-500 dark:text-amber-400">
                    <i class="ti ti-clock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-[1.5rem] border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Resolution Rate</p>
                    <h3 class="text-2xl font-black text-purple-600 mt-1">{{ $stats['rate'] }}%</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i class="ti ti-chart-pie text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Toolbar & Table Card --}}
    <div class="glass-card rounded-[1.5rem] overflow-hidden flex flex-col border border-slate-100 dark:border-slate-800">
        
        {{-- Toolbar --}}
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
            <form action="{{ route('tenant.service.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
                <div class="relative w-full max-w-md">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by Claim ID, Product, Customer..." 
                           class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2 text-[11px] font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                </div>

                <div class="w-full sm:w-48">
                    <select name="status" onchange="this.form.submit()"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-[11px] font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="In Progress" {{ $status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Pending Parts" {{ $status === 'Pending Parts' ? 'selected' : '' }}>Pending Parts</option>
                        <option value="Resolved" {{ $status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full whitespace-nowrap text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">CLAIM ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">DATE</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">CUSTOMER</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">PRODUCT</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ISSUE</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">STATUS</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">RES. TIME</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse ($services as $service)
                        <tr class="hover:bg-blue-50/20 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 text-[11px] font-black text-blue-600 dark:text-blue-400">{{ $service->claim_id }}</td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($service->claim_date)->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">
                                <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase">{{ $service->customer_name }}</div>
                                <div class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $service->customer_phone ?: 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase">{{ $service->product_name }}</td>
                            <td class="px-6 py-4 text-[11px] font-medium text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $service->issue_description }}">
                                {{ $service->issue_description }}
                            </td>
                            <td class="px-6 py-4">
                                <select @change="updateStatus({{ $service->id }}, $event.target.value)"
                                        class="bg-transparent border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer shadow-sm
                                        {{ $service->status === 'Resolved' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200' : '' }}
                                        {{ $service->status === 'In Progress' ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-blue-200' : '' }}
                                        {{ $service->status === 'Pending Parts' ? 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 border-amber-200' : '' }}
                                        {{ $service->status === 'Rejected' ? 'text-rose-600 bg-rose-50 dark:bg-rose-900/20 border-rose-200' : '' }}
                                        text-slate-600 dark:text-slate-300">
                                    <option value="In Progress" {{ $service->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Pending Parts" {{ $service->status === 'Pending Parts' ? 'selected' : '' }}>Pending Parts</option>
                                    <option value="Resolved" {{ $service->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="Rejected" {{ $service->status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $service->resolution_time ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('tenant.service.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this claim?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-600 transition-colors" title="Delete">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i class="ti ti-clipboard-x text-3xl opacity-50 mb-3"></i>
                                    <p class="text-xs font-black uppercase tracking-widest">No service claims found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $services->links() }}
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal-overlay" id="claimModal">
    <div class="modal-content custom-scrollbar">
        <div class="modal-header">
            <div class="modal-title">Create New Service Claim</div>
            <button class="close-btn" @click="closeModal()"><i class="ti ti-x"></i></button>
        </div>
        <form action="{{ route('tenant.service.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Claim ID (Optional - Auto-generated if left blank)</label>
                <input type="text" name="claim_id" class="form-control" placeholder="e.g. CLM-1024">
            </div>
            <div class="form-group">
                <label>Claim Date</label>
                <input type="date" name="claim_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Customer Phone</label>
                <input type="text" name="customer_phone" class="form-control" placeholder="Phone Number">
            </div>
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="product_name" class="form-control" required placeholder="e.g. Samsung Galaxy S23">
            </div>
            <div class="form-group">
                <label>Issue Description</label>
                <textarea name="issue_description" class="form-control" rows="3" required placeholder="Describe the issue..."></textarea>
            </div>
            <div style="display: flex; gap: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="In Progress">In Progress</option>
                        <option value="Pending Parts">Pending Parts</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Resolution Time</label>
                    <input type="text" name="resolution_time" class="form-control" placeholder="e.g. 3 days">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn border border-slate-200 text-slate-600 rounded-xl px-4 py-2 font-bold text-xs uppercase" @click="closeModal()">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 py-2 font-bold text-xs uppercase shadow-md shadow-blue-500/10">Save Claim</button>
            </div>
        </form>
    </div>
</div>

<script>
function serviceDashboard() {
    return {
        openModal() {
            document.getElementById('claimModal').classList.add('active');
        },
        closeModal() {
            document.getElementById('claimModal').classList.remove('active');
        },
        async updateStatus(id, status) {
            try {
                const response = await fetch(`/services/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                });
                const data = await response.json();
                if (data.success) {
                    // Flash notification if setup globally
                    window.location.reload();
                } else {
                    alert('Failed to update status');
                }
            } catch (e) {
                alert('Error updating status');
            }
        }
    }
}
</script>
@endsection
