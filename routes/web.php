<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('auth.index');
})->name('login');



Route::get('/', function () {
    return Inertia::render('Welcome');
})->middleware(['auth', 'verified'])->name('welcome');

// Temporary debug route to inspect server-side permissions for the authenticated user
Route::get('/debug/auth-perms', function () {
    $user = auth()->user();
    if (!$user) return response()->json(['error' => 'unauthenticated'], 401);

    $viaTrait = [];
    try {
        $viaTrait = $user->getPermissionNames()->toArray();
    } catch (\Throwable $e) {
        $viaTrait = ['error' => $e->getMessage()];
    }

    // Also compute via direct DB joins (roles + direct permissions)
    $rolePerms = \Illuminate\Support\Facades\DB::table('permissions')
        ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $user->getKey())
        ->pluck('permissions.name')
        ->toArray();

    $directPerms = \Illuminate\Support\Facades\DB::table('permissions')
        ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
        ->where('model_has_permissions.model_id', $user->getKey())
        ->pluck('permissions.name')
        ->toArray();

    return response()->json([
        'viaTrait' => $viaTrait,
        'rolePerms' => array_values(array_unique($rolePerms)),
        'directPerms' => array_values(array_unique($directPerms)),
    ]);
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Users routes with permission middleware
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show')->middleware('permission:users.view');
    Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
    Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
    Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Roles routes with permission middleware
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
    Route::get('roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'show'])->name('roles.show')->middleware('permission:roles.view');
    Route::get('roles/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
    Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
    Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');

    // Ordenes routes with permission middleware
    Route::get('ordenes', [\App\Http\Controllers\Admin\OrdenController::class, 'index'])->name('ordenes.index')->middleware('permission:ordenes.view');
    Route::get('ordenes/create', [\App\Http\Controllers\Admin\OrdenController::class, 'create'])->name('ordenes.create')->middleware('permission:ordenes.create');
    Route::post('ordenes', [\App\Http\Controllers\Admin\OrdenController::class, 'store'])->name('ordenes.store')->middleware('permission:ordenes.create');
    Route::get('ordenes/{orden}', [\App\Http\Controllers\Admin\OrdenController::class, 'show'])->name('ordenes.show')->middleware('permission:ordenes.view');
    Route::get('ordenes/{orden}/edit', [\App\Http\Controllers\Admin\OrdenController::class, 'edit'])->name('ordenes.edit')->middleware('permission:ordenes.edit');
    Route::put('ordenes/{orden}', [\App\Http\Controllers\Admin\OrdenController::class, 'update'])->name('ordenes.update')->middleware('permission:ordenes.edit');
    Route::delete('ordenes/{orden}', [\App\Http\Controllers\Admin\OrdenController::class, 'destroy'])->name('ordenes.destroy')->middleware('permission:ordenes.delete');

    // Nested routes for lotes tied to an Orden (explicit for clarity) with permission middleware
    Route::get('ordenes/{orden}/lotes', [\App\Http\Controllers\Admin\LoteController::class, 'index'])->name('ordenes.lotes.index')->middleware('permission:lotes.view');
    Route::post('ordenes/{orden}/lotes', [\App\Http\Controllers\Admin\LoteController::class, 'store'])->name('ordenes.lotes.store')->middleware('permission:lotes.create');
    Route::get('ordenes/lotes/{lote}', [\App\Http\Controllers\Admin\LoteController::class, 'show'])->name('ordenes.lotes.show')->middleware('permission:lotes.view');
    Route::put('ordenes/lotes/{lote}', [\App\Http\Controllers\Admin\LoteController::class, 'update'])->name('ordenes.lotes.update')->middleware('permission:lotes.edit');
    Route::delete('ordenes/lotes/{lote}', [\App\Http\Controllers\Admin\LoteController::class, 'destroy'])->name('ordenes.lotes.destroy')->middleware('permission:lotes.delete');

    Route::get('dashboard', function () {
        return Inertia::render('Admin/Dashboard/index');
    })->name('dashboard.index');
});

require __DIR__.'/auth.php';
