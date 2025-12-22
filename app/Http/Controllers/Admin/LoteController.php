<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Orden;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    /**
     * Display a listing of the resource for a given Orden.
     */
    public function index(Orden $orden)
    {
        $orden->load('lotes');

        $authPermissions = [];
        if (auth()->check()) {
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
            $authPermissions = array_values(array_unique(array_merge($rolePermissions, $directPermissions)));
        }

        // Return a plain array to avoid any Eloquent serialization quirks
        return Inertia::render('Admin/Ordenes/Show', [
            'orden' => $orden->toArray(),
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Store a newly created Lote for a given Orden.
     */
    public function store(Request $request, Orden $orden)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'estado_trabajo' => 'required|in:trabajado,no_trabajado,interrumpido',
            'razon_interrupcion' => 'required_if:estado_trabajo,interrumpido|nullable|string',
        ]);

        $lote = $orden->lotes()->create([
            'fecha' => $data['fecha'],
            'estado_trabajo' => $data['estado_trabajo'],
            'razon_interrupcion' => $data['razon_interrupcion'] ?? null,
        ]);

        // Crear automáticamente los registros de progreso para cada proceso del tipo de prenda
        $orden->load('tipoPrenda.procesoNodos');
        if ($orden->tipoPrenda && $orden->tipoPrenda->procesoNodos) {
            foreach ($orden->tipoPrenda->procesoNodos as $procesoNodo) {
                $lote->loteProcesoProgresos()->create([
                    'proceso_nodo_id' => $procesoNodo->id,
                    'cantidad_objetivo' => $orden->target_quantity ?? 0,
                    'cantidad_completada' => 0,
                    'cantidad_merma' => 0,
                    'cantidad_excedente' => 0,
                    'estado' => 'pendiente',
                ]);
            }
        }

        // If the client expects JSON (e.g., axios XHR), return the created lote
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['lote' => $lote->toArray()]);
        }

        // If request originates from Inertia, return a 409 with X-Inertia-Location so the Inertia client handles the visit
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.show', $orden->id));
        }

        return redirect()->route('admin.ordenes.show', $orden->id);
    }

    /**
     * Display the specified Lote.
     */
    public function show(Lote $lote)
    {
        $lote->load('orden');

        $authPermissions = [];
        if (auth()->check()) {
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
            $authPermissions = array_values(array_unique(array_merge($rolePermissions, $directPermissions)));
        }

        return Inertia::render('Admin/Ordenes/LoteShow', [
            'lote' => $lote,
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Update the specified Lote in storage.
     */
    public function update(Request $request, Lote $lote)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'estado_trabajo' => 'required|in:trabajado,no_trabajado,interrumpido',
            'razon_interrupcion' => 'required_if:estado_trabajo,interrumpido|nullable|string',
        ]);

        $lote->update([
            'fecha' => $data['fecha'],
            'estado_trabajo' => $data['estado_trabajo'],
            'razon_interrupcion' => $data['razon_interrupcion'] ?? null,
        ]);
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['lote' => $lote->toArray()]);
        }

        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.show', $lote->orden_id));
        }

        return redirect()->route('admin.ordenes.show', $lote->orden_id);
    }

    /**
     * Remove the specified Lote from storage.
     */
    public function destroy(Lote $lote)
    {
        $ordenId = $lote->orden_id;
        $lote->delete();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        // If request originates from Inertia, return a 409 with X-Inertia-Location so the Inertia client handles the visit
        if (request()->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.show', $ordenId));
        }

        return redirect()->route('admin.ordenes.show', $ordenId);
    }
}
