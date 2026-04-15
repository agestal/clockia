<?php

namespace App\Services\Integrations;

use App\Models\Negocio;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class GoogleCalendarAvailabilityService
{
    public function __construct(
        private readonly GoogleCalendarAuthService $authService,
        private readonly GoogleCalendarClient $client,
    ) {}

    public function shouldCheckLiveBusy(Negocio $negocio): bool
    {
        return $this->authService->isEnabledForBusiness($negocio);
    }

    public function busyRangesForBusiness(
        Negocio $negocio,
        CarbonInterface $from,
        CarbonInterface $to
    ): Collection {
        $integracion = $this->authService->connectedIntegration($negocio, requireActive: true);

        if (! $integracion) {
            return collect();
        }

        $calendarios = $this->authService->selectedCalendars($integracion);

        if ($calendarios->isEmpty()) {
            return collect();
        }

        try {
            $payload = $this->client->freeBusy(
                $this->authService->accessToken($integracion),
                $calendarios->pluck('external_id')->all(),
                $from,
                $to,
                $negocio->zona_horaria
            );
        } catch (\Throwable $exception) {
            $integracion->forceFill([
                'ultimo_error' => $exception->getMessage(),
            ])->save();

            return collect();
        }

        $busyRanges = collect();

        foreach ($calendarios as $calendar) {
            $calendarBusy = $payload['calendars'][$calendar->external_id]['busy'] ?? [];

            foreach ($calendarBusy as $busy) {
                if (! isset($busy['start'], $busy['end'])) {
                    continue;
                }

                $busyRanges->push([
                    'google_calendar_id' => $calendar->external_id,
                    'resource_id' => $calendar->recurso_id,
                    'is_global' => $calendar->recurso_id === null,
                    'start' => $this->normalizeToInternalClock($busy['start'], $negocio->zona_horaria),
                    'end' => $this->normalizeToInternalClock($busy['end'], $negocio->zona_horaria),
                ]);
            }
        }

        return $busyRanges;
    }

    public function slotOverlapsBusy(
        Collection $busyRanges,
        ?int $resourceId,
        CarbonInterface $start,
        CarbonInterface $end
    ): bool {
        return $busyRanges->contains(function (array $busy) use ($resourceId, $start, $end) {
            $busyResourceId = $busy['resource_id'] ?? null;

            if ($busyResourceId !== null && $resourceId !== null && (int) $busyResourceId !== $resourceId) {
                return false;
            }

            if ($busyResourceId !== null && $resourceId === null) {
                return false;
            }

            /** @var Carbon $busyStart */
            $busyStart = $busy['start'];
            /** @var Carbon $busyEnd */
            $busyEnd = $busy['end'];

            return $busyStart->lt($end) && $busyEnd->gt($start);
        });
    }

    private function normalizeToInternalClock(string $value, string $businessTimezone): Carbon
    {
        $local = Carbon::parse($value)->setTimezone($businessTimezone);

        return Carbon::parse($local->format('Y-m-d H:i:s'), config('app.timezone'));
    }
}
