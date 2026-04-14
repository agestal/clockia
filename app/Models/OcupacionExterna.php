<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $negocio_id
 * @property int|null $integracion_id
 * @property int|null $integracion_mapeo_id
 * @property int|null $recurso_id
 * @property string|null $proveedor
 * @property string $external_id
 * @property string|null $external_calendar_id
 * @property string|null $titulo
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $fecha
 * @property string|null $hora_inicio
 * @property string|null $hora_fin
 * @property \Illuminate\Support\Carbon|null $inicio_datetime
 * @property \Illuminate\Support\Carbon|null $fin_datetime
 * @property bool $es_dia_completo
 * @property string|null $origen
 * @property string|null $estado
 * @property array|null $payload_externo
 * @property \Illuminate\Support\Carbon|null $ultimo_sync_at
 * @property-read Negocio $negocio
 * @property-read Integracion|null $integracion
 * @property-read IntegracionMapeo|null $integracionMapeo
 * @property-read Recurso|null $recurso
 */
class OcupacionExterna extends Model
{
    use HasFactory;

    protected $table = 'ocupaciones_externas';

    protected $fillable = [
        'negocio_id',
        'integracion_id',
        'integracion_mapeo_id',
        'recurso_id',
        'proveedor',
        'external_id',
        'external_calendar_id',
        'titulo',
        'descripcion',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'inicio_datetime',
        'fin_datetime',
        'es_dia_completo',
        'origen',
        'estado',
        'payload_externo',
        'ultimo_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'integracion_id' => 'integer',
            'integracion_mapeo_id' => 'integer',
            'recurso_id' => 'integer',
            'fecha' => 'date',
            'inicio_datetime' => 'datetime',
            'fin_datetime' => 'datetime',
            'es_dia_completo' => 'boolean',
            'payload_externo' => 'array',
            'ultimo_sync_at' => 'datetime',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function integracion(): BelongsTo
    {
        return $this->belongsTo(Integracion::class);
    }

    public function integracionMapeo(): BelongsTo
    {
        return $this->belongsTo(IntegracionMapeo::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function scopeEnFecha(Builder $query, string $fecha): Builder
    {
        return $query->where(function (Builder $q) use ($fecha) {
            $q->where('fecha', $fecha)
                ->orWhere(function (Builder $inner) use ($fecha) {
                    $inner->whereDate('inicio_datetime', '<=', $fecha)
                        ->whereDate('fin_datetime', '>=', $fecha);
                });
        });
    }

    public function scopeEnRango(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->where(function (Builder $q) use ($desde, $hasta) {
            $q->whereBetween('fecha', [$desde, $hasta])
                ->orWhere(function (Builder $inner) use ($desde, $hasta) {
                    $inner->where('inicio_datetime', '<=', $hasta.' 23:59:59')
                        ->where('fin_datetime', '>=', $desde.' 00:00:00');
                });
        });
    }
}
