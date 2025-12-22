<?php

namespace App\Console\Commands;

use App\Models\Lote;
use App\Models\LoteProcesoProgreso;
use Illuminate\Console\Command;

class InitializeLoteProcesses extends Command
{
    protected $signature = 'lotes:initialize-processes {lote_id}';
    protected $description = 'Initialize all processes for a lote';

    public function handle()
    {
        $lote = Lote::with('orden.tipoPrenda.procesoNodos')->findOrFail($this->argument('lote_id'));

        $orden = $lote->orden;
        $tipoPrenda = $orden->tipoPrenda;

        if (!$tipoPrenda) {
            $this->error('La orden no tiene asignado un tipo de prenda');
            return;
        }

        $procesoNodos = $tipoPrenda->procesoNodos()->get();

        $created = 0;
        foreach ($procesoNodos as $nodo) {
            LoteProcesoProgreso::updateOrCreate(
                [
                    'lote_id' => $lote->id,
                    'proceso_nodo_id' => $nodo->id,
                ],
                [
                    'cantidad_objetivo' => $orden->target_quantity,
                    'estado' => 'pendiente',
                ]
            );
            $created++;
        }

        $this->info("Se inicializaron {$created} procesos para el lote {$lote->id}");
    }
}
