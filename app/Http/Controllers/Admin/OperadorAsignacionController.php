<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoteProcesoProgreso;
use App\Models\OperadorAsignacion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperadorAsignacionController extends Controller
{
    /**
     * Mostrar formulario para asignar operadores a procesos de un lote
     */
    public function edit($progresoId)
    {
        // Buscar el progreso por ID directamente
        $progreso = LoteProcesoProgreso::find($progresoId);
        
        if (!$progreso) {
            return response()->json(['error' => 'Progreso no encontrado'], 404);
        }

        $progreso->load([
            'lote',
            'lote.orden',
            'procesoNodo',
        ]);

        // Obtener todos los operadores
        try {
            $operadores = User::role('operador')->get();
        } catch (\Exception $e) {
            // Si hay error con roles, obtener todos los usuarios
            $operadores = User::all();
        }

        // Obtener asignaciones actuales para este proceso
        $asignacionesActuales = OperadorAsignacion::where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->get()
            ->keyBy('user_id');

        return Inertia::render('Admin/OperadorAsignacion/Edit', [
            'progreso' => $progreso,
            'operadores' => $operadores,
            'asignacionesActuales' => $asignacionesActuales,
        ]);
    }

    /**
     * Guardar asignación de operador a un proceso
     */
    public function assignOperador(Request $request, $progresoId)
    {
        $progreso = LoteProcesoProgreso::find($progresoId);
        if (!$progreso) {
            return response()->json(['error' => 'Progreso no encontrado'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Verificar que el usuario es operador (si es posible)
        $user = User::findOrFail($validated['user_id']);
        
        try {
            if (!$user->hasRole('operador')) {
                return response()->json(['error' => 'El usuario no tiene rol de operador'], 400);
            }
        } catch (\Exception $e) {
            // Si hay error verificando el rol, permitir de todas formas
        }

        // Crear o actualizar la asignación
        OperadorAsignacion::updateOrCreate(
            [
                'proceso_nodo_id' => $progreso->proceso_nodo_id,
                'user_id' => $user->id,
            ],
            [
                'proceso_nodo_id' => $progreso->proceso_nodo_id,
                'user_id' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Operador asignado correctamente',
        ]);
    }

    /**
     * Remover asignación de un operador
     */
    public function removeOperador(Request $request, $progresoId)
    {
        $progreso = LoteProcesoProgreso::find($progresoId);
        if (!$progreso) {
            return response()->json(['error' => 'Progreso no encontrado'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        OperadorAsignacion::where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->where('user_id', $validated['user_id'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignación removida',
        ]);
    }

    /**
     * Obtener operadores asignados a un proceso
     */
    public function getAsignados($progresoId)
    {
        $progreso = LoteProcesoProgreso::find($progresoId);
        if (!$progreso) {
            return response()->json(['error' => 'Progreso no encontrado'], 404);
        }

        $asignados = OperadorAsignacion::where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->with('user')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'user_id' => $a->user_id,
                'nombre' => $a->user->name,
                'email' => $a->user->email,
            ]);

        return response()->json($asignados);
    }
}
