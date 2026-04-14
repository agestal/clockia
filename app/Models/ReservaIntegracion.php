<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reserva_id
 * @property int|null $integracion_id
 * @property string $proveedor
 * @property string $external_id
 * @property string|null $external_calendar_id
 * @property string|null $direccion_sync
 * @property \Illuminate\Support\Carbon|null $ultimo_sync_at
 * @property string|null $estado_sync
 * @property array|null $payload_resumen
 * @property-read Reserva $reserva
 * @property-read Integracion|null $integracion
 */
class ReservaIntegracion extends Model
{
    use HasFactory;

    protected $table = 'reserva_integraciones';

    protected $fillable = [
        'reserva_id',
        'integracion_id',
        'proveedor',
        'external_id',
        'external_calendar_id',
        'direccion_sync',
        'ultimo_sync_at',
        'estado_sync',
        'payload_resumen',
    ];

    protected function casts(): array
    {
        return [
            'reserva_id' => 'integer',
            'integracion_id' => 'integer',
            'ultimo_sync_at' => 'datetime',
            'payload_resumen' => 'array',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function integracion(): BelongsTo
    {
        return $this->belongsTo(Integracion::class);
    }
}
