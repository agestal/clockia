<?php

namespace App\Services\Tools;

use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\RecursoCombinacion;
use App\Models\Servicio;
use App\Services\Reservations\DynamicExperienceAvailabilityService;

/**
 * Determines the operational complexity level of a business/service.
 *
 * Levels:
 *   1 = simple     — no operational resources, just catalog info
 *   2 = scheduled  — has availabilities, basic time-based operations
 *   3 = resourced  — has active resources with capacity, reservations, blocks
 *   4 = advanced   — combinations, external occupancies, integrations
 */
class BusinessComplexityResolver
{
    public const LEVEL_SIMPLE = 1;
    public const LEVEL_SCHEDULED = 2;
    public const LEVEL_RESOURCED = 3;
    public const LEVEL_ADVANCED = 4;

    public function nivelComplejidad(Negocio $negocio, ?Servicio $servicio = null): int
    {
        if ($this->negocioTieneCombinacionesRecursos($negocio, $servicio) || $this->negocioTieneIntegracionActiva($negocio)) {
            return self::LEVEL_ADVANCED;
        }

        if ($this->negocioTieneRecursosOperativos($negocio, $servicio)) {
            return self::LEVEL_RESOURCED;
        }

        if ($this->negocioTieneDisponibilidadesOperativas($negocio, $servicio)) {
            return self::LEVEL_SCHEDULED;
        }

        return self::LEVEL_SIMPLE;
    }

    public function negocioTieneRecursosOperativos(Negocio $negocio, ?Servicio $servicio = null): bool
    {
        if ($servicio !== null) {
            return $servicio->recursos()->activos()->exists();
        }

        return $negocio->recursos()->activos()->exists();
    }

    public function negocioTieneDisponibilidadesOperativas(Negocio $negocio, ?Servicio $servicio = null): bool
    {
        if ($this->negocioTieneExperienciasDinamicas($negocio, $servicio)) {
            return true;
        }

        $recursoIds = $this->recursoIds($negocio, $servicio);

        if (empty($recursoIds)) {
            return false;
        }

        return Disponibilidad::query()
            ->whereIn('recurso_id', $recursoIds)
            ->activos()
            ->exists();
    }

    public function negocioTieneCombinacionesRecursos(Negocio $negocio, ?Servicio $servicio = null): bool
    {
        if ($negocio->maxRecursosCombinablesEfectivo() <= 1) {
            return false;
        }

        $recursoIds = $this->recursoIds($negocio, $servicio);

        if (empty($recursoIds)) {
            return false;
        }

        return RecursoCombinacion::query()
            ->whereIn('recurso_id', $recursoIds)
            ->whereIn('recurso_combinado_id', $recursoIds)
            ->exists();
    }

    public function negocioTieneIntegracionActiva(Negocio $negocio): bool
    {
        return $negocio->integraciones()->where('activo', true)->exists();
    }

    public function negocioTieneOcupacionesExternas(Negocio $negocio, mixed $fecha = null): bool
    {
        $query = OcupacionExterna::where('negocio_id', $negocio->id);

        if ($fecha !== null) {
            $query->enFecha((string) $fecha);
        }

        return $query->exists();
    }

    public function negocioTieneExperienciasDinamicas(Negocio $negocio, ?Servicio $servicio = null): bool
    {
        $dynamicAvailability = app(DynamicExperienceAvailabilityService::class);

        if ($servicio !== null) {
            return $dynamicAvailability->supports($servicio);
        }

        return $negocio->servicios()
            ->activos()
            ->get()
            ->contains(fn (Servicio $item) => $dynamicAvailability->supports($item));
    }

    private function recursoIds(Negocio $negocio, ?Servicio $servicio): array
    {
        if ($servicio !== null) {
            return $servicio->recursos()->activos()->pluck('recursos.id')->all();
        }

        return $negocio->recursos()->activos()->pluck('id')->all();
    }
}
