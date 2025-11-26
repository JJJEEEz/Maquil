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
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'expected_started_at' => 'nullable|date',
            'expected_ended_at' => 'nullable|date|after_or_equal:expected_started_at',
            'status' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
        ]);

        $lote = $orden->lotes()->create([
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'expected_started_at' => $data['expected_started_at'] ?? null,
            'expected_ended_at' => $data['expected_ended_at'] ?? null,
            'status' => $data['status'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
        ]);

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
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'expected_started_at' => 'required|date',
            'expected_ended_at' => 'required|date|after_or_equal:expected_started_at',
            'status' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
        ]);

        $lote->update([
            'started_at' => $data['started_at'] ?? $lote->started_at,
            'ended_at' => $data['ended_at'] ?? $lote->ended_at,
            'expected_started_at' => $data['expected_started_at'] ?? $lote->expected_started_at,
            'expected_ended_at' => $data['expected_ended_at'] ?? $lote->expected_ended_at,
            'status' => $data['status'] ?? $lote->status,
            'quantity' => $data['quantity'] ?? $lote->quantity,
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
