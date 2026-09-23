<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmWorkspace extends Model
{
    protected $fillable = ['name', 'icon', 'color'];

    public function workflows()
    {
        return $this->hasMany(CrmWorkflow::class, 'workspace_id')->orderBy('order_index');
    }
}
