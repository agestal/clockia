<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Bloqueo
 *
 * @property int $id
 * @property int|null $negocio_id
 * @property int|null $recurso_id
 * @property int $tipo_bloqueo_id
 * @property \Illuminate\Support\Carbon|null $fecha
 * @property \Illuminate\Support\Carbon|null $fecha_inicio
 * @property \Illuminate\Support\Carbon|null $fecha_fin
 * @property bool $es_recurrente
 * @property int|null $dia_semana
 * @property string|null $hora_inicio
 * @property string|null $hora_fin
 * @property string|null $motivo
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Negocio|null $negocio
 * @property-read Recurso|null $recurso
 * @property-read TipoBloqueo $tipoBloqueo
 */
class Bloqueo extends Model
{
    use HasFactory;

    protected $table = 'bloqueos';

    protected $fillable = [
        'negocio_id',
        'recurso_id',
        'tipo_bloqueo_id',
        'fecha',
        'fecha_inicio',
        'fecha_fin',
        'es_recurrente',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'recurso_id' => 'integer',
            'tipo_bloqueo_id' => 'integer',
            'fecha' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_recurrente' => 'boolean',
            'dia_semana' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function tipoBloqueo(): BelongsTo
    {
        return $this->belongsTo(TipoBloqueo::class);
    }

    public function esDiaCompleto(): bool
    {
        return $this->hora_inicio === null && $this->hora_fin === null;
    }

    public function esRango(): bool
    {
        return $this->fecha_inicio !== null && $this->fecha_fin !== null;
    }

    public function esPuntual(): bool
    {
        return $this->fecha !== null && ! $this->esRango() && ! $this->es_recurrente;
    }

    public function esNegocioCompleto(): bool
    {
        return $this->recurso_id === null && $this->negocio_id !== null;
    }
}
