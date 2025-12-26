<?php

namespace App\Http\Controllers;

use App\Models\OperadorAsignacion;
use App\Models\LoteProcesoProgreso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperadorController extends Controller
{
    /**
     * Dashboard principal del operador
     * Muestra sus asignaciones actuales
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Obtener asignaciones actuales del operador
        $asignaciones = OperadorAsignacion::where('user_id', $user->id)
            ->with([
                'procesoNodo',
                'procesoNodo.tipoPrenda',
            ])
            ->get();

        // Obtener progresos asociados a las asignaciones
        $progresos = LoteProcesoProgreso::whereIn('proceso_nodo_id', $asignaciones->pluck('proceso_nodo_id'))
            ->with([
                'lote',
                'lote.orden',
                'lote.orden.tipoPrenda',
                'procesoNodo',
                'procesoNodo.tipoPrenda',
            ])
            ->where('estado', '!=', 'completado')
            ->get();

        return Inertia::render('Operador/Dashboard', [
            'asignaciones' => $asignaciones,
            'progresos' => $progresos,
        ]);
    }

    /**
     * Vista de un proceso específico asignado al operador
     */
    public function mostrarProceso(LoteProcesoProgreso $progreso)
    {
        $user = auth()->user();

        // Verificar que el operador tiene asignación para este proceso
        $asignacion = OperadorAsignacion::where('user_id', $user->id)
            ->where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->first();

        if (!$asignacion) {
            abort(403, 'No tienes asignación para este proceso');
        }

        $progreso->load([
            'lote',
            'lote.orden',
            'lote.orden.tipoPrenda',
            'procesoNodo',
            'registradoPor',
        ]);

        return Inertia::render('Operador/ProcesoDetalle', [
            'progreso' => $progreso,
            'procesoNodo' => $progreso->procesoNodo,
            'lote' => $progreso->lote,
            'orden' => $progreso->lote->orden,
        ]);
    }

    /**
     * Registrar prendas completadas en un proceso
     */
    public function registrarPrendas(Request $request, LoteProcesoProgreso $progreso)
    {
        $user = auth()->user();

        // Verificar asignación
        $asignacion = OperadorAsignacion::where('user_id', $user->id)
            ->where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->first();

        if (!$asignacion) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'cantidad_completada' => 'required|integer|min:0',
            'cantidad_merma' => 'nullable|integer|min:0',
            'cantidad_excedente' => 'nullable|integer|min:0',
            'notas' => 'nullable|string|max:1000',
        ]);

        // Actualizar el progreso
        $progreso->cantidad_completada = $validated['cantidad_completada'];
        $progreso->cantidad_merma = $validated['cantidad_merma'] ?? 0;
        $progreso->cantidad_excedente = $validated['cantidad_excedente'] ?? 0;
        $progreso->notas = $validated['notas'] ?? $progreso->notas;
        $progreso->registrado_por = $user->id;
        $progreso->estado = 'en_progreso';
        $progreso->save();

        // Registrar la hora del registro (si la tabla existe)
        try {
            $progreso->progresoHoras()->create([
                'cantidad_registrada' => $validated['cantidad_completada'],
                'registrado_a' => now(),
            ]);
        } catch (\Exception $e) {
            // Si hay error al crear el registro por hora, no bloquear la operación
        }

        return response()->json([
            'success' => true,
            'progreso' => $progreso,
            'message' => 'Progreso registrado correctamente',
        ]);
    }

    /**
     * Completar un proceso
     */
    public function completarProceso(Request $request, LoteProcesoProgreso $progreso)
    {
        $user = auth()->user();

        // Verificar asignación
        $asignacion = OperadorAsignacion::where('user_id', $user->id)
            ->where('proceso_nodo_id', $progreso->proceso_nodo_id)
            ->first();

        if (!$asignacion) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $progreso->estado = 'completado';
        $progreso->save();

        return response()->json([
            'success' => true,
            'message' => 'Proceso completado',
        ]);
    }
}
