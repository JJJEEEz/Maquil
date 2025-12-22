<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden_id',
        'fecha',
        'started_at',
        'ended_at',
        'expected_started_at',
        'expected_ended_at',
        'status',
        'quantity',
        'estado_trabajo',
        'razon_interrupcion',
        'total_prendas_terminadas',
        'total_mermas',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'expected_started_at' => 'datetime',
        'expected_ended_at' => 'datetime',
        'fecha' => 'date',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function loteProcesoProgresos()
    {
        return $this->hasMany(LoteProcesoProgreso::class);
    }

    /**
     * Calcula el total de prendas completadas sumando todos los procesos
     */
    public function calculateTotalCompletadas()
    {
        return $this->loteProcesoProgresos()
            ->where('estado', 'completado')
            ->sum('cantidad_completada');
    }

    /**
     * Calcula el total de mermas
     */
    public function calculateTotalMermas()
    {
        return $this->loteProcesoProgresos()->sum('cantidad_merma');
    }

    /**
     * Actualiza el total de prendas terminadas y mermas
     */
    public function updateTotales()
    {
        $this->update([
            'total_prendas_terminadas' => $this->calculateTotalCompletadas(),
            'total_mermas' => $this->calculateTotalMermas(),
        ]);
    }

    /**
     * Inicializa los procesos del lote basándose en el tipo de prenda de la orden
     */
    public function initializeProcesos()
    {
        // Cargar la orden con el tipo de prenda y sus procesos
        $this->load('orden.tipoPrenda.procesoNodos');
        
        // Si ya tiene procesos, no hacer nada
        if ($this->loteProcesoProgresos()->count() > 0) {
            return false;
        }

        // Verificar que haya procesos definidos
        if (!$this->orden->tipoPrenda || !$this->orden->tipoPrenda->procesoNodos) {
            return false;
        }

        // Crear los registros de progreso para cada proceso
        foreach ($this->orden->tipoPrenda->procesoNodos as $procesoNodo) {
            $this->loteProcesoProgresos()->create([
                'proceso_nodo_id' => $procesoNodo->id,
                'cantidad_objetivo' => $this->orden->target_quantity ?? 0,
                'cantidad_completada' => 0,
                'cantidad_merma' => 0,
                'cantidad_excedente' => 0,
                'estado' => 'pendiente',
            ]);
        }

        return true;
    }
}
