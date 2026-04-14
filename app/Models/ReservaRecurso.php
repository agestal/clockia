<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReservaRecurso
 *
 * @property int $id
 * @property int $reserva_id
 * @property int $recurso_id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $hora_inicio
 * @property string $hora_fin
 * @property \Illuminate\Support\Carbon|null $fecha_inicio_datetime
 * @property \Illuminate\Support\Carbon|null $fecha_fin_datetime
 * @property string|null $notas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Reserva $reserva
 * @property-read Recurso $recurso
 */
class ReservaRecurso extends Model
{
    use HasFactory;

    protected $table = 'reserva_recursos';

    protected $fillable = [
        'reserva_id',
        'recurso_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'fecha_inicio_datetime',
        'fecha_fin_datetime',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'reserva_id' => 'integer',
            'recurso_id' => 'integer',
            'fecha' => 'date',
            'fecha_inicio_datetime' => 'datetime',
            'fecha_fin_datetime' => 'datetime',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }
}
