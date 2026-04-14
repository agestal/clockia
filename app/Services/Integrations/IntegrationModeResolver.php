<?php

namespace App\Services\Integrations;

use App\Models\Integracion;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Collection;

class IntegrationModeResolver
{
    public function negocioUsaSoloClockia(Negocio $negocio): bool
    {
        return $this->obtenerIntegracionesActivas($negocio)->isEmpty();
    }

    public function negocioUsaCoexistenciaGoogle(Negocio $negocio): bool
    {
        return $this->obtenerIntegracionesActivas($negocio)
            ->contains(fn (Integracion $i) => $i->esGoogleCalendar() && $i->esModoCoexistencia());
    }

    public function negocioUsaMigracion(Negocio $negocio): bool
    {
        return $this->obtenerIntegracionesActivas($negocio)
            ->contains(fn (Integracion $i) => $i->esModoMigracion());
    }

    public function obtenerIntegracionesActivas(Negocio $negocio): Collection
    {
        if ($negocio->relationLoaded('integraciones')) {
            return $negocio->integraciones->where('activo', true)->values();
        }

        return $negocio->integraciones()->activas()->get();
    }

    public function modoEfectivoNegocio(Negocio $negocio): string
    {
        $integraciones = $this->obtenerIntegracionesActivas($negocio);

        if ($integraciones->isEmpty()) {
            return 'solo_clockia';
        }

        if ($integraciones->contains(fn (Integracion $i) => $i->esModoCoexistencia())) {
            return 'coexistencia';
        }

        if ($integraciones->contains(fn (Integracion $i) => $i->esModoMigracion())) {
            return 'migracion';
        }

        return 'solo_clockia';
    }
}
