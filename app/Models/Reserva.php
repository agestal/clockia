<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Reserva
 *
 * @property int $id
 * @property int $negocio_id
 * @property int $servicio_id
 * @property int|null $sesion_id
 * @property int|null $recurso_id
 * @property int $cliente_id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $hora_inicio
 * @property string $hora_fin
 * @property int|null $numero_personas
 * @property string $precio_calculado
 * @property string|null $precio_total
 * @property int $estado_reserva_id
 * @property string|null $notas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Negocio $negocio
 * @property-read Servicio $servicio
 * @property-read Sesion|null $sesion
 * @property-read Recurso|null $recurso
 * @property-read Cliente $cliente
 * @property-read EstadoReserva $estadoReserva
 * @property-read Collection<int, Pago> $pagos
 * @property-read string $precio_final
 */
class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'negocio_id',
        'servicio_id',
        'sesion_id',
        'recurso_id',
        'cliente_id',
        'nombre_responsable',
        'email_responsable',
        'telefono_responsable',
        'tipo_documento_responsable',
        'documento_responsable',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'inicio_datetime',
        'fin_datetime',
        'numero_personas',
        'precio_calculado',
        'precio_total',
        'estado_reserva_id',
        'notas',
        'localizador',
        'fecha_cancelacion',
        'motivo_cancelacion',
        'cancelada_por',
        'instrucciones_llegada',
        'fecha_estimada_fin',
        'documentacion_entregada',
        'horas_minimas_cancelacion',
        'permite_modificacion',
        'es_reembolsable',
        'porcentaje_senal',
        'origen_reserva',
        'importada_externamente',
        'mail_confirmacion_enviado_en',
        'mail_recordatorio_enviado_en',
        'mail_encuesta_enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'servicio_id' => 'integer',
            'sesion_id' => 'integer',
            'recurso_id' => 'integer',
            'cliente_id' => 'integer',
            'fecha' => 'date',
            'numero_personas' => 'integer',
            'precio_calculado' => 'decimal:2',
            'precio_total' => 'decimal:2',
            'estado_reserva_id' => 'integer',
            'fecha_cancelacion' => 'datetime',
            'fecha_estimada_fin' => 'datetime',
            'documentacion_entregada' => 'boolean',
            'inicio_datetime' => 'datetime',
            'fin_datetime' => 'datetime',
            'horas_minimas_cancelacion' => 'integer',
            'permite_modificacion' => 'boolean',
            'es_reembolsable' => 'boolean',
            'porcentaje_senal' => 'decimal:2',
            'importada_externamente' => 'boolean',
            'mail_confirmacion_enviado_en' => 'datetime',
            'mail_recordatorio_enviado_en' => 'datetime',
            'mail_encuesta_enviado_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $reserva): void {
            $reserva->sincronizarIntervalo();
        });
    }

    public function sincronizarIntervalo(): void
    {
        if ($this->fecha && $this->hora_inicio) {
            $inicio = self::combinarFechaHora($this->fecha, $this->hora_inicio);
            if ($inicio !== null) {
                $this->inicio_datetime = $inicio;
            }
        }

        if ($this->fecha && $this->hora_fin) {
            $fin = self::combinarFechaHora($this->fecha, $this->hora_fin);

            if ($fin !== null && $this->inicio_datetime !== null && $fin->lessThanOrEqualTo($this->inicio_datetime)) {
                $fin = $fin->copy()->addDay();
            }

            if ($fin !== null) {
                $this->fin_datetime = $fin;
            }
        }
    }

    private static function combinarFechaHora(mixed $fecha, mixed $hora): ?Carbon
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

    public static function generarLocalizador(): string
    {
        do {
            $codigo = strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(6))), 0, 8));
        } while (self::where('localizador', $codigo)->exists());

        return $codigo;
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estadoReserva(): BelongsTo
    {
        return $this->belongsTo(EstadoReserva::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function reservaRecursos(): HasMany
    {
        return $this->hasMany(ReservaRecurso::class);
    }

    public function reservaIntegraciones(): HasMany
    {
        return $this->hasMany(ReservaIntegracion::class);
    }

    public function getPrecioFinalAttribute(): string
    {
        return $this->precio_total ?? $this->precio_calculado;
    }

    public function nombreResponsableEfectivo(): ?string
    {
        return $this->nombre_responsable ?: $this->cliente?->nombre;
    }

    public function emailResponsableEfectivo(): ?string
    {
        return $this->email_responsable ?: $this->cliente?->email;
    }

    public function telefonoResponsableEfectivo(): ?string
    {
        return $this->telefono_responsable ?: $this->cliente?->telefono;
    }

    public function scopePorLocalizador(Builder $query, string $localizador): Builder
    {
        return $query->where('localizador', $localizador);
    }
}
