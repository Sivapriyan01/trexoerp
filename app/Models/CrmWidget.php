<?php
// app/Models/CrmWidget.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmWidget extends Model
{
    protected $fillable = ['dashboard_id', 'title', 'type', 'workflow_id', 'field_id', 'settings', 'order_index'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function dashboard()
    {
        return $this->belongsTo(CrmDashboard::class);
    }

    public function workflow()
    {
        return $this->belongsTo(CrmWorkflow::class);
    }
}
