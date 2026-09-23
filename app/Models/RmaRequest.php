<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RmaRequest extends Model
{
    protected $fillable = [
        'rma_number', 'bill_id', 'customer_id', 'type', 'status', 'reason', 'notes', 'created_by'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(RmaRequestItem::class);
    }
}
