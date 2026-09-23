@extends('layouts.tenant')
@section('title', 'Add Vendor')
@section('page-title', 'New Supplier Onboarding')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="glass-card rounded-[2rem] overflow-hidden shadow-2xl shadow-teal-50">
        <div class="p-8 bg-gradient-to-r from-teal-600 to-emerald-700 text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black tracking-tight">Vendor Registration</h3>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-widest">Add a new supplier to the system</p>
            </div>
            <a href="{{ route('tenant.suppliers.index') }}" class="p-2 bg-white/10 hover:bg-white/20 rounded-xl transition">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>

        <form action="{{ route('tenant.suppliers.store') }}" method="POST" class="p-8 space-y-6 bg-white/50" x-data="{ 
            form: {
                name: @js(old('name', '')),
                phone: @js(old('phone', '')),
                email: @js(old('email', '')),
                gstin: @js(old('gstin', '')),
                address: @js(old('address', '')),
                city: @js(old('city', '')),
                state: @js(old('state', '')),
                pincode: @js(old('pincode', ''))
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between px-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase">GSTIN Number</label>
                            <div class="flex items-center gap-2">
                                <template x-if="isVerified">
                                    <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-1">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Verified
                                    </span>
                                </template>
                                <span x-show="isValidating" class="text-[8px] font-black text-teal-500 animate-pulse uppercase tracking-widest">Fetching...</span>
                                <span x-show="errorMsg" x-text="errorMsg" class="text-[8px] font-black text-rose-500 uppercase tracking-widest"></span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="gstin" x-model="form.gstin" 
                                       @input="if(form.gstin.trim().length === 15) verifyGstin()"
                                       class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all uppercase"
                                       placeholder="27AAAAA0000A1Z5">
                                <div class="absolute right-3 top-2.5">
                                    <svg x-show="isValidating" class="w-5 h-5 text-teal-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </div>
                            </div>
                            <button type="button" @click="verifyGstin()" 
                                    :disabled="isValidating"
                                    class="px-4 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all disabled:opacity-50">
                                Verify
                            </button>
                        </div>
                        @error('gstin') <p class="text-[10px] text-rose-500 font-bold px-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Business Name *</label>
                        <input type="text" name="name" required x-model="form.name"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="e.g. Acme Wholesale Ltd">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Contact Phone</label>
                        <input type="text" name="phone" x-model="form.phone"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="9876543210">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Email Address</label>
                        <input type="email" name="email" x-model="form.email"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="vendor@example.com">
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Office Address</label>
                        <textarea name="address" rows="2" x-model="form.address"
                                  class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                                  placeholder="Full registered address..."></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">City</label>
                        <input type="text" name="city" x-model="form.city"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase px-1">State</label>
                            <input type="text" name="state" x-model="form.state"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase px-1">Pincode</label>
                            <input type="text" name="pincode" x-model="form.pincode"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                        </div>
                    </div>
                </div>

            <div class="pt-6 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="history.back()" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="flex-[2] py-3 bg-teal-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-teal-100 hover:scale-[1.02] active:scale-95 transition-all">Save Vendor</button>
            </div>
        </form>
    </div>
</div>
@endsection

