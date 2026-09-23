@extends('layouts.tenant')
@section('title', 'Create New Job')
@section('page-title', 'Create New Job')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedProcesses: [],
    availableProcesses: {{ json_encode($availableProcesses) }},
    showProcessModal: false,
    newProcessName: '',
    newProcessType: 'In House',
    toggleProcess(process) {
        if (this.selectedProcesses.includes(process)) {
            this.selectedProcesses = this.selectedProcesses.filter(p => p !== process);
        } else {
            this.selectedProcesses.push(process);
        }
    },
    async createProcess() {
        if (!this.newProcessName) return;
        try {
            const response = await fetch('{{ route('tenant.production.process.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.newProcessName,
                    type: this.newProcessType
                })
            });
            const data = await response.json();
            this.availableProcesses.push(data);
            this.showProcessModal = false;
            this.newProcessName = '';
        } catch (e) {
            alert('Failed to create process');
        }
    },
    async deleteProcess(id) {
        if (!confirm('Are you sure you want to delete this process?')) return;
        try {
            await fetch(`/production/process/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            this.availableProcesses = this.availableProcesses.filter(p => p.id !== id);
            this.selectedProcesses = this.selectedProcesses.filter(p => p !== this.availableProcesses.find(ap => ap.id === id)?.name);
        } catch (e) {
            alert('Failed to delete process');
        }
    },
    onProductChange(id) {
        const product = {{ json_encode($products) }}.find(p => p.id == id);
        if (product && product.default_processes) {
            this.selectedProcesses = [...product.default_processes];
        } else {
            this.selectedProcesses = [];
        }
    }
}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Manufacturing</h3>
            <p class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Create New Job</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showProcessModal = true" class="w-full md:w-auto px-6 py-3 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:scale-[1.02] active:scale-95 transition-all">
                + Add Process
            </button>
        </div>
    </div>

    <form action="{{ route('tenant.production.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT COLUMN: INFO -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer & Order Information -->
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl border border-white/20">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Customer & Order Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Customer Selection *</label>
                            <select name="customer_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Order Date *</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Deadline Date *</label>
                            <input type="date" name="deadline_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl border border-white/20">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Product Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Product Name *</label>
                            <select name="category_id" @change="onProductChange($event.target.value)" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Quantity *</label>
                            <div class="flex items-stretch gap-2">
                                <input type="number" name="total_qty" value="1" min="1"
                                    style="-webkit-appearance:textfield; -moz-appearance:textfield; appearance:textfield;"
                                    class="flex-1 min-w-0 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all text-center">
                                <select name="qty_unit"
                                    class="shrink-0 bg-blue-600 text-white rounded-2xl px-4 py-4 text-xs font-black focus:ring-2 focus:ring-blue-400 cursor-pointer border-none">
                                    <option value="KG">KG</option>
                                    <option value="TON">TON</option>
                                    <option value="LITRE">LITRE</option>
                                    <option value="PCS">PCS</option>
                                </select>
                                <input type="number" name="weight_per_pc" value="40" step="0.01" min="0.01" placeholder="KG/PC" id="weight_per_pc_input" style="display: none;"
                                    class="w-20 bg-slate-100 dark:bg-slate-700 border-none rounded-2xl px-3 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all text-center" title="Weight per Piece in KG">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Priority</label>
                            <select name="priority" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                                <option>Normal</option>
                                <option>Urgent</option>
                                <option>Low</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Estimated Value (₹)</label>
                            <input type="number" name="estimated_value" value="0" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Specifications & Additional Notes</label>
                            <textarea name="specifications" rows="1" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all outline-none" placeholder="Any special instructions..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Product Cost Breakdown -->
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl border border-white/20">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Product Cost Breakdown</h4>
                        </div>
                        @if(isset($costPeriodInfo))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400">
                                {{ $costPeriodInfo }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Electricity -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-yellow-500">⚡</span> Electricity (₹)
                            </label>
                            <input type="number" name="cost_electricity" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-yellow-400 transition-all">
                        </div>

                        <!-- Water Bill -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-blue-500">💧</span> Water Bill (₹)
                            </label>
                            <input type="number" name="cost_water_bill" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-400 transition-all">
                        </div>

                        <!-- Raw Material -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-emerald-500">📦</span> Raw Material (₹)
                            </label>
                            <input type="number" name="cost_raw_material" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-400 transition-all">
                        </div>

                        <!-- Labour Charge -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-orange-500">👷</span> Labour Charge (₹)
                            </label>
                            <input type="number" name="cost_labour" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-400 transition-all">
                        </div>

                        <!-- Machine Maintenance -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-gray-500">⚙️</span> Machine Maint. (₹)
                            </label>
                            <input type="number" name="cost_machine_maintenance" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-gray-400 transition-all">
                        </div>

                        <!-- Packing Cost -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-amber-500">🛍️</span> Packing Cost (₹)
                            </label>
                            <input type="number" name="cost_packing" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-400 transition-all">
                        </div>

                        <!-- Transport / Loading -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-indigo-500">🚚</span> Transport (₹)
                            </label>
                            <input type="number" name="cost_transport" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-400 transition-all">
                        </div>

                        <!-- Wastage Cost -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-rose-500">🗑️</span> Wastage Cost (₹)
                            </label>
                            <input type="number" name="cost_wastage" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-400 transition-all">
                        </div>

                        <!-- Other Expenses -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-pink-500">📋</span> Other Expenses (₹)
                            </label>
                            <input type="number" name="cost_other" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-pink-400 transition-all">
                        </div>

                        <!-- Welfare -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                                <span class="text-teal-500">🏥</span> Welfare (₹)
                            </label>
                            <input type="number" name="cost_welfare" step="0.01" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-400 transition-all">
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Notes (optional)</label>
                            <input type="text" name="cost_notes" placeholder="Any cost remarks for this job..."
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-violet-500 transition-all">
                        </div>
                    </div>

                    <!-- Total Cost Preview -->
                    <div class="mt-6 flex items-center justify-between px-5 py-4 bg-violet-50 dark:bg-violet-900/20 rounded-2xl">
                        <span class="text-[10px] font-black text-violet-600 uppercase tracking-widest">Total Production Cost</span>
                        <span id="costTotal" class="text-lg font-black text-violet-700 dark:text-violet-400">₹0.00</span>
                    </div>
                    <script>
                    (function(){
                        const electricityRate = {{ $electricityRate ?? 0 }};
                        const waterRate = {{ $waterRate ?? 0 }};
                        const rawMaterialRate = {{ $rawMaterialRate ?? 0 }};
                        const labourRate = {{ $labourRate ?? 0 }};
                        const machineRate = {{ $machineRate ?? 0 }};
                        const packingRate = {{ $packingRate ?? 0 }};
                        const transportRate = {{ $transportRate ?? 0 }};
                        const wastageRate = {{ $wastageRate ?? 0 }};
                        const otherRate = {{ $otherRate ?? 0 }};
                        const welfareRate = {{ $welfareRate ?? 0 }};

                        function recalc(){
                            var e = parseFloat(document.querySelector('[name=cost_electricity]').value)||0;
                            var w = parseFloat(document.querySelector('[name=cost_water_bill]').value)||0;
                            var r = parseFloat(document.querySelector('[name=cost_raw_material]').value)||0;
                            var l = parseFloat(document.querySelector('[name=cost_labour]').value)||0;
                            var mm = parseFloat(document.querySelector('[name=cost_machine_maintenance]').value)||0;
                            var pc = parseFloat(document.querySelector('[name=cost_packing]').value)||0;
                            var tr = parseFloat(document.querySelector('[name=cost_transport]').value)||0;
                            var ws = parseFloat(document.querySelector('[name=cost_wastage]').value)||0;
                            var ot = parseFloat(document.querySelector('[name=cost_other]').value)||0;
                            var wl = parseFloat(document.querySelector('[name=cost_welfare]').value)||0;
                            
                            var total = e + w + r + l + mm + pc + tr + ws + ot + wl;
                            document.getElementById('costTotal').textContent = '₹' + total.toLocaleString('en-IN',{minimumFractionDigits:2});
                        }

                        function autoCalculateCosts() {
                            var qtyInput = document.querySelector('[name=total_qty]');
                            var unitSelect = document.querySelector('[name=qty_unit]');
                            if (!qtyInput || !unitSelect) return;

                            var qty = parseFloat(qtyInput.value) || 0;
                            var unit = unitSelect.value;
                            
                            // Handle dynamic weight per pc input visibility
                            var weightPcInput = document.getElementById('weight_per_pc_input');
                            if (weightPcInput) {
                                weightPcInput.style.display = (unit === 'PCS') ? 'block' : 'none';
                            }

                            // Convert quantity to KG
                            var qtyInKg = qty;
                            if (unit === 'TON') {
                                qtyInKg = qty * 1000;
                            } else if (unit === 'PCS') {
                                var weightPerPc = parseFloat(weightPcInput ? weightPcInput.value : 40) || 40;
                                qtyInKg = qty * weightPerPc;
                            } else if (unit === 'LITRE') {
                                qtyInKg = qty;
                            }
                            
                            // Update input fields
                            document.querySelector('[name=cost_electricity]').value = (qtyInKg * electricityRate).toFixed(2);
                            document.querySelector('[name=cost_water_bill]').value = (qtyInKg * waterRate).toFixed(2);
                            document.querySelector('[name=cost_raw_material]').value = (qtyInKg * rawMaterialRate).toFixed(2);
                            document.querySelector('[name=cost_labour]').value = (qtyInKg * labourRate).toFixed(2);
                            document.querySelector('[name=cost_machine_maintenance]').value = (qtyInKg * machineRate).toFixed(2);
                            document.querySelector('[name=cost_packing]').value = (qtyInKg * packingRate).toFixed(2);
                            document.querySelector('[name=cost_transport]').value = (qtyInKg * transportRate).toFixed(2);
                            document.querySelector('[name=cost_wastage]').value = (qtyInKg * wastageRate).toFixed(2);
                            document.querySelector('[name=cost_other]').value = (qtyInKg * otherRate).toFixed(2);
                            document.querySelector('[name=cost_welfare]').value = (qtyInKg * welfareRate).toFixed(2);
                            
                            recalc();
                        }

                        document.addEventListener('DOMContentLoaded', function(){
                            // Listen to manual cost edits to recalculate the total cost
                            ['cost_electricity','cost_water_bill','cost_raw_material','cost_labour',
                             'cost_machine_maintenance','cost_packing','cost_transport','cost_wastage','cost_other','cost_welfare'].forEach(function(n){
                                var el = document.querySelector('[name='+n+']');
                                if(el) el.addEventListener('input', recalc);
                            });

                            // Listen to quantity and unit changes to auto-calculate costs
                            var qtyInput = document.querySelector('[name=total_qty]');
                            var unitSelect = document.querySelector('[name=qty_unit]');
                            
                            if (qtyInput) qtyInput.addEventListener('input', autoCalculateCosts);
                            if (unitSelect) unitSelect.addEventListener('change', autoCalculateCosts);
                            
                            var weightPcInput = document.getElementById('weight_per_pc_input');
                            if (weightPcInput) weightPcInput.addEventListener('input', autoCalculateCosts);
                            
                            // Initial calculation
                            autoCalculateCosts();
                        });
                    })();
                    </script>
                </div>
            </div>

            <!-- RIGHT COLUMN: PROCESSES -->
            <div class="space-y-6">
                <div class="glass-card rounded-[2.5rem] p-8 bg-white dark:bg-slate-900 shadow-xl border border-white/20">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Process Selection</h4>
                        </div>
                        <span class="text-[10px] font-black text-slate-400" x-text="selectedProcesses.length + ' of ' + availableProcesses.length"></span>
                    </div>

                    <div class="space-y-3 mb-8">
                        <template x-for="(proc, index) in availableProcesses" :key="proc.id">
    <div 
        @click="toggleProcess(proc.name)"
        :class="selectedProcesses.includes(proc.name) ? 'bg-blue-600 text-white border-transparent' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-transparent'"
        class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer hover:scale-[1.02] transition-all group shadow-sm">
        <div class="flex items-center gap-3">
            <div 
                :class="selectedProcesses.includes(proc.name) ? 'bg-white text-blue-600' : 'bg-white dark:bg-slate-700 text-slate-400'"
                class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black shadow-inner"
                x-text="selectedProcesses.indexOf(proc.name) !== -1 ? selectedProcesses.indexOf(proc.name) + 1 : '?'">
            </div>
            <span class="text-xs font-black uppercase tracking-tight" x-text="proc.name"></span>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-[8px] font-black uppercase tracking-tighter" x-text="proc.type"></span>
            </div>
            <button @click.stop="deleteProcess(proc.id)" 
                    class="p-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all opacity-0 group-hover:opacity-100">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
        <input type="checkbox" name="processes[]" :value="proc.name" x-model="selectedProcesses" class="hidden">
    </div>
</template>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="selectedProcesses.length === 0"
                        :class="selectedProcesses.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] active:scale-95'"
                        class="w-full py-5 bg-blue-600 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-blue-100 transition-all">
                        Ready to Create Job
                    </button>
                </div>
            </div>
        </div>
    </form>
    {{-- Add Process Modal --}}
    <div x-show="showProcessModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 dark:border-slate-800" @click.away="showProcessModal = false">
            <div class="p-8 border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">New Production Process</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Add Manufacturing Stage</p>
                    </div>
                </div>
                <button @click="showProcessModal = false" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-colors text-slate-400">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Process Name *</label>
                    <input type="text" x-model="newProcessName" placeholder="e.g. Laser Cutting, Quality Check..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-black dark:text-white focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Process Type</label>
                    <select x-model="newProcessType" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-5 py-4 text-xs font-black text-black dark:text-white focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                        <option>In House</option>
                        <option>Outsourced</option>
                        <option>Machine Automated</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="button" @click="showProcessModal = false" class="flex-1 px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                    <button type="button" @click="createProcess()" class="flex-1 px-8 py-4 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-100 dark:shadow-none transition-all">Create Process</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

