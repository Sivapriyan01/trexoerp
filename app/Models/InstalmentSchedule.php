<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalmentSchedule extends Model
{
    protected $fillable = [
        'instalment_id', 'instalment_no', 'type', 
        'due_date', 'amount', 'status', 'paid_at'
    ];

    public function instalment()
    {
        return $this->belongsTo(Instalment::class);
    }
}
