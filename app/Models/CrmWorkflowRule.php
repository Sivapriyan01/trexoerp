<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmWorkflowRule extends Model
{
    protected $fillable = ['source_workflow_id', 'target_workflow_id', 'logic_json'];

    protected $casts = [
        'logic_json' => 'array'
    ];

    public function source()
    {
        return $this->belongsTo(CrmWorkflow::class, 'source_workflow_id');
    }

    public function target()
    {
        return $this->belongsTo(CrmWorkflow::class, 'target_workflow_id');
    }
}
