<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Servicio
 *
 * @property int $id
 * @property int $negocio_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $duracion_minutos
 * @property string $precio_base
 * @property int $tipo_precio_id
 * @property bool $requiere_pago
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Negocio $negocio
 * @property-read TipoPrecio $tipoPrecio
 * @property-read Collection<int, Recurso> $recursos
 * @property-read Collection<int, ServicioRecurso> $servicioRecursos
 * @property-read Collection<int, Reserva> $reservas
 */
class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio_base',
        'tipo_precio_id',
        'requiere_pago',
        'activo',
        'notas_publicas',
        'instrucciones_previas',
        'documentacion_requerida',
        'horas_minimas_cancelacion',
        'es_reembolsable',
        'porcentaje_senal',
        'precio_por_unidad_tiempo',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'duracion_minutos' => 'integer',
            'precio_base' => 'decimal:2',
            'tipo_precio_id' => 'integer',
            'requiere_pago' => 'boolean',
            'activo' => 'boolean',
            'es_reembolsable' => 'boolean',
            'precio_por_unidad_tiempo' => 'boolean',
            'porcentaje_senal' => 'decimal:2',
            'horas_minimas_cancelacion' => 'integer',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function tipoPrecio(): BelongsTo
    {
        return $this->belongsTo(TipoPrecio::class);
    }

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(Recurso::class, 'servicio_recurso', 'servicio_id', 'recurso_id')
            ->withTimestamps();
    }

    public function servicioRecursos(): HasMany
    {
        return $this->hasMany(ServicioRecurso::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
