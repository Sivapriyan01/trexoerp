@extends('layouts.tenant')

@section('page-title', 'Store Setup')

@section('content')

<style>
    @media print {
        body.is-printing-label > *:not(#printable-label):not(style):not(script) {
            display: none !important;
        }
        body.is-printing-label {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }
        #printable-label {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Explicitly restore the transform for the barcode to keep it centered */
        #printable-label .absolute.flex.flex-col.items-center {
            transform: translate(-50%, -50%) !important;
        }
    }
</style>

@push('head')
<script>
    window.initSetup = () => {
        const setupStore = Alpine.store('setup');
        // Alpine.store('setup').activeTab is now handled by setActiveTab in layout
        Alpine.store('setup').sizes = {!! json_encode(array_values(array_filter(explode(',', $data['cat_sizes'] ?? 'S,M,L,XL,XXL,30,32,34,36,38,40,42,46,XXXL')))) !!};
        Alpine.store('setup').invFields = {!! json_encode(json_decode($data['inv_extra_details'] ?? '[]', true)) !!};
        Alpine.store('setup').prodFields = {!! json_encode(json_decode($data['prod_extra_details'] ?? '[]', true)) !!};
        Alpine.store('setup').numbers = {!! json_encode(explode(',', $data['notif_wa_summary_numbers'] ?? '')) !!};

        // Deep link to tab if provided in URL
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            setupStore.setActiveTab(tabParam);
        }
        
        // Load Label Settings
        const labelData = {!! json_encode(json_decode($data['barcode_label_json'] ?? '{}', true)) !!};
        if(labelData && labelData.settings) Alpine.store('setup').labelSettings = labelData.settings;
        if(labelData && labelData.details) Alpine.store('setup').labelDetails = labelData.details;

        // Initialize Invoice Settings
        const invSettings = Alpine.store('setup').invSettings;
        invSettings.headerHeight = {{ $data['inv_header_height'] ?? 40 }};
        invSettings.footerHeight = {{ $data['inv_footer_height'] ?? 30 }};
        invSettings.watermarkType = '{{ $data['inv_watermark_type'] ?? 'text' }}';
        invSettings.watermarkPos = '{{ $data['inv_watermark_pos'] ?? 'center' }}';
        invSettings.watermarkOpacity = {{ $data['inv_watermark_opacity'] ?? 10 }};
        invSettings.headerUrl = '{{ !empty($data['inv_header_img']) ? tenant_asset($data['inv_header_img']) : (!empty($data['business_logo']) ? tenant_asset($data['business_logo']) : '') }}';
        invSettings.footerUrl = '{{ !empty($data['inv_footer_img']) ? tenant_asset($data['inv_footer_img']) : '' }}';
        invSettings.watermarkUrl = '{{ !empty($data['inv_watermark_img']) ? tenant_asset($data['inv_watermark_img']) : '' }}';
        invSettings.watermarkText = '{{ $data['inv_watermark_text'] ?? 'Trex ERP' }}';

        // Load Invoice Architect Layout
        const layoutData = {!! json_encode(json_decode($data['invoice_layout_json'] ?? '{}', true)) !!};
        if(Object.keys(layoutData).length > 0) {
            Object.assign(Alpine.store('setup').invoiceLayout, layoutData);
        }

        // Populate Mock Data from Tenant Info
        setupStore.mockData.BUSINESS_NAME = '{{ $data['branch_name'] ?? '' }}';
        setupStore.mockData.ADDRESS = '{{ $data['branch_address'] ?? '' }}';
        setupStore.mockData.PHONE = '{{ $data['branch_phone'] ?? '' }}';
        setupStore.mockData.EMAIL = '{{ $data['store_email'] ?? '' }}';
        setupStore.mockData.GSTIN = '{{ $data['bill_gst_no'] ?? '' }}';
        setupStore.mockData.PINCODE = '{{ $data['branch_pincode'] ?? '' }}';

        // Load PO Architect Layout
        const poLayoutData = {!! json_encode(json_decode($data['po_layout_json'] ?? '{}', true)) !!};
        if(poLayoutData && poLayoutData.general) {
            Object.assign(setupStore.poLayout, poLayoutData);
        }

        // Load A4 Architect Layout
        const a4LayoutData = {!! json_encode(json_decode($data['a4_layout_json'] ?? '{}', true)) !!};
        if(a4LayoutData && a4LayoutData.general) {
            Object.assign(setupStore.a4Layout, a4LayoutData);
        }

        // Load POS Architect Layout
        const posLayoutData = {!! json_encode(json_decode($data['print_pos_layout_json'] ?? '{}', true)) !!};
        if(posLayoutData && posLayoutData.general) {
            setupStore.posLayout.general = { ...setupStore.posLayout.general, ...posLayoutData.general };
            setupStore.posLayout.company = { ...setupStore.posLayout.company, ...posLayoutData.company };
            setupStore.posLayout.productTable = { ...setupStore.posLayout.productTable, ...posLayoutData.productTable };
            setupStore.posLayout.totals = { ...setupStore.posLayout.totals, ...posLayoutData.totals };
            setupStore.posLayout.footer = { ...setupStore.posLayout.footer, ...posLayoutData.footer };
            
            if (!setupStore.posLayout.company.customRows) setupStore.posLayout.company.customRows = [];
            if (!setupStore.posLayout.footer.customRows) setupStore.posLayout.footer.customRows = [];
            if (!setupStore.posLayout.productTable.columns) setupStore.posLayout.productTable.columns = [];
        }

        // Load Invoice Types
        const invTypesData = {!! json_encode(json_decode($data['invoice_types_json'] ?? '[]', true)) !!};
        if(invTypesData && invTypesData.length > 0) {
            setupStore.invoiceTypes = invTypesData;
        }

        // Load Order Statuses
        const orderStatusesData = {!! json_encode(json_decode($data['order_statuses_json'] ?? '[]', true)) !!};
        if(orderStatusesData && orderStatusesData.length > 0) {
            setupStore.orderStatuses = orderStatusesData;
        }

        // Load Barcode Rows
        const barcodeRowsData = {!! json_encode(json_decode($data['barcode_rows_json'] ?? '[]', true)) !!};
        if(barcodeRowsData && barcodeRowsData.length > 0) {
            setupStore.barcodeRows = barcodeRowsData;
        }

        // ⚡ Premium Auto-Save Logic
        let saveTimeout;
        const form = document.getElementById('settings-form');
        
        setupStore.autoSave = () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(async () => {
                const setup = Alpine.store('setup');
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (response.ok) {
                        setup.addToast('Settings updated', 'success');
                    } else {
                        throw new Error('Save failed');
                    }
                } catch (error) {
                    console.error('Auto-save error:', error);
                    setup.addToast('Auto-save failed', 'error');
                }
            }, 1000); // 1s debounce
        };

        // Listen for changes on all inputs within the form
        if (form) {
            form.addEventListener('change', (e) => {
                // Don't auto-save for files (headers/footers) to avoid heavy uploads
                if (e.target.type === 'file') return;
                setupStore.autoSave();
            });

            // Also listen for input events on text fields for real-time feel (optional)
            form.addEventListener('input', (e) => {
                if (e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'number')) {
                    setupStore.autoSave();
                }
            });
        }
    };
</script>
@endpush

