<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Route;
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
        Vite::prefetch(concurrency: 3);

        // Register Spatie permission middleware aliases so routes can use 'role:...'
        Route::aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
        Route::aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        Route::aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);
    }
}
