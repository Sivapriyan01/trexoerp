<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instalment extends Model
{
    protected $fillable = [
        'bill_id', 'customer_id', 'total_amount', 
        'paid_amount', 'due_amount', 'status', 'next_due_date'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function schedules()
    {
        return $this->hasMany(InstalmentSchedule::class);
    }

    public function payments()
    {
        return $this->hasMany(InstalmentPayment::class);
    }

    public function getReminderLink($scheduleNo, $amount, $dueDate)
    {
        $phone = preg_replace('/[^0-9]/', '', $this->customer->phone);
        if (strlen($phone) == 10) $phone = "91" . $phone;

        $shopName = function_exists('tenant') && tenant('name') ? tenant('name') : 'TrexoERP Store';
        
        // Clean up technical prefixes and domain names
        $search = ['.localhost', 'demo.', 'demo ', 'rx ', 'trexoerp', 'http://', 'https://'];
        $shopName = str_ireplace($search, '', $shopName);
        $shopName = trim($shopName);

        if (strtolower($shopName) === 'laravel' || empty($shopName)) {
            $shopName = 'TrexoERP Store';
        }
        
        // Ensure "Store" is part of the name if preferred
        if (!str_contains(strtolower($shopName), 'store')) {
            $shopName .= ' Store';
        }

        $shopName = ucwords($shopName);

        $msg = "🔔 *Payment Reminder from {$shopName}* 🔔\n\n";
        $msg .= "Hello *{$this->customer->name}*,\n";
        $msg .= "This is a friendly reminder that your Instalment *#{$scheduleNo}* for ₹" . number_format($amount, 2) . " is due on *" . date('d M, Y', strtotime($dueDate)) . "*.\n\n";
        $msg .= "Kindly process the payment to avoid any late fees. Thank you! 🙏";

        return "https://wa.me/{$phone}?text=" . urlencode($msg);
    }
}
