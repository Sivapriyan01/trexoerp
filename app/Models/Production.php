<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'customer_id',
        'total_qty',
        'qty_unit',
        'current_stage',
        'status',
        'start_date',
        'deadline_date',
        'end_date',
        'priority',
        'estimated_value',
        'specifications',
        'assigned_processes',
        'current_stage_started_at',
        'current_stage_notes',
        'assigned_workers',
        'created_by',
        'remarks',
        'cost_electricity',
        'cost_water_bill',
        'cost_raw_material',
        'cost_labour',
        'cost_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline_date' => 'date',
        'end_date'   => 'date',
        'assigned_processes' => 'array',
        'assigned_workers' => 'array',
        'current_stage_started_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(TenantUser::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(ProductionLog::class);
    }
}
