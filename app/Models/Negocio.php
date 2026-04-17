<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TipoNegocio $tipoNegocio
 * @property-read Collection<int, Servicio> $servicios
 * @property-read Collection<int, Sesion> $sesiones
 * @property-read Collection<int, Recurso> $recursos
 * @property-read Collection<int, Reserva> $reservas
 */
class Negocio extends Model
{
    use HasFactory;

    protected $table = 'negocios';

    protected static function booted(): void
    {
        static::creating(function (self $negocio): void {
            if (empty($negocio->widget_public_key)) {
                $negocio->widget_public_key = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'nombre',
        'tipo_negocio_id',
        'email',
        'telefono',
        'zona_horaria',
        'dias_apertura',
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
        'mail_confirmacion_activo',
        'mail_recordatorio_activo',
        'mail_recordatorio_horas_antes',
        'mail_encuesta_activo',
        'mail_encuesta_horas_despues',
        'notif_email_destino',
        'notif_reserva_nueva',
        'notif_reserva_modificada',
        'notif_anulacion_reserva',
        'notif_encuesta_respondida',
        'notif_aforo_lleno_experiencia',
        'notif_aforo_lleno_dia',
        'widget_enabled',
        'widget_public_key',
        'widget_settings',
        'chat_widget_enabled',
    ];

    protected function casts(): array
    {
        return [
            'tipo_negocio_id' => 'integer',
            'activo' => 'boolean',
            'dias_apertura' => 'array',
            'horas_minimas_cancelacion' => 'integer',
            'permite_modificacion' => 'boolean',
            'max_recursos_combinables' => 'integer',
            'chat_required_fields' => 'array',
            'chat_behavior_overrides' => 'array',
            'mail_confirmacion_activo' => 'boolean',
            'mail_recordatorio_activo' => 'boolean',
            'mail_recordatorio_horas_antes' => 'integer',
            'mail_encuesta_activo' => 'boolean',
            'mail_encuesta_horas_despues' => 'integer',
            'notif_reserva_nueva' => 'boolean',
            'notif_reserva_modificada' => 'boolean',
            'notif_anulacion_reserva' => 'boolean',
            'notif_encuesta_respondida' => 'boolean',
            'notif_aforo_lleno_experiencia' => 'boolean',
            'notif_aforo_lleno_dia' => 'boolean',
            'widget_enabled' => 'boolean',
            'widget_settings' => 'array',
            'chat_widget_enabled' => 'boolean',
        ];
    }

    public function widgetSettingsResolved(): array
    {
        $defaults = [
            'primary_color' => '#7B3F00',
            'secondary_color' => '#EAD7C5',
            'text_color' => '#2B2B2B',
            'background_color' => '#FFFFFF',
            'font_family' => 'Inter, system-ui, sans-serif',
            'font_size_base' => '14px',
            'border_radius' => '10px',
            'locale' => 'es',
        ];

        $stored = is_array($this->widget_settings) ? $this->widget_settings : [];

        return array_replace($defaults, array_filter($stored, fn ($value) => $value !== null && $value !== ''));
    }

    public function maxRecursosCombinablesEfectivo(): int
    {
        return ($this->max_recursos_combinables !== null && $this->max_recursos_combinables >= 1)
            ? $this->max_recursos_combinables
            : 1;
    }

    public function diasAperturaEfectivos(): array
    {
        $dias = collect(is_array($this->dias_apertura) ? $this->dias_apertura : [])
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value >= 0 && $value <= 6)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $dias !== [] ? $dias : [0, 1, 2, 3, 4, 5, 6];
    }

    public function estaAbiertoEnDiaSemana(int $diaSemana): bool
    {
        return in_array($diaSemana, $this->diasAperturaEfectivos(), true);
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

    public function sesiones(): HasMany
    {
        return $this->hasMany(Sesion::class);
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function plantillasEmail(): HasMany
    {
        return $this->hasMany(PlantillaEmail::class);
    }

    public function encuestaPlantillas(): HasMany
    {
        return $this->hasMany(EncuestaPlantilla::class);
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }

    public function integraciones(): HasMany
    {
        return $this->hasMany(Integracion::class);
    }

    public function integracionGoogleCalendar(): HasOne
    {
        return $this->hasOne(Integracion::class)
            ->where('proveedor', 'google_calendar')
            ->latestOfMany();
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
