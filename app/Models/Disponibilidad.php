<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Disponibilidad
 *
 * @property int $id
 * @property int $recurso_id
 * @property int $dia_semana
 * @property string $hora_inicio
 * @property string $hora_fin
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Recurso $recurso
 */
class Disponibilidad extends Model
{
    use HasFactory;

    protected $table = 'disponibilidades';

    protected $fillable = [
        'recurso_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'activo',
        'nombre_turno',
        'buffer_minutos',
    ];

    protected function casts(): array
    {
        return [
            'recurso_id' => 'integer',
            'dia_semana' => 'integer',
            'activo' => 'boolean',
            'buffer_minutos' => 'integer',
        ];
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
