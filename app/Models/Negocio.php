<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Negocio
 *
 * @property int $id
 * @property string $nombre
 * @property int $tipo_negocio_id
 * @property string|null $email
 * @property string|null $telefono
 * @property string $zona_horaria
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read TipoNegocio $tipoNegocio
 * @property-read Collection<int, Servicio> $servicios
 * @property-read Collection<int, Recurso> $recursos
 * @property-read Collection<int, Reserva> $reservas
 */
class Negocio extends Model
{
    use HasFactory;

    protected $table = 'negocios';

    protected $fillable = [
        'nombre',
        'tipo_negocio_id',
        'email',
        'telefono',
        'zona_horaria',
        'activo',
        'descripcion_publica',
        'direccion',
        'url_publica',
        'politica_cancelacion',
        'horas_minimas_cancelacion',
        'permite_modificacion',
        'max_recursos_combinables',
        'chat_personality',
        'chat_required_fields',
        'chat_system_rules',
        'chat_behavior_overrides',
    ];

    protected function casts(): array
    {
        return [
            'tipo_negocio_id' => 'integer',
            'activo' => 'boolean',
            'horas_minimas_cancelacion' => 'integer',
            'permite_modificacion' => 'boolean',
            'max_recursos_combinables' => 'integer',
            'chat_required_fields' => 'array',
            'chat_behavior_overrides' => 'array',
        ];
    }

    public function maxRecursosCombinablesEfectivo(): int
    {
        return ($this->max_recursos_combinables !== null && $this->max_recursos_combinables >= 1)
            ? $this->max_recursos_combinables
            : 1;
    }

    public function chatRequiredFieldsFor(string $toolName): ?array
    {
        $fields = $this->chat_required_fields;

        if (! is_array($fields) || ! isset($fields[$toolName])) {
            return null;
        }

        return $fields[$toolName];
    }

    public function chatPersonalityOrDefault(): string
    {
        if ($this->chat_personality !== null && trim($this->chat_personality) !== '') {
            return trim($this->chat_personality);
        }

        return 'Amable, profesional y conciso. Trata al cliente de usted con cercanía.';
    }

    public function tipoNegocio(): BelongsTo
    {
        return $this->belongsTo(TipoNegocio::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }

    public function integraciones(): HasMany
    {
        return $this->hasMany(Integracion::class);
    }

    public function ocupacionesExternas(): HasMany
    {
        return $this->hasMany(OcupacionExterna::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withTimestamps();
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
