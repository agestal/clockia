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
 * App\Models\Recurso
 *
 * @property int $id
 * @property int $negocio_id
 * @property string $nombre
 * @property int $tipo_recurso_id
 * @property int|null $capacidad
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Negocio $negocio
 * @property-read TipoRecurso $tipoRecurso
 * @property-read Collection<int, Servicio> $servicios
 * @property-read Collection<int, ServicioRecurso> $servicioRecursos
 * @property-read Collection<int, Disponibilidad> $disponibilidades
 * @property-read Collection<int, Bloqueo> $bloqueos
 * @property-read Collection<int, Reserva> $reservas
 */
class Recurso extends Model
{
    use HasFactory;

    protected $table = 'recursos';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'tipo_recurso_id',
        'capacidad',
        'activo',
        'capacidad_minima',
        'combinable',
        'notas_publicas',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'tipo_recurso_id' => 'integer',
            'capacidad' => 'integer',
            'activo' => 'boolean',
            'capacidad_minima' => 'integer',
            'combinable' => 'boolean',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function tipoRecurso(): BelongsTo
    {
        return $this->belongsTo(TipoRecurso::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'servicio_recurso', 'recurso_id', 'servicio_id')
            ->withTimestamps();
    }

    public function servicioRecursos(): HasMany
    {
        return $this->hasMany(ServicioRecurso::class);
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidad::class);
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function reservaRecursos(): HasMany
    {
        return $this->hasMany(ReservaRecurso::class);
    }

    public function ocupacionesExternas(): HasMany
    {
        return $this->hasMany(OcupacionExterna::class);
    }

    public function combinaciones(): HasMany
    {
        return $this->hasMany(RecursoCombinacion::class);
    }

    public function recursosCombinables(): HasMany
    {
        return $this->hasMany(RecursoCombinacion::class, 'recurso_id')->with('recursoCombinado');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
