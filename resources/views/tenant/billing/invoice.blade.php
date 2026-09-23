@extends(isset($isPublic) && $isPublic ? 'layouts.public' : 'layouts.tenant')

@section('title', strtoupper((strtolower($bill->bill_type) === 'billing' ? 'Tax Invoice' : $bill->bill_type) ?: 'Invoice') . ' #' . $bill->invoice_no)

@section('content')
@php
    $layout = $layout ?? [];
    $s = $layout['settings'] ?? [
        'totalWidth' => 210,
        'marginTop' => 15,
        'marginBottom' => 15,
        'marginLeft' => 15,
        'marginRight' => 15,
        'logoHeight' => 25,
        'logoWidth' => 44
    ];
    $ts = $layout['tableSettings'] ?? [
        'headerSize' => 14,
        'contentSize' => 14,
        'topSpace' => 0,
        'bottomSpace' => 0,
        'headerPadding' => 1,
        'bodyPadding' => 2,
        'headerAlign' => 'left',
        'bodyAlign' => 'left'
    ];

    $getValue = function($source) use ($bill, $settings) {
        switch($source) {
            case 'LOGO': return !empty($settings['inv_header_img']) ? tenant_asset($settings['inv_header_img']) : (!empty($settings['logo']) ? tenant_asset($settings['logo']) : '');
            case 'BUSINESS_NAME': return $settings['name'] ?? config('app.name');
            case 'ADDRESS': return $settings['address'] ?? '';
            case 'PHONE': return $settings['phone'] ?? '';
            case 'GSTIN': return $settings['gst'] ?? '';
            case 'INVOICE_NO': return $bill->invoice_no;
            case 'DATE': return $bill->bill_date->format('d-m-Y');
            case 'TIME': return $bill->created_at->format('h:i A');
            case 'CUSTOMER_NAME': return $bill->customer_name ?? 'Cash Customer';
            case 'CUSTOMER_PHONE': return $bill->customer_phone ?? '';
            case 'CUSTOMER_ADDRESS': return $bill->customer_address ?? '';
            case 'CUSTOMER_GSTIN': return $bill->customer_gstin ?? '';
            case 'SUBTOTAL':
                $gstCalcType = \App\Models\Setting::get('gst_calc_type', 'inclusive');
                $isInclusive = ($gstCalcType === 'inclusive') && ($bill->gst_amount > 0);
                $displaySubtotal = $isInclusive ? ($bill->subtotal - $bill->gst_amount) : $bill->subtotal;
                return number_format($displaySubtotal, 2);
            case 'TAX_TOTAL': return number_format($bill->gst_amount, 2);
            case 'DISCOUNT_TOTAL': return number_format($bill->discount_amount, 2);
            case 'GRAND_TOTAL': return number_format($bill->grand_total, 2);
            case 'TOTAL_IN_WORDS': return 'Amount in words placeholder';
            case 'PAYMENT_MODE': return strtoupper($bill->payment_mode);
            case 'DOCUMENT_TYPE': return strtoupper((strtolower($bill->bill_type) === 'billing' ? 'Tax Invoice' : $bill->bill_type) ?: 'TAX INVOICE');
            case 'BLANK_SPACE': return '&nbsp;';
            default: return '';
        }
    };

    $getAlign = function($val) {
        if (in_array($val, ['R', 'right'])) return 'right';
        if (in_array($val, ['C', 'center'])) return 'center';
        return 'left';
    };

    $getJustify = function($val) {
        if (in_array($val, ['R', 'right'])) return 'flex-end';
        if (in_array($val, ['C', 'center'])) return 'center';
        return 'flex-start';
    };
@endphp

