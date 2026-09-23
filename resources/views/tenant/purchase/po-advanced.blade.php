@extends(isset($isPublic) && $isPublic ? 'layouts.public' : 'layouts.tenant')

@section('title', 'Purchase Order' . ' #' . $purchase->invoice_ref)

@section('content')
@php
    $layout = json_decode(\App\Models\Setting::get('po_layout_json', '{}'), true);
    
    // Default structure in case of missing data
    $defaultStructure = [
        'format' => 'default',
        'general' => [
            'pageMarginTop' => 15, 'pageMarginBottom' => 15, 'pageMarginLeft' => 15, 'pageMarginRight' => 15,
            'fontFamily' => 'Inter', 'fontSize' => 12, 'primaryColor' => '#4f46e5',
            'showLogo' => true, 'logoWidth' => 40, 'logoOnRight' => false,
            'title' => 'TAX INVOICE', 'subTitle' => 'Original for Recipient',
            'swapColumns' => false
        ],
        'company' => ['showTitle' => false, 'title' => '', 'marginTop' => 0, 'fields' => []],
        'vendor' => ['showTitle' => true, 'title' => 'VENDOR DETAILS', 'fields' => []],
        'invoiceMeta' => ['fields' => []],
        'productTable' => [
            'columns' => [], 'fontSize' => 10, 'rowHeight' => 8, 'headerBold' => true
        ],
        'totals' => ['fields' => []],
        'footer' => [
            'showBankDetails' => false, 'bankDetails' => '',
            'showTerms' => true, 'terms' => '1. Goods once sold will not be taken back.',
            'showSignature' => true, 'signatureText' => 'Authorized Signatory',
            'showPoweredBy' => true
        ]
    ];

    $l = array_merge($defaultStructure, $layout ?? []);

    // Ensure nested objects exist and maintain their defaults
    foreach(['general', 'company', 'vendor', 'invoiceMeta', 'productTable', 'totals', 'footer'] as $key) {
        $l[$key] = array_merge($defaultStructure[$key], is_array($l[$key] ?? null) ? $l[$key] : []);
    }

    $getValue = function($source, $customText = '') use ($purchase, $settings) {
        if ($source === 'CUSTOM_TEXT') return $customText;
        if ($source === 'BLANK_SPACE') return '&nbsp;';
        
        switch($source) {
            case 'BUSINESS_NAME': return $settings['name'] ?? config('app.name');
            case 'ADDRESS': return $settings['address'] ?? '';
            case 'PHONE': return $settings['phone'] ?? '';
            case 'GSTIN': return $settings['gst'] ?? '';
            case 'INVOICE_NO': return $purchase->invoice_ref;
            case 'DATE': return $purchase->invoice_date->format('d-M-Y');
            case 'TIME': return $purchase->created_at->format('h:i A');
            case 'CUSTOMER_NAME': return $purchase->vendor ? $purchase->vendor->name : 'Cash Supplier';
            case 'CUSTOMER_PHONE': return $purchase->vendor ? $purchase->vendor->phone : '-';
            case 'CUSTOMER_ADDRESS': return $purchase->vendor ? $purchase->vendor->address : '-';
            case 'CUSTOMER_GSTIN': return $purchase->vendor ? $purchase->vendor->gstin : '-';
            case 'DOCUMENT_TYPE': return 'PURCHASE ORDER';
            case 'PAYMENT_MODE': return 'N/A';
            case 'SUBTOTAL':
                $gstCalcType = \App\Models\Setting::get('gst_calc_type', 'inclusive');
                $isInclusive = ($gstCalcType === 'inclusive') && ($purchase->gst_amount > 0);
                $displaySubtotal = $isInclusive ? ($purchase->total_amount - $purchase->gst_amount) : $purchase->total_amount;
                return number_format($displaySubtotal, 2);
            case 'TAX_TOTAL': return number_format($purchase->gst_amount, 2);
            case 'DISCOUNT_TOTAL': return number_format($purchase->discount_amount, 2);
            case 'GRAND_TOTAL': return number_format($purchase->total_amount, 2);
            case 'TOTAL_IN_WORDS': 
                $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
                return ucwords($f->format($purchase->total_amount)) . ' Rupees Only';
            default: return '';
        }
    };
