<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $integracion_id
 * @property string $tipo_origen
 * @property string $external_id
 * @property string|null $external_parent_id
 * @property string|null $nombre_externo
 * @property int|null $negocio_id
 * @property int|null $recurso_id
 * @property int|null $servicio_id
 * @property array|null $configuracion
 * @property bool $activo
 * @property-read Integracion $integracion
 * @property-read Negocio|null $negocio
 * @property-read Recurso|null $recurso
 * @property-read Servicio|null $servicio
 * @property-read Collection<int, OcupacionExterna> $ocupacionesExternas
 */
class IntegracionMapeo extends Model
{
    use HasFactory;

    protected $table = 'integracion_mapeos';

    protected $fillable = [
        'integracion_id',
        'tipo_origen',
        'external_id',
        'external_parent_id',
        'nombre_externo',
        'negocio_id',
        'recurso_id',
        'servicio_id',
        'configuracion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'integracion_id' => 'integer',
            'negocio_id' => 'integer',
            'recurso_id' => 'integer',
            'servicio_id' => 'integer',
            'configuracion' => 'array',
            'activo' => 'boolean',
        ];
    }

    public function integracion(): BelongsTo
    {
        return $this->belongsTo(Integracion::class);
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function ocupacionesExternas(): HasMany
    {
        return $this->hasMany(OcupacionExterna::class);
    }
}
