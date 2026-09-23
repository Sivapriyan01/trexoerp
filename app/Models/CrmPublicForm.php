<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmPublicForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'workflow_id',
        'slug',
        'name',
        'type',
        'title',
        'description',
        'fields',
        'settings',
        'is_active',
        'expiry_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'expiry_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(CrmWorkspace::class, 'workspace_id');
    }

    public function workflow()
    {
        return $this->belongsTo(CrmWorkflow::class, 'workflow_id');
    }
}
