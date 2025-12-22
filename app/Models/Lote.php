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
}
