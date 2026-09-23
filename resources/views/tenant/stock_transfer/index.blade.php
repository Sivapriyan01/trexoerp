@extends('layouts.tenant')
@section('title', 'Stock Transfer')

@section('content')
<div class="flex flex-col h-[calc(100vh-120px)] -m-6 overflow-hidden bg-slate-50/50 dark:bg-transparent">
    
    {{-- Header: Locations & Date --}}
    <div class="glass-card mx-6 mt-4 mb-2 p-6 rounded-[2.5rem] border-white/40 shadow-sm">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-4 relative">
                <div class="absolute left-4 top-[-8px] bg-white dark:bg-slate-900 px-2 flex items-center justify-between z-10" style="width: calc(100% - 2rem);">
                    <label class="text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">From Branch</label>
                    <button type="button" onclick="openBranchModal()" class="text-[8px] font-black text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 uppercase tracking-widest transition-colors">+ Add New</button>
                </div>
                <select id="from_branch" class="w-full pl-4 pr-10 py-3.5 bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none appearance-none">
                    <option value="">Select From Branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-4 relative">
                <label class="absolute left-4 top-[-8px] bg-white dark:bg-slate-900 px-2 text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest z-10">To Branch</label>
                <select id="to_branch" class="w-full pl-4 pr-10 py-3.5 bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none appearance-none">
                    <option value="">Select To Branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-4 relative">
                <label class="absolute left-4 top-[-8px] bg-white dark:bg-slate-900 px-2 text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest z-10">Transfer Date</label>
                <input type="date" id="transfer_date" value="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none">
            </div>
        </div>
    </div>

    {{-- Product Entry Section --}}
    <div class="px-6 mb-4">
        <div class="glass-card p-4 rounded-[2rem] border-white/40 shadow-sm flex items-center justify-between gap-4">
            <div class="flex-1 flex items-center gap-4">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="scan_input" placeholder="Scan Code (Press Enter)" 
                           class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-black focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none placeholder:text-slate-400 dark:placeholder:text-slate-600"
                           onkeydown="handleScan(event)">
                    <svg width="20" height="20" class="absolute left-4 top-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 17h2a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2a1 1 0 011-1z"/></svg>
                </div>
                <button onclick="openProductSearch()" class="px-6 py-3.5 bg-blue-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-100 dark:shadow-none hover:scale-[1.02] transition-all">
                    Select Products
                </button>
            </div>
            <div class="flex items-center gap-2 px-6 py-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-2xl">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Qty:</span>
                <span id="total_qty_display" class="text-sm font-black text-slate-900 dark:text-white">0</span>
            </div>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="flex-1 px-6 pb-20 overflow-hidden flex flex-col">
        <div class="glass-card flex-1 rounded-[2.5rem] border border-slate-100 dark:border-slate-800/80 shadow-xl overflow-hidden flex flex-col bg-white dark:bg-slate-900/40">
            <div class="overflow-y-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md z-10 border-b border-slate-50 dark:border-slate-800/50">
                        <tr class="border-b border-slate-50 dark:border-slate-800/50">
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Barcode</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Product</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Size</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Brand</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Quantity</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">MRP</th>
                            <th class="px-4 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="transfer_items" class="divide-y divide-slate-50 dark:divide-slate-800/40">
                        {{-- Rows added via JS --}}
                    </tbody>
                </table>
                
                {{-- Empty State --}}
                <div id="empty_prompt" class="h-full flex flex-col items-center justify-center opacity-30 py-20">
                    <svg width="48" height="48" class="mb-4 text-blue-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">No items selected for transfer</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky Footer Actions --}}
    <div class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-slate-100 dark:border-slate-800 p-4 px-6 flex items-center justify-between z-40">
        <div class="flex items-center gap-4">
             <a href="{{ route('tenant.stock-transfer.report') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">F8</span>
                <span class="text-[9px] font-bold text-slate-400">Transfer Report</span>
            </a>
            <a href="{{ route('tenant.stock-transfer.list') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">F9</span>
                <span class="text-[9px] font-bold text-slate-400">All Stock Transfer</span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <button class="px-6 py-2.5 bg-slate-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-600 transition-all">
                Show Invoice
            </button>
            <button onclick="submitTransfer('inward', this)" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all">
                Stock Inward
            </button>
            <button onclick="submitTransfer('outward', this)" class="px-8 py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all">
                Stock Outward
            </button>
        </div>
    </div>