@endphp

<style>
    /* Format Styles */
    .a4-format-tally { border: 2px solid #000; }
    .a4-format-tally .format-border-b { border-bottom: 2px solid #000 !important; }
    .a4-format-tally .format-border-t { border-top: 2px solid #000 !important; }
    .a4-format-tally th, .a4-format-tally td { border: 1px solid #000 !important; }
    
    .a4-format-modern { border-top: 8px solid var(--primary-color, #4f46e5); }
    .a4-format-modern .format-border-b { border-bottom: 1px solid #eee !important; }
    .a4-format-modern th { border: none !important; background: #f8fafc; }
    
    .a4-format-classic { font-family: 'Georgia', serif !important; }
    .a4-format-classic .format-border-b { border-bottom: 3px double #000 !important; }
    
    .a4-format-compact { font-size: 8px !important; }
    .a4-format-compact .compact-space > * + * { margin-top: 2mm !important; }

    /* Print color overrides */
    html.dark .a4-container, .dark .a4-container {
        color: #0f172a !important;
        background-color: #ffffff !important;
    }

    @media print {
        aside, header, .no-print, .blob { display: none !important; }
        body, main, .flex-1 { padding: 0 !important; margin: 0 !important; background: white !important; overflow: visible !important; }
        @page { margin: 0; size: A4 portrait; }
    }
</style>

<div class="max-w-[210mm] mx-auto mb-6 flex justify-between items-center no-print">
    @if(empty($isPublic))
    <a href="{{ route('tenant.billing.index') }}" class="px-6 py-3 bg-white text-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200/50 hover:-translate-x-1 transition-all flex items-center gap-2 border border-slate-100">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Dashboard
    </a>
    @else
    <div></div>
    @endif
    <div class="flex gap-4">
        @if(empty($isPublic) && !empty($purchase->vendor_phone))
            <button onclick="
                this.innerText = 'Sending...'; this.disabled = true;
                fetch('{{ route('tenant.billing.whatsapp', $purchase->id) }}', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    this.innerText = 'Share on WhatsApp'; this.disabled = false;
                    if(data.success) alert('WhatsApp message sent!'); else alert('Failed: ' + (data.message || 'Error'));
                }).catch(() => { this.innerText = 'Share on WhatsApp'; this.disabled = false; alert('Error'); });
            " class="px-6 py-3 bg-[#25D366] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-[#25D366]/20 hover:scale-[1.02] transition-all flex items-center gap-2">
                Share on WhatsApp
            </button>
        @endif
        <button onclick="window.print()" class="px-8 py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] transition-all flex items-center gap-2">
            Print Document
        </button>
    </div>
</div>

<div class="a4-container mx-auto relative shadow-2xl bg-white overflow-hidden print:shadow-none" data-darkreader-ignore="true" style="width: 210mm;">
    <div class="a4-page relative flex flex-col min-h-[297mm] a4-format-{{ $l['format'] }}" 
         style="padding: {{ $l['general']['pageMarginTop'] }}mm {{ $l['general']['pageMarginRight'] }}mm {{ $l['general']['pageMarginBottom'] }}mm {{ $l['general']['pageMarginLeft'] }}mm; 
                font-family: {{ $l['general']['fontFamily'] }}, sans-serif; 
                font-size: {{ $l['general']['fontSize'] }}px; 
                --primary-color: {{ $l['general']['primaryColor'] }};">
        
        {{-- Header Block --}}
        <div class="format-border-b border-black pb-4 mb-4 flex justify-between items-start {{ $l['general']['logoOnRight'] ? 'flex-row-reverse' : 'flex-row' }}">
            <div>
                @if($l['general']['showLogo'])
                    @php $logoUrl = !empty($settings['inv_header_img']) ? tenant_asset($settings['inv_header_img']) : (!empty($settings['logo']) ? tenant_asset($settings['logo']) : ''); @endphp
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" style="width: {{ $l['general']['logoWidth'] }}mm; height: auto;">
                    @endif
                @endif
            </div>
            <div style="text-align: {{ $l['general']['logoOnRight'] ? 'left' : 'right' }}">
                <h1 style="color: {{ $l['general']['primaryColor'] }}" class="text-2xl font-black uppercase tracking-tight">{{ $l['general']['title'] }}</h1>
                <p class="text-[10px] font-bold text-slate-500">{{ $l['general']['subTitle'] }}</p>
            </div>
        </div>

        {{-- Meta & Details --}}
        <div class="grid grid-cols-2 gap-8 mb-6">
            {{-- Left Column --}}
            <div class="space-y-6 compact-space {{ $l['general']['swapColumns'] ? 'order-last' : 'order-first' }}">
                {{-- Company --}}
                @if(!empty($l['company']['fields']))
                <div style="margin-top: {{ $l['company']['marginTop'] ?? 0 }}mm">
                    @if($l['company']['showTitle'])
                        <h4 class="format-border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-500 font-black" style="font-size: {{ $l['company']['fontSize'] ?? 10 }}px; text-align: {{ $l['company']['align'] ?? 'left' }}">{{ $l['company']['title'] }}</h4>
                    @endif
                    <div class="space-y-0.5" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                        @foreach($l['company']['fields'] as $index => $f)
                            <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                 :class="{ 'ring-2 ring-purple-500 print:ring-0 rounded-md': selectedIndex === {{ $index }} }"
                                 @click.stop="selectedIndex = {{ $index }}"
                                 @dragover.prevent="$el.classList.add('border-purple-300')"
                                 @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                 @drop.prevent="
                                     $el.classList.remove('border-purple-300');
                                     if(draggedNode && draggedNode !== $el) {
                                         const rect = $el.getBoundingClientRect();
                                         if($event.clientY < rect.top + rect.height/2) {
                                             $el.parentNode.insertBefore(draggedNode, $el);
                                         } else {
                                             $el.parentNode.insertBefore(draggedNode, $el.nextSibling);
                                         }
                                         draggedNode = null;
                                     }
                                 ">
                                <div style="font-size: {{ $f['fontSize'] }}px; font-weight: {{ $f['bold'] ? '700' : '400' }}; padding-left: {{ $f['leftSpace'] ?? 0 }}mm; display: flex; gap: 8px; justify-content: {{ $f['align'] === 'right' ? 'flex-end' : ($f['align'] === 'center' ? 'center' : 'flex-start') }}" class="{{ ($f['reverse'] ?? false) ? 'flex-row-reverse' : 'flex-row' }}">
                                    @if(!empty($f['label'])) <span contenteditable="true" spellcheck="false" class="opacity-60 font-bold uppercase outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{{ $f['label'] }}</span> @endif
                                    <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{!! $getValue($f['source'], $f['customText'] ?? '') !!}</span>
                                </div>
                                <div x-show="selectedIndex === {{ $index }}"
                                     draggable="true"
                                     @dragstart="draggedNode = $el.closest('.group'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                     class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity no-print"
                                     title="Drag to move">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Meta --}}
                @if(!empty($l['invoiceMeta']['fields']))
                <div class="text-left">
                    <div class="space-y-1" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                        @foreach($l['invoiceMeta']['fields'] as $index => $f)
                            <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                 :class="{ 'ring-2 ring-purple-500 print:ring-0 rounded-md': selectedIndex === {{ $index }} }"
                                 @click.stop="selectedIndex = {{ $index }}"
                                 @dragover.prevent="$el.classList.add('border-purple-300')"
                                 @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                 @drop.prevent="
                                     $el.classList.remove('border-purple-300');
                                     if(draggedNode && draggedNode !== $el) {
                                         const rect = $el.getBoundingClientRect();
                                         if($event.clientY < rect.top + rect.height/2) {
                                             $el.parentNode.insertBefore(draggedNode, $el);
                                         } else {
                                             $el.parentNode.insertBefore(draggedNode, $el.nextSibling);
                                         }
                                         draggedNode = null;
                                     }
                                 ">
                                <div style="font-size: {{ $f['fontSize'] }}px; font-weight: {{ $f['bold'] ? '700' : '400' }}; display: flex; gap: 8px; justify-content: flex-start" class="{{ ($f['reverse'] ?? false) ? 'flex-row-reverse' : 'flex-row' }}">
                                    @if(!empty($f['label'])) <span contenteditable="true" spellcheck="false" class="opacity-60 font-bold uppercase outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{{ $f['label'] }}</span> @endif
                                    <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{!! $getValue($f['source'], $f['customText'] ?? '') !!}</span>
                                </div>
                                <div x-show="selectedIndex === {{ $index }}"
                                     draggable="true"
                                     @dragstart="draggedNode = $el.closest('.group'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                     class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity no-print"
                                     title="Drag to move">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 compact-space {{ $l['general']['swapColumns'] ? 'order-first' : 'order-last' }}">
                {{-- Bill To --}}
                @if(!empty($l['vendor']['fields']))
                <div class="text-right">
                    @if($l['vendor']['showTitle'])
                        <h4 class="format-border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-500 font-black" style="font-size: {{ $l['vendor']['fontSize'] ?? 10 }}px; text-align: {{ $l['vendor']['align'] ?? 'right' }}">{{ $l['vendor']['title'] }}</h4>
                    @endif
                    <div class="space-y-0.5" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                        @foreach($l['vendor']['fields'] as $index => $f)
                            <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                 :class="{ 'ring-2 ring-purple-500 print:ring-0 rounded-md': selectedIndex === {{ $index }} }"
                                 @click.stop="selectedIndex = {{ $index }}"
                                 @dragover.prevent="$el.classList.add('border-purple-300')"
                                 @dragleave.prevent="$el.classList.remove('border-purple-300')"
                                 @drop.prevent="
                                     $el.classList.remove('border-purple-300');
                                     if(draggedNode && draggedNode !== $el) {
                                         const rect = $el.getBoundingClientRect();
                                         if($event.clientY < rect.top + rect.height/2) {
                                             $el.parentNode.insertBefore(draggedNode, $el);
                                         } else {
                                             $el.parentNode.insertBefore(draggedNode, $el.nextSibling);
                                         }
                                         draggedNode = null;
                                     }
                                 ">
                                <div style="font-size: {{ $f['fontSize'] }}px; font-weight: {{ $f['bold'] ? '700' : '400' }}; padding-right: {{ $f['leftSpace'] ?? 0 }}mm; display: flex; gap: 8px; justify-content: {{ $f['align'] === 'left' ? 'flex-start' : ($f['align'] === 'center' ? 'center' : 'flex-end') }}" class="{{ ($f['reverse'] ?? false) ? 'flex-row-reverse' : 'flex-row' }}">
                                    @if(!empty($f['label'])) <span contenteditable="true" spellcheck="false" class="opacity-60 font-bold uppercase outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{{ $f['label'] }}</span> @endif
                                    <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{!! $getValue($f['source'], $f['customText'] ?? '') !!}</span>
                                </div>
                                <div x-show="selectedIndex === {{ $index }}"
                                     draggable="true"
                                     @dragstart="draggedNode = $el.closest('.group'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                     class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity no-print"
                                     title="Drag to move">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif


            </div>
        </div>

        {{-- Product Table --}}
        @if(!empty($l['productTable']['columns']))
        <div class="mt-4 mb-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr style="background-color: {{ $l['general']['primaryColor'] }}10">
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            <th contenteditable="true" spellcheck="false" style="width: {{ $col['width'] }}%; text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] }}px; font-weight: {{ $l['productTable']['headerBold'] ? '800' : '400' }}" class="format-border-b format-border-t border-black py-2 px-2 uppercase tracking-tighter outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">
                                {{ $col['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $index => $item)
                        <tr>
                            @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                                <td contenteditable="true" spellcheck="false" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] }}px; height: {{ $l['productTable']['rowHeight'] }}mm" class="format-border-b border-slate-100 px-2 py-2 outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">
                                    @php
                                        $val = '';
                                        switch($col['source']) {
                                            case 'SERIAL_NO': $val = $index + 1; break;
                                            case 'PRODUCT_NAME': $val = $item->product_name; break;
                                            case 'HSN_CODE': $val = '-'; break;
                                            case 'QUANTITY': $val = $item->quantity; break;
                                            case 'UNIT_PRICE': $val = number_format($item->buy_price, 2); break;
                                            case 'LINE_TOTAL': $val = number_format($item->total_amount, 2); break;
                                        }
                                    @endphp
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- GST Breakup Table --}}
        @if($purchase->gst_amount > 0)
            <div class="mt-4 mb-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-y border-black bg-slate-50">
                            <th class="text-[9px] font-black p-2 text-left uppercase">HSN/SAC</th>
                            <th class="text-[9px] font-black p-2 text-right uppercase">TAXABLE VALUE</th>
                            <th class="text-[9px] font-black p-2 text-right uppercase">CGST</th>
                            <th class="text-[9px] font-black p-2 text-right uppercase">SGST</th>
                            <th class="text-[9px] font-black p-2 text-right uppercase">TOTAL TAX</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-100">
                            <td class="text-[9px] font-bold p-2 text-left text-slate-600">NA</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($purchase->subtotal, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($purchase->gst_amount/2, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($purchase->gst_amount/2, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($purchase->gst_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Totals --}}
        @if(!empty($l['totals']['fields']))
        <div class="flex justify-end mb-8">
            <div class="w-72 space-y-1" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                @foreach($l['totals']['fields'] as $index => $f)
                    <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                         :class="{ 'ring-2 ring-purple-500 print:ring-0 rounded-md': selectedIndex === {{ $index }} }"
                         @click.stop="selectedIndex = {{ $index }}"
                         @dragover.prevent="$el.classList.add('border-purple-300')"
                         @dragleave.prevent="$el.classList.remove('border-purple-300')"
                         @drop.prevent="
                             $el.classList.remove('border-purple-300');
                             if(draggedNode && draggedNode !== $el) {
                                 const rect = $el.getBoundingClientRect();
                                 if($event.clientY < rect.top + rect.height/2) {
                                     $el.parentNode.insertBefore(draggedNode, $el);
                                 } else {
                                     $el.parentNode.insertBefore(draggedNode, $el.nextSibling);
                                 }
                                 draggedNode = null;
                             }
                         ">
                        <div style="font-size: {{ $f['fontSize'] }}px; font-weight: {{ $f['bold'] ? '900' : '500' }}; display: flex; justify-content: space-between; align-items: center;" class="py-1 {{ ($f['topBorder'] ?? false) ? 'border-t border-black pt-2 mt-2' : '' }}">
                            @if(!empty($f['label'])) <span contenteditable="true" spellcheck="false" class="uppercase tracking-widest opacity-70 outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{{ $f['label'] }}</span> @endif
                            <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{!! $getValue($f['source'], $f['customText'] ?? '') !!}</span>
                        </div>
                        <div x-show="selectedIndex === {{ $index }}"
                             draggable="true"
                             @dragstart="draggedNode = $el.closest('.group'); $event.dataTransfer.effectAllowed = 'move';"
                             class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity no-print"
                             title="Drag to move">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-8 flex justify-between items-end">
            <div class="w-1/2 space-y-4">
                @if($l['footer']['showBankDetails'] && !empty($l['footer']['bankDetails']))
                    <div>
                        <h5 class="text-[9px] font-black uppercase text-slate-500 mb-1">Bank Details</h5>
                        <div class="text-[9px] text-slate-700 whitespace-pre-wrap font-bold leading-relaxed">{!! nl2br(e($l['footer']['bankDetails'])) !!}</div>
                    </div>
                @endif
                @if($l['footer']['showTerms'] && !empty($l['footer']['terms']))
                    <div>
                        <h5 class="text-[9px] font-black uppercase text-slate-500 mb-1">Terms & Conditions</h5>
                        <div class="text-[8px] text-slate-600 whitespace-pre-wrap">{!! nl2br(e($l['footer']['terms'])) !!}</div>
                    </div>
                @endif
            </div>
            
            <div class="text-right">
                @if($l['footer']['showSignature'])
                    <div class="w-48 border-b-2 border-black mb-2 mx-auto inline-block"></div>
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ $l['footer']['signatureText'] }}</p>
                    <p class="text-[8px] font-bold text-slate-500 uppercase mt-0.5">For {{ $settings['name'] ?? config('app.name') }}</p>
                @endif
                @if($l['footer']['showPoweredBy'])
                    <div class="text-[8px] font-bold text-slate-300 uppercase tracking-widest mt-8">
                        Powered by Trex ERP
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
