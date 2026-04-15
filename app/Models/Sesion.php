<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesiones';

    protected $fillable = [
        'negocio_id',
        'servicio_id',
        'recurso_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'inicio_datetime',
        'fin_datetime',
        'aforo_total',
        'activo',
        'notas_publicas',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'servicio_id' => 'integer',
            'recurso_id' => 'integer',
            'fecha' => 'date',
            'inicio_datetime' => 'datetime',
            'fin_datetime' => 'datetime',
            'aforo_total' => 'integer',
            'activo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $sesion): void {
            $sesion->sincronizarIntervalo();
        });
    }

    public function sincronizarIntervalo(): void
    {
        if ($this->fecha && $this->hora_inicio) {
            $inicio = $this->combinarFechaHora($this->fecha, $this->hora_inicio);
            if ($inicio !== null) {
                $this->inicio_datetime = $inicio;
            }
        }

        if ($this->fecha && $this->hora_fin) {
            $fin = $this->combinarFechaHora($this->fecha, $this->hora_fin);

            if ($fin !== null && $this->inicio_datetime !== null && $fin->lessThanOrEqualTo($this->inicio_datetime)) {
                $fin = $fin->copy()->addDay();
            }

            if ($fin !== null) {
                $this->fin_datetime = $fin;
            }
        }
    }

    private function combinarFechaHora(mixed $fecha, mixed $hora): ?Carbon
    {
        if ($fecha === null || $hora === null || $hora === '') {
            return null;
        }

        $fechaString = $fecha instanceof Carbon ? $fecha->toDateString() : (string) $fecha;
        $horaString = (string) $hora;

        if (strlen($horaString) === 5) {
            $horaString .= ':00';
        }

        try {
            return Carbon::parse($fechaString.' '.$horaString);
        } catch (\Throwable) {
            return null;
        }
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
