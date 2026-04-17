<?php

namespace App\Services\Reservations;

use App\Models\Bloqueo;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;

class DynamicExperienceAvailabilityService
{
    private array $slotsCache = [];

    private array $reservationsCache = [];

    public function supports(?Servicio $servicio): bool
    {
        return $servicio !== null && $servicio->usaProgramacionDinamica();
    }

    public function slotsForDate(
        Negocio $negocio,
        Servicio $servicio,
        Carbon|string $date,
        ?int $partySize = null,
        ?int $excludeReservationId = null
    ): array {
        $slots = $this->rawSlotsForDate($negocio, $servicio, $this->normalizeDate($date), $excludeReservationId);

        if ($partySize === null || $partySize < 1) {
            return $slots;
        }

        return array_values(array_filter(
            $slots,
            fn (array $slot) => (int) ($slot['aforo_restante'] ?? 0) >= $partySize
        ));
    }

    public function slotForSelection(
        Negocio $negocio,
        Servicio $servicio,
        Carbon|string $date,
        string $horaInicio,
        ?string $horaFin = null,
        ?int $excludeReservationId = null
    ): ?array {
        foreach ($this->rawSlotsForDate($negocio, $servicio, $this->normalizeDate($date), $excludeReservationId) as $slot) {
            if (($slot['hora_inicio'] ?? null) !== $horaInicio) {
                continue;
            }

            if ($horaFin !== null && ($slot['hora_fin'] ?? null) !== $horaFin) {
                continue;
            }

            return $slot;
        }

        return null;
    }

    public function serviceSummaryForDate(
        Negocio $negocio,
        Servicio $servicio,
        Carbon|string $date,
        ?int $partySize = null,
        ?int $excludeReservationId = null
    ): array {
        $rawSlots = $this->rawSlotsForDate($negocio, $servicio, $this->normalizeDate($date), $excludeReservationId);
        $availableSlots = $partySize !== null && $partySize > 0
            ? array_values(array_filter($rawSlots, fn (array $slot) => (int) ($slot['aforo_restante'] ?? 0) >= $partySize))
            : $rawSlots;

        $aggregate = $this->aggregateSlots($rawSlots);

        return [
            'service_id' => $servicio->id,
            'service_name' => $servicio->nombre,
            'available' => count($availableSlots) > 0,
            'total_slots' => count($rawSlots),
            'available_slots' => count($availableSlots),
            'occupancy_percent' => $aggregate['occupancy_percent'],
            'capacity_total' => $aggregate['capacity_total'],
            'seats_reserved_total' => $aggregate['seats_reserved_total'],
            'seats_available_total' => $aggregate['seats_available_total'],
            'duration_minutes' => $servicio->duracion_minutos,
            'capacity' => $servicio->aforo,
            'start_time' => $servicio->horaInicioCorta(),
            'end_time' => $servicio->horaFinCorta(),
            'is_dynamic_experience' => true,
        ];
    }

    /**
     * @param  iterable<int, Servicio>  $servicios
     */
    public function daySummaryForServices(
        Negocio $negocio,
        iterable $servicios,
        Carbon|string $date,
        ?int $partySize = null,
        ?int $excludeReservationId = null
    ): array {
        $fecha = $this->normalizeDate($date);
        $serviceSummaries = [];
        $allSlots = [];

        foreach ($servicios as $servicio) {
            if (! $this->supports($servicio)) {
                continue;
            }

            $summary = $this->serviceSummaryForDate($negocio, $servicio, $fecha, $partySize, $excludeReservationId);
            $serviceSummaries[] = $summary;
            $allSlots = array_merge($allSlots, $this->rawSlotsForDate($negocio, $servicio, $fecha, $excludeReservationId));
        }

        $aggregate = $this->aggregateSlots($allSlots);

        return [
            'date' => $fecha->toDateString(),
            'available' => collect($serviceSummaries)->contains(fn (array $summary) => $summary['available'] === true),
            'occupancy_percent' => $aggregate['occupancy_percent'],
            'total_slots' => count($allSlots),
            'available_slots' => array_sum(array_map(fn (array $summary) => (int) $summary['available_slots'], $serviceSummaries)),
            'capacity_total' => $aggregate['capacity_total'],
            'seats_reserved_total' => $aggregate['seats_reserved_total'],
            'seats_available_total' => $aggregate['seats_available_total'],
            'service_occupancy' => $serviceSummaries,
        ];
    }

