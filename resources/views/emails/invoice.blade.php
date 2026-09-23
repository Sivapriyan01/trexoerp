<x-mail::message>
# Invoice #{{ $bill->invoice_no }}

Hello **{{ $bill->customer_name ?? 'Valued Customer' }}**,

Thank you for your business! Your invoice is ready and details are provided below.

<x-mail::panel>
### Summary
**Total Amount:** ₹{{ number_format($bill->grand_total, 2) }}  
**Date:** {{ $bill->bill_date->format('d M, Y') }}  
**Status:** {{ strtoupper($bill->status) }}
</x-mail::panel>

<x-mail::table>
| Item | Qty | Price | Total |
| :--- | :---: | :---: | :--- |
@foreach($bill->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ₹{{ number_format($item->mrp, 2) }} | ₹{{ number_format($item->mrp * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

@if($bill->balance > 0)
**Pending Balance:** ₹{{ number_format($bill->balance, 2) }}
@endif

<x-mail::button :url="route('tenant.billing.print', $bill->id)">
Download PDF Invoice
</x-mail::button>

If you have any questions, please feel free to contact us.

Regards,  
**{{ tenant('name') ?? config('app.name') }}**  
_Premium Retail Solutions_
</x-mail::message>
