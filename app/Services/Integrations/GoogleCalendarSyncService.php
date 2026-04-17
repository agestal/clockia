<?php

namespace App\Services\Integrations;

use App\Models\Integracion;
use App\Models\IntegracionMapeo;
use App\Models\Reserva;
use Carbon\Carbon;

class GoogleCalendarSyncService
{
    public function __construct(
        private readonly GoogleCalendarAuthService $authService,
        private readonly GoogleCalendarClient $client,
        private readonly ExternalReservationLinkService $linkService,
    ) {}

    public function syncBooking(Reserva $reserva): void
    {
        $reserva->loadMissing([
            'negocio',
            'servicio',
            'cliente',
            'reservaRecursos.recurso',
        ]);

        if ($reserva->importada_externamente) {
            return;
        }

        $integracion = $this->authService->connectedIntegration($reserva->negocio, requireActive: true);

        if (! $integracion) {
            return;
        }

        $targetCalendar = $this->resolveTargetCalendar($reserva, $integracion);

        if (! $targetCalendar) {
            $this->markError($reserva, $integracion, 'No hay ningún calendario de Google seleccionado para sincronizar reservas.');

            return;
        }

        $existingLink = $this->linkService->buscarVinculoPorReserva($reserva, 'google_calendar');

        try {
            $accessToken = $this->authService->accessToken($integracion);
            $payload = $this->buildEventPayload($reserva);
            $sameCalendar = $existingLink?->external_calendar_id === $targetCalendar->external_id;
            $hasExistingEvent = filled($existingLink?->external_id) && filled($existingLink?->external_calendar_id);

            if ($hasExistingEvent && $sameCalendar) {
                $event = $this->client->updateEvent(
                    $accessToken,
                    $targetCalendar->external_id,
                    (string) $existingLink->external_id,
                    $payload
                );
            } else {
                if ($hasExistingEvent && ! $sameCalendar) {
                    try {
                        $this->client->deleteEvent(
                            $accessToken,
                            (string) $existingLink->external_calendar_id,
                            (string) $existingLink->external_id
                        );
                    } catch (\Throwable $cleanupException) {
                        report($cleanupException);
                    }
                }

                $event = $this->client->createEvent(
                    $accessToken,
                    $targetCalendar->external_id,
                    $payload
                );
            }

            $this->linkService->vincularReservaConExterna(
                reserva: $reserva,
                proveedor: 'google_calendar',
                externalId: $event['id'] ?? null,
                integracion: $integracion,
                externalCalendarId: $targetCalendar->external_id,
                direccionSync: 'clockia_to_google',
                estadoSync: 'synced',
                payloadResumen: [
                    'html_link' => $event['htmlLink'] ?? null,
                    'status' => $event['status'] ?? null,
                ],
            );

            $integracion->forceFill([
                'ultimo_sync_at' => now(),
                'ultimo_error' => null,
            ])->save();
        } catch (\Throwable $exception) {
            $this->markError($reserva, $integracion, $exception->getMessage(), $targetCalendar);
        }
    }

    private function resolveTargetCalendar(Reserva $reserva, Integracion $integracion): ?IntegracionMapeo
    {
        $resourceIds = $reserva->reservaRecursos
            ->pluck('recurso_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $calendars = $this->authService->selectedCalendars($integracion);

        if ($calendars->isEmpty()) {
            return null;
        }

        if ($resourceIds !== []) {
            $resourceSpecific = $calendars->first(fn (IntegracionMapeo $calendar) => $calendar->recurso_id !== null
                && in_array((int) $calendar->recurso_id, $resourceIds, true));

            if ($resourceSpecific) {
                return $resourceSpecific;
            }
        }

        return $calendars
            ->sortByDesc(fn (IntegracionMapeo $calendar) => (int) $calendar->es_primario)
            ->first();
    }

    private function buildEventPayload(Reserva $reserva): array
    {
        $timezone = $reserva->negocio?->zona_horaria ?? 'UTC';
        $contactName = $reserva->nombreResponsableEfectivo();
        $serviceName = $reserva->servicio?->nombre ?? 'Reserva';
        $start = $this->bookingDateTime($reserva, $timezone, (string) $reserva->hora_inicio);
        $end = $this->bookingDateTime($reserva, $timezone, (string) $reserva->hora_fin);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $descriptionLines = array_filter([
            'Reserva creada en Clockia.',
            'Localizador: '.$reserva->localizador,
            $contactName ? 'Cliente: '.$contactName : null,
            $reserva->telefonoResponsableEfectivo() ? 'Teléfono: '.$reserva->telefonoResponsableEfectivo() : null,
            $reserva->emailResponsableEfectivo() ? 'Email: '.$reserva->emailResponsableEfectivo() : null,
            $reserva->numero_personas ? 'Personas: '.$reserva->numero_personas : null,
            $reserva->notas ? 'Notas: '.$reserva->notas : null,
        ]);

        return [
            'summary' => trim($serviceName.($contactName ? ' · '.$contactName : '')),
            'description' => implode("\n", $descriptionLines),
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'extendedProperties' => [
                'private' => [
                    'clockia_booking_id' => (string) $reserva->id,
                    'clockia_locator' => (string) $reserva->localizador,
                ],
            ],
        ];
    }

    private function bookingDateTime(Reserva $reserva, string $timezone, string $time): Carbon
    {
        return Carbon::parse(
            $reserva->fecha?->toDateString().' '.substr($time, 0, 8),
            $timezone
        );
    }

    private function markError(
        Reserva $reserva,
        Integracion $integracion,
        string $message,
        ?IntegracionMapeo $targetCalendar = null
    ): void {
        $this->linkService->vincularReservaConExterna(
            reserva: $reserva,
            proveedor: 'google_calendar',
            externalId: null,
            integracion: $integracion,
            externalCalendarId: $targetCalendar?->external_id,
            direccionSync: 'clockia_to_google',
            estadoSync: 'error',
            payloadResumen: [
                'error' => $message,
            ],
        );

        $integracion->forceFill([
            'ultimo_error' => $message,
        ])->save();
    }
}
