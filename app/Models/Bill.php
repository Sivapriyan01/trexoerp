<?php
// app/Models/Bill.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Bill
 *
 * @property int $id
 * @property string $invoice_no
 * @property string $bill_type
 * @property int|null $customer_id
 * @property string|null $customer_phone
 * @property string|null $customer_email
 * @property string|null $customer_name
 * @property string|null $customer_address
 * @property \Illuminate\Support\Carbon|null $bill_date
 * @property float $subtotal
 * @property float $discount_percent
 * @property float $discount_amount
 * @property float $gst_percent
 * @property float $gst_amount
 * @property float $round_off
 * @property float $grand_total
 * @property float $paid_amount
 * @property float $balance
 * @property string $payment_mode
 * @property string $status
 * @property string|null $remarks
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BillItem[] $items
 * @property-read \App\Models\Customer|null $customer
 */
class Bill extends Model
{
    protected $fillable = [
        'invoice_no',
        'bill_type',
        'customer_id',
        'customer_phone',
        'customer_email',
        'customer_name',
        'customer_address',
        'customer_gstin',
        'bill_date',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'gst_percent',
        'gst_amount',
        'round_off',
        'grand_total',
        'paid_amount',
        'balance',
        'payment_mode',
        'status',
        'reminder_enabled',
        'created_by',
        'remarks',
        'expected_delivery_date',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'gst_amount' => 'float',
        'grand_total' => 'float',
    ];

    // ── Relations ──────────────────────────────────────────────────────
    public function items()
    {
        return $this->hasMany(BillItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // ── Invoice Number Generator ────────────────────────────────────────
    public static function generateInvoiceNo(string $type = 'billing'): string
    {
        $customPrefix = \App\Models\Setting::get('billing_prefix', '');

        $prefix = match ($type) {
            'outward' => 'OW',
            'quick' => 'QB',
            'return' => 'RET',
            'exchange' => 'EXC',
            'replacement' => 'REP',
            'credit_note' => 'CN',
            default => !empty($customPrefix) ? $customPrefix : 'INV',
        };

        // Remove trailing dash if present in prefix to avoid double dashes
        $prefix = rtrim($prefix, '-');

        $date = now()->format('Ymd');
        $last = static::where('invoice_no', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($last) {
            $parts = explode('-', $last->invoice_no);
            $lastSeq = (int) end($parts);
            $nextNum = $lastSeq + 1;
        }

        $seq = str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$seq}";
    }

    // ── Calculate Totals ────────────────────────────────────────────────
    public function recalculate(): void
    {
        $subtotal = $this->items()->sum(DB::raw('mrp * quantity'));

        // Respect Global Discount Setting
        $isDiscountEnabled = \App\Models\Setting::get('bill_discount_enabled', '1') == '1';
        $discountAmt = 0;

        if ($isDiscountEnabled) {
            $discountAmt = $this->discount_percent > 0
                ? round($subtotal * $this->discount_percent / 100, 2)
                : $this->discount_amount;
        }

        $afterDiscount = $subtotal - $discountAmt;

        // Respect Global GST Setting
        $isGstEnabled = \App\Models\Setting::get('gst_enabled', \App\Models\Setting::get('bill_gst_enabled', '1')) == '1';
        $gstPercent = $isGstEnabled ? ($this->gst_percent ?: \App\Models\Setting::get('gst_default_percent', \App\Models\Setting::get('billing_tax_percent', 0))) : 0;

        $gstAmt = 0;
        $rawTotal = $afterDiscount;

        if ($isGstEnabled && $gstPercent > 0) {
            $gstCalcType = \App\Models\Setting::get('gst_calc_type', 'inclusive');
            if ($gstCalcType === 'inclusive') {
                $gstAmt = round($afterDiscount * ($gstPercent / (100 + $gstPercent)), 2);
                $rawTotal = $afterDiscount;
            } else {
                $gstAmt = round($afterDiscount * ($gstPercent / 100), 2);
                $rawTotal = $afterDiscount + $gstAmt;
            }
        }

        // Respect Rounding Setting
        $roundRule = (float) \App\Models\Setting::get('bill_round_value', '0.5');

        if ($roundRule == 1.0) {
            $grandTotal = round($rawTotal);
        } else {
            // Round to nearest 0.5
            $grandTotal = round($rawTotal * 2) / 2;
        }

        $roundOff = $grandTotal - $rawTotal;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmt,
            'gst_amount' => $gstAmt,
            'round_off' => $roundOff,
            'grand_total' => $grandTotal,
            'balance' => $grandTotal - $this->paid_amount,
        ]);
    }

    // ── WhatsApp Sharing ────────────────────────────────────────────────
    public function getWhatsappMessageAttribute(): string
    {
        // Priority 1: Use Business Name from Settings
        $shopName = \App\Models\Setting::get('business_name', '');

        if (empty($shopName)) {
            // Fallback: Use tenant name or default
            $shopName = function_exists('tenant') && tenant('name') ? tenant('name') : 'TrexoERP Store';
            $search = ['.localhost', 'demo.', 'demo ', 'rx ', 'trexoerp', 'http://', 'https://'];
            $shopName = str_ireplace($search, '', $shopName);
            $shopName = trim($shopName);
        }

        if (strtolower($shopName) === 'laravel' || empty($shopName)) {
            $shopName = 'TrexoERP Store';
        }

        $msg = "🌟 *Invoice from {$shopName}* 🌟\n\n";
        $msg .= "Thank you for your business! Your Invoice *#{$this->invoice_no}* is ready.\n\n";
        $msg .= "💰 *Total Amount:* ₹" . number_format($this->grand_total, 2) . "\n";
        $formattedDate = $this->bill_date instanceof \Illuminate\Support\Carbon
            ? $this->bill_date->format('d M, Y')
            : \Illuminate\Support\Carbon::parse($this->bill_date)->format('d M, Y');
        $msg .= "📅 *Date:* " . $formattedDate . "\n\n";

        // Generate a public signed URL for the invoice
        $link = \Illuminate\Support\Facades\URL::signedRoute('tenant.public.invoice', ['bill' => $this->id]);
        $msg .= "🔗 *View Invoice:* " . $link . "\n\n";

        $msg .= "Have a great day! ✨";

        return $msg;
    }

    // ── WhatsApp Sharing ────────────────────────────────────────────────
    public function getWhatsAppLinkAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->customer_phone);
        if (strlen($phone) == 10)
            $phone = "91" . $phone;

        return "https://wa.me/{$phone}?text=" . urlencode($this->whatsapp_message);
    }
}
