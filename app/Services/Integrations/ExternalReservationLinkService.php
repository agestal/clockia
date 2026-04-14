<?php

namespace App\Services\Integrations;

use App\Models\Integracion;
use App\Models\Reserva;
use App\Models\ReservaIntegracion;

class ExternalReservationLinkService
{
    public function vincularReservaConExterna(
        Reserva $reserva,
        string $proveedor,
        string $externalId,
        ?Integracion $integracion = null,
        ?string $externalCalendarId = null,
        ?string $direccionSync = null
    ): ReservaIntegracion {
        return ReservaIntegracion::updateOrCreate(
            [
                'reserva_id' => $reserva->id,
                'proveedor' => $proveedor,
            ],
            [
                'integracion_id' => $integracion?->id,
                'external_id' => $externalId,
                'external_calendar_id' => $externalCalendarId,
                'direccion_sync' => $direccionSync,
                'ultimo_sync_at' => now(),
                'estado_sync' => 'vinculado',
            ]
        );
    }

    public function buscarVinculoPorReserva(Reserva $reserva, ?string $proveedor = null): ?ReservaIntegracion
    {
        $query = ReservaIntegracion::where('reserva_id', $reserva->id);

        if ($proveedor !== null) {
            $query->where('proveedor', $proveedor);
        }

        return $query->first();
    }

    public function buscarVinculoPorExternalId(string $proveedor, string $externalId): ?ReservaIntegracion
    {
        return ReservaIntegracion::query()
            ->where('proveedor', $proveedor)
            ->where('external_id', $externalId)
            ->first();
    }

    public function desvincularReserva(Reserva $reserva, string $proveedor): bool
    {
        return ReservaIntegracion::query()
            ->where('reserva_id', $reserva->id)
            ->where('proveedor', $proveedor)
            ->delete() > 0;
    }
}
