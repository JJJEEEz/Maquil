<?php

namespace App\Http\Controllers;

use App\Models\TipoPrenda;
use App\Models\ProcesoNodo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProcesoNodoController extends Controller
{
    public function index(TipoPrenda $tipoPrenda)
    {
        $nodos = $tipoPrenda->procesoNodos()->with('parent', 'dependencias', 'dependientes')->orderBy('orden')->get();

        return Inertia::render('Admin/ProcesoNodos/Index', [
            'tipoPrenda' => $tipoPrenda,
            'nodos' => $nodos,
        ]);
    }

    public function create(TipoPrenda $tipoPrenda)
    {
        $nodos = $tipoPrenda->procesoNodos()->get();

        return Inertia::render('Admin/ProcesoNodos/Create', [
            'tipoPrenda' => $tipoPrenda,
            'nodosDisponibles' => $nodos,
        ]);
    }

    public function store(Request $request, TipoPrenda $tipoPrenda)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:operacion,inspeccion',
            'orden' => 'required|integer|min:0',
            'cantidad_entrada' => 'required|integer|min:1',
            'cantidad_salida' => 'required|integer|min:1',
            'parent_id' => 'nullable|exists:proceso_nodos,id',
            'tiempo_estimado_minutos' => 'nullable|integer|min:1',
            'dependencias' => 'nullable|array',
            'dependencias.*' => 'exists:proceso_nodos,id',
        ]);

        $nodo = $tipoPrenda->procesoNodos()->create($validated);

        if ($request->has('dependencias') && is_array($request->dependencias)) {
            $nodo->dependencias()->attach($request->dependencias);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'nodo' => $nodo->load('dependencias')], 201);
        }

        return redirect()->route('admin.proceso-nodos.index', $tipoPrenda)
            ->with('success', 'Proceso creado correctamente');
    }

    public function edit(TipoPrenda $tipoPrenda, ProcesoNodo $nodo)
    {
        $nodosDisponibles = $tipoPrenda->procesoNodos()
            ->where('id', '!=', $nodo->id)
            ->get();

        return Inertia::render('Admin/ProcesoNodos/Edit', [
            'tipoPrenda' => $tipoPrenda,
            'nodo' => $nodo->load('dependencias', 'dependientes'),
            'nodosDisponibles' => $nodosDisponibles,
        ]);
    }

    public function update(Request $request, TipoPrenda $tipoPrenda, ProcesoNodo $nodo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:operacion,inspeccion',
            'orden' => 'required|integer|min:0',
            'cantidad_entrada' => 'required|integer|min:1',
            'cantidad_salida' => 'required|integer|min:1',
            'parent_id' => 'nullable|exists:proceso_nodos,id',
            'tiempo_estimado_minutos' => 'nullable|integer|min:1',
            'dependencias' => 'nullable|array',
            'dependencias.*' => 'exists:proceso_nodos,id',
        ]);

        $nodo->update($validated);

        if ($request->has('dependencias')) {
            $nodo->dependencias()->sync($request->dependencias);
        } else {
            $nodo->dependencias()->detach();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'nodo' => $nodo->load('dependencias')]);
        }

        return redirect()->route('admin.proceso-nodos.index', $tipoPrenda)
            ->with('success', 'Proceso actualizado correctamente');
    }

    public function destroy(TipoPrenda $tipoPrenda, ProcesoNodo $nodo)
    {
        $nodo->delete();

        return redirect()->route('admin.proceso-nodos.index', $tipoPrenda)
            ->with('success', 'Proceso eliminado correctamente');
    }

    public function reorder(Request $request, TipoPrenda $tipoPrenda)
    {
        $validated = $request->validate([
            'ordenes' => 'required|array',
            'ordenes.*.id' => 'required|exists:proceso_nodos,id',
            'ordenes.*.orden' => 'required|integer|min:1',
        ]);

        foreach ($validated['ordenes'] as $item) {
            ProcesoNodo::where('id', $item['id'])
                ->where('tipo_prenda_id', $tipoPrenda->id)
                ->update(['orden' => $item['orden']]);
        }

        return redirect()->route('admin.proceso-nodos.index', $tipoPrenda)
            ->with('success', 'Orden de procesos actualizado correctamente');
    }

    public function getTreeStructure(TipoPrenda $tipoPrenda)
    {
        $nodos = $tipoPrenda->procesoNodos()
            ->with('hijos', 'dependencias', 'dependientes')
            ->whereNull('parent_id')
            ->orderBy('orden')
            ->get();

        return response()->json($this->buildTree($nodos));
    }

    private function buildTree($nodos)
    {
        $tree = [];
        foreach ($nodos as $nodo) {
            $tree[] = [
                'id' => $nodo->id,
                'nombre' => $nodo->nombre,
                'tipo' => $nodo->tipo,
                'cantidad_entrada' => $nodo->cantidad_entrada,
                'cantidad_salida' => $nodo->cantidad_salida,
                'dependencias' => $nodo->dependencias->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre]),
                'hijos' => $this->buildTree($nodo->hijos),
            ];
        }
        return $tree;
    }
}
