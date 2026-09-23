<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Purchase extends Model
{
    protected $fillable = [
        'vendor_id',
        'invoice_ref',
        'invoice_date',
        'remark',
        'discount_percent',
        'discount_amount',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'balance_amount',
        'status'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'float',
        'balance_amount' => 'float',
        'discount_percent' => 'float',
        'discount_amount' => 'float',
        'gst_percent' => 'float',
        'gst_amount' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function settlements()
    {
        return $this->hasMany(PurchaseSettlement::class);
    }
}