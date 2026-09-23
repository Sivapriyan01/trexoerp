<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    protected $fillable = ['workflow_id', 'assigned_to', 'status', 'source'];

    public function workflow()
    {
        return $this->belongsTo(CrmWorkflow::class, 'workflow_id');
    }

    public function data()
    {
        return $this->hasMany(CrmLeadData::class, 'lead_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getFieldValue($fieldName)
    {
        // Ensure data and field relations are loaded for performance and accuracy
        if (!$this->relationLoaded('data')) {
            $this->load('data.field');
        }
        
        return $this->data->filter(function($d) use ($fieldName) {
            return ($d->field->name === $fieldName) || ($d->field->label === $fieldName);
        })->first()?->value;
    }
}
