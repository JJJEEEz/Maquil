<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\LoteProcesoProgreso;
use App\Models\LoteProcesoProgresoHora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoteProcesoProgresoController extends Controller
{
    public function show(Lote $lote)
    {
        $orden = $lote->orden()->with('tipoPrenda')->first();
        $progresos = $lote->loteProcesoProgresos()
            ->with('procesoNodo', 'registradoPor', 'editadoPor')
            ->get();

        $procesoNodos = $orden->tipoPrenda->procesoNodos()
            ->orderBy('orden')
            ->get();

        return Inertia::render('Operador/LoteProcesoProgreso/Show', [
            'lote' => $lote,
            'orden' => $orden,
            'progresos' => $progresos,
            'procesoNodos' => $procesoNodos,
        ]);
    }

    public function store(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'proceso_nodo_id' => 'required|exists:proceso_nodos,id',
            'cantidad_completada' => 'required|integer|min:0',
            'cantidad_merma' => 'required|integer|min:0',
            'cantidad_excedente' => 'required|integer|min:0',
            'notas' => 'nullable|string',
        ]);

        $loteProgreso = LoteProcesoProgreso::updateOrCreate(
            [
                'lote_id' => $lote->id,
                'proceso_nodo_id' => $validated['proceso_nodo_id'],
            ],
            [
                'cantidad_completada' => $validated['cantidad_completada'],
                'cantidad_merma' => $validated['cantidad_merma'],
                'cantidad_excedente' => $validated['cantidad_excedente'],
                'notas' => $validated['notas'] ?? null,
                'registrado_por' => auth()->id(),
                'editado_por' => auth()->id(),
                'estado' => 'en_progreso',
            ]
        );

        LoteProcesoProgresoHora::create([
            'lote_proceso_progreso_id' => $loteProgreso->id,
            'hora' => now()->toTimeString(),
            'piezas_completadas' => $validated['cantidad_completada'],
            'piezas_merma' => $validated['cantidad_merma'],
            'piezas_excedente' => $validated['cantidad_excedente'],
            'registrado_por' => auth()->id(),
        ]);

        $lote->updateTotales();

        return response()->json([
            'message' => 'Progreso registrado correctamente',
            'loteProgreso' => $loteProgreso->load('registradoPor', 'editadoPor'),
        ]);
    }

    public function markAsCompleted(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'proceso_nodo_id' => 'required|exists:proceso_nodos,id',
        ]);

        $loteProgreso = LoteProcesoProgreso::where([
            ['lote_id', $lote->id],
            ['proceso_nodo_id', $validated['proceso_nodo_id']],
        ])->firstOrFail();

        $loteProgreso->markAsCompleted();

        return response()->json([
            'message' => 'Proceso marcado como completado',
            'loteProgreso' => $loteProgreso,
        ]);
    }

    public function getProgress(Lote $lote)
    {
        $progresos = $lote->loteProcesoProgresos()
            ->with('procesoNodo', 'progresoHoras.registradoPor')
            ->get();

        return response()->json([
            'progresos' => $progresos,
            'totales' => [
                'prendas_terminadas' => $lote->total_prendas_terminadas,
                'mermas' => $lote->total_mermas,
            ],
        ]);
    }
}