<div x-init="initSetup()" class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Setup your store</h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Configure your business rules and preferences</p>
        </div>
        <div class="flex gap-2">
            @if(session('success'))
                <div class="mr-4 px-4 py-2 bg-blue-600 text-white text-[10px] font-black uppercase rounded-xl animate-in slide-in-from-right duration-500 shadow-lg shadow-blue-200">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('status'))
                <div class="mr-4 px-4 py-2 bg-green-600 text-white text-[10px] font-black uppercase rounded-xl animate-in slide-in-from-right duration-500 shadow-lg shadow-green-200">
                    {{ session('status') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mr-4 px-4 py-2 bg-red-600 text-white text-[10px] font-black uppercase rounded-xl animate-in slide-in-from-right duration-500 shadow-lg shadow-red-200">
                    {{ session('error') }}
                </div>
            @endif
            
            <a href="{{ route('tenant.setup.backup') }}" class="px-6 py-2.5 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 dark:shadow-none" onclick="return confirm('Export a backup for this tenant?')">
                Backup Data
            </a>
            
            <button type="button" onclick="document.getElementById('tenant-restore-file').click()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 dark:shadow-none">
                Restore Data
            </button>
            <form action="{{ route('tenant.setup.restore') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="backup_file" id="tenant-restore-file" accept=".zip,.sql" onchange="if(confirm('Import this backup? Current data will be overwritten.')) { this.form.submit(); }">
            </form>

            <button type="submit" form="settings-form" class="px-6 py-2.5 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 dark:shadow-none">
                Save All Changes
            </button>
        </div>
    </div>

    <!-- Top Action Buttons Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @php
            $topActions = [
                ['label' => 'Add Size', 'icon' => 'plus'],
                ['label' => 'Enterprise A4 Architect', 'icon' => 'pencil'],
                ['label' => 'Legacy A4 Architect', 'icon' => 'document'],
                ['label' => 'Purchase Order Edit', 'icon' => 'document-text'],
                ['label' => 'POS Print Layout Edit', 'icon' => 'printer'],
                ['label' => 'Edit Branch Details', 'icon' => 'office-building'],
                ['label' => 'Edit Barcode Number', 'icon' => 'hashtag'],
                ['label' => 'Edit Barcode Label', 'icon' => 'barcode'],
                ['label' => 'Add Invoice Type', 'icon' => 'collection'],
                ['label' => 'Add Order Status', 'icon' => 'status-online'],
                ['label' => 'Invoice Settings', 'icon' => 'cog'],
                ['label' => 'Printer Settings', 'icon' => 'printer'],
            ];
        @endphp
        @foreach($topActions as $action)
            <button type="button" 
                    @click.prevent="
                        if('{{ $action['label'] }}' === 'Add Size') $store.setup.showSizeModal = true;
                        if('{{ $action['label'] }}' === 'Enterprise A4 Architect') $store.setup.showInvModal = true;
                        if('{{ $action['label'] }}' === 'Legacy A4 Architect') $store.setup.showA4Modal = true;
                        if('{{ $action['label'] }}' === 'Purchase Order Edit') $store.setup.showPOModal = true;
                        if('{{ $action['label'] }}' === 'POS Print Layout Edit') $store.setup.showPOSModal = true;
                        if('{{ $action['label'] }}' === 'Edit Branch Details') $store.setup.showBranchModal = true;
                        if('{{ $action['label'] }}' === 'Edit Barcode Number') $store.setup.showBarNumModal = true;
                        if('{{ $action['label'] }}' === 'Edit Barcode Label') $store.setup.showBarLabelModal = true;
                        if('{{ $action['label'] }}' === 'Add Invoice Type') $store.setup.showInvTypeModal = true;
                        if('{{ $action['label'] }}' === 'Add Order Status') $store.setup.showStatusModal = true;
                        if('{{ $action['label'] }}' === 'Invoice Settings') $store.setup.showInvSettingsModal = true;
                        if('{{ $action['label'] }}' === 'Printer Settings') $store.setup.showPrinterSettingsModal = true;
                    "
                    class="p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl hover:shadow-lg hover:border-blue-100 dark:hover:border-blue-900 transition-all text-center group">
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $action['label'] }}</span>
            </button>
        @endforeach
    </div>

    <!-- Main Configuration Tabs -->
    <div class="flex flex-col gap-6">
            <nav class="flex items-center overflow-x-auto custom-scrollbar bg-slate-100 dark:bg-slate-800 rounded-[1.5rem] p-1.5 backdrop-blur-md">
                @foreach(['billing' => 'Billing', 'purchase' => 'Purchase', 'category' => 'Category', 'gst' => 'GST', 'anniversary' => 'Anniversary', 'invoice' => 'Invoice', 'security' => 'Security', 'notification' => 'Notification', 'whatsapp' => 'WhatsApp', 'about' => 'About'] as $tab => $label)
                <button type="button" @click="$store.setup.setActiveTab('{{ $tab }}')" 
                        :class="$store.setup.activeTab === '{{ $tab }}' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="flex-shrink-0 whitespace-nowrap px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                    {{ $label }}
                </button>
                @endforeach
            </nav>

        <form id="settings-form" action="{{ route('tenant.setup.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Global State Persistence Layer --}}
            <input type="hidden" name="invoice_layout_json" :value="JSON.stringify($store.setup.invoiceLayout)">
            <input type="hidden" name="po_layout_json" :value="JSON.stringify($store.setup.poLayout)">
            <input type="hidden" name="print_pos_layout_json" :value="JSON.stringify($store.setup.posLayout)">
            <input type="hidden" name="a4_layout_json" :value="JSON.stringify($store.setup.a4Layout)">
            <input type="hidden" name="barcode_label_json" :value="JSON.stringify({settings: $store.setup.labelSettings, details: $store.setup.labelDetails})">
            <input type="hidden" name="barcode_rows_json" :value="JSON.stringify($store.setup.barcodeRows)">
            <input type="hidden" name="invoice_types_json" :value="JSON.stringify($store.setup.invoiceTypes)">
            <input type="hidden" name="order_statuses_json" :value="JSON.stringify($store.setup.orderStatuses)">
            
            <!-- Billing Tab -->
            <div x-show="$store.setup.activeTab === 'billing'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Discount Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Discount Settings</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="bill_discount_enabled" value="0">
                                <input type="checkbox" name="bill_discount_enabled" value="1" {{ ($data['bill_discount_enabled'] ?? '') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Enable Discount</span>
                        </label>
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Calculation Type</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="bill_discount_type" value="product" {{ ($data['bill_discount_type'] ?? 'total') == 'product' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-[11px] font-bold text-slate-600">Per Product</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="bill_discount_type" value="total" {{ ($data['bill_discount_type'] ?? 'total') == 'total' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-[11px] font-bold text-slate-600">Total Bill</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Points Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Points Settings</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="bill_points_enabled" value="0">
                                <input type="checkbox" name="bill_points_enabled" value="1" {{ ($data['bill_points_enabled'] ?? '') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Enable Points</span>
                        </label>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Points Value %</label>
                            <input type="number" name="bill_points_percent" value="{{ $data['bill_points_percent'] ?? '1' }}" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Payment Options -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Payment Options</h3>
                    <div class="grid grid-cols-2 gap-y-3">
                        @foreach(['Cash', 'QR', 'Card', 'Credit', 'Coupon'] as $option)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="hidden" name="pay_{{ strtolower($option) }}" value="0">
                                <input type="checkbox" name="pay_{{ strtolower($option) }}" value="1" {{ ($data['pay_'.strtolower($option)] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-tighter">{{ $option }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Row 2: Other Billing Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6 lg:col-span-1">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Other Billing</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Round of Value</label>
                            <select name="bill_round_value" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none dark:text-white appearance-none">
                                <option value="0.5" {{ ($data['bill_round_value'] ?? '') == '0.5' ? 'selected' : '' }}>0.5</option>
                                <option value="1.0" {{ ($data['bill_round_value'] ?? '') == '1.0' ? 'selected' : '' }}>1.0</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="bill_manual_add" value="0">
                            <input type="checkbox" name="bill_manual_add" value="1" {{ ($data['bill_manual_add'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600">Manual Add Product</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="bill_low_stock" value="0">
                            <input type="checkbox" name="bill_low_stock" value="1" {{ ($data['bill_low_stock'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600">Enable Low Stock Alert</span>
                        </label>
                    </div>
                </div>

                <!-- Invoice Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Invoice Settings</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="inv_auto_print" value="0">
                            <input type="checkbox" name="inv_auto_print" value="1" {{ ($data['inv_auto_print'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                            <span class="text-[11px] font-bold text-slate-600">Auto Print Invoice</span>
                        </label>
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase ml-2">Print Preference</p>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="inv_print_pos" value="0">
                                    <input type="checkbox" name="inv_print_pos" value="1" {{ ($data['inv_print_pos'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                                    <span class="text-[11px] font-bold text-slate-600">POS Thermal Printer</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="inv_print_a4" value="0">
                                    <input type="checkbox" name="inv_print_a4" value="1" {{ ($data['inv_print_a4'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                                    <span class="text-[11px] font-bold text-slate-600">A4 Sheet</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Bill Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Quick Bill</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="qb_name_req" value="0">
                            <input type="checkbox" name="qb_name_req" value="1" {{ ($data['qb_name_req'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                            <span class="text-[11px] font-bold text-slate-600">Popup if name empty</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="qb_addr_req" value="0">
                            <input type="checkbox" name="qb_addr_req" value="1" {{ ($data['qb_addr_req'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                            <span class="text-[11px] font-bold text-slate-600">Popup if address empty</span>
                        </label>
                    </div>
                </div>

                <!-- Pre Order Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Pre Order</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="po_billing" value="0">
                            <input type="checkbox" name="po_billing" value="1" {{ ($data['po_billing'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                            <span class="text-[11px] font-bold text-slate-600">Billing Process</span>
                        </label>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase ml-2">Approval Type</p>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="po_approval" value="any" {{ ($data['po_approval'] ?? 'any') == 'any' ? 'checked' : '' }} class="text-blue-600">
                                <span class="text-[11px] font-bold text-slate-600">Any User</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Purchase Tab -->
            <div x-show="$store.setup.activeTab === 'purchase'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in duration-500" x-cloak>
                
                <!-- Stock Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Inventory Sync</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_auto_stock" value="0">
                                <input type="checkbox" name="pur_auto_stock" value="1" {{ ($data['pur_auto_stock'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Auto Update Stock</span>
                        </label>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-8">Automatically increment inventory when a purchase is finalized.</p>
                    </div>
                </div>

                <!-- Price Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Cost Management</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_auto_cost" value="0">
                                <input type="checkbox" name="pur_auto_cost" value="1" {{ ($data['pur_auto_cost'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Auto Update Cost Price</span>
                        </label>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-8">Update product's base cost based on recent purchase entries.</p>
                    </div>
                </div>

                <!-- Tax Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Tax & Margin</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_tax_inc" value="0">
                                <input type="checkbox" name="pur_tax_inc" value="1" {{ ($data['pur_tax_inc'] ?? '0') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Prices are Tax Inclusive</span>
                        </label>
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Default Margin %</label>
                            <input type="number" name="pur_default_margin" value="{{ $data['pur_default_margin'] ?? '20' }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Purchase Table Settings -->
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Purchase Table Settings</h3>
                    <div class="space-y-4">
                        <!-- Show Barcode -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_show_barcode" value="0">
                                <input type="checkbox" name="pur_show_barcode" value="1" {{ ($data['pur_show_barcode'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Show Barcode</span>
                        </label>
 
                        <!-- Show Color -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_show_color" value="0">
                                <input type="checkbox" name="pur_show_color" value="1" {{ ($data['pur_show_color'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Show Color</span>
                        </label>
 
                        <!-- Show Image -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_show_image" value="0">
                                <input type="checkbox" name="pur_show_image" value="1" {{ ($data['pur_show_image'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Show Image</span>
                        </label>
 
                        <!-- Show Dealer Price -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="hidden" name="pur_show_dealer_price" value="0">
                                <input type="checkbox" name="pur_show_dealer_price" value="1" {{ ($data['pur_show_dealer_price'] ?? '1') == '1' ? 'checked' : '' }} class="peer hidden">
                                <div class="w-5 h-5 border-2 border-slate-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Show Dealer Price</span>
                        </label>
                    </div>
                </div>

            </div>

            <!-- GST Tab -->
            <div x-show="$store.setup.activeTab === 'gst'" class="max-w-2xl mx-auto space-y-8 animate-in fade-in duration-500">
                <div class="glass-card p-10 rounded-[3rem] space-y-10 border border-slate-100 dark:border-slate-800">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-8">GST Settings</h3>
                    
                    <div class="space-y-10">
                        <!-- GSTIN Configuration -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Business GSTIN</label>
                                <template x-if="$store.setup.isGstinVerified">
                                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase rounded-full tracking-widest flex items-center gap-1.5">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Verified
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <input type="text" name="bill_gst_no" x-model="$store.setup.mockData.GSTIN" 
                                           @input="if($store.setup.mockData.GSTIN.trim().length === 15) $store.setup.verifyGstin()"
                                           class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-black outline-none focus:ring-2 focus:ring-blue-600 transition-all text-slate-900 dark:text-white" 
                                           placeholder="e.g. 27AAACR1234A1Z1">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <svg x-show="!$store.setup.isGstinValidating" class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg x-show="$store.setup.isGstinValidating" class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </div>
                                </div>
                                <button type="button" @click="$store.setup.verifyGstin()" 
                                        :disabled="$store.setup.isGstinValidating"
                                        class="px-8 py-5 bg-slate-900 dark:bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all disabled:opacity-50">
                                    Verify
                                </button>
                            </div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-2">Validate your GSTIN to auto-fetch business details from the GST portal.</p>

                            <!-- Verified Info Display -->
                            <template x-if="$store.setup.isGstinVerified">
                                <div class="mt-6 p-6 bg-slate-50 dark:bg-slate-900/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 space-y-6 animate-in slide-in-from-top-4 duration-500">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Verified Business Name</label>
                                        <input type="text" name="branch_name" x-model="$store.setup.mockData.BUSINESS_NAME" 
                                               class="w-full px-5 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 transition-all text-slate-900 dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Verified Address</label>
                                        <textarea name="branch_address" x-model="$store.setup.mockData.ADDRESS" 
                                                  class="w-full px-5 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 transition-all text-slate-900 dark:text-white h-20 resize-none"></textarea>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">City</label>
                                            <input type="text" name="branch_city" x-model="$store.setup.mockData.CITY" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border-none rounded-xl text-[10px] font-bold dark:text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">State</label>
                                            <input type="text" name="branch_state" x-model="$store.setup.mockData.STATE" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border-none rounded-xl text-[10px] font-bold dark:text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Pincode</label>
                                            <input type="text" name="branch_pincode" x-model="$store.setup.mockData.PINCODE" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border-none rounded-xl text-[10px] font-bold dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <hr class="border-slate-50 dark:border-slate-800">

                        <!-- Enable GST -->
                        <div class="flex items-center gap-4 group">
                            <div class="relative flex items-center">
                                <input type="hidden" name="gst_enabled" value="0">
                                <input type="checkbox" name="gst_enabled" id="gst_enabled" value="1" {{ ($data['gst_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer">
                            </div>
                            <label for="gst_enabled" class="text-sm font-black text-slate-700 dark:text-slate-300 cursor-pointer group-hover:text-blue-600 transition-colors">Enable GST</label>
                        </div>
                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">GST Application</p>
                            <div class="space-y-4 ml-1">
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_app_type" value="per_product" {{ ($data['gst_app_type'] ?? 'per_product') == 'per_product' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">Per Product</span>
                                </label>
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_app_type" value="total_bill" {{ ($data['gst_app_type'] ?? 'per_product') == 'total_bill' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">Total Bill</span>
                                </label>
                            </div>
                        </div>

                        <!-- GST Calculation Type -->
                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">GST Calculation Type</p>
                            <div class="space-y-4 ml-1">
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_calc_type" value="inclusive" {{ ($data['gst_calc_type'] ?? 'inclusive') == 'inclusive' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">MRP Including GST</span>
                                </label>
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_calc_type" value="exclusive" {{ ($data['gst_calc_type'] ?? 'inclusive') == 'exclusive' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">Direct GST</span>
                                </label>
                            </div>
                        </div>

                        <!-- Default GST % -->
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Default GST %</label>
                            <div class="relative max-w-xs" x-data="{ open: false, value: '{{ $data['gst_default_percent'] ?? '18' }}' }">
                                <input type="text" 
                                       name="gst_default_percent" 
                                       x-model="value"
                                       @focus="open = true"
                                       @click.away="setTimeout(() => open = false, 200)"
                                       class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-sm font-black outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-900 dark:text-white"
                                       placeholder="Select or type %">
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute z-[100] w-full mt-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-2xl shadow-blue-900/10 overflow-hidden backdrop-blur-xl">
                                    <div class="p-2 grid grid-cols-3 gap-1">
                                        @foreach([3, 5, 12, 15, 18, 28] as $rate)
                                            <button type="button" 
                                                    @click="value = '{{ $rate }}'; open = false" 
                                                    class="px-3 py-3 text-[10px] font-black text-slate-600 dark:text-slate-400 hover:bg-blue-600 hover:text-white rounded-xl transition-all uppercase tracking-tighter">
                                                {{ $rate }}%
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                    <svg class="w-4 h-4" :class="open ? 'rotate-180 transition-transform' : 'transition-transform'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Inter / Intra GST -->
                        <div class="flex items-center gap-4 group pt-2">
                            <div class="relative flex items-center">
                                <input type="hidden" name="gst_inter_intra_enabled" value="0">
                                <input type="checkbox" name="gst_inter_intra_enabled" id="gst_inter_intra_enabled" value="1" {{ ($data['gst_inter_intra_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer">
                            </div>
                            <label for="gst_inter_intra_enabled" class="text-sm font-black text-slate-700 dark:text-slate-300 cursor-pointer group-hover:text-blue-600 transition-colors">Inter/ Intra GST</label>
                        </div>

                        <!-- Auto / Manual GST Type Selection -->
                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Auto/ Manual GST Type Selection</p>
                            <div class="space-y-4 ml-1">
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_selection_type" value="auto" {{ ($data['gst_selection_type'] ?? 'auto') == 'auto' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">Auto</span>
                                </label>
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="gst_selection_type" value="manual" {{ ($data['gst_selection_type'] ?? 'auto') == 'manual' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">Manual</span>
                                </label>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Anniversary Tab -->
            <div x-show="$store.setup.activeTab === 'anniversary'" class="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                <div class="glass-card p-8 rounded-[2.5rem] space-y-8 bg-gradient-to-br from-white/40 to-blue-50/30 dark:from-slate-900/40 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </span>
                                WhatsApp Reminder Template
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Personalize your customer anniversary greetings</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[8px] font-black uppercase tracking-widest animate-pulse">Live Sync Active</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-4">
                            <div class="relative group">
                                <div class="absolute -top-3 left-4 px-2 bg-white dark:bg-slate-900 text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest z-10">Message Content</div>
                                <textarea name="anniversary_reminder_message" rows="8" 
                                          class="w-full px-6 py-6 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-[2rem] text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all shadow-sm" 
                                          placeholder="Enter your anniversary message...">{{ $data['anniversary_reminder_message'] ?? "Dear {customer_name},\n\nHappy {years} Year Anniversary with {business_name}!\n\nWe appreciate your continued support.\n\nBest regards,\n{business_name}" }}</textarea>
                            </div>
                            
                            <div class="flex flex-wrap gap-2">
                                @foreach(['customer_name' => 'Name', 'years' => 'Years', 'date' => 'Date', 'invoice_no' => 'Invoice #', 'business_name' => 'Your Shop'] as $tag => $label)
                                <button type="button" @click="const area = document.querySelector('textarea[name=anniversary_reminder_message]'); const text = '{' + '{{ $tag }}' + '}'; area.value = area.value.substring(0, area.selectionStart) + text + area.value.substring(area.selectionEnd); $store.setup.autoSave()" 
                                        class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-600 dark:text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                                    + {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-blue-600 rounded-[2rem] text-white shadow-xl shadow-blue-200 dark:shadow-none">
                                <h4 class="text-[10px] font-black uppercase tracking-widest mb-4 opacity-80">Pro Tip</h4>
                                <p class="text-xs font-bold leading-relaxed">Use tags like <span class="bg-white/20 px-1 rounded">{customer_name}</span> to make your messages feel personal. Customers love personalized greetings!</p>
                            </div>
                            
                            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem]">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Preview Logic</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-tight">Auto-substitutes customer info</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-tight">Sends at 10:00 AM daily</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Tab -->
            <div x-show="$store.setup.activeTab === 'category'" class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Left Column: Category Settings (Enable/Disable) -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="glass-card rounded-[3rem] overflow-hidden border border-slate-100 dark:border-slate-800">
                        <div class="px-10 py-8 border-b border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Category Settings (Enable/Disable)</h3>
                        </div>
                        <div class="divide-y divide-slate-50 dark:divide-slate-800">
                            @php
                                $catSettings = [
                                    ['label' => 'Category Size', 'key' => 'cat_size', 'status' => 'Editable'],
                                    ['label' => 'Product Type', 'key' => 'cat_type', 'status' => 'Editable'],
                                    ['label' => 'Brand', 'key' => 'cat_brand', 'status' => 'Editable'],
                                    ['label' => 'Category MRP', 'key' => 'cat_mrp', 'status' => 'Editable'],
                                    ['label' => 'Category MRP 1', 'key' => 'cat_mrp1', 'status' => 'Non-Editable'],
                                    ['label' => 'Category MRP 2', 'key' => 'cat_mrp2', 'status' => 'Non-Editable'],
                                    ['label' => 'Category Barcode Entry', 'key' => 'cat_barcode', 'status' => 'Editable'],
                                    ['label' => 'Category Color', 'key' => 'cat_color', 'status' => 'Editable'],
                                    ['label' => 'Category Image', 'key' => 'cat_image', 'status' => 'Editable'],
                                    ['label' => 'Category Dealer Price', 'key' => 'cat_dealer', 'status' => 'Editable'],
                                    ['label' => 'Category HSN', 'key' => 'cat_hsn', 'status' => 'Editable'],
                                    ['label' => 'Category Department', 'key' => 'cat_dept', 'status' => 'Editable'],
                                    ['label' => 'Unit', 'key' => 'cat_unit', 'status' => 'Non-Editable'],
                                    ['label' => 'Model', 'key' => 'cat_model', 'status' => 'Non-Editable'],
                                    ['label' => 'GST', 'key' => 'cat_gst', 'status' => 'Non-Editable'],
                                ];
                            @endphp
                            @foreach($catSettings as $s)
                            <div x-data="{ isEnabled: {{ ($data[$s['key'].'_enabled'] ?? (in_array($s['status'], ['Non-Editable']) ? '0' : '1')) == '1' ? 'true' : 'false' }} }" 
                                 class="px-10 py-5 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all group">
                                <div class="flex items-center gap-6">
                                    <div class="relative flex items-center">
                                        <input type="hidden" name="{{ $s['key'] }}_visible" value="0">
                                        <input type="checkbox" name="{{ $s['key'] }}_visible" value="1" {{ ($data[$s['key'].'_visible'] ?? '1') == '1' ? 'checked' : '' }} 
                                               @change="$store.setup.autoSave()"
                                               class="w-5 h-5 rounded border-2 border-slate-200 dark:border-slate-700 text-blue-600 focus:ring-blue-500/20">
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $s['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-8">
                                    <span @click="isEnabled = !isEnabled; $nextTick(() => $store.setup.autoSave())" 
                                          :class="isEnabled ? 'text-emerald-500' : 'text-slate-300'"
                                          class="text-[9px] font-black uppercase tracking-wider cursor-pointer select-none transition-colors"
                                          x-text="isEnabled ? 'Editable' : 'Non-Editable'"></span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="{{ $s['key'] }}_enabled" value="0">
                                        <input type="checkbox" name="{{ $s['key'] }}_enabled" value="1" 
                                               x-model="isEnabled"
                                               @change="$store.setup.autoSave()"
                                               class="sr-only peer">
                                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Advanced Settings & Logic -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="glass-card p-10 rounded-[3rem] space-y-8 border border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Category Settings</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Advanced logic and display rules</p>
                        </div>
                        
                        <div class="space-y-6">
                            @php
                                $advLogic = [
                                    ['label' => 'Category-wise Updated MRP in Billing', 'key' => 'cat_adv_mrp'],
                                    ['label' => 'Charges', 'key' => 'cat_adv_charges'],
                                    ['label' => 'Catalog', 'key' => 'cat_adv_catalog'],
                                    ['label' => 'Category Group', 'key' => 'cat_adv_category_group'],
                                ];
                            @endphp
                            @foreach($advLogic as $adv)
                            <label class="flex items-start gap-4 cursor-pointer group">
                                <div class="mt-0.5">
                                    <input type="hidden" name="{{ $adv['key'] }}" value="0">
                                    <input type="checkbox" name="{{ $adv['key'] }}" value="1" {{ ($data[$adv['key']] ?? '0') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded border-2 border-slate-200 dark:border-slate-700 text-blue-600 focus:ring-blue-500/20">
                                </div>
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-all leading-tight">{{ $adv['label'] }}</span>
                            </label>
                            @endforeach
                        </div>

                        <div class="pt-8 border-t border-slate-50 dark:border-slate-800 space-y-4">
                            <div>
                                <label class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">Enter Recent Category Display Limit</label>
                                <p class="text-[10px] font-bold text-slate-400 leading-relaxed mt-1">(recent categories to show at the top of the list)</p>
                            </div>
                            <input type="number" name="cat_display_limit" value="{{ $data['cat_display_limit'] ?? '10' }}" 
                                   class="w-full px-6 py-5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-black outline-none focus:ring-2 focus:ring-blue-600 transition-all text-slate-900 dark:text-white" 
                                   placeholder="e.g. 10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Tab -->
            <div x-show="$store.setup.activeTab === 'invoice'" class="space-y-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Additional Info Settings -->
                    <div class="glass-card p-8 rounded-[3rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Additional Information Settings</h3>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="inv_extra_enabled" value="0">
                                <input type="checkbox" name="inv_extra_enabled" value="1" {{ ($data['inv_extra_enabled'] ?? '') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded-lg text-blue-600 focus:ring-blue-500">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Enable For Invoice</span>
                            </label>
                        </div>
                        
                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Additional Details</p>
                            <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                                <table class="w-full text-left text-[11px]">
                                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                                        <tr>
                                            <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-widest">Field Name</th>
                                            <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-widest">Field Type</th>
                                            <th class="px-4 py-3 text-center font-black text-slate-400 uppercase tracking-widest">Important</th>
                                            <th class="px-4 py-3 text-center font-black text-slate-400 uppercase tracking-widest">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                        <template x-for="(field, index) in $store.setup.invFields" :key="index">
                                            <tr>
                                                <td class="p-2"><input type="text" x-model="field.name" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border-none rounded-lg font-bold outline-none dark:text-white" placeholder="e.g. Order No"></td>
                                                <td class="p-2">
                                                    <select x-model="field.type" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border-none rounded-lg font-bold outline-none dark:text-white">
                                                        <option value="text">Text</option>
                                                        <option value="number">Number</option>
                                                        <option value="date">Date</option>
                                                    </select>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <input type="checkbox" x-model="field.important" class="w-4 h-4 rounded text-blue-600">
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" @click="$store.setup.invFields.splice(index, 1)" class="text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 p-1.5 rounded-lg transition-all">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-between items-center">
                                <button type="button" @click="$store.setup.invFields.push({name:'', type:'text', important:false})" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600 transition-all">+ Add Row</button>
                                <input type="hidden" name="inv_extra_details" :value="JSON.stringify($store.setup.invFields)">
                            </div>
                        </div>
                    </div>

                    <!-- Product Additional Info -->
                    <div class="glass-card p-8 rounded-[3rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Product Additional Information</h3>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="prod_extra_enabled" value="0">
                                <input type="checkbox" name="prod_extra_enabled" value="1" {{ ($data['prod_extra_enabled'] ?? '') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Enable For Product</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Details</p>
                            <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                                <table class="w-full text-left text-[11px]">
                                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                                        <tr>
                                            <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-widest">Field Name</th>
                                            <th class="px-4 py-3 font-black text-slate-400 uppercase tracking-widest">Field Type</th>
                                            <th class="px-4 py-3 text-center font-black text-slate-400 uppercase tracking-widest">Important</th>
                                            <th class="px-4 py-3 text-center font-black text-slate-400 uppercase tracking-widest">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                        <template x-for="(field, index) in $store.setup.prodFields" :key="index">
                                            <tr>
                                                <td class="p-2"><input type="text" x-model="field.name" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border-none rounded-lg font-bold outline-none dark:text-white" placeholder="e.g. IMEI Number"></td>
                                                <td class="p-2">
                                                    <select x-model="field.type" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border-none rounded-lg font-bold outline-none dark:text-white">
                                                        <option value="text">Text</option>
                                                        <option value="number">Number</option>
                                                        <option value="date">Date</option>
                                                    </select>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <input type="checkbox" x-model="field.important" class="w-4 h-4 rounded text-emerald-600">
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" @click="$store.setup.prodFields.splice(index, 1)" class="text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 p-1.5 rounded-lg transition-all">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-between items-center">
                                <button type="button" @click="$store.setup.prodFields.push({name:'', type:'text', important:false})" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-50 hover:text-emerald-600 transition-all">+ Add Row</button>
                                <input type="hidden" name="prod_extra_details" :value="JSON.stringify($store.setup.prodFields)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div x-show="$store.setup.activeTab === 'security'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Cloud Sync</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Auto Database Sync</span>
                            <input type="hidden" name="sec_auto_sync" value="0">
                            <input type="checkbox" name="sec_auto_sync" value="1" {{ ($data['sec_auto_sync'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Sync Interval (Min)</label>
                            <input type="number" name="sec_sync_min" value="{{ $data['sec_sync_min'] ?? '60' }}" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none dark:text-white">
                        </div>
                    </div>
                </div>
                <div class="glass-card p-6 rounded-[2.5rem] space-y-6">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Access Lock</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin Lock Mode</span>
                            <input type="hidden" name="sec_lock_enabled" value="0">
                            <input type="checkbox" name="sec_lock_enabled" value="1" {{ ($data['sec_lock_enabled'] ?? '') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                        </div>
                        <input type="password" name="sec_lock_code" placeholder="Enter PIN" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold outline-none dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Notification Tab -->
            <div x-show="$store.setup.activeTab === 'notification'" class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- App Notification -->
                    <div class="glass-card p-8 rounded-[3rem] space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center text-blue-600">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Settings Notification</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">App-based event notifications</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Notify on Trex ERP App</span>
                                <input type="hidden" name="notif_app_enabled" value="0">
                                <input type="checkbox" name="notif_app_enabled" value="1" {{ ($data['notif_app_enabled'] ?? '') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded-lg text-blue-600">
                            </label>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Select Options</label>
                                <select name="notif_app_mode" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white">
                                    <option value="show" {{ ($data['notif_app_mode'] ?? 'show') == 'show' ? 'selected' : '' }}>Show</option>
                                    <option value="hide" {{ ($data['notif_app_mode'] ?? 'show') == 'hide' ? 'selected' : '' }}>Hide</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Tab -->
            <div x-show="$store.setup.activeTab === 'whatsapp'" class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Whatsapp Sales Notification -->
                    <div class="glass-card p-8 rounded-[3rem] space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl flex items-center justify-center text-emerald-600">
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.284l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.768-5.764-5.768zm3.393 8.247c-.144.405-.833.778-1.162.827-.329.049-.652.072-1.611-.293-1.173-.446-1.926-1.637-1.983-1.714-.058-.076-.468-.621-.468-1.189 0-.568.298-.847.404-.961.107-.114.23-.143.308-.143h.221c.08 0 .188-.031.294.225.107.256.366.892.398.956.032.064.053.139.011.225-.042.085-.064.139-.127.213-.064.074-.134.165-.191.223-.064.064-.13.134-.056.262.074.128.33.543.707.879.485.431.892.565 1.02.629.128.064.202.053.277-.032.074-.085.319-.373.404-.5.085-.128.17-.107.287-.064.117.043.745.352.872.416.128.064.213.096.245.149.032.053.032.309-.112.714z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Sales Notification (WhatsApp)</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Manage instant WhatsApp sales alerts</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Notify on Whatsapp</span>
                                <input type="hidden" name="notif_wa_sales" value="0">
                                <input type="checkbox" name="notif_wa_sales" value="1" {{ ($data['notif_wa_sales'] ?? '') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500">
                            </label>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Select Options</label>
                                <select name="notif_wa_sales_mode" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white">
                                    <option value="show" {{ ($data['notif_wa_sales_mode'] ?? 'show') == 'show' ? 'selected' : '' }}>Show</option>
                                    <option value="hide" {{ ($data['notif_wa_sales_mode'] ?? 'show') == 'hide' ? 'selected' : '' }}>Hide</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- API Configuration -->
                    <div class="glass-card p-8 rounded-[3rem] space-y-6" x-data="{ showApiKey: false, showAuthKey: false, saving: false, saved: false }">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-600 dark:text-slate-400">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">API Configuration</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">WhatsApp Gateway Credentials</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- API Key with show/hide -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">API Key (App Key)</label>
                                    <div class="relative">
                                        <input :type="showApiKey ? 'text' : 'password'"
                                               id="wa_access_token_input"
                                               name="wa_access_token"
                                               value="{{ $data['wa_access_token'] ?? '' }}"
                                               class="w-full px-5 py-4 pr-12 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white focus:ring-2 focus:ring-emerald-500/20"
                                               placeholder="Enter App Key">
                                        <button type="button"
                                                @click="showApiKey = !showApiKey"
                                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 transition-colors">
                                            <svg x-show="!showApiKey" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showApiKey" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Auth Key with show/hide -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Auth Key</label>
                                    <div class="relative">
                                        <input :type="showAuthKey ? 'text' : 'password'"
                                               id="wa_auth_key_input"
                                               name="wa_auth_key"
                                               value="{{ $data['wa_auth_key'] ?? '' }}"
                                               class="w-full px-5 py-4 pr-12 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-[11px] font-bold outline-none dark:text-white focus:ring-2 focus:ring-emerald-500/20"
                                               placeholder="Enter Auth Key">
                                        <button type="button"
                                                @click="showAuthKey = !showAuthKey"
                                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 transition-colors">
                                            <svg x-show="!showAuthKey" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showAuthKey" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Credentials Button -->
                            <div class="flex items-center gap-4 pt-2">
                                <button type="button"
                                        @click="
                                            saving = true; saved = false;
                                            const form = new FormData();
                                            form.append('_token', '{{ csrf_token() }}');
                                            form.append('wa_access_token', document.getElementById('wa_access_token_input').value);
                                            form.append('wa_auth_key', document.getElementById('wa_auth_key_input').value);
                                            fetch('{{ route('tenant.setup.update') }}', { method: 'POST', body: form })
                                                .then(r => { saving = false; saved = true; setTimeout(() => saved = false, 3000); })
                                                .catch(() => { saving = false; alert('Save failed.'); });
                                        "
                                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 dark:shadow-none hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2 disabled:opacity-60"
                                        :disabled="saving">
                                    <svg x-show="!saving && !saved" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="saving" class="animate-spin" width="14" height="14" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    <span x-text="saving ? 'Saving...' : (saved ? '✅ Saved!' : 'Save Credentials')"></span>
                                </button>
                                <p x-show="saved" class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest animate-pulse">Keys updated successfully</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Summary Report Numbers -->
                <div class="glass-card p-8 rounded-[3rem] space-y-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Summary Report Whatsapp Numbers</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Configure recipients for daily summary reports</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Enter WhatsApp Number</label>
                                <div class="flex gap-2">
                                    <input type="text" id="new-wa-number" class="flex-1 px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none dark:text-white" placeholder="e.g. 9876543210">
                                    <button type="button" @click="const val = document.getElementById('new-wa-number').value.trim(); if(val && !$store.setup.numbers.includes(val)) $store.setup.numbers.push(val); document.getElementById('new-wa-number').value=''" class="px-6 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest">Add</button>
                                </div>
                            </div>
                            
                            <input type="hidden" name="notif_wa_summary_numbers" :value="$store.setup.numbers.filter(n => n).join(',')">
                            
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase ml-2">Active Recipients</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(num, index) in $store.setup.numbers" :key="index">
                                        <div x-show="num" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl animate-in fade-in zoom-in">
                                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300" x-text="num"></span>
                                            <button type="button" @click="$store.setup.numbers.splice(index, 1)" class="text-rose-500 hover:text-rose-700">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 md:col-span-2">
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] space-y-6">
                                <h4 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Report Schedule</h4>
                                <div class="flex items-center gap-4">
                                    <div class="space-y-2 flex-1">
                                        <p class="text-[9px] font-black text-slate-400 uppercase ml-1">Send Time</p>
                                        <div class="flex items-center gap-2">
                                            <select name="notif_report_hour" class="flex-1 px-4 py-3 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold outline-none dark:text-white">
                                                @for($i=1; $i<=12; $i++)
                                                    <option value="{{ sprintf('%02d', $i) }}" {{ ($data['notif_report_hour'] ?? '09') == sprintf('%02d', $i) ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}</option>
                                                @endfor
                                            </select>
                                            <span class="text-slate-400">:</span>
                                            <select name="notif_report_min" class="flex-1 px-4 py-3 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold outline-none dark:text-white">
                                                @foreach(['00','15','30','45'] as $m)
                                                    <option value="{{ $m }}" {{ ($data['notif_report_min'] ?? '00') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                @endforeach
                                            </select>
                                            <select name="notif_report_period" class="flex-1 px-4 py-3 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold outline-none dark:text-white">
                                                <option value="AM" {{ ($data['notif_report_period'] ?? 'AM') == 'AM' ? 'selected' : '' }}>AM</option>
                                                <option value="PM" {{ ($data['notif_report_period'] ?? 'AM') == 'PM' ? 'selected' : '' }}>PM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit" class="px-8 py-3.5 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-500/20">Save Schedule</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Tab -->
            <div x-show="$store.setup.activeTab === 'about'" class="glass-card p-12 rounded-[3rem] text-center max-w-2xl mx-auto">
                <div class="flex items-center justify-center mx-auto mb-8 p-6 bg-slate-900 dark:bg-slate-800 rounded-3xl shadow-xl">
                    <img src="/assets/images/trex-logo.png" alt="Trex ERP" class="w-64 h-auto object-contain">
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 mb-8">Version 2.4.0 Professional Edition</p>
                <div class="grid grid-cols-3 gap-6 text-left">
                    <div class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-[9px] font-black text-blue-600 uppercase mb-1">Smart Billing</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">High-speed checkout with automated fiscal rules.</p>
                    </div>
                    <div class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-[9px] font-black text-emerald-600 uppercase mb-1">Inventory</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">Real-time stock tracking and low-stock alerts.</p>
                    </div>
                    <div class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-[9px] font-black text-rose-600 uppercase mb-1">Reporting</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">Advanced fiscal and performance analytics.</p>
                    </div>
                    </div>
                </div>
            </div>
    </div>
</div>
</div>

{{-- Security Lock Modal --}}
@if($locked ?? false)
<div id="setup-lock-screen" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-2xl flex items-center justify-center p-4">
    <div class="glass-card p-12 rounded-[3rem] w-full max-w-md text-center space-y-8 animate-in zoom-in duration-500">
        <div class="w-20 h-20 bg-blue-600 rounded-[2.5rem] flex items-center justify-center mx-auto shadow-2xl shadow-blue-500/50">
            <svg width="32" height="32" class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Setup Locked</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Enter Secure PIN to access business rules</p>
        </div>
        <div class="space-y-4">
            <input type="password" id="lock-pin" maxlength="4" autofocus
                   class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl text-2xl font-black text-center tracking-[1em] focus:ring-4 focus:ring-blue-600/20 transition-all outline-none dark:text-white"
                   placeholder="****">
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('tenant.dashboard') }}" class="py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                    Go Back
                </a>
                <button onclick="attemptUnlock()" class="py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20">
                    Verify
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function attemptUnlock() {
    const pin = document.getElementById('lock-pin').value;
    const btn = event.target;
    
    btn.disabled = true;
    btn.innerHTML = 'Verifying...';

    try {
        const res = await fetch('{{ route("tenant.setup.unlock") }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ pin })
        });
        const data = await res.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message);
            document.getElementById('lock-pin').value = '';
        }
    } catch (err) {
        alert('Verification failed');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Verify & Unlock';
    }
}
</script>
@endif

{{-- Master Size Manager Modal --}}
<div x-show="$store.setup.showSizeModal" 
     class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100">
    
    <div class="glass-card w-full max-w-lg rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col h-[80vh]">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Add Master Sizes</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Manage global product size options</p>
            </div>
            <button @click="$store.setup.showSizeModal = false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-4">
            <div class="grid grid-cols-1 gap-2">
                <template x-for="(size, index) in $store.setup.sizes" :key="index">
                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl group border border-transparent hover:border-blue-100 dark:hover:border-blue-900 transition-all">
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-black text-slate-300 w-4" x-text="index + 1"></span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-200" x-text="size"></span>
                        </div>
                        <button type="button" @click="$store.setup.sizes.splice(index, 1)" class="opacity-0 group-hover:opacity-100 px-3 py-1.5 text-[9px] font-black text-rose-500 uppercase tracking-widest hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all">
                            Delete
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-8 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800">
            <div class="flex gap-2">
                <input type="text" id="new-size-input" 
                       class="flex-1 px-5 py-3 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 transition-all dark:text-white" 
                       placeholder="Enter new size...">
                <button type="button" 
                        @click="const val = document.getElementById('new-size-input').value.trim().toUpperCase(); if(val && !$store.setup.sizes.includes(val)) $store.setup.sizes.push(val); document.getElementById('new-size-input').value=''"
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                    + Add
                </button>
            </div>
            <input type="hidden" name="cat_sizes" :value="$store.setup.sizes.join(',')">
            <p class="text-[9px] font-bold text-slate-400 mt-4 text-center uppercase tracking-widest italic">Don't forget to click 'Save All Changes' on the main page to persist these sizes.</p>
        </div>
    </div>
</div>

{{-- Advanced Invoice Architect Modal --}}
<div x-show="$store.setup.showInvModal" 
     x-data="{ currentEditSection: 'GLOBAL' }"
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-[98vw] rounded-[3rem] overflow-hidden shadow-2xl flex flex-col h-[98vh]">
        <!-- Header -->
        <div class="p-6 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
            <div class="flex items-center gap-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Enterprise Invoice Architect</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Full Document Branding & Structural Suite</p>
                </div>
                <!-- Tabs -->
                <div class="flex items-center bg-slate-50 dark:bg-slate-800 rounded-2xl p-1.5 ml-8">
                    <template x-for="tab in [
                        { id: 'GLOBAL', label: 'layout' },
                        { id: 'PRODUCT TABLE', label: 'table' },
                        { id: 'GST TABLE', label: 'gst' },
                        { id: 'WATERMARK', label: 'watermark' },
                        { id: 'FOOTER', label: 'footer' },
                        { id: 'SOCIAL MEDIA', label: 'social' }
                    ]" :key="tab.id">
                        <button type="button" @click="currentEditSection = tab.id" 
                                :class="currentEditSection === tab.id ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                                class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                x-text="tab.label"></button>
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button @click="$store.setup.showInvModal = false" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-2xl transition-all text-slate-400">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

                <div class="flex h-[80vh] overflow-hidden">
                    <!-- Left Sidebar: Live A4 Preview -->
                    <div class="w-[300px] bg-slate-50 dark:bg-slate-800/30 p-4 flex flex-col items-center border-r border-slate-100 dark:border-slate-800 overflow-y-auto custom-scrollbar">
                        <div data-darkreader-ignore class="!bg-white shadow-lg rounded-sm border border-slate-200 relative flex flex-col transition-all duration-500 origin-top scale-[0.85] sticky top-0"
                             :style="`width: ${($store.setup.invoiceLayout.settings || {}).totalWidth * 1.5 || 320}px; aspect-ratio: 1/1.414; padding: ${(($store.setup.invoiceLayout.settings || {}).marginTop || 0) * 0.5}px ${(($store.setup.invoiceLayout.settings || {}).marginRight || 0) * 0.5}px ${(($store.setup.invoiceLayout.settings || {}).marginBottom || 0) * 0.5}px ${(($store.setup.invoiceLayout.settings || {}).marginLeft || 0) * 0.5}px; font-family: ${($store.setup.invoiceLayout.settings || {}).fontFamily || 'Inter'}, sans-serif; --primary-color: ${($store.setup.invoiceLayout.settings || {}).primaryColor || '#2563eb'}; font-size: ${($store.setup.invoiceLayout.settings || {}).fontSize || 10}px;`"
                             :class="{
                                'border-t-8 border-t-[var(--primary-color)]': ($store.setup.invoiceLayout.settings || {}).format === 'modern',
                                'border-2 border-black': ($store.setup.invoiceLayout.settings || {}).format === 'tally'
                             }"
                        >
                            <!-- Sections Rendering -->
                            <template x-for="section in ($store.setup.invoiceLayout.sections || [])" :key="section.id">
                                <div class="mb-1" x-data="{ localSelected: null, draggedRowId: null }" @click.outside="localSelected = null">
                                    <div class="grid gap-x-1" :class="`grid-cols-${section.columns || 1}`">
                                        <template x-for="c in parseInt(section.columns || 1)" :key="c">
                                            <div class="space-y-0.5">
                                                <template x-for="(row, rIndex) in (section.rows || []).filter(r => parseInt(r.col) === c)" :key="rIndex">
                                                    <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                                         :class="{'ring-2 ring-purple-500 rounded-md': localSelected === rIndex}"
                                                         @click.stop="localSelected = rIndex"
                                                         @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                                         @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                                         @drop.prevent="$el.classList.remove('border-purple-300'); if(draggedRowId !== null) { $store.setup.reorderEnterpriseFieldByIndex(section.id, c, draggedRowId, rIndex); localSelected = rIndex; draggedRowId = null; }">
                                                        <div class="w-full" :style="`font-size: ${row.fontSize/2}px; font-weight: ${row.bold ? '800' : '500'}; line-height: 1.4;`" :class="{
                                                            'text-center': row.align === 'center' || row.align === 'C',
                                                            'text-right': row.align === 'right' || row.align === 'R',
                                                            'text-left': !['center', 'C', 'right', 'R'].includes(row.align)
                                                        }">
                                                        <template x-if="row.source === 'LOGO'">
                                                            <div class="flex w-full" :class="{
                                                                'justify-center': row.align === 'center' || row.align === 'C',
                                                                'justify-end': row.align === 'right' || row.align === 'R',
                                                                'justify-start': !['center', 'C', 'right', 'R'].includes(row.align)
                                                            }">
                                                                <div :style="`width: ${($store.setup.invoiceLayout.settings || {}).logoWidth * 1.5 || 60}px; height: ${($store.setup.invoiceLayout.settings || {}).logoHeight * 1.5 || 60}px`" class="border border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 rounded-lg shrink-0">
                                                                    <img :src="$store.setup.invSettings.headerUrl" 
                                                                         x-show="$store.setup.invSettings.headerUrl"
                                                                         class="w-full h-full object-contain block"
                                                                         x-on:error="console.error('Logo failed to load:', $el.src)">
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="row.source !== 'LOGO'">
                                                            <div class="flex gap-1 w-full" :class="{
                                                                'justify-center': row.align === 'center' || row.align === 'C',
                                                                'justify-end': row.align === 'right' || row.align === 'R',
                                                                'justify-start': !['center', 'C', 'right', 'R'].includes(row.align)
                                                            }">
                                                                <span class="text-slate-400 opacity-60 shrink-0 uppercase" x-show="row.label" x-text="row.label" style="font-size: 0.8em; font-weight: 800; letter-spacing: 0.05em;"></span>
                                                                <span class="uppercase" x-text="$store.setup.getMockValue(row.source)" style="font-weight: inherit; color: #0f172a;"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    
                                                    <div x-show="localSelected === rIndex"
                                                         draggable="true"
                                                         @dragstart="draggedRowId = rIndex; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                                         class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                                         title="Drag to move">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                            <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                            <!-- Table rendering -->
                            <div x-show="($store.setup.invoiceLayout.settings || {}).showProductTable" class="mt-2 overflow-hidden" 
                                 :class="($store.setup.invoiceLayout.settings || {}).format === 'corporate' ? '' : 'border-y-[3px] border-slate-900'"
                                 :style="`margin-top: ${($store.setup.invoiceLayout.tableSettings || {}).topSpace * 0.5}px; margin-bottom: ${($store.setup.invoiceLayout.tableSettings || {}).bottomSpace * 0.5}px; text-align: ${($store.setup.invoiceLayout.tableSettings || {}).bodyAlign || 'left'}`">
                                <table class="w-full">
                                    <thead>
                                        <tr :class="($store.setup.invoiceLayout.settings || {}).format === 'corporate' ? 'text-white' : 'border-b border-slate-900'"
                                            :style="($store.setup.invoiceLayout.settings || {}).format === 'corporate' ? 'background-color: var(--primary-color);' : ''">
                                            <template x-for="col in ($store.setup.invoiceLayout.tableColumns || [])" :key="col.id">
                                                <th class="font-black uppercase" 
                                                    :class="($store.setup.invoiceLayout.settings || {}).format === 'corporate' ? '' : 'text-slate-900'"
                                                    :style="`width: ${col.width * 1.5}px; font-size: ${($store.setup.invoiceLayout.tableSettings || {}).headerSize/2.2}px; padding: ${($store.setup.invoiceLayout.tableSettings || {}).headerPadding * 0.5}px; text-align: ${col.align || 'left'}`" 
                                                    x-text="col.header"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="i in 3">
                                            <tr>
                                                <template x-for="col in ($store.setup.invoiceLayout.tableColumns || [])" :key="col.id">
                                                    <td class="truncate text-slate-700" 
                                                        :style="`font-size: ${col.fontSize/2.2}px; font-weight: ${col.bold ? '800' : '400'}; padding: ${($store.setup.invoiceLayout.tableSettings || {}).bodyPadding * 0.5}px; padding-left: ${col.leftSpace * 0.5}px; padding-top: ${col.topSpace * 0.5}px; text-align: ${col.align || 'left'}`" 
                                                        x-text="i === 1 ? $store.setup.getMockValue(col.source) : (i === 2 ? '---' : '...')"></td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- GST Table Preview -->
                            <div x-show="($store.setup.invoiceLayout.settings || {}).showGstTable" class="mt-4 border border-slate-200 rounded-sm overflow-hidden">
                                <div class="bg-slate-50 border-b border-slate-200 flex justify-between p-1 text-[5px] font-black text-slate-900 uppercase tracking-tighter" :style="`font-size: ${($store.setup.invoiceLayout.gstTable || {}).fontSize / 2}px`">
                                    <template x-for="col in ($store.setup.invoiceLayout.gstTable.columns || []).filter(c => c.enabled)">
                                        <span x-text="col.label"></span>
                                    </template>
                                </div>
                                <div class="flex justify-between p-1 text-[5px] font-bold text-slate-600" :style="`font-size: ${($store.setup.invoiceLayout.gstTable || {}).fontSize / 2}px`">
                                    <template x-for="col in ($store.setup.invoiceLayout.gstTable.columns || []).filter(c => c.enabled)">
                                        <span>123.45</span>
                                    </template>
                                </div>
                            </div>

                            <!-- Social Media Preview -->
                            <div x-show="($store.setup.invoiceLayout.settings || {}).showSocialMedia" class="mt-4 flex gap-4 justify-center border-t border-slate-100 pt-2 opacity-40">
                                <template x-for="platform in ($store.setup.invoiceLayout.socialMedia.platforms || []).filter(p => p.enabled)">
                                    <div class="flex items-center gap-1 text-[5px] font-black uppercase" :style="`font-size: ${($store.setup.invoiceLayout.socialMedia || {}).fontSize / 2}px`">
                                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                                        <span x-text="platform.handle"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Watermark Preview -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none overflow-hidden space-y-[80px]" 
                                 x-show="($store.setup.invoiceLayout.watermark || {}).text"
                                 :style="`opacity: ${($store.setup.invoiceLayout.watermark || {}).opacity || 0.1}`">
                                <span class="text-[50px] font-black uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap text-slate-200 transform -translate-y-12 translate-x-24" 
                                      x-text="($store.setup.invoiceLayout.watermark || {}).text"></span>
                                <span class="text-[50px] font-black uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap text-slate-200" 
                                      x-text="($store.setup.invoiceLayout.watermark || {}).text"></span>
                                <span class="text-[50px] font-black uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap text-slate-200 transform translate-y-12 -translate-x-24" 
                                      x-text="($store.setup.invoiceLayout.watermark || {}).text"></span>
                            </div>

                            <!-- Footer Preview -->
                            <div class="mt-auto border-t-2 border-slate-900 pt-4" x-show="($store.setup.invoiceLayout.footer || {}).declaration">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[4px] font-black text-slate-400 uppercase tracking-widest block mb-1">Terms & Conditions</label>
                                        <p class="text-[5px] leading-relaxed text-slate-600 whitespace-pre-line font-medium italic" x-text="($store.setup.invoiceLayout.footer || {}).declaration"></p>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <div class="w-20 border-b border-slate-900 mb-1"></div>
                                        <p class="text-[5px] font-black text-slate-900 uppercase tracking-widest" x-text="($store.setup.invoiceLayout.footer || {}).signatureLabel"></p>
                                        <p class="text-[4px] text-slate-400 uppercase mt-0.5">Authorized Signatory</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Controls -->
                    <div class="flex-1 p-8 overflow-y-auto custom-scrollbar bg-white dark:bg-slate-900">
                        <div class="space-y-10">
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-8">
                                    <div class="space-y-3">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Company Logo</label>
                                                <div class="flex items-center gap-4">
                                                    <label for="architect_logo_upload" class="px-6 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 cursor-pointer">
                                                        Choose Logo
                                                    </label>
                                                    <input type="file" id="architect_logo_upload" form="settings-form" class="hidden" @change="
                                                        $el.name = 'inv_header_img';
                                                        console.log('File Input Triggered:', $event.target.files[0]);
                                                        $store.setup.updateLogo('header', $event.target.files[0]);
                                                    ">
                                                    <div class="flex items-center gap-2" x-show="$store.setup.invSettings.headerUrl">
                                                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Selected</span>
                                                        <button type="button" @click="$store.setup.invSettings.headerUrl = ''; document.getElementById('header_remove_input').value = '1'" class="p-1.5 bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-all">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-show="!$store.setup.invSettings.headerUrl">None</span>
                                                </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Logo Height</label>
                                            <input type="number" x-model="$store.setup.invoiceLayout.settings.logoHeight" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Logo Width</label>
                                            <input type="number" x-model="$store.setup.invoiceLayout.settings.logoWidth" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Theme Color</label>
                                        <input type="color" x-model="$store.setup.invoiceLayout.settings.primaryColor" class="w-full h-[46px] bg-slate-50 dark:bg-slate-800 rounded-2xl p-1 cursor-pointer border-none dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Font Family</label>
                                        <select x-model="$store.setup.invoiceLayout.settings.fontFamily" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none appearance-none dark:text-white">
                                            <option value="Inter">Inter (Clean)</option>
                                            <option value="Roboto">Roboto (Classic)</option>
                                            <option value="'Courier New', Courier, monospace">Courier (Mono)</option>
                                            <option value="'Times New Roman', Times, serif">Times (Serif)</option>
                                            <option value="Montserrat">Montserrat (Modern)</option>
                                            <option value="Outfit">Outfit (Premium)</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Base Font Size</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.fontSize" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Document Style</label>
                                        <select x-model="$store.setup.invoiceLayout.settings.format" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none appearance-none dark:text-white">
                                            <option value="default">Default</option>
                                            <option value="modern">Modern Edge</option>
                                            <option value="classic">Classic Strict</option>
                                            <option value="tally">Tally Boxed</option>
                                            <option value="corporate">Corporate Solid</option>
                                            <option value="compact">Compact Minimal</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Select To set Default Invoice</label>
                                    <select class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-3 text-xs font-bold border-none appearance-none dark:text-white">
                                        <option>Select...</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-5 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Width (mm)</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.totalWidth" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Space Top (mm)</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.marginTop" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Space Bottom (mm)</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.marginBottom" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Space Left (mm)</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.marginLeft" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Space Right (mm)</label>
                                        <input type="number" x-model="$store.setup.invoiceLayout.settings.marginRight" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white">
                                    </div>
                                </div>

                                <div class="flex gap-8 border-b border-slate-100 dark:border-slate-800 pb-6">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" x-model="$store.setup.invoiceLayout.settings.showProductTable" class="w-5 h-5 rounded-lg text-blue-600 focus:ring-blue-500 border-slate-200">
                                        <span class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest group-hover:text-blue-600 transition-colors">Product Table</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" x-model="$store.setup.invoiceLayout.settings.showGstTable" class="w-5 h-5 rounded-lg text-blue-600 focus:ring-blue-500 border-slate-200">
                                        <span class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest group-hover:text-blue-600 transition-colors">GST Table</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" x-model="$store.setup.invoiceLayout.settings.showSocialMedia" class="w-5 h-5 rounded-lg text-blue-600 focus:ring-blue-500 border-slate-200">
                                        <span class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest group-hover:text-blue-600 transition-colors">Social Media</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Section Switcher --}}
                            <div class="space-y-6">
                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Select Section to Edit:</label>
                                    <select x-model="currentEditSection" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-4 text-xs font-black uppercase tracking-widest border-none appearance-none cursor-pointer hover:bg-slate-100 transition-all dark:text-white">
                                        <option value="GLOBAL">Global Content Layout</option>
                                        <option value="PRODUCT TABLE">Product Table Architect</option>
                                        <option value="GST TABLE">GST Table Architect</option>
                                        <option value="WATERMARK">Watermark & Copies</option>
                                        <option value="FOOTER">Footer & Signature</option>
                                        <option value="SOCIAL MEDIA">Social Media Suite</option>
                                    </select>
                                </div>

                                {{-- Global Layout Architect --}}
                                <div x-show="currentEditSection === 'GLOBAL'" x-transition>
                                    <div class="flex items-center justify-between mb-4">
                                        <h5 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Document Sections</h5>
                                        <button type="button" @click="$store.setup.addSection()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700">+ Add Section</button>
                                    </div>
                                    <div class="space-y-6">
                                        <template x-for="(section, index) in ($store.setup.invoiceLayout.sections || [])" :key="section.id">
                                            <div class="glass-card rounded-[2rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center border-b border-slate-100 dark:border-slate-800">
                                                    <input type="text" x-model="section.name" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0">
                                                    <div class="flex gap-2">
                                                        <button type="button" @click="$store.setup.moveSection(index, -1)" class="p-2 hover:text-blue-600"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                                        <button type="button" @click="$store.setup.moveSection(index, 1)" class="p-2 hover:text-blue-600"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                                        <button type="button" @click="$store.setup.removeSection(section.id)" class="p-2 text-rose-400"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                </div>
                                                <div class="p-0 overflow-x-auto custom-scrollbar">
                                                    <table class="w-full text-left text-[10px]">
                                                        <thead class="bg-slate-50 dark:bg-slate-800">
                                                            <tr><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black">Source</th><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black">Label</th><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black">Col</th><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black text-center">Size</th><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black text-center">B</th><th class="px-4 py-3 text-slate-400 uppercase tracking-widest font-black text-center">Align</th><th class="px-4 py-3"></th></tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                            <template x-for="(row, rIndex) in (section.rows || [])" :key="rIndex">
                                                                <tr>
                                                                    <td class="px-4 py-2">
                                                                        <select x-model="row.source" x-html="$store.setup.getFieldOptionsHTML()" @change="row.label = (row.source === 'BLANK_SPACE' ? '' : row.source.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ') + ':')" class="w-full bg-transparent border-none text-[10px] font-bold focus:ring-0">
</select>
                                                                    </td>
                                                                    <td class="px-4 py-2"><input type="text" x-model="row.label" class="w-full bg-transparent border-none text-[10px] font-bold focus:ring-0"></td>
                                                                    <td class="px-4 py-2"><select x-model="row.col" class="bg-transparent border-none text-[10px] font-bold focus:ring-0"><template x-for="c in parseInt(section.columns || 1)" :key="c"><option :value="c" x-text="'C' + c"></option></template></select></td>
                                                                    <td class="px-4 py-2 text-center"><input type="number" x-model="row.fontSize" class="w-10 text-center bg-transparent border-none text-[10px] font-bold focus:ring-0"></td>
                                                                    <td class="px-4 py-2 text-center"><input type="checkbox" x-model="row.bold" class="w-4 h-4 rounded text-blue-600"></td>
                                                                    <td class="px-4 py-2 text-center">
                                                                        <select x-model="row.align" class="bg-transparent border-none text-[10px] font-bold focus:ring-0">
                                                                            <option value="left">L</option><option value="center">C</option><option value="right">R</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="px-4 py-2 text-right"><button type="button" @click="$store.setup.removeRow(section.id, rIndex)" class="text-rose-400"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></button></td>
                                                                </tr>
                                                            </template>
                                                            <tr><td colspan="7" class="p-0"><button type="button" @click="$store.setup.addRow(section.id)" class="w-full py-3 bg-slate-50 dark:bg-slate-800/30 text-[9px] font-black text-blue-600 uppercase tracking-widest hover:bg-blue-50 transition-all">+ Add Row</button></td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Product Table Architect --}}
                                <div x-show="currentEditSection === 'PRODUCT TABLE'" x-transition>
                                    <div class="space-y-8">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Header Size</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.headerSize" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Content Size</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.contentSize" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Top Space</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.topSpace" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Bottom Space</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.bottomSpace" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Header Padding</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.headerPadding" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Body Padding</label><input type="number" x-model="$store.setup.invoiceLayout.tableSettings.bodyPadding" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none dark:text-white"></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Header Text Alignment</label><select x-model="$store.setup.invoiceLayout.tableSettings.headerAlign" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none appearance-none dark:text-white"><option value="left">LEFT</option><option value="center">CENTER</option><option value="right">RIGHT</option></select></div>
                                            <div class="space-y-2"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Product Table Body Text Alignment</label><select x-model="$store.setup.invoiceLayout.tableSettings.bodyAlign" class="w-full bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-3 text-xs font-bold border-none appearance-none dark:text-white"><option value="left">LEFT</option><option value="center">CENTER</option><option value="right">RIGHT</option></select></div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <h5 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Column Configuration</h5>
                                                <button type="button" @click="$store.setup.addTableColumn()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all">+ Add Column</button>
                                            </div>
                                            <div class="glass-card rounded-[2rem] border border-slate-100 dark:border-slate-800 overflow-x-auto custom-scrollbar">
                                                <table class="w-full text-left text-[10px] min-w-[900px]">
                                                    <thead class="bg-slate-50 dark:bg-slate-800">
                                                        <tr><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest">Column Name</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest">Width(mm)</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest">Product Details</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest text-center">Font Size</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest text-center">B</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest text-center">Left Space</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest text-center">Top Space</th><th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest text-center">Alignment</th><th class="px-4 py-4"></th></tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                        <template x-for="(col, cIndex) in ($store.setup.invoiceLayout.tableColumns || [])" :key="col.id">
                                                            <tr class="hover:bg-slate-50/50 transition-all">
                                                                <td class="px-4 py-3"><input type="text" x-model="col.header" class="w-full bg-transparent border-none text-[10px] font-black focus:ring-0"></td>
                                                                <td class="px-4 py-3"><input type="number" x-model="col.width" class="w-12 mx-auto text-center bg-transparent border-none text-[10px] font-bold focus:ring-0"></td>
                                                                <td class="px-4 py-3"><select x-model="col.source" @change="col.header = col.source.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ')" class="w-full bg-transparent border-none text-[10px] font-bold focus:ring-0"><template x-for="src in $store.setup.tableFieldSources" :key="src"><option :value="src" x-text="src.replace('_', ' ')"></option></template></select></td>
                                                                <td class="px-4 py-3"><input type="number" x-model="col.fontSize" class="w-10 mx-auto text-center bg-transparent border-none text-[10px] font-bold focus:ring-0"></td>
                                                                <td class="px-4 py-3 text-center"><input type="checkbox" x-model="col.bold" class="w-4 h-4 rounded text-blue-600"></td>
                                                                <td class="px-4 py-3"><input type="number" x-model="col.leftSpace" class="w-10 mx-auto text-center bg-slate-50 dark:bg-slate-800/50 rounded-lg text-[10px] border-none focus:ring-0 dark:text-white"></td>
                                                                <td class="px-4 py-3"><input type="number" x-model="col.topSpace" class="w-10 mx-auto text-center bg-slate-50 dark:bg-slate-800/50 rounded-lg text-[10px] border-none focus:ring-0 dark:text-white"></td>
                                                                <td class="px-4 py-3 text-center"><select x-model="col.align" class="bg-transparent border-none text-[10px] font-bold focus:ring-0"><option value="left">LEFT</option><option value="center">CENTER</option><option value="right">RIGHT</option></select></td>
                                                                <td class="px-4 py-3 text-right"><button type="button" @click="$store.setup.removeTableColumn(col.id)" class="text-rose-400 hover:text-rose-600"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- GST Table Architect --}}
                                <div x-show="currentEditSection === 'GST TABLE'" x-transition>
                                    <div class="space-y-8">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">GST Table Font Size</label>
                                            <input type="number" x-model="$store.setup.invoiceLayout.gstTable.fontSize" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-6 py-4 text-xs font-black border-none dark:text-white">
                                        </div>
                                        <div class="space-y-4">
                                            <h5 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Toggle Columns</h5>
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                <template x-for="col in ($store.setup.invoiceLayout.gstTable.columns || [])" :key="col.label">
                                                    <label class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer hover:bg-slate-100 transition-all">
                                                        <input type="checkbox" x-model="col.enabled" class="w-5 h-5 rounded-lg text-blue-600">
                                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-600" x-text="col.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Watermark Architect --}}
                                <div x-show="currentEditSection === 'WATERMARK'" x-transition>
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Watermark Text</label><input type="text" x-model="$store.setup.invoiceLayout.watermark.text" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest border-none dark:text-white"></div>
                                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Opacity</label><input type="range" min="0" max="1" step="0.05" x-model="$store.setup.invoiceLayout.watermark.opacity" class="w-full h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-blue-600"></div>
                                    </div>
                                    <div class="mt-8 space-y-4">
                                        <div class="flex items-center justify-between"><h5 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Document Copies</h5><button type="button" @click="$store.setup.invoiceLayout.watermark.copies.push('NEW COPY')" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all">+ Add Copy</button></div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <template x-for="(copy, index) in ($store.setup.invoiceLayout.watermark.copies || [])" :key="index">
                                                <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl group"><input type="text" x-model="$store.setup.invoiceLayout.watermark.copies[index]" class="flex-1 bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0"><button type="button" @click="$store.setup.invoiceLayout.watermark.copies.splice(index, 1)" class="text-rose-400 opacity-0 group-hover:opacity-100 transition-all"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Architect --}}
                                <div x-show="currentEditSection === 'FOOTER'" x-transition>
                                    <div class="space-y-6">
                                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Declaration / Terms</label><textarea x-model="$store.setup.invoiceLayout.footer.declaration" rows="4" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-6 py-4 text-xs font-bold border-none focus:ring-2 focus:ring-blue-500/20 dark:text-white"></textarea></div>
                                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Signature Label</label><input type="text" x-model="$store.setup.invoiceLayout.footer.signatureLabel" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest border-none dark:text-white"></div>
                                    </div>
                                </div>

                                {{-- Social Media Architect --}}
                                <div x-show="currentEditSection === 'SOCIAL MEDIA'" x-transition>
                                    <div class="space-y-8">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Social Media Font Size</label>
                                            <input type="number" x-model="$store.setup.invoiceLayout.socialMedia.fontSize" class="w-full bg-slate-50 dark:bg-slate-800 rounded-2xl px-6 py-4 text-xs font-black border-none dark:text-white">
                                        </div>
                                        <div class="space-y-4">
                                            <h5 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Platform Details</h5>
                                            <div class="space-y-4">
                                                <template x-for="platform in ($store.setup.invoiceLayout.socialMedia.platforms || [])" :key="platform.type">
                                                    <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800">
                                                        <input type="checkbox" x-model="platform.enabled" class="w-6 h-6 rounded-lg text-blue-600">
                                                        <div class="flex-1 space-y-2">
                                                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1" x-text="platform.type"></label>
                                                            <input type="text" x-model="platform.handle" class="w-full bg-white dark:bg-slate-900 rounded-xl px-4 py-2.5 text-xs font-bold border-none dark:text-white">
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Action Bar -->
                <div class="p-8 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">All changes are reflected in real-time in the left architect preview.</p>
                    <div class="flex gap-4">
                        <button type="button" @click="if(confirm('Discard all unsaved changes?')) { $store.setup.showInvModal = false; location.reload(); }" class="px-8 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Discard</button>
                        <button type="submit" form="settings-form" class="px-10 py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] transition-all">Save Professional Layout</button>
                    </div>
                </div>
    </div>
</div>

{{-- A4 Invoice Stylist Modal --}}
<div x-show="$store.setup.showA4Modal" 
     class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/80 backdrop-blur-md overflow-hidden"
     x-cloak x-transition>
    <div class="bg-white dark:bg-slate-900 w-full h-full flex flex-col overflow-hidden">
        {{-- Modal Header & Format Tabs --}}
        <div class="px-8 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10 shadow-sm">
            <div class="flex items-center gap-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Legacy A4 Architect</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Pixel-perfect print customization</p>
                </div>
                <div class="h-8 w-px bg-slate-100 dark:bg-slate-800"></div>
                <div class="flex bg-slate-50 dark:bg-slate-800/50 p-1 rounded-xl">
                    @foreach(['Default', 'Tally', 'Modern', 'Classic', 'Compact'] as $temp)
                    <button type="button" 
                            @click="$store.setup.a4Layout.format = '{{ strtolower($temp) }}'"
                            :class="$store.setup.a4Layout.format === '{{ strtolower($temp) }}' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                            class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all">
                        {{ $temp }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" form="settings-form" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all">Save Layout</button>
                <button @click="$store.setup.showA4Modal = false" class="p-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            {{-- Left Panel: Live A4 Preview --}}
            <div class="w-[45%] bg-slate-100 dark:bg-slate-950/50 overflow-y-auto p-12 flex justify-center scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800" 
                 x-data="{ isDown: false, startY: 0, scrollTop: 0 }"
                 @mousedown="isDown = true; startY = $event.pageY - $el.offsetTop; scrollTop = $el.scrollTop"
                 @mouseleave="isDown = false"
                 @mouseup="isDown = false"
                 @mousemove="if(!isDown) return; $event.preventDefault(); const y = $event.pageY - $el.offsetTop; const walk = (y - startY) * 2; $el.scrollTop = scrollTop - walk;"
                 :style="isDown ? 'cursor: grabbing;' : 'cursor: grab;'"
                 scrollable="true">
                <div data-darkreader-ignore class="!bg-white shadow-2xl w-full max-w-[210mm] min-h-[297mm] flex flex-col !text-black p-0 transition-all duration-500" 
                     @click="$store.setup.selectedField = null"
                     :class="{
                        'a4-format-tally': $store.setup.a4Layout.format === 'tally',
                        'a4-format-modern': $store.setup.a4Layout.format === 'modern',
                        'a4-format-classic': $store.setup.a4Layout.format === 'classic',
                        'a4-format-compact': $store.setup.a4Layout.format === 'compact',
                        'a4-format-default': $store.setup.a4Layout.format === 'default'
                     }"
                     :style="`padding-top: ${$store.setup.a4Layout.general.pageMarginTop}mm; padding-bottom: ${$store.setup.a4Layout.general.pageMarginBottom}mm; padding-left: ${$store.setup.a4Layout.general.pageMarginLeft}mm; padding-right: ${$store.setup.a4Layout.general.pageMarginRight}mm; font-family: ${$store.setup.a4Layout.general.fontFamily}, sans-serif; font-size: ${$store.setup.a4Layout.general.fontSize}px; --primary-color: ${$store.setup.a4Layout.general.primaryColor};`"
                     id="a4-preview-content">
                    
                    <style>
                        .a4-format-tally { border: 2px solid #000; }
                        .a4-format-tally .border-b { border-bottom: 2px solid #000 !important; }
                        .a4-format-tally .border-t { border-top: 2px solid #000 !important; }
                        .a4-format-tally th, .a4-format-tally td { border: 1px solid #000 !important; }
                        
                        .a4-format-modern { border-top: 8px solid var(--primary-color, #4f46e5); }
                        .a4-format-modern .border-b { border-bottom: 1px solid #eee !important; }
                        .a4-format-modern th { border: none !important; background: #f8fafc; }
                        
                        .a4-format-classic { font-family: 'Georgia', serif !important; }
                        .a4-format-classic .border-b { border-bottom: 3px double #000 !important; }
                        
                        .a4-format-compact { font-size: 8px !important; }
                        .a4-format-compact .space-y-6 > * + * { margin-top: 2mm !important; }
                    </style>
                    
                    {{-- Preview Header Block --}}
                    <div class="border-b border-black pb-4 mb-4 flex justify-between items-start transition-all duration-500"
                         :class="$store.setup.a4Layout.general.logoOnRight ? 'flex-row-reverse' : 'flex-row'">
                        <div x-show="$store.setup.a4Layout.general.showLogo">
                            <img :src="$store.setup.invSettings.headerUrl" :style="`width: ${$store.setup.a4Layout.general.logoWidth}mm; height: auto`" x-show="$store.setup.invSettings.headerUrl">
                            <div x-show="!$store.setup.invSettings.headerUrl" class="w-20 h-20 bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-300 border-2 border-dashed rounded-lg">LOGO AREA</div>
                        </div>
                        <div :class="$store.setup.a4Layout.general.logoOnRight ? 'text-left' : 'text-right'">
                            <h1 x-text="$store.setup.a4Layout.general.title" class="text-2xl font-black uppercase tracking-tight" :style="`color: ${$store.setup.a4Layout.general.primaryColor}`"></h1>
                            <p x-text="$store.setup.a4Layout.general.subTitle" class="text-[10px] font-bold text-slate-400"></p>
                        </div>
                    </div>

                    {{-- Section Rendering Logic --}}
                    <div class="grid grid-cols-2 gap-8">
                        {{-- Left Column (Company & Bill To) --}}
                        <div class="space-y-6 transition-all duration-500" :class="$store.setup.a4Layout.general.swapColumns ? 'order-last' : 'order-first'">
                            {{-- Company Section --}}
                            <div x-show="$store.setup.a4Layout.company.fields.length > 0" :style="`margin-top: ${$store.setup.a4Layout.company.marginTop}mm`">
                                <h4 x-show="$store.setup.a4Layout.company.showTitle" x-text="$store.setup.a4Layout.company.title" 
                                    class="border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-400 font-black"
                                    :style="`font-size: ${$store.setup.a4Layout.company.fontSize}px; font-weight: ${$store.setup.a4Layout.company.bold ? '900' : '500'}; text-align: ${$store.setup.a4Layout.company.align}`"></h4>
                                <div class="space-y-0.5">
                                    <template x-for="(field, index) in $store.setup.a4Layout.company.fields" :key="index">
                                        <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                             :class="{'ring-2 ring-purple-500 rounded-md': $store.setup.selectedField?.section === 'company' && $store.setup.selectedField?.index === index}"
                                             @click.stop="$store.setup.selectedField = { section: 'company', index: index }"
                                             @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                             @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                             @drop.prevent="$el.classList.remove('border-purple-300'); if($store.setup.draggedItem?.section === 'company') { $store.setup.reorderField('company', $store.setup.draggedItem.index, index); $store.setup.selectedField = { section: 'company', index: index }; $store.setup.draggedItem = null; }">
                                            
                                            <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" class="flex gap-2" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                                <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                                <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                            </div>

                                            <div x-show="$store.setup.selectedField?.section === 'company' && $store.setup.selectedField?.index === index"
                                                 draggable="true"
                                                 @dragstart="$store.setup.draggedItem = { section: 'company', index: index }; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                                 class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                                 title="Drag to move">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                    <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Meta Section --}}
                            <div x-show="$store.setup.a4Layout.invoiceMeta.fields.length > 0" class="text-left">
                                <div class="space-y-1">
                                    <template x-for="(field, index) in $store.setup.a4Layout.invoiceMeta.fields" :key="index">
                                        <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                             :class="{'ring-2 ring-purple-500 rounded-md': $store.setup.selectedField?.section === 'invoiceMeta' && $store.setup.selectedField?.index === index}"
                                             @click.stop="$store.setup.selectedField = { section: 'invoiceMeta', index: index }"
                                             @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                             @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                             @drop.prevent="$el.classList.remove('border-purple-300'); if($store.setup.draggedItem?.section === 'invoiceMeta') { $store.setup.reorderField('invoiceMeta', $store.setup.draggedItem.index, index); $store.setup.selectedField = { section: 'invoiceMeta', index: index }; $store.setup.draggedItem = null; }">
                                            
                                            <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; display: flex; gap: 8px; justify-content: flex-start`" 
                                                 class="flex gap-1" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                                <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                                <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                            </div>

                                            <div x-show="$store.setup.selectedField?.section === 'invoiceMeta' && $store.setup.selectedField?.index === index"
                                                 draggable="true"
                                                 @dragstart="$store.setup.draggedItem = { section: 'invoiceMeta', index: index }; $event.dataTransfer.effectAllowed = 'move';"
                                                 class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                                 title="Drag to move">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                    <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column (Ship To & Meta) --}}
                        <div class="space-y-6 transition-all duration-500" :class="$store.setup.a4Layout.general.swapColumns ? 'order-first' : 'order-last'">
                            {{-- Bill To Section --}}
                            <div x-show="$store.setup.a4Layout.customer1.fields.length > 0" class="text-right">
                                <h4 x-show="$store.setup.a4Layout.customer1.showTitle" x-text="$store.setup.a4Layout.customer1.title" 
                                    class="border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-400 font-black"
                                    :style="`font-size: ${$store.setup.a4Layout.customer1.fontSize}px; font-weight: ${$store.setup.a4Layout.customer1.bold ? '900' : '500'}; text-align: right`"></h4>
                                <div class="space-y-0.5">
                                    <template x-for="(field, index) in $store.setup.a4Layout.customer1.fields" :key="index">
                                        <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                             :class="{'ring-2 ring-purple-500 rounded-md': $store.setup.selectedField?.section === 'customer1' && $store.setup.selectedField?.index === index}"
                                             @click.stop="$store.setup.selectedField = { section: 'customer1', index: index }"
                                             @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                             @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                             @drop.prevent="$el.classList.remove('border-purple-300'); if($store.setup.draggedItem?.section === 'customer1') { $store.setup.reorderField('customer1', $store.setup.draggedItem.index, index); $store.setup.selectedField = { section: 'customer1', index: index }; $store.setup.draggedItem = null; }">
                                            
                                            <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-right: ${field.leftSpace}mm; justify-content: ${field.align === 'left' ? 'flex-start' : (field.align === 'center' ? 'center' : 'flex-end')}`" class="flex gap-2" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                                <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                                <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                            </div>

                                            <div x-show="$store.setup.selectedField?.section === 'customer1' && $store.setup.selectedField?.index === index"
                                                 draggable="true"
                                                 @dragstart="$store.setup.draggedItem = { section: 'customer1', index: index }; $event.dataTransfer.effectAllowed = 'move';"
                                                 class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                                 title="Drag to move">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                    <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Ship To Section --}}
                            <div x-show="$store.setup.a4Layout.customer2.fields.length > 0">
                                <h4 x-show="$store.setup.a4Layout.customer2.showTitle" x-text="$store.setup.a4Layout.customer2.title" 
                                    class="border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-400 font-black"
                                    :style="`font-size: ${$store.setup.a4Layout.customer2.fontSize}px; font-weight: ${$store.setup.a4Layout.customer2.bold ? '900' : '500'}; text-align: ${$store.setup.a4Layout.customer2.align}`"></h4>
                                <div class="space-y-0.5">
                                    <template x-for="(field, index) in $store.setup.a4Layout.customer2.fields" :key="index">
                                        <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                             :class="{'ring-2 ring-purple-500 rounded-md': $store.setup.selectedField?.section === 'customer2' && $store.setup.selectedField?.index === index}"
                                             @click.stop="$store.setup.selectedField = { section: 'customer2', index: index }"
                                             @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                             @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                             @drop.prevent="$el.classList.remove('border-purple-300'); if($store.setup.draggedItem?.section === 'customer2') { $store.setup.reorderField('customer2', $store.setup.draggedItem.index, index); $store.setup.selectedField = { section: 'customer2', index: index }; $store.setup.draggedItem = null; }">
                                            
                                            <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" class="flex gap-2" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                                <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                                <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                            </div>

                                            <div x-show="$store.setup.selectedField?.section === 'customer2' && $store.setup.selectedField?.index === index"
                                                 draggable="true"
                                                 @dragstart="$store.setup.draggedItem = { section: 'customer2', index: index }; $event.dataTransfer.effectAllowed = 'move';"
                                                 class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                                 title="Drag to move">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                    <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Standard Product Table Preview --}}
                    <div class="mt-12" x-show="$store.setup.a4Layout.format !== 'tally'">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr :style="`background-color: ${$store.setup.a4Layout.general.primaryColor}10`">
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <th :style="`width: ${col.width}%; text-align: ${col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px; font-weight: ${$store.setup.a4Layout.productTable.headerBold ? '800' : '400'}`" 
                                            class="border-y border-black py-2 px-1 uppercase tracking-tighter" x-text="col.name"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="i in [1,2,3]">
                                    <tr>
                                        <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                            <td :style="`text-align: ${col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px; height: ${$store.setup.a4Layout.productTable.rowHeight}mm`" class="border-b border-slate-100 px-1 py-2">
                                                <span x-text="$store.setup.getMockValue(col.source)"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Standard Totals Preview (A4) --}}
                    <div class="flex justify-end mt-4" x-show="$store.setup.a4Layout.format !== 'tally'">
                        <div class="w-64 space-y-1">
                            <template x-for="(field, index) in $store.setup.a4Layout.totals.fields" :key="index">
                                <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                     :class="{'ring-2 ring-purple-500 rounded-md': $store.setup.selectedField?.section === 'totals' && $store.setup.selectedField?.index === index}"
                                     @click.stop="$store.setup.selectedField = { section: 'totals', index: index }"
                                     @dragover.prevent="$event.dataTransfer.dropEffect = 'move'; $el.classList.add('border-purple-300')"
                                     @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                     @drop.prevent="$el.classList.remove('border-purple-300'); if($store.setup.draggedItem?.section === 'totals') { $store.setup.reorderField('totals', $store.setup.draggedItem.index, index); $store.setup.selectedField = { section: 'totals', index: index }; $store.setup.draggedItem = null; }">
                                    
                                    <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" 
                                         :class="[field.align === 'left' ? 'flex justify-between' : 'flex gap-2', field.reverse ? 'flex-row-reverse' : 'flex-row']">
                                        <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                        <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                    </div>

                                    <div x-show="$store.setup.selectedField?.section === 'totals' && $store.setup.selectedField?.index === index"
                                         draggable="true"
                                         @dragstart="$store.setup.draggedItem = { section: 'totals', index: index }; $event.dataTransfer.effectAllowed = 'move';"
                                         class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity"
                                         title="Drag to move">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                            <polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line>
                                        </svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Tally Exact Layout Preview --}}
                    <div class="mt-4 mb-4" x-show="$store.setup.a4Layout.format === 'tally'">
                        <table class="w-full border-collapse" style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                            <thead>
                                <tr>
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <th :style="`width: ${col.width}%; text-align: ${col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px; font-weight: ${$store.setup.a4Layout.productTable.headerBold ? '800' : '400'}`" 
                                            class="py-1 px-1 border border-black uppercase tracking-tighter" x-text="col.name"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="i in [1, 2, 3]">
                                    <tr>
                                        <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                            <td :style="`text-align: ${col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px; ${col.source === 'PRODUCT_NAME' ? 'font-weight: bold;' : ''}`" class="border-l border-r border-black align-top px-1 py-1">
                                                <span x-text="$store.setup.getMockValue(col.source)"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                                
                                {{-- Tax Rows under Description --}}
                                <tr>
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <td class="px-1 py-1 border-l border-r border-black align-top" :style="`text-align: ${col.source === 'PRODUCT_NAME' ? 'right' : col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px;`">
                                            <span x-show="col.source === 'PRODUCT_NAME'" class="italic font-bold pr-8">CGST</span>
                                            <span x-show="col.source === 'LINE_TOTAL'">125.00</span>
                                        </td>
                                    </template>
                                </tr>
                                <tr>
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <td class="px-1 py-1 border-l border-r border-black align-top" :style="`text-align: ${col.source === 'PRODUCT_NAME' ? 'right' : col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px;`">
                                            <span x-show="col.source === 'PRODUCT_NAME'" class="italic font-bold pr-8">SGST</span>
                                            <span x-show="col.source === 'LINE_TOTAL'">125.00</span>
                                        </td>
                                    </template>
                                </tr>

                                {{-- Empty Spacing Row --}}
                                <tr>
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <td class="px-1 py-8 border-l border-r border-black"></td>
                                    </template>
                                </tr>

                                {{-- Total Row --}}
                                <tr class="border-t border-black font-bold">
                                    <template x-for="col in $store.setup.a4Layout.productTable.columns.filter(c => c.enabled)">
                                        <td class="px-1 py-1 border-l border-r border-black" :style="`text-align: ${col.source === 'PRODUCT_NAME' ? 'right' : col.align}; font-size: ${$store.setup.a4Layout.productTable.fontSize}px;`">
                                            <span x-show="col.source === 'PRODUCT_NAME'">Total</span>
                                            <span x-show="col.source === 'LINE_TOTAL'">INR 2,250.00</span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="border border-black p-1 flex justify-between mb-0" style="border-top: none;">
                            <div class="text-[10px]">
                                <div>Amount Chargeable (in words)</div>
                                <div class="font-bold">Two Thousand Two Hundred Fifty Only</div>
                            </div>
                            <div class="text-[10px] italic">E. & O.E</div>
                        </div>

                        <table class="w-full border-collapse border border-black mt-0" style="border-top: none;">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[0]?.enabled">HSN/SAC</th>
                                    <th rowspan="2" class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[1]?.enabled">Taxable Value</th>
                                    <th colspan="2" class="py-1 px-1 border-b border-r border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[2]?.enabled || $store.setup.a4Layout.gstTable?.columns?.[3]?.enabled">CGST</th>
                                    <th colspan="2" class="py-1 px-1 border-b border-r border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[4]?.enabled || $store.setup.a4Layout.gstTable?.columns?.[5]?.enabled">SGST</th>
                                    <th colspan="2" class="py-1 px-1 border-b border-r border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[6]?.enabled || $store.setup.a4Layout.gstTable?.columns?.[7]?.enabled">IGST</th>
                                    <th rowspan="2" class="py-1 px-1 border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[8]?.enabled">Total Tax<br>Amount</th>
                                </tr>
                                <tr>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[2]?.enabled">Rate</th>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[3]?.enabled">Amount</th>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[4]?.enabled">Rate</th>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[5]?.enabled">Amount</th>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[6]?.enabled">Rate</th>
                                    <th class="py-1 px-1 border-r border-b border-black text-center font-normal" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[7]?.enabled">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-1 py-1 text-left border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[0]?.enabled">9984</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[1]?.enabled">2,000.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[2]?.enabled">9%</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[3]?.enabled">90.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[4]?.enabled">9%</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[5]?.enabled">90.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[6]?.enabled">18%</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[7]?.enabled">180.00</td>
                                    <td class="px-1 py-1 text-right border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[8]?.enabled">180.00</td>
                                </tr>
                                <tr class="font-bold border-t border-black">
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[0]?.enabled">Total</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[1]?.enabled">2,000.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[2]?.enabled"></td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[3]?.enabled">90.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[4]?.enabled"></td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[5]?.enabled">90.00</td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[6]?.enabled"></td>
                                    <td class="px-1 py-1 text-right border-r border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[7]?.enabled">180.00</td>
                                    <td class="px-1 py-1 text-right border-black" :style="`font-size: ${$store.setup.a4Layout.gstTable?.fontSize || 10}px;`" x-show="$store.setup.a4Layout.gstTable?.columns?.[8]?.enabled">180.00</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="p-1 text-[10px] border border-black mb-6" style="border-top: none;">
                            <span class="opacity-80">Tax Amount (in words) : </span> <span class="font-bold">One Hundred Eighty Only</span>
                        </div>
                    </div>

                    {{-- Footer Block --}}
                    <div class="mt-auto pt-8 border-t border-black">
                        <div class="flex justify-between items-end">
                            <div class="max-w-[60%]">
                                <p class="text-[8px] font-black uppercase text-slate-400 mb-1 tracking-widest">Terms & Conditions</p>
                                <pre x-text="$store.setup.a4Layout.footer.terms" class="whitespace-pre-wrap font-sans text-[9px] text-slate-600 italic"></pre>
                            </div>
                            <div x-show="$store.setup.a4Layout.footer.showSignature" class="text-center w-48">
                                <div class="border-b border-black w-full mb-2 h-12"></div>
                                <p class="text-[8px] font-black uppercase tracking-widest">Authorized Signatory</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel: Configuration Controls --}}
            <div class="w-[55%] bg-slate-50 dark:bg-slate-900 border-l border-slate-100 dark:border-slate-800 overflow-y-auto p-8 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800" scrollable="true" x-data="{ activeTab: 'general' }">
                {{-- Tabs for Settings --}}
                <div class="flex gap-4 border-b border-slate-100 dark:border-slate-800 mb-8 sticky top-0 bg-slate-50 dark:bg-slate-900 z-10 py-2">
                    <template x-for="tab in ['general', 'company', 'customers', 'table', 'footer', 'sample']">
                        <button type="button" @click="activeTab = tab"
                                :class="activeTab === tab ? 'text-blue-600 border-blue-600' : 'text-slate-400 border-transparent'"
                                class="pb-3 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all" x-text="tab"></button>
                    </template>
                </div>

                {{-- General Settings --}}
                <div x-show="activeTab === 'general'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Branding & Titles</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Main Title</label>
                                <input type="text" x-model="$store.setup.a4Layout.general.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500 dark:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Sub Title</label>
                                <input type="text" x-model="$store.setup.a4Layout.general.subTitle" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500 dark:text-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Theme Color</label>
                                <input type="color" x-model="$store.setup.a4Layout.general.primaryColor" class="w-full h-10 bg-slate-100 dark:bg-slate-800 border-none rounded-xl p-1 cursor-pointer dark:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Logo Image</label>
                                <div>
                                    <label for="a4_logo_upload" class="w-full flex items-center justify-center h-10 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 cursor-pointer shadow-sm transition-all">
                                        Choose File
                                    </label>
                                    <input type="file" id="a4_logo_upload" form="settings-form" class="hidden" @change="
                                        $el.name = 'inv_header_img';
                                        if($event.target.files[0]) {
                                            $store.setup.updateLogo('header', $event.target.files[0]);
                                        }
                                    ">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Logo Width (mm)</label>
                                <input type="number" x-model="$store.setup.a4Layout.general.logoWidth" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white" style="height: 40px;">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" x-model="$store.setup.a4Layout.general.showLogo" class="w-5 h-5 rounded-lg text-blue-600">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Show Logo</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Typography Options</h5>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Font Family</label>
                                    <select x-model="$store.setup.a4Layout.general.fontFamily" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                                        <option value="Inter">Inter (Modern Clean)</option>
                                        <option value="Roboto">Roboto (Professional)</option>
                                        <option value="'Courier New', Courier, monospace">Courier New (Typewriter/Classic)</option>
                                        <option value="'Times New Roman', Times, serif">Times New Roman (Traditional)</option>
                                        <option value="Montserrat">Montserrat (Geometric/Bold)</option>
                                        <option value="Outfit">Outfit (Tech/Premium)</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Base Font Size (px)</label>
                                    <input type="number" x-model="$store.setup.a4Layout.general.fontSize" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Advanced Layout Positioning</h5>
                            <div class="grid grid-cols-2 gap-6">
                                <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black uppercase text-slate-900 dark:text-white">Swap Columns</p>
                                        <p class="text-[8px] font-bold text-slate-400">Move Company Info to Right side</p>
                                    </div>
                                    <input type="checkbox" x-model="$store.setup.a4Layout.general.swapColumns" class="w-6 h-6 rounded-lg text-blue-600">
                                </label>
                                <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black uppercase text-slate-900 dark:text-white">Logo on Right</p>
                                        <p class="text-[8px] font-bold text-slate-400">Move Logo to Right side of header</p>
                                    </div>
                                    <input type="checkbox" x-model="$store.setup.a4Layout.general.logoOnRight" class="w-6 h-6 rounded-lg text-blue-600">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Page Dimensions (Margins in mm)</h4>
                        <div class="grid grid-cols-4 gap-4">
                            <template x-for="m in ['Top', 'Bottom', 'Left', 'Right']">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest" x-text="m"></label>
                                    <input type="number" x-model="$store.setup.a4Layout.general['pageMargin' + m]" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Invoice Info (Meta) Architect for A4 --}}
                <div x-show="activeTab === 'general'" class="mt-8 space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Invoice Info Architect (No, Date, etc.)</h4>
                            <button type="button" @click="$store.setup.a4Layout.invoiceMeta.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                    class="text-[9px] font-black uppercase text-blue-600">+ Add Info Field</button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.a4Layout.invoiceMeta.fields" :key="'meta-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.a4Layout.invoiceMeta.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Company Details Tab --}}
                <div x-show="activeTab === 'company'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Header Settings</h4>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.a4Layout.company.showTitle" class="w-4 h-4 rounded text-blue-600">
                                <span class="text-[9px] font-black uppercase tracking-widest">Show Header</span>
                            </label>
                        </div>
                        <input type="text" x-model="$store.setup.a4Layout.company.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                        
                        <div class="grid grid-cols-4 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Font Size</label>
                                <input type="number" x-model="$store.setup.a4Layout.company.fontSize" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Align</label>
                                <select x-model="$store.setup.a4Layout.company.align" 
                                        @change="$store.setup.a4Layout.company.fields.forEach(f => f.align = $event.target.value)"
                                        class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-2 py-2 text-sm font-bold text-slate-900 dark:text-white">
                                    <option value="left">Left</option><option value="center">Center</option><option value="right">Right</option>
                                </select>
                            </div>
                            <div class="space-y-2 pt-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="$store.setup.a4Layout.company.bold" class="w-4 h-4 rounded text-blue-600">
                                    <span class="text-[9px] font-black uppercase tracking-widest">Bold</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Field Architect</h4>
                            <button type="button" @click="$store.setup.a4Layout.company.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                    class="text-[9px] font-black uppercase tracking-widest text-blue-600">+ Add Field</button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.a4Layout.company.fields" :key="'comp-field-' + fIdx">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative group">
                                    <button type="button" @click="$store.setup.a4Layout.company.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg">×</button>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black uppercase text-slate-400">Data Source</label>
                                            <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1.5 text-[10px] font-bold text-slate-900 dark:text-white">
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black uppercase text-slate-400">Custom Label</label>
                                            <input type="text" x-model="field.label" placeholder="e.g. GST No:" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1.5 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1.5 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black uppercase text-slate-400">Font</label>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black uppercase text-slate-400">L-Space</label>
                                            <input type="number" x-model="field.leftSpace" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-end pb-1.5 gap-4">
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="checkbox" x-model="field.bold" class="w-3 h-3 rounded text-blue-600">
                                                <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            </label>
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="checkbox" x-model="field.reverse" class="w-3 h-3 rounded text-blue-600">
                                                <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Customers Tab --}}
                <div x-show="activeTab === 'customers'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    {{-- Customer 1 (Bill To) --}}
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Customer 1 (Bill To)</h4>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.a4Layout.customer1.showTitle" class="w-4 h-4 rounded text-blue-600">
                                <span class="text-[9px] font-black uppercase tracking-widest">Show Header</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" x-model="$store.setup.a4Layout.customer1.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                                <template x-for="a in ['left', 'center', 'right']">
                                    <button type="button" @click="$store.setup.a4Layout.customer1.align = a; $store.setup.a4Layout.customer1.fields.forEach(f => f.align = a)"
                                            :class="$store.setup.a4Layout.customer1.align === a ? 'bg-white dark:bg-slate-900 shadow-sm text-blue-600' : 'text-slate-400'"
                                            class="flex-1 text-[9px] font-black uppercase py-1.5 rounded-lg transition-all" x-text="a"></button>
                                </template>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <p class="text-[9px] font-black uppercase text-slate-400">Fields</p>
                                <button type="button" @click="$store.setup.a4Layout.customer1.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                        class="text-[9px] font-black uppercase text-blue-600">+ Add</button>
                            </div>
                            <template x-for="(field, fIdx) in $store.setup.a4Layout.customer1.fields" :key="'cust1-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.a4Layout.customer1.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Customer 2 (Ship To) --}}
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Customer 2 (Ship To)</h4>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.a4Layout.customer2.showTitle" class="w-4 h-4 rounded text-blue-600">
                                <span class="text-[9px] font-black uppercase tracking-widest">Show Header</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" x-model="$store.setup.a4Layout.customer2.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                                <template x-for="a in ['left', 'center', 'right']">
                                    <button type="button" @click="$store.setup.a4Layout.customer2.align = a; $store.setup.a4Layout.customer2.fields.forEach(f => f.align = a)"
                                            :class="$store.setup.a4Layout.customer2.align === a ? 'bg-white dark:bg-slate-900 shadow-sm text-blue-600' : 'text-slate-400'"
                                            class="flex-1 text-[9px] font-black uppercase py-1.5 rounded-lg transition-all" x-text="a"></button>
                                </template>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.a4Layout.customer2.fields">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.a4Layout.customer2.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="$store.setup.a4Layout.customer2.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                    class="w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase text-slate-400 hover:border-blue-500 hover:text-blue-600 transition-all">+ Add Field to Ship To</button>
                        </div>
                    </div>
                </div>

                {{-- Product Table Tab --}}
                <div x-show="activeTab === 'table'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Table Style</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Font Size</label>
                                <input type="number" x-model="$store.setup.a4Layout.productTable.fontSize" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Row Height (mm)</label>
                                <input type="number" x-model="$store.setup.a4Layout.productTable.rowHeight" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white">
                            </div>
                            <div class="space-y-4 pt-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="$store.setup.a4Layout.productTable.headerBold" class="w-4 h-4 rounded text-blue-600">
                                    <span class="text-[9px] font-black uppercase tracking-widest">Bold Header</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Column Configuration</h4>
                            <button type="button" @click="$store.setup.a4Layout.productTable.columns.push({ source: 'PRODUCT_NAME', name: 'New Column', width: 10, align: 'left', enabled: true })"
                                    class="text-[9px] font-black uppercase text-blue-600">+ Add Column</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(col, cIdx) in $store.setup.a4Layout.productTable.columns" :key="'a4-col-' + cIdx">
                                <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl relative group">
                                    <button type="button" @click="$store.setup.a4Layout.productTable.columns.splice(cIdx, 1)" class="absolute -top-2 -right-2 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10 text-xs font-bold leading-none">×</button>
                                    <input type="checkbox" x-model="col.enabled" class="w-5 h-5 rounded text-blue-600">
                                    <div class="flex-1 space-y-2">
                                        <input type="text" x-model="col.name" class="w-full bg-transparent border-none p-0 text-[11px] font-black uppercase text-slate-900 dark:text-white" placeholder="Column Name">
                                        <select x-model="col.source" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[9px] font-bold text-slate-900 dark:text-white">
                                            <template x-for="s in $store.setup.tableFieldSources"><option :value="s" x-text="s.replace('_', ' ')"></option></template>
                                        </select>
                                    </div>
                                    <div class="w-20">
                                        <label class="text-[8px] font-black uppercase text-slate-400 block mb-1">Width %</label>
                                        <input type="number" x-model="col.width" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-center text-slate-900 dark:text-white">
                                    </div>
                                    <div class="w-16">
                                        <label class="text-[8px] font-black uppercase text-slate-400 block mb-1">Align</label>
                                        <select x-model="col.align" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-1 py-1 text-[8px] font-bold uppercase text-slate-900 dark:text-white">
                                            <option value="left">L</option><option value="center">C</option><option value="right">R</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] space-y-6" x-show="$store.setup.a4Layout.format !== 'tally'">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Totals Architect (Subtotal, GST, etc.)</h4>
                            <button type="button" @click="$store.setup.a4Layout.totals.fields.push({ source: '', label: '', fontSize: 10, bold: true, leftSpace: 0, customText: '', align: 'right', reverse: false })"
                                    class="text-[9px] font-black uppercase text-blue-600">+ Add Total Field</button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.a4Layout.totals.fields" :key="'a4-total-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.a4Layout.totals.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- GST Table Architect (Tally Only) --}}
                    <div class="glass-card p-6 rounded-[2rem] space-y-6" x-show="$store.setup.a4Layout.format === 'tally'">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">GST Tax Table Architect</h4>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Font Size</span>
                                <input type="number" x-model="$store.setup.a4Layout.gstTable.fontSize" class="w-24 bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white">
                            </div>
                            
                            <div class="space-y-3">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Toggle Tax Columns</span>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <template x-for="col in ($store.setup.a4Layout.gstTable?.columns || [])" :key="col.label">
                                        <label class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 cursor-pointer hover:bg-slate-50">
                                            <input type="checkbox" x-model="col.enabled" class="w-4 h-4 rounded text-blue-600">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-300" x-text="col.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Tab --}}
                <div x-show="activeTab === 'footer'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Terms & Conditions</h4>
                        <textarea x-model="$store.setup.a4Layout.footer.terms" rows="6" 
                                  class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-[1.5rem] px-6 py-4 text-xs font-medium focus:ring-2 focus:ring-blue-500 dark:text-white"
                                  placeholder="Enter your business terms..."></textarea>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Additional Details</h4>
                        <div class="grid grid-cols-2 gap-6">
                            <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer">
                                <span class="text-[10px] font-black uppercase text-slate-600">Show Signature</span>
                                <input type="checkbox" x-model="$store.setup.a4Layout.footer.showSignature" class="w-5 h-5 rounded-lg text-blue-600">
                            </label>
                            <label class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer">
                                <span class="text-[10px] font-black uppercase text-slate-600">Bank Details</span>
                                <input type="checkbox" x-model="$store.setup.a4Layout.footer.showBankDetails" class="w-5 h-5 rounded-lg text-blue-600">
                            </label>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Footer Font Size</label>
                            <input type="number" x-model="$store.setup.a4Layout.footer.fontSize" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Sample Data Tab (A4) --}}
                <div x-show="activeTab === 'sample'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Sample Data Editor</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Edit these values to see how your invoice looks with real data. These values are only for preview purposes.</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(val, key) in $store.setup.mockData" :key="'mock-a4-' + key">
                                <div class="space-y-1.5">
                                    <label class="text-[8px] font-black uppercase text-slate-400 tracking-widest" x-text="key.replace(/_/g, ' ')"></label>
                                    <input type="text" x-model="$store.setup.mockData[key]" 
                                           class="w-full bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-xl px-3 py-1.5 text-[10px] font-bold focus:ring-2 focus:ring-blue-500 dark:text-white">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PO Invoice Edit Modal --}}
<div x-show="$store.setup.showPOModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/80 backdrop-blur-md overflow-hidden"
     x-cloak x-transition>
    <div class="bg-white dark:bg-slate-900 w-full h-full flex flex-col overflow-hidden">
        {{-- Modal Header & Format Tabs --}}
        <div class="px-8 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10 shadow-sm">
            <div class="flex items-center gap-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Purchase Order Architect</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Custom Purchase Order Layout Builder</p>
                </div>
                <div class="h-8 w-px bg-slate-100 dark:bg-slate-800"></div>
                <div class="flex bg-slate-50 dark:bg-slate-800/50 p-1 rounded-xl">
                    @foreach(['Default', 'Modern', 'Classic', 'Compact'] as $temp)
                    <button type="button" 
                            @click="$store.setup.poLayout.format = '{{ strtolower($temp) }}'"
                            :class="$store.setup.poLayout.format === '{{ strtolower($temp) }}' ? 'bg-white dark:bg-slate-700 text-sky-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                            class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all">
                        {{ $temp }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" form="settings-form" class="px-8 py-2.5 bg-sky-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-sky-700 shadow-xl shadow-sky-500/20 transition-all">Save Purchase Order Layout</button>
                <button @click="$store.setup.showPOModal = false" class="p-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            {{-- Left Panel: Live PO Preview --}}
            <div class="w-[45%] bg-slate-100 dark:bg-slate-950/50 overflow-y-auto p-12 flex justify-center scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800" 
                 x-data="{ isDown: false, startY: 0, scrollTop: 0 }"
                 @mousedown="isDown = true; startY = $event.pageY - $el.offsetTop; scrollTop = $el.scrollTop"
                 @mouseleave="isDown = false"
                 @mouseup="isDown = false"
                 @mousemove="if(!isDown) return; $event.preventDefault(); const y = $event.pageY - $el.offsetTop; const walk = (y - startY) * 2; $el.scrollTop = scrollTop - walk;"
                 :style="isDown ? 'cursor: grabbing;' : 'cursor: grab;'"
                 scrollable="true">
                <div class="!bg-white shadow-2xl w-full max-w-[210mm] min-h-[297mm] flex flex-col !text-black p-0 transition-all duration-500" 
                     :class="{
                        'po-format-modern': $store.setup.poLayout.format === 'modern',
                        'po-format-classic': $store.setup.poLayout.format === 'classic',
                        'po-format-compact': $store.setup.poLayout.format === 'compact',
                        'po-format-default': $store.setup.poLayout.format === 'default'
                     }"
                     :style="`padding-top: ${$store.setup.poLayout.general.pageMarginTop}mm; padding-bottom: ${$store.setup.poLayout.general.pageMarginBottom}mm; padding-left: ${$store.setup.poLayout.general.pageMarginLeft}mm; padding-right: ${$store.setup.poLayout.general.pageMarginRight}mm; font-family: ${$store.setup.poLayout.general.fontFamily}, sans-serif; font-size: ${$store.setup.poLayout.general.fontSize}px; --po-primary: ${$store.setup.poLayout.general.primaryColor};`"
                     id="po-preview-content">
                    
                    <style>
                        .po-format-modern { border-top: 8px solid var(--po-primary, #0ea5e9); }
                        .po-format-classic { font-family: 'Georgia', serif !important; }
                        .po-format-compact { font-size: 8px !important; }
                    </style>

                    {{-- Preview Header Block --}}
                    <div class="border-b border-black pb-4 mb-4 flex justify-between items-start">
                        <div x-show="$store.setup.poLayout.general.showLogo">
                            <img :src="$store.setup.invSettings.headerUrl" :style="`width: ${$store.setup.poLayout.general.logoWidth}mm; height: auto`" x-show="$store.setup.invSettings.headerUrl">
                            <div x-show="!$store.setup.invSettings.headerUrl" class="w-20 h-20 bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-300 border-2 border-dashed rounded-lg">LOGO AREA</div>
                        </div>
                        <div class="text-right">
                            <h1 x-text="$store.setup.poLayout.general.title" class="text-2xl font-black uppercase tracking-tight" :style="`color: ${$store.setup.poLayout.general.primaryColor}`"></h1>
                            <p x-text="$store.setup.poLayout.general.subTitle" class="text-[10px] font-bold text-slate-400"></p>
                        </div>
                    </div>

                    {{-- Section Rendering Logic --}}
                    <div class="grid grid-cols-2 gap-8">
                        {{-- Company & Vendor Column --}}
                        <div class="space-y-6">
                            {{-- Ship To (Business) Section --}}
                            <div x-show="$store.setup.poLayout.company.fields.length > 0" :style="`margin-top: ${$store.setup.poLayout.company.marginTop}mm`">
                                <h4 x-show="$store.setup.poLayout.company.showTitle" x-text="$store.setup.poLayout.company.title" 
                                    class="border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-400 font-black"
                                    :style="`font-size: ${$store.setup.poLayout.company.fontSize}px; font-weight: ${$store.setup.poLayout.company.bold ? '900' : '500'}`"></h4>
                                <div class="space-y-0.5">
                                    <template x-for="field in $store.setup.poLayout.company.fields">
                                        <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" class="flex gap-2" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                            <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                            <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Vendor Section --}}
                            <div x-show="$store.setup.poLayout.vendor.fields.length > 0">
                                <h4 x-show="$store.setup.poLayout.vendor.showTitle" x-text="$store.setup.poLayout.vendor.title" 
                                    class="border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-400 font-black"
                                    :style="`font-size: ${$store.setup.poLayout.vendor.fontSize}px; font-weight: ${$store.setup.poLayout.vendor.bold ? '900' : '500'}`"></h4>
                                <div class="space-y-0.5">
                                    <template x-for="field in $store.setup.poLayout.vendor.fields">
                                        <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" class="flex gap-2" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                            <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                            <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Meta Column --}}
                        <div class="space-y-6">
                            <div x-show="$store.setup.poLayout.invoiceMeta.fields.length > 0" class="text-right">
                                <div class="space-y-1">
                                    <template x-for="field in $store.setup.poLayout.invoiceMeta.fields">
                                        <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" 
                                             class="flex gap-1" :class="field.reverse ? 'flex-row-reverse' : 'flex-row'">
                                            <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
<span x-show="field.label && field.separator" x-text="field.separator" class="opacity-30"></span>
                                            <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Table Preview --}}
                    <div class="mt-12">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr :style="`background-color: ${$store.setup.poLayout.general.primaryColor}10`">
                                    <template x-for="col in $store.setup.poLayout.productTable.columns.filter(c => c.enabled)">
                                        <th :style="`width: ${col.width}%; text-align: ${col.align}; font-size: ${$store.setup.poLayout.productTable.fontSize}px; font-weight: ${$store.setup.poLayout.productTable.headerBold ? '800' : '400'}`" 
                                            class="border-y border-black py-2 px-1 uppercase tracking-tighter" x-text="col.name"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="i in [1,2,3]">
                                    <tr>
                                        <template x-for="col in $store.setup.poLayout.productTable.columns.filter(c => c.enabled)">
                                            <td :style="`text-align: ${col.align}; font-size: ${$store.setup.poLayout.productTable.fontSize}px; height: ${$store.setup.poLayout.productTable.rowHeight}mm`" class="border-b border-slate-100 px-1 py-2">
                                                <span x-text="$store.setup.getMockValue(col.source)"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals Preview (PO) --}}
                    <div class="flex justify-end mt-4">
                        <div class="w-64 space-y-1">
                            <template x-for="field in $store.setup.poLayout.totals.fields">
                                <div :style="`font-size: ${field.fontSize}px; font-weight: ${field.bold ? '700' : '400'}; padding-left: ${field.leftSpace}mm; justify-content: ${field.align === 'right' ? 'flex-end' : (field.align === 'center' ? 'center' : 'flex-start')}`" 
                                     :class="[field.align === 'left' ? 'flex justify-between' : 'flex gap-2', field.reverse ? 'flex-row-reverse' : 'flex-row']">
                                    <span x-show="field.label" x-text="field.label" class="opacity-50"></span>
                                    <span x-text="$store.setup.getMockValue(field.source, field.customText)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer Block --}}
                    <div class="mt-auto pt-8 border-t border-black">
                        <div class="flex justify-between items-end">
                            <div class="max-w-[60%]">
                                <p class="text-[8px] font-black uppercase text-slate-400 mb-1 tracking-widest">Special Instructions</p>
                                <pre x-text="$store.setup.poLayout.footer.terms" class="whitespace-pre-wrap font-sans text-[9px] text-slate-600 italic"></pre>
                            </div>
                            <div x-show="$store.setup.poLayout.footer.showSignature" class="text-center w-48">
                                <div class="border-b border-black w-full mb-2 h-12"></div>
                                <p class="text-[8px] font-black uppercase tracking-widest">Authorized Signature</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel: Configuration Controls --}}
            <div class="w-[55%] bg-slate-50 dark:bg-slate-900 border-l border-slate-100 dark:border-slate-800 overflow-y-auto p-8 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800" scrollable="true" x-data="{ activeTab: 'general' }">
                <div class="flex gap-4 border-b border-slate-100 dark:border-slate-800 mb-8 sticky top-0 bg-slate-50 dark:bg-slate-900 z-10 py-2">
                    <template x-for="tab in ['general', 'ship-to', 'vendor', 'table', 'footer', 'sample']">
                        <button type="button" @click="activeTab = tab"
                                :class="activeTab === tab ? 'text-sky-600 border-sky-600' : 'text-slate-400 border-transparent'"
                                class="pb-3 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all" x-text="tab"></button>
                    </template>
                </div>

                {{-- General --}}
                <div x-show="activeTab === 'general'" class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Purchase Order Title</label>
                                <input type="text" x-model="$store.setup.poLayout.general.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Primary Color</label>
                                <input type="color" x-model="$store.setup.poLayout.general.primaryColor" class="w-full h-10 bg-slate-100 dark:bg-slate-800 border-none rounded-xl p-1 cursor-pointer dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ship To --}}
                <div x-show="activeTab === 'ship-to'" class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <input type="text" x-model="$store.setup.poLayout.company.title" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.poLayout.company.fields" :key="'po-comp-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.poLayout.company.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label (e.g. Phone:)" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="$store.setup.poLayout.company.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                    class="w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase text-slate-400 hover:text-sky-600 hover:border-sky-500 transition-all">+ Add Ship To Field</button>
                        </div>

                        {{-- PO Invoice Info (Meta) Architect --}}
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Purchase Order Info (No, Date, etc.)</h4>
                                <button type="button" @click="$store.setup.poLayout.invoiceMeta.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                        class="text-[9px] font-black uppercase text-sky-600">+ Add Info Field</button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(field, fIdx) in $store.setup.poLayout.invoiceMeta.fields" :key="'po-meta-field-' + fIdx">
                                    <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                        <button type="button" @click="$store.setup.poLayout.invoiceMeta.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                        <div class="col-span-2 space-y-2">
                                            <select x-model="field.source" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                                <option value="">Select Field</option>
                                                <template x-for="item in $store.setup.fieldSources.flatMap(c => [{isCat: true, name: c.category}, ...c.fields.map(f => ({isCat: false, name: f}))])"><option :value="item.isCat ? '' : item.name" :disabled="item.isCat" x-text="item.isCat ? '── ' + item.name + ' ──' : item.name.replace(/_/g, ' ')" :class="item.isCat ? 'font-black text-slate-400 bg-slate-50' : 'pl-2'"></option></template>
                                            </select>
                                            <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                            <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                                <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                                <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[8px] font-black uppercase text-slate-400">L-Sp</span>
                                                <input type="number" x-model="field.leftSpace" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                            </div>
                                        </div>
                                        <div class="flex flex-col justify-center items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-sky-600">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vendor --}}
                <div x-show="activeTab === 'vendor'" class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Vendor Details Architect</h4>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.poLayout.vendor.fields" :key="'po-vendor-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.poLayout.vendor.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                            <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-blue-600">
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Rev</span>
                                            <input type="checkbox" x-model="field.reverse" class="w-4 h-4 rounded text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="$store.setup.poLayout.vendor.fields.push({ source: '', label: '', fontSize: 9, bold: false, leftSpace: 0, customText: '', align: 'left', reverse: false })"
                                    class="w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase text-slate-400 hover:text-sky-600 hover:border-sky-500 transition-all">+ Add Vendor Field</button>
                        </div>
                    </div>
                </div>


                {{-- Table --}}
                <div x-show="activeTab === 'table'" class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Column Configuration</h4>
                            <button type="button" @click="$store.setup.poLayout.productTable.columns.push({ source: 'PRODUCT_NAME', name: 'New Column', width: 10, align: 'left', enabled: true })"
                                    class="text-[9px] font-black uppercase text-sky-600">+ Add Column</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(col, cIdx) in $store.setup.poLayout.productTable.columns" :key="'po-col-' + cIdx">
                                <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl relative group">
                                    <button type="button" @click="$store.setup.poLayout.productTable.columns.splice(cIdx, 1)" class="absolute -top-2 -right-2 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10 text-xs font-bold leading-none">×</button>
                                    <input type="checkbox" x-model="col.enabled" class="w-5 h-5 rounded text-sky-600">
                                    <div class="flex-1 space-y-1">
                                        <input type="text" x-model="col.name" class="w-full bg-transparent border-none p-0 text-[11px] font-black uppercase text-slate-900 dark:text-white">
                                    <select x-model="col.source" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[9px] font-bold text-slate-900 dark:text-white">
                                        <template x-for="s in $store.setup.tableFieldSources"><option :value="s" x-text="s.replace('_', ' ')"></option></template>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <input type="number" x-model="col.width" class="w-full bg-white dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-center text-slate-900 dark:text-white">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Totals Architect (PO) --}}
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Totals Architect (Subtotal, GST, etc.)</h4>
                            <button type="button" @click="$store.setup.poLayout.totals.fields.push({ source: '', label: '', fontSize: 10, bold: true, leftSpace: 0, customText: '', align: 'right', reverse: false })"
                                    class="text-[9px] font-black uppercase text-sky-600">+ Add Total Field</button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(field, fIdx) in $store.setup.poLayout.totals.fields" :key="'po-total-field-' + fIdx">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 grid grid-cols-4 gap-3 relative group">
                                    <button type="button" @click="$store.setup.poLayout.totals.fields.splice(fIdx, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-lg z-10">×</button>
                                    <div class="col-span-2 space-y-2">
                                        <select x-model="field.source" x-html="$store.setup.getFieldOptionsHTML()" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </select>
                                        <input type="text" x-model="field.label" placeholder="Label" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[10px] font-bold text-slate-900 dark:text-white">
                                        <div x-show="field.source === 'CUSTOM_TEXT'" class="mt-2">
                                            <textarea x-model="field.customText" placeholder="Enter manual text here..." class="w-full bg-white dark:bg-slate-900 border-none rounded-xl px-3 py-2 text-[9px] font-bold h-12 outline-none resize-none text-slate-900 dark:text-white"></textarea>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Size</span>
                                            <input type="number" x-model="field.fontSize" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-2 py-1 text-[10px] font-bold text-slate-900 dark:text-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black uppercase text-slate-400">Align</span>
                                            <div class="flex bg-slate-50 dark:bg-slate-900 p-0.5 rounded-lg w-full">
                                                <template x-for="a in ['left', 'center', 'right']">
                                                    <button type="button" @click="field.align = a" 
                                                            :class="field.align === a ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600' : 'text-slate-400'"
                                                            class="flex-1 text-[8px] font-black uppercase py-0.5 rounded-md transition-all" x-text="a[0]"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center items-center gap-2">
                                        <span class="text-[8px] font-black uppercase text-slate-400">Bold</span>
                                        <input type="checkbox" x-model="field.bold" class="w-5 h-5 rounded text-sky-600">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div x-show="activeTab === 'footer'" class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Special Instructions</h4>
                        <textarea x-model="$store.setup.poLayout.footer.terms" rows="5" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-xs font-medium focus:ring-2 focus:ring-sky-500 dark:text-white"></textarea>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.poLayout.footer.showSignature" class="w-5 h-5 rounded-lg text-sky-600">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Show Authorized Signature</span>
                        </label>
                    </div>
                </div>

                {{-- Sample Data Tab (PO) --}}
                <div x-show="activeTab === 'sample'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="glass-card p-6 rounded-[2rem] space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Sample Data Editor</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Edit these values to see how your Purchase Order looks with real data. These values are only for preview purposes.</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(val, key) in $store.setup.mockData" :key="'mock-po-' + key">
                                <div class="space-y-1.5">
                                    <label class="text-[8px] font-black uppercase text-slate-400 tracking-widest" x-text="key.replace(/_/g, ' ')"></label>
                                    <input type="text" x-model="$store.setup.mockData[key]" 
                                           class="w-full bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-xl px-3 py-1.5 text-[10px] font-bold focus:ring-2 focus:ring-sky-500 dark:text-white">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Branch Details Modal --}}
<div x-show="$store.setup.showBranchModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-4xl rounded-[3rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Branch Details</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Configure your primary store information</p>
            </div>
            <button @click="$store.setup.showBranchModal = false" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-2xl transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-8 space-y-6 overflow-y-auto custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Store / Branch Name*</label>
                    <input type="text" name="branch_name" value="{{ $data['branch_name'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Branch Phone*</label>
                    <input type="text" name="branch_phone" value="{{ $data['branch_phone'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Pincode</label>
                    <input type="text" name="branch_pincode" value="{{ $data['branch_pincode'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">City</label>
                    <input type="text" name="branch_city" value="{{ $data['branch_city'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">District</label>
                    <input type="text" name="branch_district" value="{{ $data['branch_district'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">State</label>
                    <input type="text" name="branch_state" value="{{ $data['branch_state'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">State Code</label>
                    <input type="text" name="branch_state_code" value="{{ $data['branch_state_code'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">GST State Code</label>
                    <input type="text" name="branch_gst_state_code" value="{{ $data['branch_gst_state_code'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>

                <div class="space-y-2 md:col-span-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Branch Location</label>
                    <input type="text" name="branch_location" value="{{ $data['branch_location'] ?? '' }}" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                </div>

                <div class="space-y-2 md:col-span-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Branch Address*</label>
                    <textarea name="branch_address" class="w-full h-24 px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none resize-none focus:ring-2 focus:ring-blue-600 dark:text-white">{{ $data['branch_address'] ?? '' }}</textarea>
                </div>

                <div class="md:col-span-3">
                    <label class="flex items-center gap-4 p-4 bg-blue-50 dark:bg-slate-800/50 rounded-[1.5rem] cursor-pointer group hover:bg-blue-100 dark:hover:bg-slate-800 transition-all border border-blue-100/50 dark:border-slate-700">
                        <div class="relative">
                            <input type="hidden" name="branch_size_enabled" value="0">
                            <input type="checkbox" name="branch_size_enabled" value="1" {{ ($data['branch_size_enabled'] ?? '') == '1' ? 'checked' : '' }} class="peer hidden">
                            <div class="w-6 h-6 border-2 border-blue-200 dark:border-slate-700 rounded-lg peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all flex items-center justify-center">
                                <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-blue-900 dark:text-blue-400 uppercase tracking-wider">Enable Size Feature</span>
                            <span class="text-[10px] font-bold text-blue-600/60 dark:text-slate-500 uppercase tracking-widest">Enable sizes (S, M, L, etc.) for products in this branch</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="p-8 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 text-right">
            <button type="submit" form="settings-form" class="px-10 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all">Update Branch</button>
        </div>
    </div>
</div>


{{-- Customize Barcode Number Modal --}}
<div x-show="$store.setup.showBarNumModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-xl rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[75vh]">
        <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Customize Barcode Number</h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Build your barcode number structure</p>
            </div>
            <button @click="$store.setup.showBarNumModal = false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 py-4 space-y-4 overflow-y-auto flex-1">
            
            {{-- Default Barcode Position --}}
            <div class="flex items-center gap-3">
                <label class="text-[9px] font-black text-slate-400 uppercase">Default Barcode Position:</label>
                <select x-model="$store.setup.barcodePosition" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                    <option value="Front">Front</option>
                    <option value="Back">Back</option>
                </select>
            </div>

            {{-- Info Box + Preview --}}
            <div class="glass-card px-4 py-3 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700">
                <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 leading-relaxed">
                    Your selected barcode number now includes the default barcode, Branch ID, Purchase ID, and Line Item after selection.
                </p>
                <div class="text-center">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Preview Barcode</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white tracking-[0.3em]" x-text="$store.setup.barcodePreview"></p>
                </div>
            </div>

            {{-- Dynamic Rows Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b-2 border-slate-100 dark:border-slate-700">
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Select Name</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">No. of Chars</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Spl Char Position</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Spl Char</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in $store.setup.barcodeRows" :key="index">
                            <tr class="border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-2 px-1">
                                    <select x-model="row.name" class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                                        <option value="">Select...</option>
                                        <option value="UNIT">UNIT</option>
                                        <option value="BUYING VALUE">BUYING VALUE</option>
                                        <option value="QUANTITY">QUANTITY</option>
                                        <option value="PROFIT PERCENTAGE">PROFIT %</option>
                                        <option value="PROFIT AMOUNT">PROFIT AMT</option>
                                        <option value="MRP">MRP</option>
                                        <option value="SELLING PRICE">SELLING PRICE</option>
                                    </select>
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" min="1" x-model="row.numChars" class="w-14 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold text-center outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                                </td>
                                <td class="py-2 px-1">
                                    <select x-model="row.specialPos" class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                                        <option value="">Select...</option>
                                        <option value="FRONT">FRONT</option>
                                        <option value="BACK">BACK</option>
                                    </select>
                                </td>
                                <td class="py-2 px-1">
                                    <input type="text" x-model="row.specialChar" maxlength="3" class="w-14 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold text-center outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                                </td>
                                <td class="py-2 px-1">
                                    <button type="button" @click="$store.setup.removeBarcodeRow(index)" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Add Row Button --}}
            <button type="button" @click="$store.setup.addBarcodeRow()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-1.5">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                + ADD
            </button>
        </div>
        <div class="px-5 py-3 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 flex justify-end gap-3">
            <button type="button" @click="$store.setup.showBarNumModal = false" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Close</button>
            <button type="submit" form="settings-form" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-700 shadow-xl shadow-emerald-500/20 transition-all">Save</button>
        </div>
    </div>
</div>

{{-- Advanced Label Architect Modal --}}
<div x-show="$store.setup.showBarLabelModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-[90vw] rounded-[3rem] overflow-hidden shadow-2xl flex flex-col h-[95vh]">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Label Architect</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Professional product label designer with real-time preview</p>
            </div>
            <button @click="$store.setup.showBarLabelModal = false" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-2xl transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-hidden flex">
            <!-- Left Side: Controls -->
            <div class="w-2/3 overflow-y-auto p-8 space-y-8 border-r border-slate-50 dark:border-slate-800">
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-6">
                    <!-- Print Options -->
                    <div class="glass-card p-6 rounded-[2.5rem] space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Print Options</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Print Barcode</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="$store.setup.labelSettings.printBarcode" @change="if($store.setup.labelSettings.printBarcode) $store.setup.labelSettings.printQrCode = false" class="sr-only peer">
                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Print QR Code</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="$store.setup.labelSettings.printQrCode" @change="if($store.setup.labelSettings.printQrCode) $store.setup.labelSettings.printBarcode = false" class="sr-only peer">
                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Select Barcode</label>
                                <select x-model="$store.setup.labelSettings.barcodeType" class="w-20 px-1 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[9px] font-black outline-none dark:text-white">
                                    <option value="">Select...</option>
                                    <option value="ean13">EAN-13</option>
                                    <option value="code128">Code 128</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- QR Configuration -->
                    <div x-show="$store.setup.labelSettings.printQrCode" class="glass-card p-6 rounded-[2.5rem] space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">QR Configuration</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Total Width</label>
                                <input type="number" x-model="$store.setup.labelSettings.totalWidth" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">QR Per Line</label>
                                <input type="number" x-model="$store.setup.labelSettings.qrPerLine" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center text-emerald-600">
                                <label class="text-[10px] font-bold uppercase">Move QR Top</label>
                                <input type="number" step="0.1" x-model="$store.setup.labelSettings.qrTop" class="w-16 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-right text-[10px] font-black outline-none">
                            </div>
                            <div class="flex justify-between items-center text-emerald-600">
                                <label class="text-[10px] font-bold uppercase">Move QR Left</label>
                                <input type="number" step="0.1" x-model="$store.setup.labelSettings.qrLeft" class="w-16 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-right text-[10px] font-black outline-none">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">QR Code Size</label>
                                <input type="number" x-model="$store.setup.labelSettings.qrSize" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Barcode Configuration -->
                    <div x-show="$store.setup.labelSettings.printBarcode" class="glass-card p-6 rounded-[2.5rem] space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Barcode Configuration</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Barcode Per Line</label>
                                <input type="number" x-model="$store.setup.labelSettings.barPerLine" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center text-emerald-600">
                                <label class="text-[10px] font-bold uppercase">Move BarCode Top</label>
                                <input type="number" step="0.1" x-model="$store.setup.labelSettings.barTop" class="w-16 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-right text-[10px] font-black outline-none">
                            </div>
                            <div class="flex justify-between items-center text-emerald-600">
                                <label class="text-[10px] font-bold uppercase">Move BarCode Left</label>
                                <input type="number" step="0.1" x-model="$store.setup.labelSettings.barLeft" class="w-16 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-right text-[10px] font-black outline-none">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Barcode Height</label>
                                <input type="number" x-model="$store.setup.labelSettings.barHeight" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Barcode Width</label>
                                <input type="number" x-model="$store.setup.labelSettings.barWidth" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Barcode Number Size</label>
                                <input type="number" x-model="$store.setup.labelSettings.barNumberSize" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Number Alignment</label>
                                <select x-model="$store.setup.labelSettings.barNumAlign" class="w-16 px-1 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[9px] font-black outline-none text-right dark:text-white">
                                    <option value="Left">Left</option>
                                    <option value="Center">Center</option>
                                    <option value="Right">Right</option>
                                </select>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Number Position</label>
                                <select x-model="$store.setup.labelSettings.barNumPos" class="w-16 px-1 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[9px] font-black outline-none text-right dark:text-white">
                                    <option value="Top">Top</option>
                                    <option value="Bottom">Bottom</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Label Dimensions -->
                    <div class="glass-card p-6 rounded-[2.5rem] space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Dimensions (mm)</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Label Width</label>
                                <input type="number" x-model="$store.setup.labelSettings.width" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Label Height</label>
                                <input type="number" x-model="$store.setup.labelSettings.height" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Inner Space</label>
                                <input type="number" x-model="$store.setup.labelSettings.innerSpace" class="w-16 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-right text-[10px] font-black dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Spacing Configuration -->
                    <div class="glass-card p-6 rounded-[2.5rem] space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Label Spacing</h4>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-slate-400 uppercase">Space Top</label>
                                <input type="number" x-model="$store.setup.labelSettings.spaceTop" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-center text-[10px] font-black dark:text-white">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-slate-400 uppercase">Space Bottom</label>
                                <input type="number" x-model="$store.setup.labelSettings.spaceBottom" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-center text-[10px] font-black dark:text-white">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-slate-400 uppercase">Space Left</label>
                                <input type="number" x-model="$store.setup.labelSettings.spaceLeft" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-center text-[10px] font-black dark:text-white">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-slate-400 uppercase">Space Right</label>
                                <input type="number" x-model="$store.setup.labelSettings.spaceRight" class="w-full px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-center text-[10px] font-black dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Architect Table -->
                <div class="glass-card rounded-[2.5rem] overflow-hidden border border-slate-100 dark:border-slate-800">
                    <table class="w-full text-left text-[10px]">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest">Select Details</th>
                                <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest">Details Input</th>
                                <th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest">Size</th>
                                <th class="px-4 py-4 font-black text-slate-400 uppercase tracking-widest">Bold</th>
                                <th class="px-4 py-4 font-black text-emerald-600 uppercase tracking-widest">Top</th>
                                <th class="px-4 py-4 font-black text-emerald-600 uppercase tracking-widest">Left</th>
                                <th class="px-6 py-4 text-right pr-8 font-black text-slate-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <template x-for="(item, index) in $store.setup.labelDetails" :key="index">
                                <tr>
                                    <td class="px-6 py-3">
                                        <select x-model="item.type" class="w-full bg-transparent font-black text-slate-700 dark:text-slate-200 outline-none">
                                            <option>Store Name</option>
                                            <option>MRP</option>
                                            <option>Product Name</option>
                                            <option>Barcode</option>
                                            <option>Size</option>
                                            <option>Custom Text</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-3">
                                        <input type="text" x-model="item.input" class="w-full bg-transparent font-bold text-slate-500 outline-none" placeholder="Input text...">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.1" x-model="item.size" class="w-12 px-1 py-1 bg-slate-50 dark:bg-slate-800 rounded font-black text-center dark:text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select x-model="item.weight" class="w-12 bg-transparent font-black outline-none">
                                            <option value="400">400</option>
                                            <option value="500">500</option>
                                            <option value="600">600</option>
                                            <option value="700">700</option>
                                            <option value="800">800</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.1" x-model="item.top" class="w-12 px-1 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded font-black text-center text-emerald-600">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.1" x-model="item.left" class="w-12 px-1 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded font-black text-center text-emerald-600">
                                    </td>
                                    <td class="px-6 py-3 text-right pr-8">
                                        <button @click="$store.setup.labelDetails.splice(index, 1)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div class="p-6 bg-slate-50/50 dark:bg-slate-800/50">
                        <button @click="$store.setup.labelDetails.push({type: 'Custom Text', input: '', size: 4.0, weight: 500, top: 0, left: 0})" class="px-6 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all">+ Add Extra Details</button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Preview -->
            <div class="w-1/3 bg-slate-900/5 dark:bg-slate-800/20 p-8 flex flex-col items-center justify-center space-y-8 relative overflow-hidden">
                <div class="absolute top-8 left-8">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Live Architectural Preview</p>
                </div>
                
                <!-- The Label Canvas -->
                <div class="relative bg-white shadow-2xl border border-slate-200 transition-all duration-300 overflow-hidden"
                     :style="`width: ${$store.setup.labelSettings.width * 5}px; height: ${$store.setup.labelSettings.height * 5}px; padding: ${$store.setup.labelSettings.spaceTop * 2}px ${$store.setup.labelSettings.spaceRight * 2}px ${$store.setup.labelSettings.spaceBottom * 2}px ${$store.setup.labelSettings.spaceLeft * 2}px;`"
                >
                    <!-- Simulated QR Code -->
                    <div x-show="$store.setup.labelSettings.printQrCode"
                         @mousedown.prevent="$store.setup.startDrag($event, 'qr')"
                         @wheel.prevent="$store.setup.onWheel($event, 'qr')"
                         class="absolute bg-slate-900 rounded flex items-center justify-center cursor-move hover:ring-2 hover:ring-blue-500 hover:ring-offset-2 z-10 select-none"
                         :class="{ 'transition-all duration-300': !$store.setup.dragging || $store.setup.dragging.type !== 'qr' }"
                         :style="`width: ${$store.setup.labelSettings.qrSize}px; height: ${$store.setup.labelSettings.qrSize}px; top: calc(50% + ${$store.setup.labelSettings.qrTop * 5}px); left: calc(50% + ${$store.setup.labelSettings.qrLeft * 5}px);`"
                    >
                        <svg width="80%" height="80%" viewBox="0 0 24 24" fill="white"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2z"/></svg>
                    </div>

                    <!-- Simulated Barcode -->
                    <div x-show="$store.setup.labelSettings.printBarcode"
                         @mousedown.prevent="$store.setup.startDrag($event, 'barcode')"
                         @wheel.prevent="$store.setup.onWheel($event, 'barcode')"
                         class="absolute flex flex-col items-center cursor-move hover:ring-2 hover:ring-blue-500 hover:ring-offset-2 z-10 select-none"
                         :class="{ 'transition-all duration-300': !$store.setup.dragging || $store.setup.dragging.type !== 'barcode' }"
                         :style="`top: calc(50% + ${$store.setup.labelSettings.barTop * 5}px); left: calc(50% + ${$store.setup.labelSettings.barLeft * 5}px); transform: translate(-50%, -50%);`"
                    >
                        <div x-show="$store.setup.labelSettings.barNumPos === 'Top'"
                             class="text-slate-900 font-black tracking-widest transition-all"
                             :style="`font-size: ${$store.setup.labelSettings.barNumberSize * 1.5}px; text-align: ${$store.setup.labelSettings.barNumAlign.toLowerCase()}; width: 100%;`"
                        >12345</div>
                        <div class="flex items-stretch bg-[repeating-linear-gradient(90deg,transparent,transparent_2px,#0f172a_2px,#0f172a_4px,transparent_4px,transparent_5px,#0f172a_5px,#0f172a_8px)] transition-all duration-300"
                             :style="`height: ${$store.setup.labelSettings.barHeight * 3}px; width: ${$store.setup.labelSettings.barWidth * 100}px;`"
                        ></div>
                        <div x-show="$store.setup.labelSettings.barNumPos === 'Bottom'"
                             class="text-slate-900 font-black tracking-widest transition-all"
                             :style="`font-size: ${$store.setup.labelSettings.barNumberSize * 1.5}px; text-align: ${$store.setup.labelSettings.barNumAlign.toLowerCase()}; width: 100%;`"
                        >12345</div>
                    </div>

                    <!-- Dynamic Details Layer -->
                    <template x-for="(item, index) in $store.setup.labelDetails">
                        <div class="absolute w-full whitespace-nowrap cursor-move hover:text-blue-600 hover:drop-shadow-lg z-10 select-none"
                             @mousedown.prevent="$store.setup.startDrag($event, 'text', index)"
                             @wheel.prevent="$store.setup.onWheel($event, 'text', index)"
                             :class="{ 'transition-all duration-300': !$store.setup.dragging || $store.setup.dragging.type !== 'text' || $store.setup.dragging.index !== index }"
                             :style="`font-size: ${item.size * 2}px; font-weight: ${item.weight}; top: calc(50% + ${item.top * 5}px); left: calc(50% + ${item.left * 5}px); color: #1e293b;`"
                             x-text="item.input"
                        ></div>
                    </template>
                </div>

                <div class="text-center space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dimensions: <span class="text-slate-900 dark:text-white" x-text="`${$store.setup.labelSettings.width} x ${$store.setup.labelSettings.height} mm`"></span></p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic opacity-60">Drag to move • Scroll to resize • Shift+Scroll for width</p>
                </div>
            </div>
        </div>

        <div class="p-8 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Changes are updated in the live architect</p>
            </div>
            <div class="flex gap-4">
                <button type="button" @click="$store.setup.showLabelPreview = true" class="px-10 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Preview</button>
                <button type="submit" form="settings-form" class="px-10 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all">Save and New</button>
            </div>
        </div>
    </div>
</div>

{{-- High Res Preview Modal --}}
<div x-show="$store.setup.showLabelPreview" 
     class="fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
     x-cloak x-transition>
    <div @click.outside="$store.setup.showLabelPreview = false" class="glass-card rounded-[3rem] p-12 flex flex-col items-center justify-center relative max-w-2xl w-full">
        <button @click="$store.setup.showLabelPreview = false" class="absolute top-6 right-6 p-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-2xl transition-all">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-8">Actual Size Print Preview</h3>
        
        <!-- Dynamic Print Page Size -->
        <style x-text="`@media print { @page { size: ${$store.setup.labelSettings.width}mm ${$store.setup.labelSettings.height}mm; margin: 0; } }`"></style>

        <!-- Physical Size Canvas -->
        <div id="printable-label" class="relative bg-white shadow-xl border border-slate-200 overflow-hidden"
             :style="`width: ${$store.setup.labelSettings.width}mm; height: ${$store.setup.labelSettings.height}mm; padding: ${$store.setup.labelSettings.spaceTop}mm ${$store.setup.labelSettings.spaceRight}mm ${$store.setup.labelSettings.spaceBottom}mm ${$store.setup.labelSettings.spaceLeft}mm;`"
        >
            <!-- Simulated QR Code -->
            <div x-show="$store.setup.labelSettings.printQrCode"
                 class="absolute bg-slate-900 rounded flex items-center justify-center"
                 :style="`width: ${$store.setup.labelSettings.qrSize / 5}mm; height: ${$store.setup.labelSettings.qrSize / 5}mm; top: calc(50% + ${$store.setup.labelSettings.qrTop}mm); left: calc(50% + ${$store.setup.labelSettings.qrLeft}mm);`"
            >
                <svg width="80%" height="80%" viewBox="0 0 24 24" fill="white"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2z"/></svg>
            </div>

            <!-- Simulated Barcode -->
            <div x-show="$store.setup.labelSettings.printBarcode"
                 class="absolute flex flex-col items-center"
                 :style="`top: calc(50% + ${$store.setup.labelSettings.barTop}mm); left: calc(50% + ${$store.setup.labelSettings.barLeft}mm); transform: translate(-50%, -50%);`"
            >
                <div x-show="$store.setup.labelSettings.barNumPos === 'Top'"
                     class="text-slate-900 font-black tracking-widest"
                     :style="`font-size: ${($store.setup.labelSettings.barNumberSize * 1.5) / 5}mm; text-align: ${$store.setup.labelSettings.barNumAlign.toLowerCase()}; width: 100%;`"
                >12345</div>
                <div class="flex items-stretch bg-[repeating-linear-gradient(90deg,transparent,transparent_0.4mm,#0f172a_0.4mm,#0f172a_0.8mm,transparent_0.8mm,transparent_1mm,#0f172a_1mm,#0f172a_1.6mm)]"
                     :style="`height: ${($store.setup.labelSettings.barHeight * 3) / 5}mm; width: ${($store.setup.labelSettings.barWidth * 100) / 5}mm;`"
                ></div>
                <div x-show="$store.setup.labelSettings.barNumPos === 'Bottom'"
                     class="text-slate-900 font-black tracking-widest"
                     :style="`font-size: ${($store.setup.labelSettings.barNumberSize * 1.5) / 5}mm; text-align: ${$store.setup.labelSettings.barNumAlign.toLowerCase()}; width: 100%;`"
                >12345</div>
            </div>

            <!-- Dynamic Details Layer -->
            <template x-for="item in $store.setup.labelDetails">
                <div class="absolute w-full whitespace-nowrap"
                     :style="`font-size: ${(item.size * 2) / 5}mm; font-weight: ${item.weight}; top: calc(50% + ${item.top}mm); left: calc(50% + ${item.left}mm); color: #1e293b;`"
                     x-text="item.input"
                ></div>
            </template>
        </div>
        
        <div class="mt-8 flex gap-4">
             <button @click="
                    const label = document.getElementById('printable-label');
                    const placeholder = document.createElement('div');
                    placeholder.id = 'label-placeholder';
                    
                    // Isolate the label to the body
                    label.parentNode.insertBefore(placeholder, label);
                    document.body.appendChild(label);
                    document.body.classList.add('is-printing-label');
                    
                    const restoreState = () => {
                        document.body.classList.remove('is-printing-label');
                        if (document.getElementById('label-placeholder')) {
                            placeholder.parentNode.insertBefore(label, placeholder);
                            placeholder.remove();
                        }
                        window.removeEventListener('afterprint', restoreState);
                    };
                    window.addEventListener('afterprint', restoreState);
                    
                    // Trigger print
                    setTimeout(() => window.print(), 100);
                " class="mt-8 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all tracking-wide text-sm flex items-center gap-2">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    PRINT TEST LABEL
                </button>
        </div>
    </div>
</div>

{{-- Add Invoice Type Modal --}}
<div x-show="$store.setup.showInvTypeModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-5xl rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
        <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Add New Invoice Type</h3>
            </div>
            <button @click="$store.setup.showInvTypeModal = false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 py-4 space-y-4 overflow-y-auto flex-1">
            
            {{-- Existing Invoice Types Header --}}
            <h4 class="text-xs font-black text-slate-900 dark:text-white">Existing Invoice Types</h4>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 w-8">#</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Invoice Type</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Prefix</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1">Format</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Sequence</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Reset</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Sales</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Stock</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Quick</th>
                            <th class="text-[8px] font-black text-slate-400 uppercase tracking-widest py-2 px-1 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(inv, index) in $store.setup.invoiceTypes" :key="index">
                            <tr class="border-b border-slate-50 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-2 px-1 text-[10px] font-bold text-slate-400" x-text="index + 1 + '.'"></td>
                                <td class="py-2 px-1">
                                    <div class="flex items-center gap-1.5">
                                        <span x-text="inv.icon" class="text-sm"></span>
                                        <span class="text-[10px] font-bold text-slate-900 dark:text-white" x-text="inv.name"></span>
                                    </div>
                                </td>
                                <td class="py-2 px-1">
                                    <div class="text-[9px] font-mono font-bold text-slate-600 dark:text-slate-300">
                                        <span>Reg: <span x-text="inv.prefix"></span></span><br>
                                        <span class="text-slate-400">GST: <span x-text="inv.gstPrefix"></span></span>
                                    </div>
                                </td>
                                <td class="py-2 px-1">
                                    <code class="text-[9px] font-mono font-bold text-slate-500 bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded" x-text="inv.format"></code>
                                </td>
                                <td class="py-2 px-1 text-center text-[10px] font-bold text-slate-600 dark:text-slate-300" x-text="inv.sequence + ' digits'"></td>
                                <td class="py-2 px-1 text-center">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full"
                                          :class="inv.reset === 'Daily' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-slate-400'"
                                          x-text="inv.reset"></span>
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <span x-show="inv.sales" class="text-emerald-500">✅</span>
                                    <span x-show="!inv.sales" class="text-rose-400">❌</span>
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <span x-show="inv.stock" class="text-emerald-500">✅</span>
                                    <span x-show="!inv.stock" class="text-rose-400">❌</span>
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <span x-show="inv.quick" class="text-emerald-500">✅</span>
                                    <span x-show="!inv.quick" class="text-rose-400">❌</span>
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="$store.setup.editInvoiceType(index)" class="p-1 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="$store.setup.removeInvoiceType(index)" class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Add / Edit Form --}}
            <div class="glass-card px-4 py-3 rounded-xl border border-slate-100 dark:border-slate-700 space-y-3">
                <h4 class="text-[9px] font-black text-blue-600 uppercase tracking-widest" x-text="$store.setup.editingInvIndex !== null ? '✏️ Edit Invoice Type' : '➕ Add New Invoice Type'"></h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">Invoice Name</label>
                        <input type="text" x-model="$store.setup.newInvType.name" placeholder="e.g. Billing" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">Prefix (Reg)</label>
                        <input type="text" x-model="$store.setup.newInvType.prefix" placeholder="e.g. INV1" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">GST Prefix</label>
                        <input type="text" x-model="$store.setup.newInvType.gstPrefix" placeholder="e.g. INV-G1" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">Format</label>
                        <input type="text" x-model="$store.setup.newInvType.format" placeholder="{PREFIX}.{YY}.{SEQ}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold font-mono outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">Sequence Digits</label>
                        <input type="number" min="1" max="10" x-model="$store.setup.newInvType.sequence" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold text-center outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase">Reset</label>
                        <select x-model="$store.setup.newInvType.reset" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                            <option value="Daily">Daily</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                            <option value="Never">Never</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 pt-4 cursor-pointer">
                        <input type="checkbox" x-model="$store.setup.newInvType.sales" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-[9px] font-bold text-slate-500 uppercase">Sales</span>
                    </label>
                    <label class="flex items-center gap-2 pt-4 cursor-pointer">
                        <input type="checkbox" x-model="$store.setup.newInvType.stock" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-[9px] font-bold text-slate-500 uppercase">Stock</span>
                    </label>
                    <label class="flex items-center gap-2 pt-4 cursor-pointer">
                        <input type="checkbox" x-model="$store.setup.newInvType.quick" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-[9px] font-bold text-slate-500 uppercase">Quick</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="px-5 py-3 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 flex justify-end gap-3">
            <button type="button" @click="$store.setup.addInvoiceType()" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all" x-text="$store.setup.editingInvIndex !== null ? 'Update' : 'Add'"></button>
            <button type="button" @click="$store.setup.showInvTypeModal = false" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Close</button>
        </div>
    </div>
</div>

{{-- Add Order Status Modal --}}
<div x-show="$store.setup.showStatusModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
        <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Add Order Status</h3>
            <button @click="$store.setup.showStatusModal = false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 py-4 space-y-4 overflow-y-auto flex-1">
            
            {{-- Pending Input Rows --}}
            <template x-for="(status, idx) in $store.setup.pendingStatuses" :key="idx">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Name --}}
                    <input type="text" x-model="status.name" :placeholder="'Enter status name ' + (idx + 1)" class="flex-1 min-w-[140px] px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-600 text-slate-900 dark:text-white">
                    
                    {{-- Icon Picker --}}
                    <div class="relative">
                        <button type="button" @click="status.showIconPicker = !status.showIconPicker" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-all min-w-[42px] text-center" x-text="status.icon || 'Icon'">
                        </button>
                        <div x-show="status.showIconPicker" @click.outside="status.showIconPicker = false" class="absolute top-full left-0 mt-1 z-50 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 p-2 grid grid-cols-5 gap-1 w-[180px]">
                            <template x-for="(icon, iIdx) in $store.setup.statusIcons" :key="iIdx">
                                <button type="button" @click="status.icon = icon; status.showIconPicker = false" class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg text-lg transition-all text-center" x-text="icon"></button>
                            </template>
                        </div>
                    </div>
                    
                    {{-- Color Picker --}}
                    <input type="color" x-model="status.color" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer p-0.5">
                    
                    {{-- Checkboxes --}}
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="status.orderCreated" class="w-3.5 h-3.5 text-blue-600 rounded">
                        <span class="text-[8px] font-bold text-slate-500 uppercase">Order Created</span>
                    </label>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="status.invoiceGenerate" class="w-3.5 h-3.5 text-blue-600 rounded">
                        <span class="text-[8px] font-bold text-slate-500 uppercase">Invoice Generate</span>
                    </label>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="status.completed" class="w-3.5 h-3.5 text-blue-600 rounded">
                        <span class="text-[8px] font-bold text-slate-500 uppercase">Completed</span>
                    </label>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="status.rejected" class="w-3.5 h-3.5 text-blue-600 rounded">
                        <span class="text-[8px] font-bold text-slate-500 uppercase">Rejected</span>
                    </label>
                    
                    {{-- Remove Row --}}
                    <button type="button" @click="$store.setup.removePendingStatus(idx)" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>

            {{-- Divider --}}
            <div class="border-t border-slate-100 dark:border-slate-800"></div>

            {{-- Saved Status List --}}
            <div class="space-y-2">
                <template x-for="(s, sIdx) in $store.setup.orderStatuses" :key="sIdx">
                    <div class="flex items-center justify-between py-2 px-1 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 rounded-lg transition-colors">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-slate-400 w-5" x-text="(sIdx + 1) + '.'"></span>
                            <span x-show="s.icon" class="text-sm" x-text="s.icon"></span>
                            <span class="text-[11px] font-bold text-slate-900 dark:text-white" x-text="s.name"></span>
                            <span x-show="s.orderCreated" class="text-[9px] font-bold text-blue-600 ml-1">(Order Created)</span>
                            <span x-show="s.invoiceGenerate" class="text-[9px] font-bold text-emerald-600 ml-1">(Invoice Generate)</span>
                            <span x-show="s.completed" class="text-[9px] font-bold text-purple-600 ml-1">(Completed)</span>
                            <span x-show="s.rejected" class="text-[9px] font-bold text-rose-600 ml-1">(Rejected)</span>
                        </div>
                        <button type="button" @click="$store.setup.removeOrderStatus(sIdx)" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
        <div class="px-5 py-3 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 flex justify-end gap-3">
            <button type="button" @click="$store.setup.addPendingStatus()" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">+ Add</button>
            <button type="button" @click="$store.setup.saveStatuses()" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-700 shadow-xl shadow-emerald-500/20 transition-all">Save</button>
        </div>
    </div>
</div>

{{-- Global Invoice Settings Modal --}}
<div x-show="$store.setup.showInvSettingsModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-6xl rounded-[3rem] overflow-hidden shadow-2xl flex flex-col h-[90vh]">
        <!-- Modal Header -->
        <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Invoice Architect</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Configure global document branding and behavior</p>
            </div>
            <button @click="$store.setup.showInvSettingsModal = false" type="button" class="p-3 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-2xl transition-all">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
            <!-- Left Side: Controls -->
            <div class="w-full md:w-[55%] overflow-y-auto p-8 space-y-8 border-r border-slate-50 dark:border-slate-800" x-data="{ subTab: 'header' }">
                <!-- Sub Tabs Navigation -->
                <div class="flex gap-4 p-1.5 bg-slate-100 dark:bg-slate-800/50 rounded-2xl w-fit">
                    <button type="button" @click="subTab = 'header'" :class="subTab === 'header' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Header</button>
                    <button type="button" @click="subTab = 'footer'" :class="subTab === 'footer' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Footer</button>
                    <button type="button" @click="subTab = 'watermark'" :class="subTab === 'watermark' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Watermark</button>
                </div>

                <!-- Header Tab -->
                <div x-show="subTab === 'header'" class="space-y-8 animate-in fade-in duration-300">
                    <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Header Dimensions</h4>
                            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full" x-text="$store.setup.invSettings.headerHeight + 'mm'"></span>
                        </div>
                        <div class="space-y-4">
                            <input type="range" name="inv_header_height" min="20" max="80" x-model="$store.setup.invSettings.headerHeight" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            <div class="flex justify-between text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                <span>20mm</span>
                                <span>80mm</span>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Header Image</h4>
                        <div class="flex items-start gap-8">
                            <div class="relative group">
                                <div class="w-48 h-32 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden">
                                    <template x-if="$store.setup.invSettings.headerUrl">
                                        <img :src="$store.setup.invSettings.headerUrl" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!$store.setup.invSettings.headerUrl">
                                        <svg width="32" height="32" class="text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </template>
                                </div>
                                <template x-if="$store.setup.invSettings.headerUrl">
                                    <button type="button" @click="$store.setup.invSettings.headerUrl = ''; document.getElementById('header_remove_input').value = '1'" class="absolute -top-2 -right-2 p-1.5 bg-rose-500 text-white rounded-lg shadow-lg hover:bg-rose-600 transition-all">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </template>
                            </div>
                            <div class="flex-1 space-y-4">
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Upload a high-resolution image for your invoice header. Recommended size: 2000x800px.</p>
                                <input type="file" @change="$el.name = 'inv_header_img'; const file = $event.target.files[0]; if(file) $store.setup.invSettings.headerUrl = URL.createObjectURL(file)" class="hidden" id="header_img_input">
                                <label for="header_img_input" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 cursor-pointer transition-all">Upload New Header</label>
                                <input type="hidden" name="remove_inv_header_img" id="header_remove_input" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Tab -->
                <div x-show="subTab === 'footer'" class="space-y-8 animate-in fade-in duration-300">
                    <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Footer Dimensions</h4>
                            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full" x-text="$store.setup.invSettings.footerHeight + 'mm'"></span>
                        </div>
                        <div class="space-y-4">
                            <input type="range" name="inv_footer_height" min="15" max="60" x-model="$store.setup.invSettings.footerHeight" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                            <div class="flex justify-between text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                <span>15mm</span>
                                <span>60mm</span>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Footer Image</h4>
                        <div class="flex items-start gap-8">
                            <div class="relative group">
                                <div class="w-48 h-32 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden">
                                    <template x-if="$store.setup.invSettings.footerUrl">
                                        <img :src="$store.setup.invSettings.footerUrl" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!$store.setup.invSettings.footerUrl">
                                        <svg width="32" height="32" class="text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </template>
                                </div>
                                <template x-if="$store.setup.invSettings.footerUrl">
                                    <button type="button" @click="$store.setup.invSettings.footerUrl = ''; document.getElementById('footer_remove_input').value = '1'" class="absolute -top-2 -right-2 p-1.5 bg-rose-500 text-white rounded-lg shadow-lg hover:bg-rose-600 transition-all">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </template>
                            </div>
                            <div class="flex-1 space-y-4">
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Upload a consistent footer image for all documents. Best for terms, bank details, or contact info.</p>
                                <input type="file" name="inv_footer_img" @change="const file = $event.target.files[0]; if(file) $store.setup.invSettings.footerUrl = URL.createObjectURL(file)" class="hidden" id="footer_img_input">
                                <label for="footer_img_input" class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 cursor-pointer transition-all">Upload New Footer</label>
                                <input type="hidden" name="remove_inv_footer_img" id="footer_remove_input" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Watermark Tab -->
                <div x-show="subTab === 'watermark'" class="space-y-8 animate-in fade-in duration-300">
                    <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Watermark Activation</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="inv_watermark_enabled" value="0">
                                <input type="checkbox" name="inv_watermark_enabled" value="1" {{ ($data['inv_watermark_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 p-1.5 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                            <button type="button" @click="$store.setup.invSettings.watermarkType = 'text'" :class="$store.setup.invSettings.watermarkType === 'text' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-500'" class="py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Text Watermark</button>
                            <button type="button" @click="$store.setup.invSettings.watermarkType = 'image'" :class="$store.setup.invSettings.watermarkType === 'image' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600' : 'text-slate-500'" class="py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Image Watermark</button>
                            <input type="hidden" name="inv_watermark_type" :value="$store.setup.invSettings.watermarkType">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight" x-text="$store.setup.invSettings.watermarkType === 'text' ? 'Watermark Content' : 'Watermark Asset'"></h4>
                            
                            <div x-show="$store.setup.invSettings.watermarkType === 'text'" class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Display Text</label>
                                <input type="text" name="inv_watermark_text" x-model="$store.setup.invSettings.watermarkText" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none ring-1 ring-slate-100 dark:ring-slate-700 focus:ring-2 focus:ring-blue-600 transition-all text-slate-900 dark:text-white">
                            </div>

                            <div x-show="$store.setup.invSettings.watermarkType === 'image'" class="space-y-4">
                                <div class="relative group">
                                    <div class="w-full h-32 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden">
                                        <template x-if="$store.setup.invSettings.watermarkUrl">
                                            <img :src="$store.setup.invSettings.watermarkUrl" class="w-full h-full object-contain">
                                        </template>
                                        <template x-if="!$store.setup.invSettings.watermarkUrl">
                                            <svg width="24" height="24" class="text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </template>
                                    </div>
                                    <input type="file" name="inv_watermark_img" @change="const file = $event.target.files[0]; if(file) $store.setup.invSettings.watermarkUrl = URL.createObjectURL(file)" class="hidden" id="watermark_img_input">
                                    <label for="watermark_img_input" class="absolute inset-0 cursor-pointer"></label>
                                    <template x-if="$store.setup.invSettings.watermarkUrl">
                                        <button type="button" @click="$store.setup.invSettings.watermarkUrl = ''; document.getElementById('watermark_remove_input').value = '1'" class="absolute -top-2 -right-2 p-1.5 bg-rose-500 text-white rounded-lg shadow-lg hover:bg-rose-600 transition-all">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </template>
                                </div>
                                <input type="hidden" name="remove_inv_watermark_img" id="watermark_remove_input" value="0">
                            </div>
                        </div>

                        <div class="glass-card p-8 rounded-[2.5rem] space-y-6">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Styling & Position</h4>
                            
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Position</label>
                                <select name="inv_watermark_pos" x-model="$store.setup.invSettings.watermarkPos" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-bold outline-none ring-1 ring-slate-100 dark:ring-slate-700 dark:text-white">
                                    <option value="center">Center</option>
                                    <option value="top-left">Top Left</option>
                                    <option value="top-right">Top Right</option>
                                    <option value="bottom-left">Bottom Left</option>
                                    <option value="bottom-right">Bottom Right</option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Opacity</label>
                                    <span class="text-[10px] font-black text-blue-600" x-text="$store.setup.invSettings.watermarkOpacity + '%'"></span>
                                </div>
                                <input type="range" name="inv_watermark_opacity" min="0" max="100" x-model="$store.setup.invSettings.watermarkOpacity" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live Architectural Preview -->
            <div class="w-full md:w-[45%] bg-slate-900/5 dark:bg-slate-800/20 p-12 flex flex-col items-center justify-center relative overflow-hidden border-l border-slate-50 dark:border-slate-800">
                <div class="text-center mb-8">
                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-2">Live Architectural Preview</p>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Document Visualization</h4>
                </div>

                <!-- The Invoice Canvas -->
                <div class="w-full max-w-[152px] h-[215px] min-h-[215px] bg-white shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-slate-200 rounded-sm relative overflow-hidden flex flex-col mx-auto transition-all duration-500 hover:shadow-[0_40px_80px_rgba(0,0,0,0.15)]">
                    <!-- Header Preview -->
                    <div class="w-full bg-slate-50 border-b border-slate-100 flex items-center justify-center overflow-hidden transition-all duration-300" 
                         :style="`height: ${$store.setup.invSettings.headerHeight * 0.72}px;`"
                    >
                        <template x-if="$store.setup.invSettings.headerUrl">
                            <img :src="$store.setup.invSettings.headerUrl" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!$store.setup.invSettings.headerUrl">
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">HEADER AREA</span>
                        </template>
                    </div>

                    <!-- Body / Watermark Area -->
                    <div class="flex-1 relative flex items-center justify-center">
                        <!-- Watermark Preview -->
                        <div class="absolute transition-all duration-300 pointer-events-none"
                             :style="`opacity: ${$store.setup.invSettings.watermarkOpacity / 100};`"
                             :class="{
                                'inset-0 flex items-center justify-center': $store.setup.invSettings.watermarkPos === 'center',
                                'top-4 left-4': $store.setup.invSettings.watermarkPos === 'top-left',
                                'top-4 right-4': $store.setup.invSettings.watermarkPos === 'top-right',
                                'bottom-4 left-4': $store.setup.invSettings.watermarkPos === 'bottom-left',
                                'bottom-4 right-4': $store.setup.invSettings.watermarkPos === 'bottom-right'
                             }"
                        >
                            <template x-if="$store.setup.invSettings.watermarkType === 'text'">
                                <span class="text-[20px] font-black text-slate-400 rotate-[-45deg] whitespace-nowrap uppercase tracking-[0.5em] opacity-30" x-text="$store.setup.invSettings.watermarkText || 'TREXOERP'"></span>
                            </template>
                            <template x-if="$store.setup.invSettings.watermarkType === 'image'">
                                <div class="w-32 h-32 flex items-center justify-center">
                                    <template x-if="$store.setup.invSettings.watermarkUrl">
                                        <img :src="$store.setup.invSettings.watermarkUrl" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!$store.setup.invSettings.watermarkUrl">
                                        <div class="w-full h-full bg-slate-100 rounded-full flex items-center justify-center">
                                            <svg width="12" height="12" class="text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Dummy Content -->
                        <div class="w-full p-4 space-y-2 opacity-10">
                            <div class="h-1 bg-slate-400 w-1/3"></div>
                            <div class="h-1 bg-slate-300 w-full"></div>
                            <div class="h-1 bg-slate-300 w-full"></div>
                            <div class="h-1 bg-slate-300 w-2/3"></div>
                            <div class="pt-4 space-y-1">
                                <div class="h-2 bg-slate-400 w-full"></div>
                                <div class="h-2 bg-slate-400 w-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Preview -->
                    <div class="w-full bg-slate-50 border-t border-slate-100 flex items-center justify-center overflow-hidden transition-all duration-300" 
                         :style="`height: ${$store.setup.invSettings.footerHeight * 0.72}px;`"
                    >
                        <template x-if="$store.setup.invSettings.footerUrl">
                            <img :src="$store.setup.invSettings.footerUrl" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!$store.setup.invSettings.footerUrl">
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">FOOTER AREA</span>
                        </template>
                    </div>
                </div>

                <div class="mt-8 text-center space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic opacity-60">Architectural visualization of document rules</p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-8 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-50 dark:border-slate-800 text-right flex justify-between items-center">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic"><span class="text-blue-600 animate-pulse mr-2">●</span>Live architect is active</p>
            <div class="flex gap-4">
                <button @click="$store.setup.showInvSettingsModal = false" type="button" class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                <button type="submit" form="settings-form" class="px-10 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all">Save Architect Changes</button>
            </div>
        </div>
    </div>
</div>

{{-- POS Print Layout Architect Modal --}}
<div x-show="$store.setup.showPOSModal" 
     class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/80 backdrop-blur-md overflow-hidden"
     x-cloak x-transition>
    <div class="bg-white dark:bg-slate-900 w-full h-full flex flex-col overflow-hidden">
        {{-- Modal Header --}}
        <div class="px-8 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10 shadow-sm">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">POS Thermal Print Architect</h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Fully editable thermal receipt layout</p>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" form="settings-form" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all">Save Layout</button>
                <button type="button" @click="$store.setup.showPOSModal = false" class="p-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            {{-- Left Panel: Live POS Preview --}}
            <div class="w-1/2 bg-slate-100 dark:bg-slate-950/50 overflow-y-auto p-12 flex justify-center scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800">
                <div class="!bg-white shadow-2xl flex flex-col !text-black p-4 transition-all duration-500 max-w-sm mx-auto w-full"
                     :style="`font-family: ${$store.setup.posLayout.general.fontFamily}, monospace; font-size: ${$store.setup.posLayout.general.fontSize}px;`">
                    
                    {{-- Logo & Title --}}
                    <div class="text-center mb-4 flex flex-col items-center">
                        <div x-show="$store.setup.posLayout.general.showLogo" class="mb-2">
                            <img :src="$store.setup.invSettings.headerUrl" :style="`width: ${$store.setup.posLayout.general.logoWidth}mm; height: auto`" x-show="$store.setup.invSettings.headerUrl">
                            <div x-show="!$store.setup.invSettings.headerUrl" class="w-16 h-16 bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-300 border border-dashed rounded-lg">LOGO</div>
                        </div>
                        <h2 x-text="$store.setup.mockData.BUSINESS_NAME || 'STORE NAME'" :class="{'font-bold': $store.setup.posLayout.company.bold, 'text-base': true}"></h2>
                        <div x-show="$store.setup.posLayout.company.showPhone" class="mt-1" :style="`font-size: ${$store.setup.posLayout.company.fontSize}px`">
                            Mob: <span x-text="$store.setup.mockData.PHONE || '+1234567890'"></span>
                        </div>
                        <div x-show="$store.setup.posLayout.company.showEmail" class="mt-1" :style="`font-size: ${$store.setup.posLayout.company.fontSize}px`">
                            Email: <span x-text="$store.setup.mockData.EMAIL || 'store@example.com'"></span>
                        </div>
                        <div x-show="$store.setup.posLayout.company.showAddress" class="mt-1 text-center" :style="`font-size: ${$store.setup.posLayout.company.fontSize}px`">
                            <span x-text="$store.setup.mockData.ADDRESS || '123 Store Street, City'"></span>
                        </div>
                        <div x-show="$store.setup.posLayout.company.showGstin" class="mt-1" :style="`font-size: ${$store.setup.posLayout.company.fontSize}px`">
                            GSTIN: <span x-text="$store.setup.mockData.GSTIN || '27AAACR1234A1Z1'"></span>
                        </div>
                        <template x-for="(row, index) in $store.setup.posLayout.company.customRows" :key="'comp-'+index">
                            <div class="mt-1" :style="`font-size: ${row.fontSize}px; font-weight: ${row.bold ? 'bold' : 'normal'}; text-align: ${row.align}`">
                                <span x-text="$store.setup.getMockValue(row.source, row.value) || row.label"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-dashed border-black my-2"></div>
                    <div class="text-center font-bold" x-text="$store.setup.posLayout.general.title"></div>
                    <div class="border-t border-dashed border-black my-2"></div>

                    {{-- Invoice Meta --}}
                    <div class="flex justify-between mb-2">
                        <div>No: INV-001</div>
                        <div>Date: 01/01/24</div>
                    </div>

                    <div class="border-t border-dashed border-black my-2"></div>

                    {{-- Table Header --}}
                    <div class="flex justify-between font-bold" :style="{'font-size': $store.setup.posLayout.productTable.fontSize + 'px', 'font-weight': $store.setup.posLayout.productTable.headerBold ? 'bold' : 'normal'}">
                        <template x-for="(col, index) in $store.setup.posLayout.productTable.columns.filter(c => c.enabled)" :key="index">
                            <div x-text="col.name" :style="`width: ${col.width}%; text-align: ${col.align}`"></div>
                        </template>
                    </div>
                    <div class="border-t border-dashed border-black my-2"></div>

                    {{-- Table Row Mock --}}
                    <div class="flex justify-between mb-2" :style="{'font-size': $store.setup.posLayout.productTable.fontSize + 'px'}">
                        <template x-for="(col, index) in $store.setup.posLayout.productTable.columns.filter(c => c.enabled)" :key="index">
                            <div :style="`width: ${col.width}%; text-align: ${col.align}`">
                                <span x-html="String($store.setup.getMockValue(col.source, 'Mock') || '').replace(/\n/g, '<br>')"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-dashed border-black my-2"></div>

                    {{-- Totals --}}
                    <div class="flex flex-col gap-1" :style="`font-size: ${$store.setup.posLayout.totals.fontSize}px; font-weight: ${$store.setup.posLayout.totals.bold ? 'bold' : 'normal'}`">
                        <div class="flex justify-between" x-show="$store.setup.posLayout.totals.showSubtotal">
                            <div>SUBTOTAL</div>
                            <div>$10.00</div>
                        </div>
                        <div class="flex justify-between" x-show="$store.setup.posLayout.totals.showTax">
                            <div>GST (5%)</div>
                            <div>$0.50</div>
                        </div>
                        <div class="flex justify-between" x-show="$store.setup.posLayout.totals.showDiscount">
                            <div>DISCOUNT</div>
                            <div>-$1.00</div>
                        </div>
                        <div class="flex justify-between font-bold mt-1 text-base border-t border-dashed border-black pt-2" x-show="$store.setup.posLayout.totals.showGrandTotal">
                            <div>GRAND TOTAL</div>
                            <div>$9.50</div>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-black my-2"></div>
                    <div class="text-center">Mode: CASH</div>
                    <div class="border-t border-dashed border-black my-2"></div>

                    {{-- Footer --}}
                    <div class="text-center mt-4 flex flex-col gap-2" :style="`font-size: ${$store.setup.posLayout.footer.fontSize}px`">
                        <template x-for="(row, index) in $store.setup.posLayout.footer.customRows" :key="'foot-'+index">
                            <div :style="`font-size: ${row.fontSize}px; font-weight: ${row.bold ? 'bold' : 'normal'}; text-align: ${row.align}`">
                                <span x-text="$store.setup.getMockValue(row.source, row.value) || row.label"></span>
                            </div>
                        </template>
                        <div x-html="String($store.setup.posLayout.footer.message || '').replace(/\n/g, '<br>')"></div>
                        <div x-show="$store.setup.posLayout.footer.showPoweredBy" class="opacity-70 text-[8px] mt-2">
                            Powered by Trex ERP
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel: Controls --}}
            <div class="w-1/2 bg-white dark:bg-slate-900 border-l border-slate-100 dark:border-slate-800 overflow-y-auto flex flex-col scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800 p-8 space-y-6">
                
                {{-- General Settings --}}
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider mb-4">General Settings</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Receipt Title</label>
                            <input type="text" x-model="$store.setup.posLayout.general.title" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.posLayout.general.showLogo" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Logo</span>
                            </label>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Logo Width (mm)</label>
                                <input type="number" x-model="$store.setup.posLayout.general.logoWidth" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Base Font Size (px)</label>
                                <input type="number" x-model="$store.setup.posLayout.general.fontSize" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Info --}}
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Header Info</h4>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.posLayout.company.bold" class="w-3.5 h-3.5 rounded border-2 text-blue-600 focus:ring-blue-500">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Bold Store Name</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Font Size</span>
                                <input type="number" x-model="$store.setup.posLayout.company.fontSize" class="w-12 px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-bold outline-none text-center">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.company.showPhone" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Phone</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.company.showEmail" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Email</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.company.showAddress" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Address</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.company.showGstin" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show GSTIN</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3 border-t border-slate-100 dark:border-slate-800 pt-4">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Custom Header Rows</span>
                            <button @click="$store.setup.addPOSRow('company')" type="button" class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all">+ Add Row</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(row, index) in $store.setup.posLayout.company.customRows" :key="'cr-'+index">
                                <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex flex-col gap-2 relative group">
                                    <button @click="$store.setup.removePOSRow('company', index)" type="button" class="absolute -top-2 -right-2 bg-red-100 text-red-600 w-6 h-6 flex items-center justify-center rounded-full hover:bg-red-200 opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="flex gap-2">
                                        <select x-model="row.source" class="w-1/2 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none text-slate-900 dark:text-white">
                                            <option value="CUSTOM_TEXT">Custom Text</option>
                                            <option value="EMPLOYEE">Employee Name</option>
                                            <option value="COUNTER">Counter No</option>
                                            <option value="TYPE">Order Type</option>
                                            <option value="DATE">Date</option>
                                            <option value="TIME">Time</option>
                                        </select>
                                        <input x-show="row.source === 'CUSTOM_TEXT'" type="text" x-model="row.value" placeholder="Text..." class="flex-1 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none text-slate-900 dark:text-white">
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <select x-model="row.align" class="flex-1 px-2 py-1 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold outline-none">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="checkbox" x-model="row.bold" class="w-3 h-3 rounded border-2 text-blue-600">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase">Bold</span>
                                        </label>
                                        <div class="flex items-center gap-1 ml-auto">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase">Size</span>
                                            <input type="number" x-model="row.fontSize" class="w-10 px-1 py-0.5 bg-slate-50 dark:bg-slate-800 rounded text-[9px] font-bold outline-none text-center">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Table Columns --}}
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Table Columns</h4>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.posLayout.productTable.headerBold" class="w-3.5 h-3.5 rounded border-2 text-blue-600 focus:ring-blue-500">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Bold Headers</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Font Size</span>
                                <input type="number" x-model="$store.setup.posLayout.productTable.fontSize" class="w-12 px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-bold outline-none text-center">
                            </div>
                            <button @click="$store.setup.addPOSColumn()" type="button" class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all">+ Add Column</button>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(col, index) in $store.setup.posLayout.productTable.columns" :key="index">
                            <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 relative group">
                                <button @click="$store.setup.removePOSColumn(index)" type="button" class="absolute -top-2 -right-2 bg-red-100 text-red-600 w-6 h-6 flex items-center justify-center rounded-full hover:bg-red-200 opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="col.enabled" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                                </label>
                                <select x-model="col.source" class="w-24 px-2 py-1 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold outline-none">
                                    <option value="PRODUCT_NAME">Item Name</option>
                                    <option value="QUANTITY">Quantity</option>
                                    <option value="UNIT_PRICE">Unit Price</option>
                                    <option value="LINE_TOTAL">Total</option>
                                    <option value="HSN_CODE">HSN</option>
                                    <option value="DISCOUNT">Discount</option>
                                    <option value="TAX_RATE">Tax %</option>
                                    <option value="TAX_AMOUNT">Tax Amt</option>
                                </select>
                                <div class="flex-1">
                                    <input type="text" x-model="col.name" class="w-full px-2 py-1 bg-transparent border-b border-slate-200 dark:border-slate-700 text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all">
                                </div>
                                <select x-model="col.align" class="w-16 px-1 py-1 bg-slate-50 dark:bg-slate-800 border-none rounded text-[9px] font-bold outline-none">
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                                <div class="w-12">
                                    <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Width %</div>
                                    <input type="number" x-model="col.width" class="w-full px-1 py-1 bg-slate-50 dark:bg-slate-800 rounded text-[10px] font-bold outline-none text-slate-900 dark:text-white text-center">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Totals</h4>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="$store.setup.posLayout.totals.bold" class="w-3.5 h-3.5 rounded border-2 text-blue-600 focus:ring-blue-500">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Bold Text</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Font Size</span>
                                <input type="number" x-model="$store.setup.posLayout.totals.fontSize" class="w-12 px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-bold outline-none text-center">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.totals.showSubtotal" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Subtotal</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.totals.showTax" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Tax</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.totals.showDiscount" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Discount</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.totals.showGrandTotal" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show Grand Total</span>
                        </label>
                    </div>
                </div>

                {{-- Footer Settings --}}
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Footer Details</h4>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase">Font Size</span>
                            <input type="number" x-model="$store.setup.posLayout.footer.fontSize" class="w-12 px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-bold outline-none text-center">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Custom Message</label>
                            <textarea x-model="$store.setup.posLayout.footer.message" rows="3" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold outline-none text-slate-900 dark:text-white focus:border-blue-500 transition-all"></textarea>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="$store.setup.posLayout.footer.showPoweredBy" class="w-4 h-4 rounded border-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Show 'Powered by Trex ERP'</span>
                        </label>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3 border-t border-slate-100 dark:border-slate-800 pt-4">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Custom Footer Rows</span>
                            <button @click="$store.setup.addPOSRow('footer')" type="button" class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all">+ Add Row</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(row, index) in $store.setup.posLayout.footer.customRows" :key="'fr-'+index">
                                <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex flex-col gap-2 relative group">
                                    <button @click="$store.setup.removePOSRow('footer', index)" type="button" class="absolute -top-2 -right-2 bg-red-100 text-red-600 w-6 h-6 flex items-center justify-center rounded-full hover:bg-red-200 opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="flex gap-2">
                                        <select x-model="row.source" class="w-1/2 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none text-slate-900 dark:text-white">
                                            <option value="CUSTOM_TEXT">Custom Text</option>
                                            <option value="SAVING_TEXT">You Saved</option>
                                            <option value="DATE">Date</option>
                                            <option value="TIME">Time</option>
                                        </select>
                                        <input x-show="row.source === 'CUSTOM_TEXT'" type="text" x-model="row.value" placeholder="Text..." class="flex-1 px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[10px] font-bold outline-none text-slate-900 dark:text-white">
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <select x-model="row.align" class="flex-1 px-2 py-1 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-[9px] font-bold outline-none">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="checkbox" x-model="row.bold" class="w-3 h-3 rounded border-2 text-blue-600">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase">Bold</span>
                                        </label>
                                        <div class="flex items-center gap-1 ml-auto">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase">Size</span>
                                            <input type="number" x-model="row.fontSize" class="w-10 px-1 py-0.5 bg-slate-50 dark:bg-slate-800 rounded text-[9px] font-bold outline-none text-center">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Printer Settings Modal --}}
<div x-show="$store.setup.showPrinterSettingsModal"
     class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     x-cloak x-transition>
    <div class="glass-card w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col">

        {{-- Modal Header --}}
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Printer Settings</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Configure print output channels</p>
                </div>
            </div>
            <button @click="$store.setup.showPrinterSettingsModal = false" type="button"
                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all text-slate-400 hover:text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 space-y-4">

            {{-- Auto Print toggle --}}
            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-900 dark:text-white">Auto Print Invoice</p>
                        <p class="text-[9px] font-bold text-slate-400">Print automatically after billing</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer" for="printer_auto_print_modal">
                    <input type="checkbox" id="printer_auto_print_modal"
                           {{ ($data['inv_auto_print'] ?? '1') == '1' ? 'checked' : '' }}
                           class="sr-only peer"
                           onchange="document.querySelector('[name=inv_auto_print][type=checkbox]').checked = this.checked">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="space-y-2">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">Print Channels</p>

                {{-- POS Thermal Printer --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-blue-100 dark:hover:border-blue-900/50 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-blue-300/30">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-black text-slate-900 dark:text-white">POS Thermal Printer</p>
                                <p class="text-[9px] font-bold text-slate-400">58mm / 80mm thermal receipt</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer" for="printer_pos_modal">
                            <input type="checkbox" id="printer_pos_modal"
                                   {{ ($data['inv_print_pos'] ?? '1') == '1' ? 'checked' : '' }}
                                   class="sr-only peer"
                                   onchange="document.querySelector('[name=inv_print_pos][type=checkbox]').checked = this.checked">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                {{-- A4 Printer --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-emerald-100 dark:hover:border-emerald-900/50 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-300/30">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-black text-slate-900 dark:text-white">A4 Sheet Printer</p>
                                <p class="text-[9px] font-bold text-slate-400">Full-page A4 invoice format</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer" for="printer_a4_modal">
                            <input type="checkbox" id="printer_a4_modal"
                                   {{ ($data['inv_print_a4'] ?? '0') == '1' ? 'checked' : '' }}
                                   class="sr-only peer"
                                   onchange="document.querySelector('[name=inv_print_a4][type=checkbox]').checked = this.checked">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <p class="text-[9px] font-bold text-slate-400 text-center pt-1">
                💡 Both can be enabled to print to both printers simultaneously
            </p>
        </div>

        {{-- Modal Footer --}}
        <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex gap-3 bg-slate-50/50 dark:bg-slate-800/50">
            <button type="submit" form="settings-form"
                    class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">
                Save Settings
            </button>
            <button type="button" @click="$store.setup.showPrinterSettingsModal = false"
                    class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                Close
            </button>
        </div>

    </div>
</div>
</form>

@endsection


