<?php
// app/Models/CrmApi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmApi extends Model
{
    protected $fillable = ['name', 'workspace_id', 'workflow_id', 'api_key', 'is_active'];

    public function workspace()
    {
        return $this->belongsTo(CrmWorkspace::class);
    }

    public function workflow()
    {
        return $this->belongsTo(CrmWorkflow::class);
    }
}
