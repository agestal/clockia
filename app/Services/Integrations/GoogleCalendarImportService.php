<?php

namespace App\Services\Integrations;

use App\Models\Negocio;
use App\Models\OcupacionExterna;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class GoogleCalendarImportService
{
    public function __construct(
        private readonly GoogleCalendarAuthService $authService,
        private readonly GoogleCalendarClient $client,
    ) {}

    public function importUpcomingEvents(int|Negocio $business, ?int $daysAhead = null): Collection
    {
        $negocio = $business instanceof Negocio ? $business : Negocio::query()->findOrFail($business);
        $integracion = $this->authService->connectedIntegration($negocio);

        if (! $integracion) {
            throw new RuntimeException('El negocio no tiene una integración de Google Calendar conectada.');
        }

        $calendarios = $this->authService->selectedCalendars($integracion);

        if ($calendarios->isEmpty()) {
            throw new RuntimeException('No hay calendarios seleccionados para importar.');
        }

        $accessToken = $this->authService->accessToken($integracion);
        $desde = now($negocio->zona_horaria)->startOfDay();
        $hasta = $desde->copy()->addDays($daysAhead ?? (int) config('services.google_calendar.import_days', 30))->endOfDay();
        $ocupaciones = collect();

        foreach ($calendarios as $calendar) {
            $events = $this->client->listEvents($accessToken, $calendar->external_id, $desde, $hasta);

            foreach ($events as $event) {
                $ocupacion = $this->syncEvent($negocio, $integracion->id, $calendar, $event);

                if ($ocupacion !== null) {
                    $ocupaciones->push($ocupacion);
                }
            }
        }

        $integracion->forceFill([
            'ultimo_sync_at' => now(),
            'ultimo_error' => null,
        ])->save();

        return $ocupaciones;
    }

    private function syncEvent(Negocio $negocio, int $integracionId, $calendar, array $event): ?OcupacionExterna
    {
        $eventId = (string) ($event['id'] ?? '');

        if ($eventId === '') {
            return null;
        }

        if (($event['status'] ?? null) === 'cancelled') {
            OcupacionExterna::query()
                ->where('proveedor', 'google_calendar')
                ->where('external_calendar_id', $calendar->external_id)
                ->where('external_id', $eventId)
                ->delete();

            return null;
        }

        [$inicio, $fin, $fecha, $horaInicio, $horaFin, $esDiaCompleto] = $this->normalizeEventWindow($negocio, $calendar->timezone, $event);

        return OcupacionExterna::query()->updateOrCreate(
            [
                'proveedor' => 'google_calendar',
                'external_calendar_id' => $calendar->external_id,
                'external_id' => $eventId,
            ],
            [
                'negocio_id' => $negocio->id,
                'integracion_id' => $integracionId,
                'integracion_mapeo_id' => $calendar->id,
                'recurso_id' => $calendar->recurso_id,
                'titulo' => $event['summary'] ?? '(Sin título)',
                'descripcion' => $event['description'] ?? null,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'inicio_datetime' => $inicio,
                'fin_datetime' => $fin,
                'es_dia_completo' => $esDiaCompleto,
                'origen' => 'importacion_google_calendar',
                'estado' => $event['status'] ?? 'confirmed',
                'payload_externo' => $event,
                'ultimo_sync_at' => now(),
            ]
        );
    }

    private function normalizeEventWindow(Negocio $negocio, ?string $calendarTimezone, array $event): array
    {
        $eventTimezone = $event['start']['timeZone']
            ?? $event['end']['timeZone']
            ?? $calendarTimezone
            ?? $negocio->zona_horaria;

        if (isset($event['start']['dateTime'], $event['end']['dateTime'])) {
            $inicio = Carbon::parse($event['start']['dateTime'], $eventTimezone)->setTimezone($negocio->zona_horaria);
            $fin = Carbon::parse($event['end']['dateTime'], $eventTimezone)->setTimezone($negocio->zona_horaria);

            return [
                $inicio,
                $fin,
                $inicio->toDateString(),
                $inicio->format('H:i:s'),
                $fin->format('H:i:s'),
                false,
            ];
        }

        $inicio = Carbon::parse($event['start']['date'], $eventTimezone)->startOfDay()->setTimezone($negocio->zona_horaria);
        $finExclusivo = isset($event['end']['date'])
            ? Carbon::parse($event['end']['date'], $eventTimezone)->startOfDay()->setTimezone($negocio->zona_horaria)
            : $inicio->copy()->addDay();

        if ($finExclusivo->lessThanOrEqualTo($inicio)) {
            $finExclusivo = $inicio->copy()->addDay();
        }

        return [
            $inicio,
            $finExclusivo,
            $inicio->toDateString(),
            null,
            null,
            true,
        ];
    }
}