<style>
    /* Prevent global dark mode from turning invoice text white and preserve exact slate hierarchy */
    .dark .a4-container .text-slate-900 { color: #0f172a !important; }
    .dark .a4-container .text-slate-800 { color: #1e293b !important; }
    .dark .a4-container .text-slate-700 { color: #334155 !important; }
    .dark .a4-container .text-slate-600 { color: #475569 !important; }
    .dark .a4-container .text-slate-500 { color: #64748b !important; }
    .dark .a4-container .text-slate-400 { color: #94a3b8 !important; }
    .dark .a4-container .text-slate-300 { color: #cbd5e1 !important; }
    .dark .a4-container .text-slate-200 { color: #e2e8f0 !important; }
    .dark .a4-container .text-slate-100 { color: #f1f5f9 !important; }
    .dark .a4-container .text-slate-50 { color: #f8fafc !important; }
    
    html.dark .a4-container, .dark .a4-container {
        color: #0f172a !important; /* Base text color */
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
    <div></div> <!-- empty spacer -->
    @endif
    <div class="flex gap-4">
        @if(empty($isPublic) && !empty($bill->customer_phone))
            <button x-data="{ loading: false, send() { 
                this.loading = true; 
                fetch('{{ route('tenant.billing.whatsapp', $bill->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    this.loading = false;
                    if(data.success) { alert('WhatsApp message sent successfully!'); }
                    else { alert('Failed to send: ' + (data.message || 'Unknown Error')); }
                }).catch(err => {
                    this.loading = false;
                    alert('Error sending message');
                });
            }}" 
            @click="send" 
            :disabled="loading"
            class="px-6 py-3 bg-[#25D366] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-[#25D366]/20 hover:scale-[1.02] transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="!loading" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                <svg x-cloak x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="loading ? 'Sending...' : 'Send WhatsApp'"></span>
            </button>
        @endif
        <button onclick="window.print()" class="px-8 py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] transition-all flex items-center gap-2">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print A4 Document
        </button>
    </div>
</div>

<div class="a4-container mx-auto relative shadow-2xl bg-white overflow-hidden print:shadow-none" 
     style="width: {{ $s['totalWidth'] }}mm; font-family: {{ $s['fontFamily'] ?? 'Inter' }}, sans-serif; --primary-color: {{ $s['primaryColor'] ?? '#2563eb' }}; font-size: {{ $s['fontSize'] ?? 10 }}px;" 
     data-darkreader-ignore="true">
    {{-- Multi-Copy Loop --}}
    @foreach(($layout['watermark']['copies'] ?? ['ORIGINAL']) as $copyLabel)
        <div class="a4-page relative flex flex-col {{ !$loop->last ? 'break-after-page' : '' }}" 
             style="padding: {{ $s['marginTop'] }}mm {{ $s['marginRight'] }}mm {{ $s['marginBottom'] }}mm {{ $s['marginLeft'] }}mm; width: {{ $s['totalWidth'] }}mm; 
             {{ ($s['format'] ?? 'default') === 'modern' ? 'border-top: 8px solid var(--primary-color);' : '' }}
             {{ ($s['format'] ?? 'default') === 'tally' ? 'border: 2px solid black;' : '' }}">
            
            {{-- Watermark Overlay --}}
            @if(!empty($layout['watermark']['text']))
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none overflow-hidden space-y-[160px] z-0"
                 style="opacity: {{ $layout['watermark']['opacity'] ?? 0.1 }}">
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap transform -translate-y-24 translate-x-48">{{ $layout['watermark']['text'] }}</span>
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap">{{ $layout['watermark']['text'] }}</span>
                <span class="text-[100px] font-black text-slate-200 uppercase tracking-[0.4em] rotate-[-45deg] whitespace-nowrap transform translate-y-24 -translate-x-48">{{ $layout['watermark']['text'] }}</span>
            </div>
            @endif

            {{-- Copy Label --}}
            <div class="absolute top-8 right-8 text-[9px] font-black text-blue-400 bg-blue-50 px-3 py-1 rounded-lg z-10">{{ $copyLabel }}</div>

            <div class="relative z-10 flex flex-col h-full">
                {{-- Dynamic Sections --}}
                <div x-data="{ selectedIndex: null, draggedNode: null }" @click.outside="selectedIndex = null">
                    @foreach(($layout['sections'] ?? []) as $sectionIndex => $section)
                        <div class="mb-6">
                            <div class="grid grid-cols-{{ $section['columns'] ?? 1 }} gap-8">
                                @for($i = 1; $i <= ($section['columns'] ?? 1); $i++)
                                    <div class="space-y-1">
                                        @foreach(array_filter($section['rows'] ?? [], fn($r) => ($r['col'] ?? 1) == $i) as $rIndex => $row)
                                            <div class="relative group cursor-pointer transition-all border border-transparent hover:border-slate-200"
                                                 :class="{ 'ring-2 ring-purple-500 print:ring-0 rounded-md': selectedIndex === '{{ $sectionIndex }}-{{ $i }}-{{ $rIndex }}' }"
                                                 @click.stop="selectedIndex = '{{ $sectionIndex }}-{{ $i }}-{{ $rIndex }}'"
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
                                                <div style="font-size: {{ $row['fontSize'] ?? 11 }}px; font-weight: {{ ($row['bold'] ?? false) ? '800' : '500' }}; line-height: 1.4; text-align: {{ $getAlign($row['align'] ?? 'left') }}">
                                                    @if($row['source'] === 'LOGO')
                                                        @if($val = $getValue('LOGO'))
                                                            <div style="display: flex; justify-content: {{ $getJustify($row['align'] ?? 'left') }}">
                                                                <img src="{{ $val }}" style="height: {{ $s['logoHeight'] }}mm; width: {{ $s['logoWidth'] }}mm; object-fit: contain; margin-bottom: 10px;">
                                                            </div>
                                                        @else
                                                            <div class="w-16 h-16 bg-slate-50 flex items-center justify-center text-[10px] text-slate-300 font-black mb-2">LOGO</div>
                                                        @endif
                                                    @elseif($row['source'] === 'CUSTOM_TEXT')
                                                        <div style="display: flex; gap: 4px; justify-content: {{ $getJustify($row['align'] ?? 'left') }}">
                                                            @if(!empty($row['label']))
                                                                <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text" style="color: #94a3b8; text-transform: uppercase; font-size: 0.8em; font-weight: 800; letter-spacing: 0.05em;">{{ $row['label'] }}</span>
                                                            @endif
                                                            <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text" style="color: #0f172a; text-transform: uppercase; font-weight: inherit;">{{ $row['customText'] ?? '' }}</span>
                                                        </div>
                                                    @else
                                                        <div style="display: flex; gap: 4px; justify-content: {{ $getJustify($row['align'] ?? 'left') }}">
                                                            @if(!empty($row['label']))
                                                                <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text" style="color: #94a3b8; text-transform: uppercase; font-size: 0.8em; font-weight: 800; letter-spacing: 0.05em;">{{ $row['label'] }}</span>
                                                            @endif
                                                            <span contenteditable="true" spellcheck="false" class="outline-none hover:bg-slate-50 focus:bg-slate-100 transition-colors cursor-text" style="color: #0f172a; text-transform: uppercase; font-weight: inherit;">{!! $getValue($row['source']) !!}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div x-show="selectedIndex === '{{ $sectionIndex }}-{{ $i }}-{{ $rIndex }}'"
                                                     draggable="true"
                                                     @dragstart="draggedNode = $el.closest('.group'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '');"
                                                     class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-50 w-6 h-6 bg-white border-2 border-purple-500 rounded-full flex items-center justify-center cursor-move shadow-lg transition-opacity no-print"
                                                     title="Drag to move">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="19 9 22 12 19 15"></polyline><polyline points="9 19 12 22 15 19"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Product Table --}}
                @if($s['showProductTable'] ?? true)
                <div class="mt-4 {{ ($s['format'] ?? 'default') === 'corporate' ? '' : 'border-y-[3px] border-slate-900' }} overflow-hidden" 
                     style="margin-top: {{ $ts['topSpace'] }}mm; margin-bottom: {{ $ts['bottomSpace'] }}mm;">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="{{ ($s['format'] ?? 'default') === 'corporate' ? 'text-white' : 'border-b-2 border-slate-900 text-slate-900' }}"
                                style="{{ ($s['format'] ?? 'default') === 'corporate' ? 'background-color: var(--primary-color);' : '' }}">
                                @foreach(($layout['tableColumns'] ?? []) as $col)
                                    <th class="uppercase tracking-widest" 
                                        style="width: {{ $col['width'] }}mm; padding: {{ $ts['headerPadding'] }}mm; font-size: {{ $ts['headerSize'] }}px; font-weight: 900; text-align: {{ $col['align'] ?? 'left' }}">
                                        {{ $col['header'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($bill->items as $index => $item)
                                <tr>
                                    @foreach(($layout['tableColumns'] ?? []) as $col)
                                        <td class="text-slate-800 uppercase tracking-tight" 
                                            style="padding: {{ $ts['bodyPadding'] }}mm; font-size: {{ $ts['contentSize'] }}px; font-weight: 600; text-align: {{ $col['align'] ?? 'left' }}">
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
                    <div class="mt-4 pt-4 border-t-2 border-slate-900">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-y border-slate-200 bg-slate-50">
                                    <th class="text-[9px] font-black p-2 text-left">HSN/SAC</th>
                                    <th class="text-[9px] font-black p-2 text-right">TAXABLE VALUE</th>
                                    <th class="text-[9px] font-black p-2 text-right">CGST</th>
                                    <th class="text-[9px] font-black p-2 text-right">SGST</th>
                                    <th class="text-[9px] font-black p-2 text-right">TOTAL TAX</th>
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

                <div class="flex justify-end items-end mt-4 pt-4 border-t border-slate-200">
                    <div class="w-72">
                        <div class="space-y-1">
                            <div class="flex justify-between items-center py-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Subtotal</p>
                                <p class="text-[11px] font-black text-slate-800">{{ number_format($bill->subtotal, 2) }}</p>
                            </div>
                            @if($bill->discount_amount > 0)
                            <div class="flex justify-between items-center py-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Discount</p>
                                <p class="text-[11px] font-black text-emerald-600">-{{ number_format($bill->discount_amount, 2) }}</p>
                            </div>
                            @endif
                            @if($bill->gst_amount > 0)
                            <div class="flex justify-between items-center py-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Tax Amount ({{ $bill->gst_percent }}%)</p>
                                <p class="text-[11px] font-black text-slate-800">{{ number_format($bill->gst_amount, 2) }}</p>
                            </div>
                            @endif
                            <div class="flex justify-between items-center py-3 mt-2 border-t border-slate-200">
                                <p class="text-xs font-black text-slate-900 uppercase">Grand Total</p>
                                <p class="text-lg font-black text-blue-600 tracking-tighter">₹{{ number_format($bill->grand_total, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Signatures --}}
                <div class="mt-auto pt-16 grid grid-cols-2 gap-8">
                    <div>
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-2">Terms & Conditions</label>
                        <p class="text-[9px] leading-relaxed text-slate-600 whitespace-pre-line font-medium italic">{{ $layout['footer']['declaration'] ?? '1. Goods once sold will not be taken back.' }}</p>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <div class="w-48 border-b-2 border-slate-900 mb-2"></div>
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ $layout['footer']['signatureLabel'] ?? 'Authorized Signatory' }}</p>
                        <p class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">For {{ $settings['name'] ?? config('app.name') }}</p>
                    </div>
                </div>

            </div>
        </div>
    @endforeach
</div>
@endsection
