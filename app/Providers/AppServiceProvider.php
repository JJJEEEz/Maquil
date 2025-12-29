<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\UrlGenerator;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
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
    public function boot(UrlGenerator $url): void
    {
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Register Spatie permission middleware aliases so routes can use 'role:...'
        Route::aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
        Route::aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        Route::aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);

        // Share authPermissions globally as fallback (DB-based) to ensure availability
        Inertia::share('authPermissions', function () {
            try {
                if (!auth()->check()) return [];
                $userId = auth()->user()->getKey();

                $rolePermissions = DB::table('permissions')
                    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_id', $userId)
                    ->pluck('permissions.name')
                    ->toArray();

                $directPermissions = DB::table('permissions')
                    ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
                    ->where('model_has_permissions.model_id', $userId)
                    ->pluck('permissions.name')
                    ->toArray();

                $all = array_unique(array_merge($rolePermissions, $directPermissions));
                return array_values($all);
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
