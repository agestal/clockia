<?php

namespace App\Services;

use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;

/**
 * Resolves booking policies following a strict precedence order:
 *   1. Reserva override (if set and not null)
 *   2. Servicio value
 *   3. Negocio value
 *   4. System fallback
 *
 * This is the single source of truth for effective policy values
 * at any point of the booking lifecycle.
 */
class PolicyResolver
{
    /**
     * System-wide defaults applied when neither the reserva, servicio
     * nor the negocio provide a value for a given policy key.
     */
    private const DEFAULTS = [
        'horas_minimas_cancelacion' => 0,
        'permite_modificacion' => true,
        'es_reembolsable' => true,
        'porcentaje_senal' => null,
    ];

    public function horasMinimasCancelacion(?Reserva $reserva, ?Servicio $servicio, ?Negocio $negocio): int
    {
        return (int) $this->resolveNumeric(
            'horas_minimas_cancelacion',
            $reserva,
            $servicio,
            $negocio
        );
    }

    public function permiteModificacion(?Reserva $reserva, ?Servicio $servicio, ?Negocio $negocio): bool
    {
        return (bool) $this->resolveBoolean(
            'permite_modificacion',
            $reserva,
            $servicio,
            $negocio,
            servicioSupportsKey: false
        );
    }

    public function esReembolsable(?Reserva $reserva, ?Servicio $servicio, ?Negocio $negocio): bool
    {
        return (bool) $this->resolveBoolean(
            'es_reembolsable',
            $reserva,
            $servicio,
            $negocio,
            negocioSupportsKey: false
        );
    }

    public function porcentajeSenal(?Reserva $reserva, ?Servicio $servicio, ?Negocio $negocio): ?string
    {
        $value = $this->resolveNullable(
            'porcentaje_senal',
            $reserva,
            $servicio,
            $negocio,
            negocioSupportsKey: false
        );

        if ($value === null) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Returns the full policy snapshot for a given reserva context.
     * Useful for exposing effective values on show views or API responses.
     *
     * @return array{horas_minimas_cancelacion: int, permite_modificacion: bool, es_reembolsable: bool, porcentaje_senal: ?string}
     */
    public function resolverParaReserva(Reserva $reserva): array
    {
        $servicio = $reserva->relationLoaded('servicio') ? $reserva->servicio : $reserva->servicio()->first();
        $negocio = $reserva->relationLoaded('negocio') ? $reserva->negocio : $reserva->negocio()->first();

        return [
            'horas_minimas_cancelacion' => $this->horasMinimasCancelacion($reserva, $servicio, $negocio),
            'permite_modificacion' => $this->permiteModificacion($reserva, $servicio, $negocio),
            'es_reembolsable' => $this->esReembolsable($reserva, $servicio, $negocio),
            'porcentaje_senal' => $this->porcentajeSenal($reserva, $servicio, $negocio),
        ];
    }

    private function resolveNumeric(
        string $key,
        ?Reserva $reserva,
        ?Servicio $servicio,
        ?Negocio $negocio,
        bool $servicioSupportsKey = true,
        bool $negocioSupportsKey = true
    ): int {
        $value = $this->pickFirstNotNull($key, $reserva, $servicio, $negocio, $servicioSupportsKey, $negocioSupportsKey);

        return $value !== null ? (int) $value : (int) self::DEFAULTS[$key];
    }

    private function resolveBoolean(
        string $key,
        ?Reserva $reserva,
        ?Servicio $servicio,
        ?Negocio $negocio,
        bool $servicioSupportsKey = true,
        bool $negocioSupportsKey = true
    ): bool {
        $value = $this->pickFirstNotNull($key, $reserva, $servicio, $negocio, $servicioSupportsKey, $negocioSupportsKey);

        return $value !== null ? (bool) $value : (bool) self::DEFAULTS[$key];
    }

    private function resolveNullable(
        string $key,
        ?Reserva $reserva,
        ?Servicio $servicio,
        ?Negocio $negocio,
        bool $servicioSupportsKey = true,
        bool $negocioSupportsKey = true
    ): mixed {
        $value = $this->pickFirstNotNull($key, $reserva, $servicio, $negocio, $servicioSupportsKey, $negocioSupportsKey);

        return $value ?? self::DEFAULTS[$key];
    }

    private function pickFirstNotNull(
        string $key,
        ?Reserva $reserva,
        ?Servicio $servicio,
        ?Negocio $negocio,
        bool $servicioSupportsKey,
        bool $negocioSupportsKey
    ): mixed {
        if ($reserva !== null && $this->hasAttribute($reserva, $key) && $reserva->getAttribute($key) !== null) {
            return $reserva->getAttribute($key);
        }

        if ($servicioSupportsKey && $servicio !== null && $this->hasAttribute($servicio, $key) && $servicio->getAttribute($key) !== null) {
            return $servicio->getAttribute($key);
        }

        if ($negocioSupportsKey && $negocio !== null && $this->hasAttribute($negocio, $key) && $negocio->getAttribute($key) !== null) {
            return $negocio->getAttribute($key);
        }

        return null;
    }

    private function hasAttribute(object $model, string $key): bool
    {
        return array_key_exists($key, $model->getAttributes())
            || in_array($key, $model->getFillable(), true);
    }
}
