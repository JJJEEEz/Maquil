<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPrenda extends Model
{
    use HasFactory;

    protected $table = 'tipos_prendas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class);
    }

    public function procesoNodos()
    {
        return $this->hasMany(ProcesoNodo::class);
    }
}
