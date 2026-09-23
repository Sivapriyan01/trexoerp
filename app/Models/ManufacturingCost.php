<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturingCost extends Model
{
    protected $fillable = [
        'month',
        'electricity',
        'water_bill',
        'raw_material',
        'labour_charge',
        'machine_maintenance',
        'packing_cost',
        'transport_loading',
        'wastage_cost',
        'other_expenses',
        'notes',
        'created_by',
        'shift',
        'bags_40kg',
        'target_bags',
        'downtime',
        'labour_count',
        'welfare',
        'power_units',
        'total_qty',
        'qty_unit',
        'weight_per_pc',
    ];

    protected $casts = [
        'month'               => 'date',
        'electricity'         => 'float',
        'water_bill'          => 'float',
        'raw_material'        => 'float',
        'labour_charge'       => 'float',
        'machine_maintenance' => 'float',
        'packing_cost'        => 'float',
        'transport_loading'   => 'float',
        'wastage_cost'        => 'float',
        'other_expenses'      => 'float',
        'bags_40kg'           => 'integer',
        'target_bags'         => 'integer',
        'labour_count'        => 'integer',
        'welfare'             => 'float',
        'power_units'         => 'float',
        'total_qty'           => 'float',
        'weight_per_pc'       => 'float',
    ];

    public function getTotalAttribute(): float
    {
        return $this->electricity 
            + $this->water_bill 
            + $this->raw_material 
            + $this->labour_charge
            + $this->welfare
            + $this->machine_maintenance
            + $this->packing_cost
            + $this->transport_loading
            + $this->wastage_cost
            + $this->other_expenses;
    }

    // --- Shift Cost Calculations ---
    public function getTotalKgAttribute(): float
    {
        if ($this->total_qty > 0) {
            if ($this->qty_unit === 'TON') {
                return $this->total_qty * 1000;
            } elseif ($this->qty_unit === 'PCS') {
                return $this->total_qty * $this->weight_per_pc;
            }
            return $this->total_qty; // KG & LITRE
        }
        return $this->bags_40kg * 40;
    }

    public function getEquivalent40kgBagsAttribute(): float
    {
        return $this->total_kg / 40;
    }

    public function getAchievementPercentAttribute(): float
    {
        $target = $this->target_bags;
        $achieved = $this->total_qty > 0 ? $this->total_qty : $this->bags_40kg;
        return $target > 0 ? ($achieved / $target) * 100 : 0;
    }

    public function getTotalLabourCostAttribute(): float
    {
        return $this->labour_charge + $this->welfare;
    }

    public function getTotalShiftCostAttribute(): float
    {
        return $this->total_labour_cost + $this->electricity;
    }

    public function getLabourCostPerKgAttribute(): float
    {
        $totalKg = $this->total_kg;
        return $totalKg > 0 ? $this->total_labour_cost / $totalKg : 0;
    }

    public function getEnergyCostPerKgAttribute(): float
    {
        $totalKg = $this->total_kg;
        return $totalKg > 0 ? $this->electricity / $totalKg : 0;
    }

    public function getTotalCostPerKgAttribute(): float
    {
        $totalKg = $this->total_kg;
        return $totalKg > 0 ? $this->total_shift_cost / $totalKg : 0;
    }

    public function creator()
    {
        return $this->belongsTo(TenantUser::class, 'created_by');
    }
}
