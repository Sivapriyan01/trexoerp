<?php

namespace App\Listeners;

use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Events\TenantCreated;

class CreateTenantAdmin
{
    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        // Switch to tenant context and create the admin user
        $tenant->run(function () use ($tenant) {
            TenantUser::create([
                'name'      => $tenant->name ?? 'Admin',
                'email'     => $tenant->email,
                'password'  => '1234', // As requested by user
                'role'      => 'branch_admin',
                'is_active' => true,
            ]);
        });
    }
}
