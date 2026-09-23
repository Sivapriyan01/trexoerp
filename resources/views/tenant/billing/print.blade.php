<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ strtoupper((strtolower($bill->bill_type) === 'billing' ? 'Tax Invoice' : $bill->bill_type) ?: 'Invoice') }} #{{ $bill->invoice_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.4; color: #000; margin: 0; padding: 20px; width: 80mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .header { margin-bottom: 15px; }
        .store-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; border-bottom: 1px solid #000; padding: 5px 0; font-size: 10px; }
        td { padding: 5px 0; vertical-align: top; font-size: 11px; }
        .total-row td { border-top: 1px solid #000; padding-top: 10px; }
        .footer { margin-top: 20px; font-size: 10px; }
        .no-print { margin-top: 20px; display: flex; gap: 10px; justify-content: center; }
        .btn-wa { background: #25D366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; font-family: sans-serif; font-size: 12px; }
        @media print {
            body { padding: 0; }
            @page { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        @if(empty($isPublic))
            <button id="wa-btn" onclick="sendWa()" class="btn-wa" style="border:none;cursor:pointer;">Share on WhatsApp</button>
            <button onclick="window.print()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #ccc; cursor: pointer;">Print Receipt</button>
        @endif
    </div>
    <div class="header text-center">
        @if(!empty($settings['inv_header_img']) || !empty($settings['logo']))
            <img src="{{ !empty($settings['inv_header_img']) ? tenant_asset($settings['inv_header_img']) : tenant_asset($settings['logo']) }}" style="max-width: 50mm; height: auto; margin-bottom: 10px;">
        @endif
        <div class="store-name">{{ $settings['name'] ?? config('app.name') }}</div>
        <div>{{ $settings['address'] ?? '' }}</div>
        <div>Mob: {{ $settings['phone'] ?? '' }}</div>
        @if(!empty($settings['gst']))
            <div>GSTIN: {{ $settings['gst'] }}</div>
        @endif
        
        @if($bill->customer_name)
            <div class="divider"></div>
            <div>Customer: {{ $bill->customer_name }}</div>
            <div>Mob: {{ $bill->customer_phone }}</div>
            @if($bill->customer_gstin)
                <div>GSTIN: {{ $bill->customer_gstin }}</div>
            @endif
        @endif
    </div>

    <div class="divider"></div>
    <div class="text-center font-bold" style="font-size: 14px; text-transform: uppercase;">
        @php
            $displayType = strtolower($bill->bill_type) === 'billing' ? 'TAX INVOICE' : $bill->bill_type;
        @endphp
        {{ strtoupper($displayType ?: 'TAX INVOICE') }}
    </div>
    <div class="divider"></div>
    <div style="display: flex; justify-content: space-between;">
        <span>No: {{ $bill->invoice_no }}</span>
        <span>Date: {{ $bill->bill_date->format('d/m/y') }}</span>
    </div>
    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">ITEM</th>
                <th style="width: 15%;" class="text-center">QTY</th>
                <th style="width: 35%;" class="text-right">AMT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->mrp * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="font-bold">SUBTOTAL</td>
            <td class="text-right font-bold">
                @php
                    $gstCalcType = \App\Models\Setting::get('gst_calc_type', 'inclusive');
                    $isInclusive = ($gstCalcType === 'inclusive') && ($bill->gst_amount > 0);
                    $displaySubtotal = $isInclusive ? ($bill->subtotal - $bill->gst_amount) : $bill->subtotal;
                @endphp
                ₹{{ number_format($displaySubtotal, 2) }}
            </td>
        </tr>
        @if($bill->discount_amount > 0)
        <tr>
            <td>DISCOUNT</td>
            <td class="text-right">-₹{{ number_format($bill->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if($bill->gst_amount > 0)
        <tr>
            <td>GST ({{ $bill->gst_percent }}%)</td>
            <td class="text-right">₹{{ number_format($bill->gst_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="font-bold" style="font-size: 14px;">GRAND TOTAL</td>
            <td class="text-right font-bold" style="font-size: 14px;">₹{{ number_format($bill->grand_total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="text-center">Mode: {{ strtoupper($bill->payment_mode) }}</div>
    <div class="divider"></div>

    <div class="footer text-center">
        <p>{{ $settings['terms'] ?? '' }}</p>
        <p>Powered by TrexoERP POS</p>
    </div>
    <script>
        function sendWa() {
            var btn = document.getElementById('wa-btn');
            btn.innerText = 'Sending...';
            btn.disabled = true;
            fetch('{{ route('tenant.billing.whatsapp', $bill->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(res => res.json()).then(data => {
                btn.innerText = 'Share on WhatsApp';
                btn.disabled = false;
                if(data.success) { alert('WhatsApp message sent successfully!'); }
                else { alert('Failed to send: ' + (data.message || 'Unknown Error')); }
            }).catch(err => {
                btn.innerText = 'Share on WhatsApp';
                btn.disabled = false;
                alert('Error sending message');
            });
        }
    </script>
</body>
</html>

