<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'from_stage',
        'to_stage',
        'action_by',
        'notes',
        'workers'
    ];

    protected $casts = [
        'workers' => 'array'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function actor()
    {
        return $this->belongsTo(TenantUser::class, 'action_by');
    }
}
