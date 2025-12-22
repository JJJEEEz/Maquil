<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteProcesoProgresoHora extends Model
{
    use HasFactory;

    protected $table = 'lote_proceso_progreso_horas';

    protected $fillable = [
        'lote_proceso_progreso_id',
        'hora',
        'piezas_completadas',
        'piezas_merma',
        'piezas_excedente',
        'registrado_por',
    ];

    public function loteProcesoProgreso()
    {
        return $this->belongsTo(LoteProcesoProgreso::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
