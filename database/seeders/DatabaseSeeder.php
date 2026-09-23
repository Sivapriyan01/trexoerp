<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. Create or update Super Admin (Central DB)
        // -------------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('admin@1234'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Super Admin created/updated: admin@gmail.com / admin@1234');

        // -------------------------------------------------------
        // 2. Create Demo Tenant
        // -------------------------------------------------------
        $tenant = Tenant::firstOrCreate(
            ['id' => 'demo'],
            [
                'name'   => 'Square Demo Store',
                'email'  => 'demo@square.in',
                'plan'   => 'pro',
                'status' => 'active',
            ]
        );

        // Attach domain to tenant
        $tenant->domains()->firstOrCreate([
            'domain' => 'demo.localhost',
        ]);

        $this->command->info('Demo tenant created: demo.localhost');

        // Ensure the tenant database exists and is migrated.
        $tenantDatabaseManager = $tenant->database()->manager();
        $tenantDatabaseName = $tenant->database()->getName();

        if (! $tenantDatabaseManager->databaseExists($tenantDatabaseName)) {
            $this->command->info("Tenant database {$tenantDatabaseName} does not exist. Creating it now.");
            $createDatabaseJob = new CreateDatabase($tenant);
            $createDatabaseJob->handle(app(DatabaseManager::class));

            $this->command->info("Migrating tenant database {$tenantDatabaseName}.");
            $migrateDatabaseJob = new MigrateDatabase($tenant);
            $migrateDatabaseJob->handle();
        }

        // -------------------------------------------------------
        // 3. Seed Tenant Users inside tenant DB
        // -------------------------------------------------------
        tenancy()->initialize($tenant);
        app(\Stancl\Tenancy\Database\DatabaseManager::class)->connectToTenant($tenant);

        \App\Models\TenantUser::firstOrCreate(
            ['email' => 'nanthu@demo.com'],
            [
                'name'      => 'Nanthakumar',
                'password'  => Hash::make('Demo@1234'),
                'role'      => 'branch_admin',
                'phone'     => '8680852478',
                'address'   => '12 Ruagi Chennai',
                'is_active' => true,
            ]
        );

        \App\Models\TenantUser::firstOrCreate(
            ['email' => 'cashier@demo.com'],
            [
                'name'      => 'Demo Cashier',
                'password'  => Hash::make('Demo@1234'),
                'role'      => 'cashier',
                'phone'     => '9090909090',
                'is_active' => true,
            ]
        );

        tenancy()->end();

        $this->command->info('Tenant users created:');
        $this->command->info('  Branch Admin: nanthu@demo.com / Demo@1234');
        $this->command->info('  Cashier:      cashier@demo.com / Demo@1234');
    }
}
