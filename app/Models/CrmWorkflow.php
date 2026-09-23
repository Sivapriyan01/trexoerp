<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmWorkflow extends Model
{
    protected $fillable = ['name', 'icon', 'color', 'order_index', 'is_active', 'workspace_id'];

    public function workspace()
    {
        return $this->belongsTo(CrmWorkspace::class, 'workspace_id');
    }

    public function fields()
    {
        return $this->hasMany(CrmWorkflowField::class, 'workflow_id')->orderBy('order_index');
    }

    public function leads()
    {
        return $this->hasMany(CrmLead::class, 'workflow_id');
    }
}
