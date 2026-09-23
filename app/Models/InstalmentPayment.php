<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalmentPayment extends Model
{
    protected $fillable = [
        'instalment_id', 'schedule_id', 'amount', 
        'payment_date', 'payment_method', 'remark'
    ];

    public function instalment()
    {
        return $this->belongsTo(Instalment::class);
    }

    public function schedule()
    {
        return $this->belongsTo(InstalmentSchedule::class);
    }
}
