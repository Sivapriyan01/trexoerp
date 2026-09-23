<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RmaRequestItem extends Model
{
    protected $fillable = [
        'rma_request_id', 'bill_item_id', 'category_id', 'quantity', 'condition', 'reason', 'inspection_status'
    ];

    public function rmaRequest()
    {
        return $this->belongsTo(RmaRequest::class);
    }

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
