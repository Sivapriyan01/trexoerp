@extends(isset($isPublic) && $isPublic ? 'layouts.public' : 'layouts.tenant')

@section('title', strtoupper((strtolower($bill->bill_type) === 'billing' ? 'Tax Invoice' : $bill->bill_type) ?: 'Invoice') . ' #' . $bill->invoice_no)

@section('content')
@php
    if (!function_exists('numberToWords')) {
        function numberToWords($number) {
            $no = floor($number);
            $decimal = round($number - $no, 2) * 100;
            $hundred = null;
            $digits_length = strlen($no);
            $i = 0;
            $str = array();
            $words = array(0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
            $digits = array('', 'hundred','thousand','lakh', 'crore');
            while($i < $digits_length) {
                $divider = ($i == 2) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += $divider == 10 ? 1 : 2;
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural .' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
                } else $str[] = null;
            }
            $Rupees = implode('', array_reverse($str));
            $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
            return ucwords(trim($Rupees . $paise));
        }
    }

    $layout = json_decode(\App\Models\Setting::get('a4_layout_json', '{}'), true);

    // Default structure in case of missing data
    $l = array_merge([
        'format' => 'default',
        'general' => [
            'pageMarginTop' => 15, 'pageMarginBottom' => 15, 'pageMarginLeft' => 15, 'pageMarginRight' => 15,
            'fontFamily' => 'Inter', 'fontSize' => 12, 'primaryColor' => '#4f46e5',
            'showLogo' => true, 'logoWidth' => 40, 'logoOnRight' => false,
            'title' => 'TAX INVOICE', 'subTitle' => 'Original for Recipient',
            'swapColumns' => false
        ],
        'company' => ['showTitle' => false, 'title' => '', 'marginTop' => 0, 'fields' => []],
        'customer1' => ['showTitle' => true, 'title' => 'BILL TO', 'fields' => []],
        'customer2' => ['showTitle' => false, 'title' => 'SHIP TO', 'fields' => []],
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
    ], $layout);

    // Ensure nested objects exist
    foreach(['general', 'company', 'customer1', 'customer2', 'invoiceMeta', 'productTable', 'totals', 'footer'] as $key) {
        if(!isset($l[$key])) $l[$key] = [];
    }

    $getValue = function($source, $customText = '') use ($bill, $settings) {
        if ($source === 'CUSTOM_TEXT') return $customText;
        if ($source === 'BLANK_SPACE') return '&nbsp;';
        
        switch($source) {
            case 'BUSINESS_NAME': return $settings['name'] ?? config('app.name');
            case 'ADDRESS': return $settings['address'] ?? '';
            case 'PHONE': return $settings['phone'] ?? '';
            case 'GSTIN': return $settings['gst'] ?? '';
            case 'INVOICE_NO': return $bill->invoice_no;
            case 'DATE': return $bill->bill_date->format('d-M-Y');
            case 'TIME': return $bill->created_at->format('h:i A');
            case 'CUSTOMER_NAME': return $bill->customer_name ?: 'Cash Customer';
            case 'CUSTOMER_PHONE': return $bill->customer_phone ?: '-';
            case 'CUSTOMER_ADDRESS': return $bill->customer_address ?: '-';
            case 'CUSTOMER_GSTIN': return $bill->customer_gstin ?: '-';
            case 'DOCUMENT_TYPE': return strtoupper((strtolower($bill->bill_type) === 'billing' ? 'Tax Invoice' : $bill->bill_type) ?: 'TAX INVOICE');
            case 'PAYMENT_MODE': return strtoupper($bill->payment_mode);
            case 'SUBTOTAL':
                $gstCalcType = \App\Models\Setting::get('gst_calc_type', 'inclusive');
                $isInclusive = ($gstCalcType === 'inclusive') && ($bill->gst_amount > 0);
                $displaySubtotal = $isInclusive ? ($bill->subtotal - $bill->gst_amount) : $bill->subtotal;
                return number_format($displaySubtotal, 2);
            case 'TAX_TOTAL': return number_format($bill->gst_amount, 2);
            case 'DISCOUNT_TOTAL': return number_format($bill->discount_amount, 2);
            case 'GRAND_TOTAL': return number_format($bill->grand_total, 2);
            case 'TOTAL_IN_WORDS': 
                return numberToWords($bill->grand_total) . ' Rupees Only';
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
    @php
        $backRoute = (strtolower($bill->bill_type) === 'outward') ? route('tenant.billing.outward') : route('tenant.billing.index');
    @endphp
    <a href="{{ $backRoute }}" class="px-6 py-3 bg-white text-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200/50 hover:-translate-x-1 transition-all flex items-center gap-2 border border-slate-100">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Dashboard
    </a>
    @else
    <div></div>
    @endif
    <div class="flex gap-4">
        @if(empty($isPublic) && !empty($bill->customer_phone))
            <button onclick="
                this.innerText = 'Sending...'; this.disabled = true;
                fetch('{{ route('tenant.billing.whatsapp', $bill->id) }}', {
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
                
        {{-- Watermark --}}
        @if(!empty($settings['watermark_text']))
        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center overflow-hidden z-0 opacity-{{ $settings['watermark_opacity'] ?? '10' }}">
            <div class="space-y-48 flex flex-col items-center justify-center h-full w-full">
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap transform -translate-y-24 translate-x-48">{{ $settings['watermark_text'] }}</span>
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap">{{ $settings['watermark_text'] }}</span>
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap transform translate-y-24 -translate-x-48">{{ $settings['watermark_text'] }}</span>
            </div>
        </div>
        @endif
        
        {{-- Header Block --}}
        <div class="format-border-b border-black pb-4 mb-4 flex justify-between items-start {{ $l['general']['logoOnRight'] ? 'flex-row-reverse' : 'flex-row' }} relative z-10">
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
                @if(!empty($l['customer1']['fields']))
                <div class="text-right">
                    @if($l['customer1']['showTitle'])
                        <h4 class="format-border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-500 font-black" style="font-size: {{ $l['customer1']['fontSize'] ?? 10 }}px; text-align: {{ $l['customer1']['align'] ?? 'right' }}">{{ $l['customer1']['title'] }}</h4>
                    @endif
                    <div class="space-y-0.5" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                        @foreach($l['customer1']['fields'] as $index => $f)
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

                {{-- Ship To --}}
                @if(!empty($l['customer2']['fields']))
                <div>
                    @if($l['customer2']['showTitle'])
                        <h4 class="format-border-b border-slate-200 pb-1 mb-2 uppercase tracking-widest text-slate-500 font-black" style="font-size: {{ $l['customer2']['fontSize'] ?? 10 }}px; text-align: {{ $l['customer2']['align'] ?? 'left' }}">{{ $l['customer2']['title'] }}</h4>
                    @endif
                    <div class="space-y-0.5" x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                        @foreach($l['customer2']['fields'] as $index => $f)
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
            </div>
        </div>

        @if($l['format'] === 'tally')
            {{-- Dynamic Tally Layout (Respects Architect Columns) --}}
            @if(!empty($l['productTable']['columns']))
            <table class="w-full border-collapse mt-4 mb-4" style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                <thead>
                    <tr>
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            <th class="py-1 px-1 border border-black text-center uppercase" style="width: {{ $col['width'] }}%; text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px; font-weight: {{ ($l['productTable']['headerBold'] ?? true) ? '800' : '400' }}">{{ $col['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->items as $index => $item)
                    <tr>
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            <td class="px-1 py-1 border-l border-r border-black align-top" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px; {{ $col['source'] === 'PRODUCT_NAME' ? 'font-weight: bold;' : '' }}">
                                @php
                                    $val = '';
                                    switch($col['source']) {
                                        case 'SERIAL_NO': $val = $index + 1; break;
                                        case 'PRODUCT_NAME': $val = $item->product_name; break;
                                        case 'HSN_CODE': $val = $item->hsn ?: 'NA'; break;
                                        case 'QUANTITY': $val = $item->quantity; break;
                                        case 'UNIT_PRICE': $val = number_format($item->mrp, 2); break;
                                        case 'LINE_TOTAL': $val = number_format($item->total, 2); break;
                                        case 'DISCOUNT': $val = number_format($item->discount_amount ?? 0, 2); break;
                                    }
                                @endphp
                                {{ $val }}
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                    
                    {{-- Tax Rows under Description --}}
                    @if($bill->gst_amount > 0)
                    <tr>
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            @if($col['source'] === 'PRODUCT_NAME')
                                <td class="px-1 py-1 border-l border-r border-black text-right italic" style="font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px; font-weight: bold; padding-right: 2rem;">CGST</td>
                            @elseif($col['source'] === 'LINE_TOTAL')
                                <td class="px-1 py-1 border-l border-r border-black" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">{{ number_format($bill->gst_amount/2, 2) }}</td>
                            @else
                                <td class="px-1 py-1 border-l border-r border-black"></td>
                            @endif
                        @endforeach
                    </tr>
                    <tr>
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            @if($col['source'] === 'PRODUCT_NAME')
                                <td class="px-1 py-1 border-l border-r border-black text-right italic" style="font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px; font-weight: bold; padding-right: 2rem;">SGST</td>
                            @elseif($col['source'] === 'LINE_TOTAL')
                                <td class="px-1 py-1 border-l border-r border-black" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">{{ number_format($bill->gst_amount/2, 2) }}</td>
                            @else
                                <td class="px-1 py-1 border-l border-r border-black"></td>
                            @endif
                        @endforeach
                    </tr>
                    @endif

                    {{-- Empty Spacing Row --}}
                    <tr>
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            <td class="px-1 py-8 border-l border-r border-black"></td>
                        @endforeach
                    </tr>

                    {{-- Total Row --}}
                    <tr class="border-t border-black font-bold">
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            @if($col['source'] === 'PRODUCT_NAME')
                                <td class="px-1 py-1 border-l border-r border-black text-right" style="font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">Total</td>
                            @elseif($col['source'] === 'LINE_TOTAL')
                                <td class="px-1 py-1 border-l border-r border-black" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">INR {{ number_format($bill->grand_total, 2) }}</td>
                            @else
                                <td class="px-1 py-1 border-l border-r border-black"></td>
                            @endif
                        @endforeach
                    </tr>
                    
                    {{-- Payment Made Row --}}
                    @if($bill->paid_amount > 0)
                    <tr class="border-t border-black font-bold">
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            @if($col['source'] === 'PRODUCT_NAME')
                                <td class="px-1 py-1 border-l border-r border-black text-right uppercase" style="font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">PAYMENT MADE</td>
                            @elseif($col['source'] === 'LINE_TOTAL')
                                <td class="px-1 py-1 border-l border-r border-black" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">(-){{ number_format($bill->paid_amount, 2) }}</td>
                            @else
                                <td class="px-1 py-1 border-l border-r border-black"></td>
                            @endif
                        @endforeach
                    </tr>
                    <tr class="border-t border-black font-bold">
                        @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                            @if($col['source'] === 'PRODUCT_NAME')
                                <td class="px-1 py-1 border-l border-r border-black text-right uppercase" style="font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">Balance Due</td>
                            @elseif($col['source'] === 'LINE_TOTAL')
                                <td class="px-1 py-1 border-l border-r border-black" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] ?? 10 }}px;">INR {{ number_format(max(0, $bill->grand_total - $bill->paid_amount), 2) }}</td>
                            @else
                                <td class="px-1 py-1 border-l border-r border-black"></td>
                            @endif
                        @endforeach
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif
            
            {{-- Amount in words row --}}
            @php
                $words = numberToWords($bill->grand_total) . ' Only';
            @endphp
            <div class="border border-black p-1 flex justify-between mb-0" style="border-top: none;">
                <div class="text-[10px]">
                    <div>Amount Chargeable (in words)</div>
                    <div class="font-bold">{{ $words }}</div>
                </div>
                <div class="text-[10px] italic">E. & O.E</div>
            </div>

            {{-- GST Tally Table --}}
            @if($bill->gst_amount > 0)
            <table class="w-full border-collapse border border-black mt-0" style="border-top: none;">
                @php
                    $gstTableCols = $l['gstTable']['columns'] ?? [
                        ['label' => 'HSN/SAC', 'enabled' => true],
                        ['label' => 'Taxable Value', 'enabled' => true],
                        ['label' => 'CGST Rate', 'enabled' => true],
                        ['label' => 'CGST Amount', 'enabled' => true],
                        ['label' => 'SGST Rate', 'enabled' => true],
                        ['label' => 'SGST Amount', 'enabled' => true],
                        ['label' => 'IGST Rate', 'enabled' => false],
                        ['label' => 'IGST Amount', 'enabled' => false],
                        ['label' => 'Total Tax Amount', 'enabled' => true]
                    ];
                    $gstFontSize = $l['gstTable']['fontSize'] ?? 10;
                @endphp
                <thead>
                    <tr>
                        @if($gstTableCols[0]['enabled']) <th rowspan="2" class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">HSN/SAC</th> @endif
                        @if($gstTableCols[1]['enabled']) <th rowspan="2" class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Taxable Value</th> @endif
                        @if($gstTableCols[2]['enabled'] || $gstTableCols[3]['enabled']) <th colspan="{{ ($gstTableCols[2]['enabled'] ? 1 : 0) + ($gstTableCols[3]['enabled'] ? 1 : 0) }}" class="py-1 px-1 border-b border-r border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">CGST</th> @endif
                        @if($gstTableCols[4]['enabled'] || $gstTableCols[5]['enabled']) <th colspan="{{ ($gstTableCols[4]['enabled'] ? 1 : 0) + ($gstTableCols[5]['enabled'] ? 1 : 0) }}" class="py-1 px-1 border-b border-r border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">SGST</th> @endif
                        @if($gstTableCols[6]['enabled'] || $gstTableCols[7]['enabled']) <th colspan="{{ ($gstTableCols[6]['enabled'] ? 1 : 0) + ($gstTableCols[7]['enabled'] ? 1 : 0) }}" class="py-1 px-1 border-b border-r border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">IGST</th> @endif
                        @if($gstTableCols[8]['enabled']) <th rowspan="2" class="py-1 px-1 border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Total Tax<br>Amount</th> @endif
                    </tr>
                    <tr>
                        @if($gstTableCols[2]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Rate</th> @endif
                        @if($gstTableCols[3]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Amount</th> @endif
                        @if($gstTableCols[4]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Rate</th> @endif
                        @if($gstTableCols[5]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Amount</th> @endif
                        @if($gstTableCols[6]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Rate</th> @endif
                        @if($gstTableCols[7]['enabled']) <th class="py-1 px-1 border-r border-b border-black text-center font-normal" style="font-size: {{ $gstFontSize }}px;">Amount</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $taxGroups = [];
                        $totalItemSum = 0;
                        foreach($bill->items as $item) {
                            $hsn = $item->hsn ?: 'NA';
                            if (!isset($taxGroups[$hsn])) {
                                $taxGroups[$hsn] = 0;
                            }
                            $val = $item->total;
                            $taxGroups[$hsn] += $val;
                            $totalItemSum += $val;
                        }
                    @endphp
                    @foreach($taxGroups as $hsn => $amount)
                        @php
                            $ratio = $totalItemSum > 0 ? ($amount / $totalItemSum) : 0;
                            $taxableVal = $bill->subtotal * $ratio;
                            $taxVal = $bill->gst_amount * $ratio;
                        @endphp
                        <tr>
                            @if($gstTableCols[0]['enabled']) <td class="px-1 py-1 text-left border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ $hsn }}</td> @endif
                            @if($gstTableCols[1]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($taxableVal, 2) }}</td> @endif
                            @if($gstTableCols[2]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_percent/2, 2) }}%</td> @endif
                            @if($gstTableCols[3]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($taxVal/2, 2) }}</td> @endif
                            @if($gstTableCols[4]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_percent/2, 2) }}%</td> @endif
                            @if($gstTableCols[5]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($taxVal/2, 2) }}</td> @endif
                            @if($gstTableCols[6]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_percent, 2) }}%</td> @endif
                            @if($gstTableCols[7]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($taxVal, 2) }}</td> @endif
                            @if($gstTableCols[8]['enabled']) <td class="px-1 py-1 text-right border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($taxVal, 2) }}</td> @endif
                        </tr>
                    @endforeach
                    <tr class="font-bold border-t border-black">
                        @if($gstTableCols[0]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">Total</td> @endif
                        @if($gstTableCols[1]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->subtotal, 2) }}</td> @endif
                        @if($gstTableCols[2]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;"></td> @endif
                        @if($gstTableCols[3]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_amount/2, 2) }}</td> @endif
                        @if($gstTableCols[4]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;"></td> @endif
                        @if($gstTableCols[5]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_amount/2, 2) }}</td> @endif
                        @if($gstTableCols[6]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;"></td> @endif
                        @if($gstTableCols[7]['enabled']) <td class="px-1 py-1 text-right border-r border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_amount, 2) }}</td> @endif
                        @if($gstTableCols[8]['enabled']) <td class="px-1 py-1 text-right border-black" style="font-size: {{ $gstFontSize }}px;">{{ number_format($bill->gst_amount, 2) }}</td> @endif
                    </tr>
                </tbody>
            </table>
            @php
                $taxWords = numberToWords($bill->gst_amount) . ' Only';
            @endphp
            <div class="p-1 text-[10px] border border-black mb-6" style="border-top: none;">
                <span class="opacity-80">Tax Amount (in words) : </span> <span class="font-bold">{{ $taxWords }}</span>
            </div>
            @endif
        @else
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
                    @foreach($bill->items as $index => $item)
                        <tr>
                            @foreach(array_filter($l['productTable']['columns'], fn($c) => $c['enabled'] ?? true) as $col)
                                <td contenteditable="true" spellcheck="false" style="text-align: {{ $col['align'] }}; font-size: {{ $l['productTable']['fontSize'] }}px; height: {{ $l['productTable']['rowHeight'] }}mm" class="format-border-b border-slate-100 px-2 py-2 outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">
                                    @php
                                        $val = '';
                                        switch($col['source']) {
                                            case 'SERIAL_NO': $val = $index + 1; break;
                                            case 'PRODUCT_NAME': $val = $item->product_name; break;
                                            case 'HSN_CODE': $val = $item->hsn ?: '-'; break;
                                            case 'QUANTITY': $val = $item->quantity; break;
                                            case 'UNIT_PRICE': $val = number_format($item->mrp, 2); break;
                                            case 'LINE_TOTAL': $val = number_format($item->total, 2); break;
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
        @if($bill->gst_amount > 0)
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
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($bill->subtotal, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($bill->gst_amount/2, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($bill->gst_amount/2, 2) }}</td>
                            <td class="text-[9px] font-bold p-2 text-right text-slate-600">{{ number_format($bill->gst_amount, 2) }}</td>
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
                            @php
                                $dLabel = $f['label'] ?? '';
                                if (empty($dLabel)) {
                                    switch($f['source']) {
                                        case 'SUBTOTAL': $dLabel = 'Subtotal'; break;
                                        case 'TAX_TOTAL': $dLabel = 'Total Tax'; break;
                                        case 'DISCOUNT_TOTAL': $dLabel = 'Discount'; break;
                                        case 'GRAND_TOTAL': $dLabel = 'Grand Total'; break;
                                        case 'TOTAL_IN_WORDS': $dLabel = 'Amount in Words'; break;
                                        case 'ROUND_OFF': $dLabel = 'Round Off'; break;
                                    }
                                }
                            @endphp
                            @if(!empty($dLabel)) <span contenteditable="true" spellcheck="false" class="uppercase tracking-widest opacity-70 outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text">{{ $dLabel }}</span> @endif
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
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-8 flex justify-between items-end relative z-10">
            <div class="w-1/2 space-y-4">
                @if(($l['footer']['showBankDetails'] ?? false) && !empty($l['footer']['bankDetails']))
                    <div>
                        <h5 class="text-[9px] font-black uppercase text-slate-500 mb-1">Bank Details</h5>
                        <div class="text-[9px] text-slate-700 whitespace-pre-wrap font-bold leading-relaxed">{!! nl2br(e($l['footer']['bankDetails'])) !!}</div>
                    </div>
                @endif
                @php
                    $termsText = !empty($settings['billing_terms']) ? $settings['billing_terms'] : ($l['footer']['terms'] ?? '');
                @endphp
                @if(($l['footer']['showTerms'] ?? false) && !empty($termsText))
                    <div>
                        <h5 class="text-[9px] font-black uppercase text-slate-500 mb-1">Terms & Conditions</h5>
                        <div class="text-[8px] text-slate-600 whitespace-pre-wrap">{!! nl2br(e($termsText)) !!}</div>
                    </div>
                @endif
            </div>
            
            <div class="text-right">
                @if($l['footer']['showSignature'] ?? false)
                    <div class="w-48 border-b-2 border-black mb-2 mx-auto inline-block"></div>
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ $l['footer']['signatureText'] ?? 'Authorized Signatory' }}</p>
                    <p class="text-[8px] font-bold text-slate-500 uppercase mt-0.5">For {{ $settings['name'] ?? config('app.name') }}</p>
                @endif
                @if($l['footer']['showPoweredBy'] ?? false)
                    <div class="text-[8px] font-bold text-slate-300 uppercase tracking-widest mt-8">
                        Powered by Trex ERP
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
