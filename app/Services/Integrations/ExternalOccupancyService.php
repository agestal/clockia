<?php

namespace App\Services\Integrations;

use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\Recurso;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ExternalOccupancyService
{
    public function obtenerOcupacionesNegocioEnFecha(Negocio $negocio, string $fecha): Collection
    {
        return OcupacionExterna::query()
            ->where('negocio_id', $negocio->id)
            ->enFecha($fecha)
            ->orderBy('inicio_datetime')
            ->get();
    }

    public function obtenerOcupacionesRecursoEnFecha(Recurso $recurso, string $fecha): Collection
    {
        return OcupacionExterna::query()
            ->where('recurso_id', $recurso->id)
            ->enFecha($fecha)
            ->orderBy('inicio_datetime')
            ->get();
    }

    public function obtenerOcupacionesNegocioEnRango(Negocio $negocio, string $desde, string $hasta): Collection
    {
        return OcupacionExterna::query()
            ->where('negocio_id', $negocio->id)
            ->enRango($desde, $hasta)
            ->orderBy('inicio_datetime')
            ->get();
    }

    public function obtenerOcupacionesRecursoEnRango(Recurso $recurso, string $desde, string $hasta): Collection
    {
        return OcupacionExterna::query()
            ->where('recurso_id', $recurso->id)
            ->enRango($desde, $hasta)
            ->orderBy('inicio_datetime')
            ->get();
    }

    public function slotSolapaConOcupacionExterna(
        int $negocioId,
        ?int $recursoId,
        Carbon $inicio,
        Carbon $fin
    ): bool {
        $query = OcupacionExterna::query()
            ->where('negocio_id', $negocioId)
            ->where(function ($q) use ($inicio, $fin) {
                $q->where(function ($inner) use ($inicio, $fin) {
                    $inner->where('inicio_datetime', '<', $fin)
                        ->where('fin_datetime', '>', $inicio);
                })->orWhere(function ($inner) use ($inicio) {
                    $inner->where('es_dia_completo', true)
                        ->where('fecha', $inicio->toDateString());
                });
            });

        if ($recursoId !== null) {
            $query->where(function ($q) use ($recursoId) {
                $q->where('recurso_id', $recursoId)
                    ->orWhereNull('recurso_id');
            });
        }

        return $query->exists();
    }
}
