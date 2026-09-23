@extends('layouts.tenant')
@section('title', 'Membership Plans')
@section('page-title', 'Membership Plans')

@section('content')
<div class="space-y-6 md:space-y-8" x-data="{ showModal: false, editMode: false, currentPlan: null }">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Membership Plans</h1>
            <p class="text-sm text-slate-500 mt-1">Manage the tiers and packages available to customers.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.membership.index') }}" class="w-full md:w-auto bg-slate-100 text-slate-600 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-slate-100 hover:scale-[1.02] transition">
                Back to Memberships
            </a>
            <button @click="showModal = true; editMode = false; currentPlan = null" class="w-full md:w-auto bg-purple-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center shadow-xl shadow-purple-100 hover:scale-[1.02] transition">
                + Add Plan
            </button>
        </div>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="glass-card p-6 rounded-[2rem] relative flex flex-col {{ !$plan->is_active ? 'opacity-70' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    @if(!$plan->is_active)
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-widest">Inactive</span>
                    @endif
                </div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $plan->name }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $plan->duration_days }} Days</p>
                <div class="mt-4 mb-6">
                    <span class="text-3xl font-black text-slate-900">₹{{ number_format($plan->price, 0) }}</span>
                </div>
                <p class="text-sm text-slate-600 flex-grow">{{ $plan->description ?: 'No description provided.' }}</p>
                
                <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <button @click="editMode = true; currentPlan = {{ json_encode($plan) }}; showModal = true" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline">Edit</button>
                    <form action="{{ route('tenant.membership.plans.destroy', $plan) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline" onclick="return confirm('Delete this plan?')">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card p-12 text-center rounded-[2rem]">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl mx-auto flex items-center justify-center mb-4">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-1">No Plans Found</h3>
                <p class="text-sm text-slate-500">Create your first membership plan to get started.</p>
            </div>
        @endforelse
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showModal = false" class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2rem] p-8 shadow-2xl relative">
            <button @click="showModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h3 class="text-xl font-black text-slate-900 tracking-tight mb-6" x-text="editMode ? 'Edit Plan' : 'Create Plan'"></h3>
            
            <form :action="editMode ? '{{ route('tenant.membership.plans.store') }}/' + currentPlan.id : '{{ route('tenant.membership.plans.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Plan Name</label>
                    <input type="text" name="name" :value="editMode ? currentPlan.name : ''" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-100 outline-none" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price (₹)</label>
                        <input type="number" step="0.01" name="price" :value="editMode ? currentPlan.price : ''" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-100 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Duration (Days)</label>
                        <input type="number" name="duration_days" :value="editMode ? currentPlan.duration_days : '365'" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-100 outline-none" required>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Discount Percent (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount_percent" :value="editMode ? currentPlan.discount_percent : '0'" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-100 outline-none" required>
                        <p class="text-[9px] text-slate-400 mt-1">This discount will be automatically applied to the customer's bill.</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-100 outline-none" x-text="editMode ? currentPlan.description : ''"></textarea>
                </div>

                <div class="flex items-center gap-2 mt-4">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded text-purple-600 focus:ring-purple-500" :checked="!editMode || currentPlan.is_active">
                    <label for="is_active" class="text-sm font-bold text-slate-700">Plan is Active</label>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-purple-600 text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-purple-700 transition">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
