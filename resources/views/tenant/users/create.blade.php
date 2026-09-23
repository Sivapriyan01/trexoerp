@extends('layouts.tenant')
@section('title', 'Add New Staff')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="glass-card rounded-[2.5rem] p-10">
        <div class="mb-10">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Staff Information</h3>
            <p class="text-slate-500 font-medium">Create a new account for your team member.</p>
        </div>

        <form action="{{ route('tenant.users.store') }}" method="POST" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Name --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe" 
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com" 
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>

                {{-- Role --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Access Role</label>
                    <select name="role" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all appearance-none">
                        <option value="admin">Administrator</option>
                        <option value="staff" selected>Staff / Cashier</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>

                {{-- Phone --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 00000 00000" 
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" 
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 flex gap-4">
                <a href="{{ route('tenant.users.index') }}" class="flex-1 px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-sm uppercase tracking-widest text-center hover:bg-slate-200 transition">
                    Cancel
                </a>
                <button type="submit" class="flex-[2] px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">
                    Create Staff Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

