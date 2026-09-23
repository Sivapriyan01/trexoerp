<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'gstin',
        'city',
        'state',
        'pincode',
        'points',
        'wallet_balance',
        'bill_count',
        'anniversary_date',
        'anniversary_reminder_enabled',
    ];

    protected $casts = [
        'points' => 'float',
        'wallet_balance' => 'float',
        'bill_count' => 'integer',
        'anniversary_date' => 'date',
        'anniversary_reminder_enabled' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $customer->code = 'CUS-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function memberships()
    {
        return $this->hasMany(CustomerMembership::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(CustomerMembership::class)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->latest('start_date');
    }
}
