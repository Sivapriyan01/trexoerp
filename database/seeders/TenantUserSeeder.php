<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        TenantUser::updateOrCreate(
            ['email' => 'hema@gmail.com'],
            [
                'name'      => 'Hema Admin',
                'password'  => Hash::make('password'),
                'role'      => 'branch_admin',
                'is_active' => true,
            ]
        );
    }
}
