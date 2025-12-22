<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteProcesoProgreso extends Model
{
    use HasFactory;

    protected $table = 'lote_proceso_progresos';

    protected $fillable = [
        'lote_id',
        'proceso_nodo_id',
        'cantidad_objetivo',
        'cantidad_completada',
        'cantidad_merma',
        'cantidad_excedente',
        'estado',
        'registrado_por',
        'editado_por',
        'inicio_proceso',
        'fin_proceso',
        'notas',
    ];

    protected $casts = [
        'inicio_proceso' => 'datetime',
        'fin_proceso' => 'datetime',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function procesoNodo()
    {
        return $this->belongsTo(ProcesoNodo::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function editadoPor()
    {
        return $this->belongsTo(User::class, 'editado_por');
    }

    public function progresoHoras()
    {
        return $this->hasMany(LoteProcesoProgresoHora::class);
    }

    public function markAsCompleted()
    {
        $this->update([
            'estado' => 'completado',
            'fin_proceso' => now(),
        ]);
    }

    public function markAsInProgress()
    {
        if ($this->estado === 'pendiente') {
            $this->update([
                'estado' => 'en_progreso',
                'inicio_proceso' => now(),
            ]);
        }
    }
}
