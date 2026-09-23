<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmWorkflowField extends Model
{
    protected $fillable = ['workflow_id', 'label', 'name', 'type', 'is_required', 'options', 'order_index'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(CrmWorkflow::class, 'workflow_id');
    }
}
