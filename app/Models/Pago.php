<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Pago
 *
 * @property int $id
 * @property int $reserva_id
 * @property int $tipo_pago_id
 * @property int $estado_pago_id
 * @property string $importe
 * @property string|null $referencia_externa
 * @property \Illuminate\Support\Carbon|null $fecha_pago
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Reserva $reserva
 * @property-read TipoPago $tipoPago
 * @property-read EstadoPago $estadoPago
 */
class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'tipo_pago_id',
        'estado_pago_id',
        'concepto_pago_id',
        'importe',
        'referencia_externa',
        'fecha_pago',
        'enlace_pago_externo',
        'iniciado_por_bot',
    ];

    protected function casts(): array
    {
        return [
            'reserva_id' => 'integer',
            'tipo_pago_id' => 'integer',
            'estado_pago_id' => 'integer',
            'concepto_pago_id' => 'integer',
            'importe' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'iniciado_por_bot' => 'boolean',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class);
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class);
    }

    public function conceptoPago(): BelongsTo
    {
        return $this->belongsTo(ConceptoPago::class);
    }
}
