<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'authPermissions' => function () use ($request) {
                try {
                    $user = $request->user();
                    if (!$user) return [];

                    $userId = $user->getKey();

                    // Permissions via roles
                    $rolePermissions = DB::table('permissions')
                        ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                        ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
                        ->where('model_has_roles.model_id', $userId)
                        ->pluck('permissions.name')
                        ->toArray();

                    // Direct model permissions
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
            },
        ];
    }
}
