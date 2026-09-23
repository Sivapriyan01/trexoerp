@extends('layouts.tenant')
@section('title', 'Customer Memberships')
@section('page-title', 'Memberships')

@section('content')
<div class="space-y-6 md:space-y-8" x-data="{ showAssignModal: false }">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Customer Memberships</h1>
            <p class="text-sm text-slate-500 mt-1">Manage active subscriptions and assign new memberships.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.membership.plans') }}" class="w-full md:w-auto bg-slate-100 text-slate-600 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-slate-100 hover:scale-[1.02] transition">
                Manage Plans
            </a>
            <button @click="showAssignModal = true" class="w-full md:w-auto bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-blue-100 hover:scale-[1.02] transition">
                + Assign Membership
            </button>
        </div>
    </div>

    {{-- Memberships Table --}}
    <div class="glass-card rounded-[2rem] overflow-hidden">
        <div class="p-5 md:p-6 border-b border-slate-100 bg-white/50 gap-4">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Active Subscriptions</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Plan</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Duration</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($memberships as $membership)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-900">{{ $membership->customer->name ?? 'Unknown' }}</p>
                                <p class="text-[10px] font-bold text-slate-400">{{ $membership->customer->phone ?? 'No Phone' }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-bold text-slate-700">{{ $membership->plan->name ?? 'Legacy Plan' }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-xs font-black text-slate-700">{{ $membership->start_date->format('d M, Y') }} - {{ $membership->end_date->format('d M, Y') }}</p>
                                @if($membership->end_date->isPast())
                                    <p class="text-[9px] font-bold text-rose-500">Expired {{ $membership->end_date->diffForHumans() }}</p>
                                @else
                                    <p class="text-[9px] font-bold text-emerald-500">Expires {{ $membership->end_date->diffForHumans() }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                @if($membership->status === 'active' && !$membership->end_date->isPast())
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Active</span>
                                @elseif($membership->status === 'cancelled')
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancelled</span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-100 text-rose-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Expired</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <form action="{{ route('tenant.membership.update', $membership) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="payment_status" value="{{ $membership->payment_status }}">
                                    <input type="hidden" name="end_date" value="{{ $membership->end_date->format('Y-m-d') }}">
                                    @if($membership->status === 'active')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline" onclick="return confirm('Cancel this membership?')">Cancel</button>
                                    @else
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:underline">Reactivate</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center text-slate-400 italic">No memberships assigned yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $memberships->links() }}
        </div>
    </div>

    {{-- Assign Modal --}}
    <div x-show="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showAssignModal = false" class="bg-white w-full max-w-lg rounded-[2rem] p-8 shadow-2xl relative">
            <button @click="showAssignModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h3 class="text-xl font-black text-slate-900 tracking-tight mb-6">Assign Membership</h3>
            
            <form action="{{ route('tenant.membership.assign') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer</label>
                    <select name="customer_id" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 outline-none" required>
                        <option value="">Select a Customer...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Membership Plan</label>
                    <select name="membership_plan_id" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 outline-none" required>
                        <option value="">Select a Plan...</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} - ₹{{ $plan->price }} ({{ $plan->duration_days }} days)</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 outline-none" required>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition">Assign Membership</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