    public function weeklySchedule(Negocio $negocio, ?Servicio $servicio = null): array
    {
        $dynamicServices = $servicio !== null
            ? collect($this->supports($servicio) ? [$servicio] : [])
            : $negocio->servicios()
                ->activos()
                ->get()
                ->filter(fn (Servicio $item) => $this->supports($item))
                ->values();

        $days = [];

        for ($day = 0; $day <= 6; $day++) {
            $turnos = [];

            if ($negocio->estaAbiertoEnDiaSemana($day)) {
                foreach ($dynamicServices as $dynamicService) {
                    $turnos[] = [
                        'service_id' => $dynamicService->id,
                        'service_name' => $dynamicService->nombre,
                        'hora_inicio' => $dynamicService->horaInicioCorta(),
                        'hora_fin' => $dynamicService->horaFinCorta(),
                        'duracion_minutos' => $dynamicService->duracion_minutos,
                        'aforo' => $dynamicService->aforo,
                    ];
                }
            }

            $days[] = [
                'dia_semana' => $day,
                'abierto' => count($turnos) > 0,
                'turnos' => $turnos,
            ];
        }

        return $days;
    }

    private function rawSlotsForDate(Negocio $negocio, Servicio $servicio, Carbon $date, ?int $excludeReservationId = null): array
    {
        if (! $this->supports($servicio)) {
            return [];
        }

        $cacheKey = implode('|', [$negocio->id, $servicio->id, $date->toDateString(), $excludeReservationId ?? 'none']);

        if (array_key_exists($cacheKey, $this->slotsCache)) {
            return $this->slotsCache[$cacheKey];
        }

        if (! $negocio->estaAbiertoEnDiaSemana((int) $date->dayOfWeek)) {
            return $this->slotsCache[$cacheKey] = [];
        }

        if ($this->hasFullDayBlock($negocio, $servicio, $date)) {
            return $this->slotsCache[$cacheKey] = [];
        }

        $capacity = max(0, (int) $servicio->aforo);
        if ($capacity <= 0) {
            return $this->slotsCache[$cacheKey] = [];
        }

        $windowStart = $this->buildDateTime($date, (string) $servicio->hora_inicio);
        $windowEnd = $this->buildDateTime($date, (string) $servicio->hora_fin);

        if ($windowStart === null || $windowEnd === null || $windowEnd->lessThanOrEqualTo($windowStart)) {
            return $this->slotsCache[$cacheKey] = [];
        }

        $slots = [];
        $cursor = $windowStart->copy();

        while (true) {
            $slotEnd = $cursor->copy()->addMinutes((int) $servicio->duracion_minutos);

            if ($slotEnd->greaterThan($windowEnd)) {
                break;
            }

            if ($this->slotHasPartialBlock($negocio, $servicio, $date, $cursor, $slotEnd)) {
                $cursor = $slotEnd;

                continue;
            }

            $reservedSeats = $this->reservedSeatsForSlot($negocio, $servicio, $date, $cursor, $slotEnd, $excludeReservationId);
            $remainingSeats = max(0, $capacity - $reservedSeats);
            $occupancyPercent = $capacity > 0
                ? (int) round(min(1, $reservedSeats / $capacity) * 100)
                : null;

            $slots[] = [
                'fecha' => $date->toDateString(),
                'hora_inicio' => $cursor->format('H:i'),
                'hora_fin' => $slotEnd->format('H:i'),
                'inicio_datetime' => $cursor->toDateTimeString(),
                'fin_datetime' => $slotEnd->toDateTimeString(),
                'slot_key' => sha1('dynamic_experience|'.$servicio->id.'|'.$date->toDateString().'|'.$cursor->format('H:i').'|'.$slotEnd->format('H:i')),
                'booking_time_mode' => 'experience_slot',
                'accepts_start_time_within_slot' => false,
                'recurso_id' => null,
                'recurso_ids' => [],
                'recurso_nombre' => null,
                'nombre_turno' => $servicio->nombre,
                'capacidad' => $capacity,
                'es_combinacion' => false,
                'es_sesion' => false,
                'es_experiencia_dinamica' => true,
                'sesion_id' => null,
                'aforo_total' => $capacity,
                'aforo_restante' => $remainingSeats,
                'ocupacion_porcentaje' => $occupancyPercent,
                'notas_publicas' => $servicio->notas_publicas,
                'recursos' => [],
                'numero_recursos' => 0,
                'capacidad_total' => $capacity,
                'servicio_id' => $servicio->id,
                'servicio_nombre' => $servicio->nombre,
            ];

            $cursor = $slotEnd;
        }

        return $this->slotsCache[$cacheKey] = $slots;
    }

    private function hasFullDayBlock(Negocio $negocio, Servicio $servicio, Carbon $date): bool
    {
        return Bloqueo::query()
            ->where('activo', true)
            ->where($this->blockScope($negocio, $servicio))
            ->where($this->blockDateScope($date))
            ->whereNull('hora_inicio')
            ->whereNull('hora_fin')
            ->exists();
    }

    private function slotHasPartialBlock(
        Negocio $negocio,
        Servicio $servicio,
        Carbon $date,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        return Bloqueo::query()
            ->where('activo', true)
            ->where($this->blockScope($negocio, $servicio))
            ->where($this->blockDateScope($date))
            ->whereNotNull('hora_inicio')
            ->whereNotNull('hora_fin')
            ->where('hora_inicio', '<', $slotEnd->format('H:i:s'))
            ->where('hora_fin', '>', $slotStart->format('H:i:s'))
            ->exists();
    }

