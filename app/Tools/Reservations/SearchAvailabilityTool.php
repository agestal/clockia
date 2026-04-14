<?php

namespace App\Tools\Reservations;

use App\Models\Bloqueo;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Services\Reservations\ResourceCombinationService;
use App\Services\Reservations\ServiceSlotMatcher;
use App\Services\Tools\BusinessComplexityResolver;
use App\Tools\Data\SearchAvailabilityInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SearchAvailabilityTool extends ToolDefinition
{
    public function name(): string
    {
        return 'search_availability';
    }

    public function description(): string
    {
        return 'Busca huecos disponibles reales para un servicio en una fecha concreta, adaptándose a la complejidad del negocio.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'servicio_id' => ['type' => 'integer'],
                'fecha' => ['type' => 'string', 'format' => 'date'],
                'numero_personas' => ['type' => 'integer', 'nullable' => true, 'minimum' => 1],
            ],
            'required' => ['negocio_id', 'servicio_id', 'fecha'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = SearchAvailabilityInput::fromArray($input);

        $negocio = Negocio::find($dto->negocio_id);
        if (! $negocio) {
            throw new EntityNotFoundException('Negocio', $dto->negocio_id);
        }

        $servicio = Servicio::query()
            ->where('negocio_id', $dto->negocio_id)
            ->where('id', $dto->servicio_id)
            ->activos()
            ->first();

        if (! $servicio) {
            throw new EntityNotFoundException('Servicio', $dto->servicio_id);
        }

        $complexity = app(BusinessComplexityResolver::class);
        $nivel = $complexity->nivelComplejidad($negocio, $servicio);

        // LEVEL 1: Simple business — no resources, no schedules
        if ($nivel === BusinessComplexityResolver::LEVEL_SIMPLE) {
            return $this->respuestaSimple($dto, $negocio, $servicio);
        }

        // LEVEL 2+: Resource-based availability
        $fechaCarbon = Carbon::parse($dto->fecha);
        $diaSemana = (int) $fechaCarbon->dayOfWeek;
        $duracion = $servicio->duracion_minutos;

        // STEP 1: Individual resources
        $recursosIndividuales = $servicio->recursos()
            ->activos()
            ->when($dto->numero_personas !== null, function ($q) use ($dto) {
                $q->where(function ($inner) use ($dto) {
                    $inner->whereNull('capacidad')
                        ->orWhere('capacidad', '>=', $dto->numero_personas);
                });
                $q->where(function ($inner) use ($dto) {
                    $inner->whereNull('capacidad_minima')
                        ->orWhere('capacidad_minima', '<=', $dto->numero_personas);
                });
            })
            ->get();

        $checkExternal = $nivel >= BusinessComplexityResolver::LEVEL_ADVANCED
            && $complexity->negocioTieneOcupacionesExternas($negocio, $dto->fecha);

        $slots = $this->buscarSlotsParaRecursos(
            $recursosIndividuales, $fechaCarbon, $diaSemana, $duracion, $dto, $checkExternal, $servicio
        );

        // STEP 2: Combinations — only if needed AND allowed
        if ($dto->numero_personas !== null
            && $dto->numero_personas > 1
            && $nivel >= BusinessComplexityResolver::LEVEL_ADVANCED
            && $complexity->negocioTieneCombinacionesRecursos($negocio, $servicio)
        ) {
            $todosRecursos = $servicio->recursos()->activos()->get();
            $combinaciones = app(ResourceCombinationService::class)
                ->buscarCombinacionesValidas($servicio, $dto->numero_personas, $todosRecursos);

            foreach ($combinaciones as $combinacion) {
                $slots = array_merge($slots, $this->buscarSlotsParaCombinacion(
                    $combinacion, $fechaCarbon, $diaSemana, $duracion, $dto, $checkExternal, $servicio
                ));
            }
        }

        usort($slots, function ($a, $b) {
            $cmp = strcmp($a['inicio_datetime'], $b['inicio_datetime']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return ($a['es_combinacion'] ? 1 : 0) <=> ($b['es_combinacion'] ? 1 : 0);
        });

        return ToolResult::ok([
            'negocio_id' => $dto->negocio_id,
            'servicio_id' => $servicio->id,
            'servicio_nombre' => $servicio->nombre,
            'fecha' => $dto->fecha,
            'duracion_minutos' => $duracion,
            'numero_personas' => $dto->numero_personas,
            'total_slots' => count($slots),
            'slots' => $slots,
            'availability_mode' => 'precise',
            'complexity_level' => $nivel,
            'has_precise_slots' => true,
        ]);
    }

    /**
     * For simple businesses without operational resources/schedules.
     * Returns service-level info without pretending there's a detailed agenda.
     */
    private function respuestaSimple(SearchAvailabilityInput $dto, Negocio $negocio, Servicio $servicio): ToolResult
    {
        $fechaCarbon = Carbon::parse($dto->fecha);

        $bloqueadoNegocio = Bloqueo::query()
            ->where('activo', true)
            ->where('negocio_id', $negocio->id)
            ->whereNull('recurso_id')
            ->whereNull('hora_inicio')
            ->whereNull('hora_fin')
            ->where(function ($q) use ($dto, $fechaCarbon) {
                $diaSemana = (int) $fechaCarbon->dayOfWeek;
                $q->where('fecha', $dto->fecha)
                    ->orWhere(function ($inner) use ($dto) {
                        $inner->whereNotNull('fecha_inicio')
                            ->where('fecha_inicio', '<=', $dto->fecha)
                            ->where('fecha_fin', '>=', $dto->fecha);
                    })
                    ->orWhere(function ($inner) use ($diaSemana) {
                        $inner->where('es_recurrente', true)
                            ->where('dia_semana', $diaSemana);
                    });
            })
            ->exists();

        return ToolResult::ok([
            'negocio_id' => $dto->negocio_id,
            'servicio_id' => $servicio->id,
            'servicio_nombre' => $servicio->nombre,
            'fecha' => $dto->fecha,
            'duracion_minutos' => $servicio->duracion_minutos,
            'numero_personas' => $dto->numero_personas,
            'total_slots' => 0,
            'slots' => [],
            'availability_mode' => 'simple',
            'complexity_level' => BusinessComplexityResolver::LEVEL_SIMPLE,
            'has_precise_slots' => false,
            'requires_human_followup' => true,
            'servicio_disponible' => ! $bloqueadoNegocio,
            'mensaje' => $bloqueadoNegocio
                ? 'El negocio tiene un bloqueo activo para esta fecha.'
                : 'Este servicio no tiene agenda detallada. Contacta con el negocio para confirmar disponibilidad.',
        ]);
    }

    private function buscarSlotsParaRecursos(Collection $recursos, Carbon $fecha, int $diaSemana, int $duracion, SearchAvailabilityInput $dto, bool $checkExternal, ?Servicio $servicio = null): array
    {
        $slots = [];
        $matcher = app(ServiceSlotMatcher::class);

        foreach ($recursos as $recurso) {
            $disponibilidades = Disponibilidad::query()
                ->where('recurso_id', $recurso->id)
                ->where('dia_semana', $diaSemana)
                ->activos()
                ->orderBy('hora_inicio')
                ->get();

            if ($disponibilidades->isEmpty()) {
                continue;
            }

            if ($this->recursoEstaBloqueadoDiaCompleto($recurso->id, $dto->negocio_id, $dto->fecha, $diaSemana)) {
                continue;
            }

            // Filter disponibilidades by service semantic compatibility
            if ($servicio !== null) {
                $disponibilidades = $disponibilidades->filter(
                    fn (Disponibilidad $d) => $matcher->disponibilidadEsCompatible($servicio, $d)
                );

                if ($disponibilidades->isEmpty()) {
                    continue;
                }
            }

            foreach ($disponibilidades as $disp) {
                $buffer = $disp->buffer_minutos ?? 0;
                $slotsDisp = $this->generarSlots($fecha, $disp, $duracion, $buffer);

                foreach ($slotsDisp as $slot) {
                    if ($this->slotOcupado($recurso->id, $dto->negocio_id, $slot['inicio'], $slot['fin'], $checkExternal)) {
                        continue;
                    }

                    if ($this->slotBloqueadoParcial($recurso->id, $dto->negocio_id, $dto->fecha, $diaSemana, $slot['hora_inicio'], $slot['hora_fin'])) {
                        continue;
                    }

                    $slots[] = [
                        'fecha' => $dto->fecha,
                        'hora_inicio' => $slot['hora_inicio'],
                        'hora_fin' => $slot['hora_fin'],
                        'inicio_datetime' => $slot['inicio']->toDateTimeString(),
                        'fin_datetime' => $slot['fin']->toDateTimeString(),
                        'recurso_id' => $recurso->id,
                        'recurso_nombre' => $recurso->nombre,
                        'nombre_turno' => $disp->nombre_turno,
                        'capacidad' => $recurso->capacidad,
                        'es_combinacion' => false,
                        'recursos' => [['id' => $recurso->id, 'nombre' => $recurso->nombre, 'capacidad' => $recurso->capacidad]],
                        'numero_recursos' => 1,
                        'capacidad_total' => $recurso->capacidad,
                    ];
                }
            }
        }

        return $slots;
    }

    private function buscarSlotsParaCombinacion(array $combinacion, Carbon $fecha, int $diaSemana, int $duracion, SearchAvailabilityInput $dto, bool $checkExternal, ?Servicio $servicio = null): array
    {
        /** @var Collection $recursos */
        $recursos = $combinacion['recursos'];
        $slotsComunes = null;
        $matcher = app(ServiceSlotMatcher::class);

        foreach ($recursos as $recurso) {
            $disponibilidades = Disponibilidad::query()
                ->where('recurso_id', $recurso->id)
                ->where('dia_semana', $diaSemana)
                ->activos()
                ->orderBy('hora_inicio')
                ->get();

            if ($disponibilidades->isEmpty()) {
                return [];
            }

            if ($this->recursoEstaBloqueadoDiaCompleto($recurso->id, $dto->negocio_id, $dto->fecha, $diaSemana)) {
                return [];
            }

            if ($servicio !== null) {
                $disponibilidades = $disponibilidades->filter(
                    fn (Disponibilidad $d) => $matcher->disponibilidadEsCompatible($servicio, $d)
                );

                if ($disponibilidades->isEmpty()) {
                    return [];
                }
            }

            $slotsRecurso = [];
            foreach ($disponibilidades as $disp) {
                $buffer = $disp->buffer_minutos ?? 0;
                foreach ($this->generarSlots($fecha, $disp, $duracion, $buffer) as $slot) {
                    if ($this->slotOcupado($recurso->id, $dto->negocio_id, $slot['inicio'], $slot['fin'], $checkExternal)) {
                        continue;
                    }
                    if ($this->slotBloqueadoParcial($recurso->id, $dto->negocio_id, $dto->fecha, $diaSemana, $slot['hora_inicio'], $slot['hora_fin'])) {
                        continue;
                    }
                    $slotsRecurso[$slot['hora_inicio'].'-'.$slot['hora_fin']] = $slot;
                }
            }

            $slotsComunes = $slotsComunes === null
                ? $slotsRecurso
                : array_intersect_key($slotsComunes, $slotsRecurso);

            if (empty($slotsComunes)) {
                return [];
            }
        }

        $recursosData = $recursos->map(fn (Recurso $r) => [
            'id' => $r->id,
            'nombre' => $r->nombre,
            'capacidad' => $r->capacidad,
        ])->all();

        $nombreCombinado = $recursos->map(fn ($r) => $r->nombre)->join(' + ');
        $primerTurnoQuery = Disponibilidad::query()
            ->where('recurso_id', $recursos->first()->id)
            ->where('dia_semana', $diaSemana)
            ->activos()
            ->get();

        $primerTurno = $servicio !== null
            ? $primerTurnoQuery->first(fn (Disponibilidad $d) => $matcher->disponibilidadEsCompatible($servicio, $d))
            : $primerTurnoQuery->first();

        $result = [];
        foreach ($slotsComunes ?? [] as $slot) {
            $result[] = [
                'fecha' => $dto->fecha,
                'hora_inicio' => $slot['hora_inicio'],
                'hora_fin' => $slot['hora_fin'],
                'inicio_datetime' => $slot['inicio']->toDateTimeString(),
                'fin_datetime' => $slot['fin']->toDateTimeString(),
                'recurso_id' => $recursos->first()->id,
                'recurso_nombre' => $nombreCombinado,
                'nombre_turno' => $primerTurno?->nombre_turno,
                'capacidad' => $combinacion['capacidad_total'],
                'es_combinacion' => true,
                'recursos' => $recursosData,
                'numero_recursos' => $combinacion['numero_recursos'],
                'capacidad_total' => $combinacion['capacidad_total'],
            ];
        }

        return $result;
    }

    private function generarSlots(Carbon $fecha, Disponibilidad $disp, int $duracion, int $buffer): array
    {
        $slots = [];
        $inicioDisp = Carbon::parse($fecha->toDateString().' '.substr((string) $disp->hora_inicio, 0, 5).':00');
        $finDisp = Carbon::parse($fecha->toDateString().' '.substr((string) $disp->hora_fin, 0, 5).':00');
        $cursor = $inicioDisp->copy();

        while (true) {
            $finSlot = $cursor->copy()->addMinutes($duracion);
            if ($finSlot->greaterThan($finDisp)) {
                break;
            }
            $slots[] = [
                'hora_inicio' => $cursor->format('H:i'),
                'hora_fin' => $finSlot->format('H:i'),
                'inicio' => $cursor->copy(),
                'fin' => $finSlot->copy(),
            ];
            $cursor->addMinutes($duracion + $buffer);
        }

        return $slots;
    }

    private function slotOcupado(int $recursoId, int $negocioId, Carbon $inicio, Carbon $fin, bool $checkExternal): bool
    {
        $reservaOcupada = Reserva::query()
            ->where('recurso_id', $recursoId)
            ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
            ->where('inicio_datetime', '<', $fin)
            ->where('fin_datetime', '>', $inicio)
            ->exists();

        if ($reservaOcupada) {
            return true;
        }

        if (! $checkExternal) {
            return false;
        }

        return OcupacionExterna::query()
            ->where('negocio_id', $negocioId)
            ->where(function ($q) use ($recursoId) {
                $q->where('recurso_id', $recursoId)
                    ->orWhereNull('recurso_id');
            })
            ->where('inicio_datetime', '<', $fin)
            ->where('fin_datetime', '>', $inicio)
            ->exists();
    }

    private function recursoEstaBloqueadoDiaCompleto(int $recursoId, int $negocioId, string $fecha, int $diaSemana): bool
    {
        return Bloqueo::query()
            ->where('activo', true)
            ->where(function ($q) use ($recursoId, $negocioId) {
                $q->where('recurso_id', $recursoId)
                    ->orWhere(function ($inner) use ($negocioId) {
                        $inner->where('negocio_id', $negocioId)->whereNull('recurso_id');
                    });
            })
            ->whereNull('hora_inicio')
            ->whereNull('hora_fin')
            ->where(function ($q) use ($fecha, $diaSemana) {
                $q->where('fecha', $fecha)
                    ->orWhere(function ($inner) use ($fecha) {
                        $inner->whereNotNull('fecha_inicio')
                            ->where('fecha_inicio', '<=', $fecha)
                            ->where('fecha_fin', '>=', $fecha);
                    })
                    ->orWhere(function ($inner) use ($diaSemana) {
                        $inner->where('es_recurrente', true)
                            ->where('dia_semana', $diaSemana);
                    });
            })
            ->exists();
    }

    private function slotBloqueadoParcial(int $recursoId, int $negocioId, string $fecha, int $diaSemana, string $horaInicio, string $horaFin): bool
    {
        return Bloqueo::query()
            ->where('activo', true)
            ->where(function ($q) use ($recursoId, $negocioId) {
                $q->where('recurso_id', $recursoId)
                    ->orWhere(function ($inner) use ($negocioId) {
                        $inner->where('negocio_id', $negocioId)->whereNull('recurso_id');
                    });
            })
            ->whereNotNull('hora_inicio')
            ->whereNotNull('hora_fin')
            ->where(function ($q) use ($fecha, $diaSemana) {
                $q->where('fecha', $fecha)
                    ->orWhere(function ($inner) use ($fecha) {
                        $inner->whereNotNull('fecha_inicio')
                            ->where('fecha_inicio', '<=', $fecha)
                            ->where('fecha_fin', '>=', $fecha);
                    })
                    ->orWhere(function ($inner) use ($diaSemana) {
                        $inner->where('es_recurrente', true)
                            ->where('dia_semana', $diaSemana);
                    });
            })
            ->where('hora_inicio', '<', $horaFin.':00')
            ->where('hora_fin', '>', $horaInicio.':00')
            ->exists();
    }

    private function estadosCancelados(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = \App\Models\EstadoReserva::query()
                ->whereIn('nombre', ['Cancelada', 'No presentada'])
                ->pluck('id')
                ->all();
        }

        return $ids;
    }
}
