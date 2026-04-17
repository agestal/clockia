<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class BusinessOnboardingSession extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_DISCOVERING = 'discovering';

    public const STATUS_NEEDS_INPUT = 'needs_input';

    public const STATUS_READY_FOR_REVIEW = 'ready_for_review';

    public const STATUS_PROVISIONED = 'provisioned';

    public const STATUS_FAILED = 'failed';

    protected $table = 'business_onboarding_sessions';

    protected $fillable = [
        'created_by_user_id',
        'requested_tipo_negocio_id',
        'provisioned_negocio_id',
        'status',
        'source_url',
        'source_host',
        'requested_business_name',
        'requested_admin_name',
        'requested_admin_email',
        'requested_admin_password_hash',
        'draft_payload',
        'missing_required_fields',
        'last_error',
        'discovery_started_at',
        'discovery_finished_at',
        'confirmed_at',
        'provisioned_at',
    ];

    protected function casts(): array
    {
        return [
            'created_by_user_id' => 'integer',
            'requested_tipo_negocio_id' => 'integer',
            'provisioned_negocio_id' => 'integer',
            'draft_payload' => 'array',
            'missing_required_fields' => 'array',
            'discovery_started_at' => 'datetime',
            'discovery_finished_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'provisioned_at' => 'datetime',
        ];
    }

    public static function labels(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_DISCOVERING => 'Explorando',
            self::STATUS_NEEDS_INPUT => 'Faltan datos',
            self::STATUS_READY_FOR_REVIEW => 'Listo para revisar',
            self::STATUS_PROVISIONED => 'Provisionado',
            self::STATUS_FAILED => 'Con error',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function requestedTipoNegocio(): BelongsTo
    {
        return $this->belongsTo(TipoNegocio::class, 'requested_tipo_negocio_id');
    }

    public function provisionedNegocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'provisioned_negocio_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(BusinessOnboardingSource::class, 'business_onboarding_session_id');
    }

    public function statusLabel(): string
    {
        return self::labels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_READY_FOR_REVIEW => 'badge-success',
            self::STATUS_PROVISIONED => 'badge-primary',
            self::STATUS_DISCOVERING => 'badge-warning',
            self::STATUS_FAILED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function draft(): array
    {
        $draft = is_array($this->draft_payload) ? $this->draft_payload : [];

        $draft['business'] = array_replace([
            'nombre' => $this->requested_business_name,
            'tipo_negocio_id' => $this->requested_tipo_negocio_id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'permite_modificacion' => true,
            'url_publica' => $this->source_url,
        ], Arr::get($draft, 'business', []));

        $draft['admin'] = array_replace([
            'name' => $this->requested_admin_name,
            'email' => $this->requested_admin_email,
            'password_ready' => $this->requested_admin_password_hash !== null,
        ], Arr::get($draft, 'admin', []));

        $draft['experience_candidates'] = array_values(array_filter(
            Arr::get($draft, 'experience_candidates', []),
            static fn ($candidate) => is_array($candidate)
        ));

        $draft['opening_hours'] = array_values(array_filter(
            Arr::get($draft, 'opening_hours', []),
            static fn ($row) => is_array($row) || is_string($row)
        ));

        $draft['notes'] = array_values(array_filter(
            Arr::get($draft, 'notes', []),
            static fn ($note) => is_string($note) && trim($note) !== ''
        ));

        $draft['missing_required_fields'] = $this->missingRequiredFieldsResolved();

        return $draft;
    }

    public function missingRequiredFieldsResolved(): array
    {
        $values = is_array($this->missing_required_fields) ? $this->missing_required_fields : [];

        return array_values(array_filter(
            array_map(static fn ($value) => is_string($value) ? trim($value) : '', $values),
            static fn ($value) => $value !== ''
        ));
    }

    public function readyForProvisioning(): bool
    {
        $draft = $this->draft();

        return $this->status !== self::STATUS_PROVISIONED
            && $this->missingRequiredFieldsResolved() === []
            && filled(Arr::get($draft, 'business.nombre'))
            && Arr::get($draft, 'business.tipo_negocio_id') !== null
            && filled(Arr::get($draft, 'business.zona_horaria'))
            && filled(Arr::get($draft, 'admin.email'))
            && ($draft['admin']['password_ready'] ?? false) === true;
    }
}
