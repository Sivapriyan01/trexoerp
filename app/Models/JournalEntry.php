<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = ['date', 'reference_number', 'description', 'total_amount'];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
