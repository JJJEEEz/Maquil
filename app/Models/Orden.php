<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;

    /**
     * Table name explicitly set to Spanish plural to avoid incorrect
     * English pluralization (ordens).
     */
    protected $table = 'ordenes';

    protected $fillable = [
        'name',
        'description',
        'client',
        'quality',
        'status',
        'target_quantity',
        'target_date',
    ];

    protected $casts = [
        'target_quantity' => 'integer',
        'target_date' => 'date',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }
}
