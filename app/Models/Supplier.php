<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
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
        'is_active',
        'advance_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($supplier) {
            if (empty($supplier->code)) {
                $supplier->code = 'SUP-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'vendor_id');
    }
}
