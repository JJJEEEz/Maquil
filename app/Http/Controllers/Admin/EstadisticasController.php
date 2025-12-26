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
            'lotes' => function ($query) {
                $query->with([
                    'loteProcesoProgresos' => function ($query) {
                        $query->with(['procesoNodo', 'registradoPor']);
                    }
                ]);
            },
            'tipoPrenda'
        ])
        ->orderBy('id', 'desc')
        ->get();

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
            'ordenes' => $ordenes,
            'stats' => $stats,
        ]);
    }
}
