@extends('layouts.tenant')
@section('title', 'Add Customer')
@section('page-title', 'New Customer Profile')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-card rounded-[2rem] overflow-hidden shadow-2xl shadow-blue-50">
        <div class="p-8 bg-gradient-to-r from-blue-600 to-blue-700 text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black tracking-tight">Customer Registration</h3>
                <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest">Create a new loyalty profile</p>
            </div>
            <a href="{{ route('tenant.customers.index') }}" class="p-2 bg-white/10 hover:bg-white/20 rounded-xl transition">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>

        <form action="{{ route('tenant.customers.store') }}" method="POST" class="p-8 space-y-6 bg-white/50" x-data="{ 
            form: {
                name: @js(old('name', '')),
                phone: @js(old('phone', '')),
                email: @js(old('email', '')),
                gstin: @js(old('gstin', '')),
                address: @js(old('address', '')),
                city: @js(old('city', '')),
                state: @js(old('state', '')),
                pincode: @js(old('pincode', '')),
                anniversary_date: @js(old('anniversary_date', ''))
            },
            isValidating: false, 
            isVerified: false,
            errorMsg: '',
            async verifyGstin() {
                if (!this.form.gstin || this.form.gstin.trim().length !== 15) return;
                
                this.isValidating = true;
                this.isVerified = false;
                this.errorMsg = '';
                
                try {
                    const response = await fetch('{{ route('tenant.gst.validate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                        },
                        body: JSON.stringify({ gstin: this.form.gstin.trim() })
                    });

                    if (!response.ok) {
                        const err = await response.json().catch(() => ({}));
                        this.errorMsg = 'Error ' + response.status + ': ' + (err.message || 'Server Error');
                        return;
                    }

                    const result = await response.json();
                    if (result.success && result.data) {
                        this.isVerified = true;
                        this.form.name = result.data.legal_name || this.form.name;
                        this.form.address = result.data.address || this.form.address;
                        this.form.city = result.data.city || this.form.city;
                        this.form.state = result.data.state || this.form.state;
                        this.form.pincode = result.data.pincode || this.form.pincode;
                    } else {
                        this.errorMsg = result.message || 'Not found';
                    }
                } catch (e) { 
                    this.errorMsg = 'Connection Error';
                } finally { 
                    this.isValidating = false; 
                }
            }
        }">
            @csrf
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Full Name *</label>
                        <input type="text" name="name" required x-model="form.name"
                               class="w-full px-4 py-3 bg-white border {{ $errors->has('name') ? 'border-rose-300 ring-4 ring-rose-50' : 'border-slate-200 focus:ring-4 focus:ring-blue-50' }} rounded-xl text-sm font-bold outline-none transition-all"
                               placeholder="e.g. John Doe">
                        @error('name') <p class="text-[10px] text-rose-600 font-bold px-1 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Mobile Number *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400 font-bold text-xs">+91</span>
                            <input type="text" name="phone" required x-model="form.phone"
                                   class="w-full pl-12 pr-4 py-3 bg-white border {{ $errors->has('phone') ? 'border-rose-300 ring-4 ring-rose-50' : 'border-slate-200 focus:ring-4 focus:ring-blue-50' }} rounded-xl text-sm font-bold outline-none transition-all"
                                   placeholder="9876543210">
                        </div>
                        @error('phone') <p class="text-[10px] text-rose-600 font-bold px-1 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between px-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase">GSTIN (Optional)</label>
                        <div class="flex items-center gap-2">
                            <template x-if="isVerified">
                                <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-1">
                                    <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            </template>
                            <span x-show="isValidating" class="text-[8px] font-black text-blue-500 animate-pulse uppercase tracking-widest">Fetching...</span>
                            <span x-show="errorMsg" x-text="errorMsg" class="text-[8px] font-black text-rose-500 uppercase tracking-widest"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="gstin" x-model="form.gstin" 
                                   @input="if(form.gstin.trim().length === 15) verifyGstin()"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all uppercase"
                                   placeholder="e.g. 22AAAAA0000A1Z5">
                            <div class="absolute right-3 top-2.5">
                                <svg x-show="isValidating" class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                        </div>
                        <button type="button" @click="verifyGstin()" 
                                :disabled="isValidating"
                                class="px-4 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all disabled:opacity-50">
                            Verify
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-500 uppercase px-1">Address / Location</label>
                    <textarea name="address" rows="2" x-model="form.address"
                              class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all"
                              placeholder="House No, Street, City..."></textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">City</label>
                        <input type="text" name="city" x-model="form.city"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">State</label>
                        <input type="text" name="state" x-model="form.state"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Pincode</label>
                        <input type="text" name="pincode" x-model="form.pincode"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-blue-600 uppercase px-1">Anniversary Date</label>
                        <input type="date" name="anniversary_date" x-model="form.anniversary_date"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="anniversary_reminder_enabled" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Send Reminders</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="history.back()" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="flex-[2] py-3 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection

