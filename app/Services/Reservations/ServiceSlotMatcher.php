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
    ];

    private const FRANJA_HORARIA = [
        'brunch' => ['09:00', '13:30'],
        'comida' => ['12:00', '16:30'],
        'cena' => ['19:00', '23:59'],
    ];

    public function disponibilidadEsCompatible(Servicio $servicio, Disponibilidad $disponibilidad): bool
    {
        $servicioKey = $this->normalizarNombreServicio($servicio->nombre);

        if ($servicioKey === null) {
            return true;
        }

        // Layer 1: match by nombre_turno
        if ($disponibilidad->nombre_turno !== null && $disponibilidad->nombre_turno !== '') {
            return $this->turnoCoincideConServicio($servicioKey, $disponibilidad->nombre_turno);
        }

        // Layer 2: fallback by time heuristic
        return $this->horaCoincideConServicio($servicioKey, $disponibilidad->hora_inicio);
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
