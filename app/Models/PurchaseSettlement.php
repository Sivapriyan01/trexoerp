<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseSettlement extends Model
{
    protected $fillable = [
        'purchase_id', 'vendor_id', 'date', 'amount', 
        'payment_mode', 'document_number', 'description', 'entry_type'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }
}
