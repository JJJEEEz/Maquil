<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\Lote;
use App\Models\ProcesoNodo;
use App\Models\LoteProcesoProgreso;
use Inertia\Inertia;

class EstadisticasController extends Controller
{
    /**
     * Mostrar estadísticas de procesos por órdenes y lotes
     */
    public function index()
    {
        // Obtener todas las órdenes con sus lotes y procesos
        $ordenes = Orden::with([
            'lotes.loteProcesoProgresos.procesoNodo',
            'lotes.loteProcesoProgresos.registradoPor',
            'tipoPrenda'
        ])
        ->orderBy('id', 'desc')
        ->get();

        // Procesar y transformar a arrays
        $ordenesArray = $ordenes->map(function ($orden) {
            // Calcular progreso total de la orden
            $totalObjetivo = 0;
            $totalCompletado = 0;
            $totalEnProgreso = 0;

            foreach ($orden->lotes as $lote) {
                foreach ($lote->loteProcesoProgresos as $progreso) {
                    $totalObjetivo += $progreso->cantidad_objetivo;
                    $totalCompletado += $progreso->cantidad_completada;
                    if ($progreso->cantidad_completada < $progreso->cantidad_objetivo) {
                        $totalEnProgreso += ($progreso->cantidad_objetivo - $progreso->cantidad_completada);
                    }
                }
            }

            $porcentajeProgreso = $totalObjetivo > 0 
                ? round((($totalCompletado + $totalEnProgreso) / $totalObjetivo) * 100)
                : 0;

            return [
                'id' => $orden->id,
                'name' => $orden->name,
                'client' => $orden->client,
                'tipo_prenda_id' => $orden->tipo_prenda_id,
                'tipoPrenda' => $orden->tipoPrenda ? $orden->tipoPrenda->toArray() : null,
                'lotes' => $orden->lotes->map(function ($lote) {
                    // Forzar que los loteProcesoProgresos se incluyan como camelCase
                    $loteArray = [
                        'id' => $lote->id,
                        'orden_id' => $lote->orden_id,
                        'fecha' => $lote->fecha,
                        'estado_trabajo' => $lote->estado_trabajo,
                        'loteProcesoProgresos' => $lote->loteProcesoProgresos->map(function ($progreso) {
                            return [
                                'id' => $progreso->id,
                                'lote_id' => $progreso->lote_id,
                                'proceso_nodo_id' => $progreso->proceso_nodo_id,
                                'cantidad_objetivo' => $progreso->cantidad_objetivo,
                                'cantidad_completada' => $progreso->cantidad_completada,
                                'cantidad_merma' => $progreso->cantidad_merma,
                                'cantidad_excedente' => $progreso->cantidad_excedente,
                                'estado' => $progreso->estado,
                                'procesoNodo' => $progreso->procesoNodo ? $progreso->procesoNodo->toArray() : null,
                                'registradoPor' => $progreso->registradoPor ? $progreso->registradoPor->toArray() : null,
                            ];
                        })->toArray(),
                    ];
                    return $loteArray;
                })->toArray(),
                'progreso_total' => [
                    'objetivo' => $totalObjetivo,
                    'completado' => $totalCompletado,
                    'en_progreso' => $totalEnProgreso,
                    'porcentaje' => $porcentajeProgreso,
                ],
            ];
        })->toArray();

        // Calcular estadísticas globales
        $stats = [
            'total_ordenes' => Orden::count(),
            'total_lotes' => Lote::count(),
            'total_procesos' => LoteProcesoProgreso::count(),
            'procesos_completados' => LoteProcesoProgreso::where('estado', 'completado')->count(),
            'procesos_en_progreso' => LoteProcesoProgreso::where('estado', 'en_progreso')->count(),
            'procesos_pendientes' => LoteProcesoProgreso::where('estado', 'pendiente')->count(),
            'prendas_completadas' => LoteProcesoProgreso::sum('cantidad_completada'),
            'prendas_merma' => LoteProcesoProgreso::sum('cantidad_merma'),
            'prendas_excedentes' => LoteProcesoProgreso::sum('cantidad_excedente'),
        ];

        return Inertia::render('Admin/Estadisticas/Index', [
            'ordenes' => $ordenesArray,
            'stats' => $stats,
        ]);
    }
}

