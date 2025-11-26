<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Orden;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

class OrdenController extends Controller
{
    /**
     * Display a listing of the Ordenes.
     */
    public function index()
    {
        $ordenes = Orden::withCount('lotes')->orderBy('id', 'desc')->paginate(15);

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
        return Inertia::render('Admin/Ordenes/Index', [
            'ordenes' => $ordenes,
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Show the form for creating a new Orden.
     */
    public function create()
    {
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
        return Inertia::render('Admin/Ordenes/Form', [
            'orden' => null,
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Store a newly created Orden in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'quality' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:' . implode(',', Orden::statuses()),
            'target_quantity' => 'nullable|integer|min:0',
            'target_date' => 'nullable|date|after_or_equal:today',
            'lotes' => 'nullable|array',
            'lotes.*.started_at' => 'nullable|date',
            'lotes.*.ended_at' => 'nullable|date|after_or_equal:lotes.*.started_at',
            'lotes.*.quantity' => 'required_with:lotes|integer|min:0',
        ]);

        $orden = Orden::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'client' => $data['client'] ?? null,
            'quality' => $data['quality'] ?? null,
            'status' => $data['status'] ?? Orden::STATUS_PENDING,
            'target_quantity' => $data['target_quantity'] ?? 0,
            'target_date' => $data['target_date'] ?? null,
        ]);

        // Create nested lotes if provided
        if (!empty($data['lotes']) && is_array($data['lotes'])) {
            foreach ($data['lotes'] as $loteData) {
                Lote::create([
                    'orden_id' => $orden->id,
                    'started_at' => $loteData['started_at'] ?? null,
                    'ended_at' => $loteData['ended_at'] ?? null,
                    'quantity' => $loteData['quantity'] ?? 0,
                ]);
            }
        }

        // If the client expects JSON (e.g., axios) return the created orden
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['orden' => $orden->toArray()]);
        }

        // If request originates from Inertia, respond with 409 and X-Inertia-Location
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.index'));
        }

        return redirect()->route('admin.ordenes.index');
    }

    /**
     * Display the specified Orden with its lotes.
     */
    public function show(Orden $orden)
    {
        $orden->load('lotes');

        $authPermissions = [];
        if (auth()->check()) {
            $authPermissions = auth()->user()->getPermissionNames()->toArray();
        }

        return Inertia::render('Admin/Ordenes/Show', [
            'orden' => $orden,
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Show the form for editing the specified Orden.
     */
    public function edit(Orden $orden)
    {
        $authPermissions = [];
        if (auth()->check()) {
            $authPermissions = auth()->user()->getPermissionNames()->toArray();
        }

        return Inertia::render('Admin/Ordenes/Form', [
            'orden' => $orden,
            'authPermissions' => $authPermissions,
        ]);
    }

    /**
     * Update the specified Orden in storage.
     */
    public function update(Request $request, Orden $orden)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'quality' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:' . implode(',', Orden::statuses()),
            'target_quantity' => 'nullable|integer|min:0',
            'target_date' => 'nullable|date|after_or_equal:today',
            'lotes' => 'nullable|array',
            'lotes.*.id' => 'nullable|integer|exists:lotes,id',
            'lotes.*.started_at' => 'nullable|date',
            'lotes.*.ended_at' => 'nullable|date|after_or_equal:lotes.*.started_at',
            'lotes.*.quantity' => 'required_with:lotes|integer|min:0',
        ]);

        $orden->name = $data['name'];
        $orden->description = $data['description'] ?? null;
        $orden->client = $data['client'] ?? null;
        $orden->quality = $data['quality'] ?? null;
        $orden->status = $data['status'] ?? $orden->status;
        $orden->target_quantity = $data['target_quantity'] ?? $orden->target_quantity;
        $orden->target_date = $data['target_date'] ?? $orden->target_date;
        $orden->save();

        // Upsert nested lotes: update if id present, otherwise create
        if (!empty($data['lotes']) && is_array($data['lotes'])) {
            foreach ($data['lotes'] as $loteData) {
                if (!empty($loteData['id'])) {
                    $lote = Lote::find($loteData['id']);
                    if ($lote && $lote->orden_id == $orden->id) {
                        $lote->started_at = $loteData['started_at'] ?? $lote->started_at;
                        $lote->ended_at = $loteData['ended_at'] ?? $lote->ended_at;
                        $lote->quantity = $loteData['quantity'] ?? $lote->quantity;
                        $lote->save();
                    }
                } else {
                    Lote::create([
                        'orden_id' => $orden->id,
                        'started_at' => $loteData['started_at'] ?? null,
                        'ended_at' => $loteData['ended_at'] ?? null,
                        'quantity' => $loteData['quantity'] ?? 0,
                    ]);
                }
            }
        }

        // If the client expects JSON (axios/XHR) return the updated orden
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['orden' => $orden->toArray()]);
        }

        // If request originates from Inertia, respond with 409 and X-Inertia-Location
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.index'));
        }

        return redirect()->route('admin.ordenes.index');
    }

    /**
     * Remove the specified Orden from storage.
     */
    public function destroy(Orden $orden)
    {
        $orden->delete();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if (request()->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('admin.ordenes.index'));
        }

        return redirect()->route('admin.ordenes.index');
    }
}
