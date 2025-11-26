<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden_id',
        'started_at',
        'ended_at',
        'expected_started_at',
        'expected_ended_at',
        'status',
        'quantity',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'expected_started_at' => 'datetime',
        'expected_ended_at' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
