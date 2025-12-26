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

    // Tipos de Prendas routes
    Route::get('tipos-prendas', [\App\Http\Controllers\TipoPrendaController::class, 'index'])->name('tipos-prendas.index')->middleware('permission:tipos_prendas.view');
    Route::get('tipos-prendas/create', [\App\Http\Controllers\TipoPrendaController::class, 'create'])->name('tipos-prendas.create')->middleware('permission:tipos_prendas.create');
    Route::post('tipos-prendas', [\App\Http\Controllers\TipoPrendaController::class, 'store'])->name('tipos-prendas.store')->middleware('permission:tipos_prendas.create');
    Route::get('tipos-prendas/{tipoPrenda}/edit', [\App\Http\Controllers\TipoPrendaController::class, 'edit'])->name('tipos-prendas.edit')->middleware('permission:tipos_prendas.edit');
    Route::put('tipos-prendas/{tipoPrenda}', [\App\Http\Controllers\TipoPrendaController::class, 'update'])->name('tipos-prendas.update')->middleware('permission:tipos_prendas.edit');
    Route::delete('tipos-prendas/{tipoPrenda}', [\App\Http\Controllers\TipoPrendaController::class, 'destroy'])->name('tipos-prendas.destroy')->middleware('permission:tipos_prendas.delete');

    // Procesos Nodos routes
    Route::get('tipos-prendas/{tipoPrenda}/procesos', [\App\Http\Controllers\ProcesoNodoController::class, 'index'])->name('proceso-nodos.index')->middleware('permission:procesos.view');
    Route::get('tipos-prendas/{tipoPrenda}/procesos/create', [\App\Http\Controllers\ProcesoNodoController::class, 'create'])->name('proceso-nodos.create')->middleware('permission:procesos.create');
    Route::post('tipos-prendas/{tipoPrenda}/procesos', [\App\Http\Controllers\ProcesoNodoController::class, 'store'])->name('proceso-nodos.store')->middleware('permission:procesos.create');
    Route::get('tipos-prendas/{tipoPrenda}/procesos/{nodo}/edit', [\App\Http\Controllers\ProcesoNodoController::class, 'edit'])->name('proceso-nodos.edit')->middleware('permission:procesos.edit');
    Route::put('tipos-prendas/{tipoPrenda}/procesos/{nodo}', [\App\Http\Controllers\ProcesoNodoController::class, 'update'])->name('proceso-nodos.update')->middleware('permission:procesos.edit');
    Route::delete('tipos-prendas/{tipoPrenda}/procesos/{nodo}', [\App\Http\Controllers\ProcesoNodoController::class, 'destroy'])->name('proceso-nodos.destroy')->middleware('permission:procesos.delete');
    Route::post('tipos-prendas/{tipoPrenda}/procesos/reorder', [\App\Http\Controllers\ProcesoNodoController::class, 'reorder'])->name('proceso-nodos.reorder')->middleware('permission:procesos.edit');
    Route::get('tipos-prendas/{tipoPrenda}/procesos/tree', [\App\Http\Controllers\ProcesoNodoController::class, 'getTreeStructure'])->name('proceso-nodos.tree');

    // Lotes routes
    Route::get('lotes', [\App\Http\Controllers\LoteController::class, 'index'])->name('lotes.index')->middleware('permission:lotes.view');
    Route::get('lotes/{lote}', [\App\Http\Controllers\LoteController::class, 'show'])->name('lotes.show')->middleware('permission:lotes.view');
    Route::put('lotes/{lote}/estado-trabajo', [\App\Http\Controllers\LoteController::class, 'updateEstadoTrabajo'])->name('lotes.updateEstadoTrabajo')->middleware('permission:lotes.edit');
    Route::post('lotes/{lote}/inicializar-procesos', [\App\Http\Controllers\LoteController::class, 'initializeProcesos'])->name('lotes.initializeProcesos')->middleware('permission:lotes.edit');

    // Operador Asignación routes
    Route::get('progreso/{progreso}/asignar-operador', [\App\Http\Controllers\Admin\OperadorAsignacionController::class, 'edit'])
        ->name('operador-asignacion.edit')
        ->middleware('permission:ordenes.edit')
        ->where('progreso', '[0-9]+');
    Route::post('progreso/{progreso}/asignar-operador', [\App\Http\Controllers\Admin\OperadorAsignacionController::class, 'assignOperador'])
        ->name('operador-asignacion.assign')
        ->middleware('permission:ordenes.edit')
        ->where('progreso', '[0-9]+');
    Route::post('progreso/{progreso}/remover-operador', [\App\Http\Controllers\Admin\OperadorAsignacionController::class, 'removeOperador'])
        ->name('operador-asignacion.remove')
        ->middleware('permission:ordenes.edit')
        ->where('progreso', '[0-9]+');
    Route::get('progreso/{progreso}/operadores-asignados', [\App\Http\Controllers\Admin\OperadorAsignacionController::class, 'getAsignados'])
        ->name('operador-asignacion.getAsignados')
        ->middleware('permission:ordenes.view')
        ->where('progreso', '[0-9]+');

    Route::get('dashboard', function () {
        return Inertia::render('Admin/Dashboard/index');
    })->name('dashboard.index');

    // Estadísticas routes
    Route::get('estadisticas', [\App\Http\Controllers\Admin\EstadisticasController::class, 'index'])->name('estadisticas.index')->middleware('role:admin');
});

// Operador routes
Route::middleware(['auth'])->prefix('operador')->name('operador.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\OperadorController::class, 'dashboard'])->name('dashboard')->middleware('permission:operador.dashboard');
    Route::get('proceso/{progreso}', [\App\Http\Controllers\OperadorController::class, 'mostrarProceso'])->name('proceso.detalle')->middleware('permission:operador.registrar');
    Route::post('progreso/{progreso}/registrar', [\App\Http\Controllers\OperadorController::class, 'registrarPrendas'])->name('progreso.registrar')->middleware('permission:operador.registrar');
    Route::post('progreso/{progreso}/completar', [\App\Http\Controllers\OperadorController::class, 'completarProceso'])->name('progreso.completar')->middleware('permission:operador.registrar');
    
    // Rutas antiguas (mantenerlas por compatibilidad)
    Route::get('lotes/{lote}/dashboard', [\App\Http\Controllers\LoteController::class, 'dashboard'])->name('lotes.dashboard')->middleware('permission:procesos.registrar');
    Route::get('lotes/{lote}/progreso', [\App\Http\Controllers\LoteProcesoProgresoController::class, 'show'])->name('progreso.show')->middleware('permission:procesos.registrar');
    Route::post('lotes/{lote}/progreso', [\App\Http\Controllers\LoteProcesoProgresoController::class, 'store'])->name('progreso.store')->middleware('permission:procesos.registrar');
    Route::post('lotes/{lote}/progreso/marcar-completado', [\App\Http\Controllers\LoteProcesoProgresoController::class, 'markAsCompleted'])->name('progreso.marcar-completado')->middleware('permission:procesos.registrar');
    Route::get('lotes/{lote}/progreso/api', [\App\Http\Controllers\LoteProcesoProgresoController::class, 'getProgress'])->name('progreso.api')->middleware('permission:procesos.registrar');
});

require __DIR__.'/auth.php';
