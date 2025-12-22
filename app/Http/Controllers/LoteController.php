<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoteController extends Controller
{
    public function index()
    {
        $lotes = Lote::with('orden.tipoPrenda')->paginate(15);

        return Inertia::render('Admin/Lotes/Index', [
            'lotes' => $lotes,
        ]);
    }

    public function show(Lote $lote)
    {
        $lote->load('orden.tipoPrenda', 'loteProcesoProgresos.procesoNodo', 'loteProcesoProgresos.registradoPor');

        return Inertia::render('Admin/Lotes/Show', [
            'lote' => $lote,
        ]);
    }

    public function updateEstadoTrabajo(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'estado_trabajo' => 'required|in:trabajado,no_trabajado,interrumpido',
            'razon_interrupcion' => 'required_if:estado_trabajo,interrumpido|nullable|string',
        ]);

        $lote->update($validated);

        return redirect()->back()
            ->with('success', 'Estado del lote actualizado correctamente');
    }

    public function dashboard(Lote $lote)
    {
        $lote->load('orden.tipoPrenda', 'loteProcesoProgresos.procesoNodo.dependencias', 'loteProcesoProgresos.registradoPor');

        return Inertia::render('Operador/Dashboard/Lote', [
            'lote' => $lote,
            'orden' => $lote->orden,
            'progresos' => $lote->loteProcesoProgresos,
            'procesoNodos' => $lote->orden->tipoPrenda->procesoNodos ?? [],
        ]);
    }

    public function initializeProcesos(Lote $lote)
    {
        $initialized = $lote->initializeProcesos();
        
        if ($initialized) {
            return redirect()->back()->with('success', 'Procesos inicializados correctamente');
        }
        
        return redirect()->back()->with('info', 'El lote ya tiene procesos inicializados o no hay procesos definidos en el tipo de prenda');
    }
}
