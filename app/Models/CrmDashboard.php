<?php
// app/Models/CrmDashboard.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmDashboard extends Model
{
    protected $fillable = ['name', 'workspace_id', 'created_by'];

    public function widgets()
    {
        return $this->hasMany(CrmWidget::class, 'dashboard_id')->orderBy('order_index');
    }

    public function workspace()
    {
        return $this->belongsTo(CrmWorkspace::class);
    }
}
