<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole() && $this->app->bound('request')) {
            $request = $this->app->make('request');
            if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }
    }
}
