@extends('layouts.tenant')
@section('title', 'Edit Vendor')
@section('page-title', 'Update Vendor Details')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="glass-card rounded-[2rem] overflow-hidden shadow-2xl shadow-teal-50">
        <div class="p-8 bg-gradient-to-r from-teal-600 to-emerald-700 text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black tracking-tight">Edit Vendor</h3>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-widest">Update profile for {{ $supplier->name }}</p>
            </div>
            <a href="{{ route('tenant.suppliers.index') }}" class="p-2 bg-white/10 hover:bg-white/20 rounded-xl transition">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>

        <form action="{{ route('tenant.suppliers.update', $supplier) }}" method="POST" class="p-8 space-y-6 bg-white/50">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ 
                gstin: '{{ old('gstin', $supplier->gstin) }}', 
                isValidating: false, 
                isVerified: !!'{{ $supplier->gstin }}',
                async verifyGstin() {
                    if (this.gstin.length !== 15) return;
                    this.isValidating = true;
                    try {
                        const response = await fetch('/billing/gst/validate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ gstin: this.gstin })
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.isVerified = true;
                            if (result.data.legal_name) document.querySelector('input[name=&quot;name&quot;]').value = result.data.legal_name;
                            if (result.data.address) document.querySelector('textarea[name=&quot;address&quot;]').value = result.data.address;
                            if (result.data.city) document.querySelector('input[name=&quot;city&quot;]').value = result.data.city;
                            if (result.data.state) document.querySelector('input[name=&quot;state&quot;]').value = result.data.state;
                            if (result.data.pincode) document.querySelector('input[name=&quot;pincode&quot;]').value = result.data.pincode;
                        }
                    } catch (e) { console.error(e); }
                    finally { this.isValidating = false; }
                }
            }">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between px-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase">GSTIN Number</label>
                            <template x-if="isVerified">
                                <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-1">
                                    <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            </template>
                        </div>
                        <div class="relative">
                            <input type="text" name="gstin" x-model="gstin" @input="if(gstin.length === 15) verifyGstin()"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all uppercase"
                                   placeholder="27AAAAA0000A1Z5">
                            <div class="absolute right-3 top-2.5">
                                <svg x-show="isValidating" class="w-5 h-5 text-teal-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                        </div>
                        @error('gstin') <p class="text-[10px] text-rose-500 font-bold px-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Business Name *</label>
                        <input type="text" name="name" required value="{{ old('name', $supplier->name) }}"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="e.g. Acme Wholesale Ltd">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="9876543210">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                               placeholder="vendor@example.com">
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">Office Address</label>
                        <textarea name="address" rows="2" 
                                  class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all"
                                  placeholder="Full registered address...">{{ old('address', $supplier->address) }}</textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-500 uppercase px-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                               class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase px-1">State</label>
                            <input type="text" name="state" value="{{ old('state', $supplier->state) }}"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase px-1">Pincode</label>
                            <input type="text" name="pincode" value="{{ old('pincode', $supplier->pincode) }}"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold focus:ring-4 focus:ring-teal-50 outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }} class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Active Vendor</label>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="history.back()" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="flex-[2] py-3 bg-teal-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-teal-100 hover:scale-[1.02] active:scale-95 transition-all">Update Vendor</button>
            </div>
        </form>
    </div>
</div>
@endsection

