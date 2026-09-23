<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'product_name','product_type','size','brand',
        'quantity','buy_price','total_amount',
        'discount_percent','discount_amount',
        'gst_percent','gst_amount','grand_total',
        'profit_percent','profit_amount',
        'mrp','dealer_price','barcode','color','image'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}