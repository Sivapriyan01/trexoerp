<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'discount_percent',
        'duration_days',
        'is_active',
        'benefits',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'benefits' => 'array',
    ];

    public function customerMemberships()
    {
        return $this->hasMany(CustomerMembership::class);
    }
}
