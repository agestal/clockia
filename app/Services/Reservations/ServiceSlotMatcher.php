<?php

namespace App\Services\Reservations;

use App\Models\Disponibilidad;
use App\Models\Servicio;

/**
 * Filters availability slots by semantic compatibility with a service.
 *
 * Layer 1: match by nombre_turno (e.g. "Turno de cena" for service "Cena")
 * Layer 2: fallback by time-of-day heuristic if no nombre_turno available
 */
class ServiceSlotMatcher
{
    private const TURNO_MAP = [
        'cena' => ['cena', 'nocturno', 'noche'],
        'comida' => ['comida', 'mediodía', 'mediodia', 'almuerzo'],
        'brunch' => ['brunch', 'desayuno'],
        'manana' => ['mañana', 'matinal', 'morning'],
        'tarde' => ['tarde', 'vespertino', 'afternoon'],
    ];

    private const FRANJA_HORARIA = [
        'brunch' => ['09:00', '13:30'],
        'comida' => ['12:00', '16:30'],
        'cena' => ['19:00', '23:59'],
        'manana' => ['09:00', '14:00'],
        'tarde' => ['15:00', '20:00'],
    ];

    /**
     * Service name keywords that should match against nombre_turno or
     * the turno's own name when the service name itself doesn't carry
     * a time-of-day hint (e.g. wine experiences like "Orixe", "Creaciones Singulares").
     *
     * These services are matched by comparing the nombre_turno of the
     * disponibilidad against the service name: if the turno explicitly
     * references the service, it's compatible.
     */
    public function disponibilidadEsCompatible(Servicio $servicio, Disponibilidad $disponibilidad): bool
    {
        $servicioKey = $this->normalizarNombreServicio($servicio->nombre);

        if ($servicioKey !== null) {
            // Layer 1: match by nombre_turno
            if ($disponibilidad->nombre_turno !== null && $disponibilidad->nombre_turno !== '') {
                return $this->turnoCoincideConServicio($servicioKey, $disponibilidad->nombre_turno);
            }

            // Layer 2: fallback by time heuristic
            return $this->horaCoincideConServicio($servicioKey, $disponibilidad->hora_inicio);
        }

        // Layer 3: for services without time-of-day hint (e.g. wine experiences),
        // check if the turno name explicitly references the service name
        if ($disponibilidad->nombre_turno !== null && $disponibilidad->nombre_turno !== '') {
            return $this->turnoReferenciaServicio($servicio->nombre, $disponibilidad->nombre_turno);
        }

        // No time-of-day hint and no turno name → compatible with everything
        return true;
    }

    private function normalizarNombreServicio(string $nombre): ?string
    {
        $lower = mb_strtolower(trim($nombre), 'UTF-8');

        foreach (array_keys(self::TURNO_MAP) as $key) {
            if (str_contains($lower, $key)) {
                return $key;
            }
        }

        if (str_contains($lower, 'almuerzo')) {
            return 'comida';
        }

        return null;
    }

    /**
     * Check if a turno name explicitly references a specific service by name.
     * E.g. turno "Creaciones Singulares" matches service "Creaciones Singulares".
     */
    private function turnoReferenciaServicio(string $servicioNombre, string $turnoNombre): bool
    {
        $servicioLower = mb_strtolower(trim($servicioNombre), 'UTF-8');
        $turnoLower = mb_strtolower(trim($turnoNombre), 'UTF-8');

        // Turno name contains the service name or vice versa
        if (str_contains($turnoLower, $servicioLower) || str_contains($servicioLower, $turnoLower)) {
            return true;
        }

        // Generic turno names are compatible with any service
        $genericTurnos = [
            'turno de mañana',
            'turno de tarde',
            'turno de noche',
            'mañana',
            'tarde',
            'noche',
            'horario de oficina',
            'horario comercial',
            'office hours',
            'disponibilidad general',
        ];
        foreach ($genericTurnos as $generic) {
            if (str_contains($turnoLower, $generic)) {
                return true;
            }
        }

        return false;
    }

    private function turnoCoincideConServicio(string $servicioKey, string $nombreTurno): bool
    {
        $turnoLower = mb_strtolower(trim($nombreTurno), 'UTF-8');
        $keywords = self::TURNO_MAP[$servicioKey] ?? [];

        foreach ($keywords as $keyword) {
            if (str_contains($turnoLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function horaCoincideConServicio(string $servicioKey, ?string $horaInicio): bool
    {
        if ($horaInicio === null) {
            return true;
        }

        $hora = substr((string) $horaInicio, 0, 5);
        $franja = self::FRANJA_HORARIA[$servicioKey] ?? null;

        if ($franja === null) {
            return true;
        }

        return $hora >= $franja[0] && $hora <= $franja[1];
    }
}
