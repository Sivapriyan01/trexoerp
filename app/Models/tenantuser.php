<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TenantUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'branch_id',
        'is_active',
        'full_access',
        'permissions',
        'whatsapp_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'full_access' => 'boolean',
        'permissions' => 'array',
        'whatsapp_enabled' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function hasPermission($module)
    {
        if ($this->full_access) return true;
        if (!$this->permissions) return false;
        
        // Support exact match or prefix match for complex permissions
        return in_array($module, $this->permissions) || collect($this->permissions)->some(fn($p) => str_starts_with($p, $module . ':'));
    }

    public function hasCrmPermission($workflowId, $action)
    {
        if ($this->full_access) return true;
        if (!$this->permissions) return false;

        return in_array("CRM_WF:{$workflowId}:{$action}", $this->permissions);
    }


    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->isStoreAdmin()) {
                throw new \Exception("The Store Admin account is protected and cannot be deleted.");
            }
        });
    }

    public function isStoreAdmin(): bool
    {
        $tenant = function_exists('tenant') ? tenant() : null;
        if ($tenant && !empty($tenant->email) && strcasecmp($this->email, $tenant->email) === 0) {
            return true;
        }

        if ($this->full_access && ($this->role === 'branch_admin' || $this->role === 'admin' || $this->role === 'branch admin')) {
            $firstAdminId = static::where('full_access', true)->orderBy('id', 'asc')->value('id');
            if ($this->id === $firstAdminId || static::where('full_access', true)->count() <= 1) {
                return true;
            }
        }

        return false;
    }

    public function isBranchAdmin()
    {
        return $this->role === 'branch_admin' || $this->role === 'admin' || $this->role === 'branch admin';
    }
}