    private function blockScope(Negocio $negocio, Servicio $servicio): \Closure
    {
        return function ($query) use ($negocio, $servicio) {
            $query
                ->where(function ($inner) use ($negocio) {
                    $inner->where('negocio_id', $negocio->id)
                        ->whereNull('servicio_id')
                        ->whereNull('recurso_id');
                })
                ->orWhere(function ($inner) use ($negocio, $servicio) {
                    $inner->where('servicio_id', $servicio->id)
                        ->where(function ($serviceScope) use ($negocio) {
                            $serviceScope->whereNull('negocio_id')
                                ->orWhere('negocio_id', $negocio->id);
                        });
                });
        };
    }

    private function blockDateScope(Carbon $date): \Closure
    {
        $dateString = $date->toDateString();
        $dayOfWeek = (int) $date->dayOfWeek;

        return function ($query) use ($dateString, $dayOfWeek) {
            $query->whereDate('fecha', $dateString)
                ->orWhere(function ($inner) use ($dateString) {
                    $inner->whereNotNull('fecha_inicio')
                        ->whereNotNull('fecha_fin')
                        ->where('fecha_inicio', '<=', $dateString)
                        ->where('fecha_fin', '>=', $dateString);
                })
                ->orWhere(function ($inner) use ($dayOfWeek) {
                    $inner->where('es_recurrente', true)
                        ->where('dia_semana', $dayOfWeek);
                });
        };
    }

    private function reservedSeatsForSlot(
        Negocio $negocio,
        Servicio $servicio,
        Carbon $date,
        Carbon $slotStart,
        Carbon $slotEnd,
        ?int $excludeReservationId = null
    ): int {
        $cacheKey = implode('|', [
            $negocio->id,
            $servicio->id,
            $date->toDateString(),
            $slotStart->format('H:i'),
            $slotEnd->format('H:i'),
            $excludeReservationId ?? 'none',
        ]);

        if (array_key_exists($cacheKey, $this->reservationsCache)) {
            return $this->reservationsCache[$cacheKey];
        }

        return $this->reservationsCache[$cacheKey] = (int) Reserva::query()
            ->where('negocio_id', $negocio->id)
            ->where('servicio_id', $servicio->id)
            ->whereDate('fecha', $date->toDateString())
            ->whereNotIn('estado_reserva_id', $this->cancelledReservationStateIds())
            ->when($excludeReservationId !== null, fn ($query) => $query->where('id', '<>', $excludeReservationId))
            ->where(function ($query) use ($slotStart, $slotEnd) {
                $query->where(function ($datetimeQuery) use ($slotStart, $slotEnd) {
                    $datetimeQuery->whereNotNull('inicio_datetime')
                        ->whereNotNull('fin_datetime')
                        ->where('inicio_datetime', '<', $slotEnd)
                        ->where('fin_datetime', '>', $slotStart);
                })->orWhere(function ($legacyQuery) use ($slotStart, $slotEnd) {
                    $legacyQuery->whereNull('inicio_datetime')
                        ->whereNull('fin_datetime')
                        ->where('hora_inicio', '<', $slotEnd->format('H:i:s'))
                        ->where('hora_fin', '>', $slotStart->format('H:i:s'));
                });
            })
            ->sum('numero_personas');
    }

    private function aggregateSlots(array $slots): array
    {
        $capacityTotal = array_sum(array_map(fn (array $slot) => (int) ($slot['aforo_total'] ?? 0), $slots));
        $availableTotal = array_sum(array_map(fn (array $slot) => (int) ($slot['aforo_restante'] ?? 0), $slots));
        $reservedTotal = max(0, $capacityTotal - $availableTotal);

        return [
            'capacity_total' => $capacityTotal,
            'seats_available_total' => $availableTotal,
            'seats_reserved_total' => $reservedTotal,
            'occupancy_percent' => $capacityTotal > 0
                ? (int) round(min(1, $reservedTotal / $capacityTotal) * 100)
                : null,
        ];
    }

    private function normalizeDate(Carbon|string $date): Carbon
    {
        return $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
    }

    private function buildDateTime(Carbon $date, string $time): ?Carbon
    {
        $normalized = trim($time);

        if ($normalized === '') {
            return null;
        }

        if (strlen($normalized) === 5) {
            $normalized .= ':00';
        }

        try {
            return Carbon::parse($date->toDateString().' '.$normalized);
        } catch (\Throwable) {
            return null;
        }
    }

    private function cancelledReservationStateIds(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = EstadoReserva::query()
                ->whereIn('nombre', ['Cancelada', 'No presentada'])
                ->pluck('id')
                ->all();
        }

        return $ids;
    }
}
