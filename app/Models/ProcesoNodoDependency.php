<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoNodoDependency extends Model
{
    use HasFactory;

    protected $table = 'proceso_nodo_dependencies';

    protected $fillable = [
        'proceso_nodo_id',
        'padre_proceso_nodo_id',
    ];

    public function procesoNodo()
    {
        return $this->belongsTo(ProcesoNodo::class);
    }

    public function padreProceso()
    {
        return $this->belongsTo(ProcesoNodo::class, 'padre_proceso_nodo_id');
    }
}
