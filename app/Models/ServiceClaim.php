<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceClaim extends Model
{
    protected $fillable = [
        'claim_id',
        'claim_date',
        'customer_name',
        'customer_phone',
        'product_name',
        'issue_description',
        'status',
        'resolution_time',
    ];
}