</div>

{{-- Product Search Modal --}}
<div id="search_modal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/50">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest">Select Products</h3>
            <button onclick="closeProductSearch()" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
            <input type="text" id="modal_search_query" placeholder="Search by name, brand, or barcode..." 
                   class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-900 dark:text-slate-100 transition-all outline-none mb-6 placeholder:text-slate-400 dark:placeholder:text-slate-600"
                   oninput="doSearch(this.value)">
            
            <div id="search_results" class="grid grid-cols-2 gap-4">
                {{-- Results --}}
            </div>
        </div>
    </div>
</div>

{{-- Add Branch Modal --}}
<div id="branch_modal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[70] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl w-full max-w-md flex flex-col overflow-hidden">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/50">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest">Add New Branch</h3>
            <button onclick="closeBranchModal()" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 flex flex-col gap-4">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Branch Name <span class="text-rose-500">*</span></label>
                <input type="text" id="new_branch_name" placeholder="e.g. Main Warehouse" 
                       class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200 transition-all outline-none">
            </div>
            <button onclick="submitNewBranch(this)" class="w-full py-3.5 mt-2 bg-blue-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-100 dark:shadow-none hover:bg-blue-700 transition-colors">
                Save Branch
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let cart = [];

    function handleScan(e) {
        if (e.key === 'Enter') {
            const code = e.target.value.trim();
            if (!code) return;
            
            fetch(`{{ route('tenant.billing.products.scan') }}?barcode=${code}`)
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        addToCart(data.product);
                        e.target.value = '';
                    } else {
                        showToast('Product not found', 'error');
                    }
                });
        }
    }

    function openProductSearch() {
        document.getElementById('search_modal').classList.remove('hidden');
        document.getElementById('modal_search_query').focus();
    }

    function closeProductSearch() {
        document.getElementById('search_modal').classList.add('hidden');
    }

    function openBranchModal() {
        document.getElementById('branch_modal').classList.remove('hidden');
        document.getElementById('new_branch_name').focus();
    }

    function closeBranchModal() {
        document.getElementById('branch_modal').classList.add('hidden');
        document.getElementById('new_branch_name').value = '';
    }

    async function submitNewBranch(btn) {
        const name = document.getElementById('new_branch_name').value.trim();
        if (!name) return showToast('Branch name is required', 'error');

        if (btn.disabled) return;
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">Saving...</span>';

        try {
            const res = await fetch(`{{ route('tenant.branches.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name: name })
            });

            const result = await res.json();
            if (result.success) {
                showToast('✅ ' + result.message, 'success');
                
                // Add to selects
                const opt1 = new Option(result.branch.name, result.branch.id);
                const opt2 = new Option(result.branch.name, result.branch.id);
                document.getElementById('from_branch').add(opt1);
                document.getElementById('to_branch').add(opt2);
                
                closeBranchModal();
            } else {
                showToast('❌ ' + (result.message || 'Error saving branch'), 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function doSearch(q) {
        if (q.length < 2) return;
        fetch(`{{ route('tenant.billing.products.search') }}?q=${q}`)
            .then(r => r.json())
            .then(data => {
                const grid = document.getElementById('search_results');
                grid.innerHTML = data.map(p => `
                    <div onclick='handleProductClick(${JSON.stringify(p).replace(/'/g, "&apos;")})' 
                         class="p-4 border border-slate-100 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group">
                        <p class="text-xs font-black text-slate-900 group-hover:text-blue-600 transition-colors">${p.product_name}</p>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-[10px] font-bold text-slate-400">${p.brand || ''} | ${p.size || ''}</span>
                            <span class="text-xs font-black text-blue-600">₹${p.mrp}</span>
                        </div>
                    </div>
                `).join('');
            });
    }

    function handleProductClick(p) {
        addToCart(p);
        closeProductSearch();
    }

    function addToCart(p) {
        const exist = cart.find(i => i.id === p.id);
        if (exist) {
            exist.qty++;
        } else {
            cart.push({
                id: p.id,
                barcode: p.barcode,
                name: p.product_name,
                type: p.product_type || '-',
                size: p.size || '-',
                brand: p.brand || '-',
                qty: 1,
                mrp: p.mrp
            });
        }
        renderCart();
    }

    function updateQty(id, qty) {
        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty = Math.max(1, parseInt(qty) || 1);
            renderCart();
        }
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('transfer_items');
        const empty = document.getElementById('empty_prompt');
        
        if (cart.length === 0) {
            tbody.innerHTML = '';
            empty.classList.remove('hidden');
            document.getElementById('total_qty_display').textContent = '0';
            return;
        }

        empty.classList.add('hidden');
        let totalQty = 0;
        
        tbody.innerHTML = cart.map(i => {
            const rowTotal = i.qty * i.mrp;
            totalQty += i.qty;
            return `
                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400">${i.barcode || '-'}</td>
                    <td class="px-4 py-4">
                        <p class="text-xs font-black text-slate-900 dark:text-slate-200">${i.name}</p>
                    </td>
                    <td class="px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400">${i.type}</td>
                    <td class="px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400">${i.size}</td>
                    <td class="px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400">${i.brand}</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center">
                            <input type="number" value="${i.qty}" 
                                   class="w-16 px-2 py-1 bg-slate-50 dark:bg-slate-950/50 border border-slate-150 dark:border-slate-800 rounded-lg text-center text-xs font-black outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-slate-800 text-slate-800 dark:text-slate-200"
                                   onchange="updateQty(${i.id}, this.value)">
                        </div>
                    </td>
                    <td class="px-4 py-4 text-xs font-black text-slate-900 dark:text-slate-200 text-right">₹${i.mrp}</td>
                    <td class="px-4 py-4 text-xs font-black text-blue-600 dark:text-blue-400 text-right">₹${rowTotal.toFixed(2)}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="removeItem(${i.id})" class="p-2 text-rose-300 hover:text-rose-500 transition-colors">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        document.getElementById('total_qty_display').textContent = totalQty;
    }

    async function submitTransfer(type, btn) {
        if (cart.length === 0) return showToast('Add items first', 'error');
        
        const fromBranch = document.getElementById('from_branch').value;
        const toBranch = document.getElementById('to_branch').value;
        
        if (fromBranch && toBranch && fromBranch === toBranch) {
            return showToast('Source and destination cannot be same', 'error');
        }

        if (btn.disabled) return;
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">Saving...</span>';

        try {
            const res = await fetch(`{{ route('tenant.stock-transfer.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    from_branch_id: fromBranch || null,
                    to_branch_id: toBranch || null,
                    transfer_date: document.getElementById('transfer_date').value,
                    type: type,
                    items: cart.map(i => ({ id: i.id, qty: i.qty }))
                })
            });

            const result = await res.json();
            if (result.success) {
                showToast('✅ ' + result.message, 'success');
                // Don't reload immediately, let the user see the success
                btn.innerHTML = '✅ Saved';
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showToast('❌ ' + (result.message || 'Error saving transfer'), 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (e) {
            showToast('Network error', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function showToast(msg, type = 'info') {
        const t = document.createElement('div');
        t.className = `fixed bottom-24 right-6 z-[100] px-6 py-4 rounded-2xl shadow-2xl text-xs font-black uppercase tracking-widest transition-all
                       ${type === 'success' ? 'bg-emerald-500 text-white shadow-emerald-100' :
                         type === 'error'   ? 'bg-rose-500 text-white shadow-rose-100' :
                                              'bg-slate-800 text-white shadow-slate-100'}`;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.3);
        border-radius: 10px;
    }
</style>
@endpush
