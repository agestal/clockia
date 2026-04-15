<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $negocio_id
 * @property string $proveedor
 * @property string $nombre
 * @property string $modo_operacion
 * @property string $estado
 * @property array|null $configuracion
 * @property \Illuminate\Support\Carbon|null $ultimo_sync_at
 * @property string|null $ultimo_error
 * @property bool $activo
 * @property-read Negocio $negocio
 * @property-read Collection<int, IntegracionCuenta> $cuentas
 * @property-read Collection<int, IntegracionMapeo> $mapeos
 * @property-read Collection<int, OcupacionExterna> $ocupacionesExternas
 * @property-read Collection<int, ReservaIntegracion> $reservaIntegraciones
 */
class Integracion extends Model
{
    use HasFactory;

    protected $table = 'integraciones';

    protected $fillable = [
        'negocio_id',
        'proveedor',
        'nombre',
        'modo_operacion',
        'estado',
        'configuracion',
        'ultimo_sync_at',
        'ultimo_error',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'configuracion' => 'array',
            'ultimo_sync_at' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function cuentas(): HasMany
    {
        return $this->hasMany(IntegracionCuenta::class);
    }

    public function cuentaActiva(): HasOne
    {
        return $this->hasOne(IntegracionCuenta::class)
            ->where('activo', true)
            ->latestOfMany();
    }

    public function mapeos(): HasMany
    {
        return $this->hasMany(IntegracionMapeo::class);
    }

    public function mapeosCalendario(): HasMany
    {
        return $this->hasMany(IntegracionMapeo::class)
            ->where('tipo_origen', 'calendario');
    }

    public function calendariosSeleccionados(): HasMany
    {
        return $this->mapeosCalendario()
            ->where('activo', true)
            ->where('seleccionado', true);
    }

    public function ocupacionesExternas(): HasMany
    {
        return $this->hasMany(OcupacionExterna::class);
    }

    public function reservaIntegraciones(): HasMany
    {
        return $this->hasMany(ReservaIntegracion::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function esGoogleCalendar(): bool
    {
        return $this->proveedor === 'google_calendar';
    }

    public function estaConectada(): bool
    {
        return $this->estado === 'conectada';
    }

    public function esModoCoexistencia(): bool
    {
        return $this->modo_operacion === 'coexistencia';
    }

    public function esModoMigracion(): bool
    {
        return $this->modo_operacion === 'migracion';
    }

    public function esModoSoloClockia(): bool
    {
        return $this->modo_operacion === 'solo_clockia';
    }
}
