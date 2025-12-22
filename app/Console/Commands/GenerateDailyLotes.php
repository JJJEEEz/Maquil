<?php

namespace App\Console\Commands;

use App\Models\Orden;
use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyLotes extends Command
{
    protected $signature = 'lotes:generate-daily';
    protected $description = 'Generate daily lotes for active orders';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $ordenes = Orden::where('status', Orden::STATUS_IN_PROGRESS)
            ->orWhere('status', Orden::STATUS_PENDING)
            ->get();

        $created = 0;

        foreach ($ordenes as $orden) {
            // Verificar si ya existe lote para hoy
            $existeLote = Lote::where('orden_id', $orden->id)
                ->whereDate('fecha', $today)
                ->exists();

            if (!$existeLote) {
                // Obtener la cantidad total completada hasta ahora
                $cantidadCompletada = $orden->lotes()
                    ->sum('total_prendas_terminadas');

                // Solo crear si aún no se alcanza la cantidad objetivo
                if ($cantidadCompletada < $orden->target_quantity) {
                    Lote::create([
                        'orden_id' => $orden->id,
                        'fecha' => $today,
                        'estado_trabajo' => 'trabajado',
                    ]);

                    $created++;
                }
            }
        }

        $this->info("Se crearon {$created} lotes diarios");
    }
}
