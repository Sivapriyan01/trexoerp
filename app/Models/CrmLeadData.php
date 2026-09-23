<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeadData extends Model
{
    protected $fillable = ['lead_id', 'field_id', 'value'];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'lead_id');
    }

    public function field()
    {
        return $this->belongsTo(CrmWorkflowField::class, 'field_id');
    }
}
