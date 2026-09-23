<?php
// app/Models/DailyExpense.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExpense extends Model
{
    protected $fillable = [
        'expense_date',
        'type',
        'category',
        'description',
        'source',
        'payment_mode',
        'amount',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'float',
    ];

    /** Scope: only regular expenses */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /** Scope: only petty cash inward */
    public function scopePettyCash($query)
    {
        return $query->where('type', 'petty_cash');
    }

    /** Scope: filter by date range */
    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }
}
