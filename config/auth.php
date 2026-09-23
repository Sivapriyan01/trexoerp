<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard'     => 'web',        // Default = Super Admin
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [

        // 🔐 Super Admin (Central DB)
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // 🏢 Tenant Users (Tenant DB)
        'tenant' => [
            'driver'   => 'session',
            'provider' => 'tenant_users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        // 👤 Super Admin Model (Central Database)
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        // 👥 Tenant User Model (Tenant Database)
        'tenant_users' => [
    'driver' => 'eloquent',
    'model'  => App\Models\TenantUser::class,
],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [

        // 🔐 Super Admin Password Reset
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        // 🔐 Tenant User Password Reset
        'tenant_users' => [
            'provider' => 'tenant_users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];