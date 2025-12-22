<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoNodo extends Model
{
    use HasFactory;

    protected $table = 'proceso_nodos';

    protected $fillable = [
        'tipo_prenda_id',
        'nombre',
        'tipo',
        'orden',
        'cantidad_entrada',
        'cantidad_salida',
        'parent_id',
        'tiempo_estimado_minutos',
    ];

    public function tipoPrenda()
    {
        return $this->belongsTo(TipoPrenda::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProcesoNodo::class, 'parent_id');
    }

    public function hijos()
    {
        return $this->hasMany(ProcesoNodo::class, 'parent_id');
    }

    public function dependencias()
    {
        return $this->belongsToMany(
            ProcesoNodo::class,
            'proceso_nodo_dependencies',
            'proceso_nodo_id',
            'padre_proceso_nodo_id'
        )->withTimestamps();
    }

    public function dependientes()
    {
        return $this->belongsToMany(
            ProcesoNodo::class,
            'proceso_nodo_dependencies',
            'padre_proceso_nodo_id',
            'proceso_nodo_id'
        )->withTimestamps();
    }

    public function loteProcesoProgresos()
    {
        return $this->hasMany(LoteProcesoProgreso::class);
    }

    public function operadorAsignaciones()
    {
        return $this->hasMany(OperadorAsignacion::class);
    }
}
