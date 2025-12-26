<?php

namespace App\Http\Controllers;

use App\Models\TipoPrenda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoPrendaController extends Controller
{
    public function index()
    {
        $tiposPrendas = TipoPrenda::with('procesoNodos')->paginate(15);

        return Inertia::render('Admin/TipoPrendas/Index', [
            'tiposPrendas' => $tiposPrendas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TipoPrendas/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:tipos_prendas|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoPrenda = TipoPrenda::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'tipoPrenda' => $tipoPrenda], 201);
        }

        return redirect()->route('admin.tipos-prendas.index')
            ->with('success', 'Tipo de prenda creado correctamente');
    }

    public function edit(TipoPrenda $tipoPrenda)
    {
        return Inertia::render('Admin/TipoPrendas/Edit', [
            'tipoPrenda' => $tipoPrenda,
        ]);
    }

    public function update(Request $request, TipoPrenda $tipoPrenda)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:tipos_prendas,nombre,' . $tipoPrenda->id . '|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoPrenda->update($validated);

        return redirect()->route('admin.tipos-prendas.index')
            ->with('success', 'Tipo de prenda actualizado correctamente');
    }

    public function destroy(TipoPrenda $tipoPrenda)
    {
        $tipoPrenda->delete();

        return redirect()->route('admin.tipos-prendas.index')
            ->with('success', 'Tipo de prenda eliminado correctamente');
    }
}
