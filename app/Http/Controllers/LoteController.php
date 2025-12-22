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
        $lote->load('orden.tipoPrenda', 'loteProcesoProgresos.procesoNodo');

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
        $lote->load('orden.tipoPrenda', 'loteProcesoProgresos.procesoNodo.dependencias');

        return Inertia::render('Operador/Dashboard/Lote', [
            'lote' => $lote,
        ]);
    }
}
