<?php

namespace App\Tools\Reservations;

use App\Models\Bloqueo;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Sesion;
use App\Services\Integrations\GoogleCalendarAvailabilityService;
use App\Services\Reservations\DynamicExperienceAvailabilityService;
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

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario quiere reservar o saber si hay hueco real para un servicio en una fecha concreta.',
            'Cuando ya tienes identificado el servicio y la fecha, y opcionalmente el número de personas.',
            'Si el usuario habla de comer, cenar o brunch, primero interpreta esa intención como servicio si el catálogo del negocio lo permite.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para crear la reserva: solo busca disponibilidad.',
            'No la uses para responder políticas de cancelación, ubicación o precio si no se está consultando disponibilidad.',
            'No uses los nombres internos de mesas o recursos como respuesta final por defecto si el negocio no quiere exponer inventario interno.',
        ];
    }

    public function argumentGuidance(): array
    {
        return [
            'negocio_id' => 'Siempre debe corresponder al negocio actual de la conversación.',
            'servicio_id' => 'Debe ser un servicio real del negocio que encaje con la intención del usuario.',
            'fecha' => 'Usa una fecha absoluta YYYY-MM-DD ya resuelta desde expresiones como mañana o pasado mañana.',
            'numero_personas' => 'Si el usuario ya lo indicó, inclúyelo. Es clave para filtrar capacidad cuando aplica.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Si el resultado devuelve una sola opción realmente útil, propónla directamente.',
            'Si hay varias opciones equivalentes para el cliente, resume por horas o descriptores públicos en vez de listar mesas técnicas.',
            'Si el usuario pidió solo comida o cena y no una hora exacta, puedes proponer una hora real devuelta por la herramienta en vez de pedirla de nuevo por inercia.',
            'Si el negocio funciona con una ventana flexible de recogida o atención, presenta la franja como rango y permite que el cliente elija una hora concreta dentro de esa ventana.',
            'No digas que la reserva está hecha: esta herramienta solo comprueba disponibilidad.',
        ];
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

        $dynamicAvailability = app(DynamicExperienceAvailabilityService::class);
        if ($dynamicAvailability->supports($servicio)) {
            return $this->respuestaExperienciaDinamica($dto, $negocio, $servicio, $dynamicAvailability);
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
        $googleBusyRanges = collect();

        if ($nivel >= BusinessComplexityResolver::LEVEL_ADVANCED) {
            $googleBusyRanges = app(GoogleCalendarAvailabilityService::class)
                ->busyRangesForBusiness(
                    $negocio,
                    $fechaCarbon->copy()->startOfDay(),
                    $fechaCarbon->copy()->endOfDay()
                );
        }

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
            $recursosIndividuales, $fechaCarbon, $diaSemana, $duracion, $dto, $checkExternal, $googleBusyRanges, $servicio
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
                    $combinacion, $fechaCarbon, $diaSemana, $duracion, $dto, $checkExternal, $googleBusyRanges, $servicio
                ));
            }
        }

        // STEP 3: Session-based slots (group experiences like wine tastings)
        $sessionSlots = $this->buscarSlotsDeSesiones($dto, $servicio, $fechaCarbon, $googleBusyRanges);
        $slots = array_merge($slots, $sessionSlots);

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

    public function resultExplanation(array $input, \App\Tools\ToolResult $result): array
    {
        $serviceName = data_get($result->data, 'servicio_nombre');
        $date = data_get($result->data, 'fecha');
        $totalSlots = (int) data_get($result->data, 'total_slots', 0);
        $mode = data_get($result->data, 'availability_mode', 'simple');

        $nextStepHint = match (true) {
            $mode === 'simple' => 'La herramienta no ha podido devolver agenda detallada. Explícalo con naturalidad y guía al usuario sin fingir precisión.',
            $totalSlots === 0 => 'No hay huecos disponibles con el criterio consultado. Sé claro y ofrece alternativas cercanas si las hay o si el negocio lo permite.',
            $totalSlots === 1 => 'Hay una única opción clara. Propónla directamente en vez de recitar una lista.',
            default => 'Si varias opciones comparten la misma franja o son equivalentes para el cliente, agrúpalas y evita repetir recursos internos.',
        };

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Comprueba huecos reales disponibles, no crea reservas.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $serviceName !== null && $date !== null
                ? "Ya tienes el resultado de disponibilidad de {$serviceName} para {$date}."
                : 'Ya tienes un resultado real de disponibilidad para seguir la conversación.',
            'next_step_hint' => $nextStepHint,
            'presentation_hint' => $totalSlots > 0
                ? 'Resume horarios o sesiones en lenguaje de cliente. Si varias opciones solo cambian por la sala o recurso interno, no las presentes como si fueran alternativas distintas.'
                : 'Comunica la falta de disponibilidad sin sonar técnico y ofrece margen de maniobra si el negocio lo permite.',
            'public_summary' => match (true) {
                $mode === 'simple' => 'La disponibilidad requiere seguimiento humano porque no hay agenda operativa detallada.',
                $totalSlots === 0 => 'No se han encontrado huecos disponibles con el criterio consultado.',
                $totalSlots === 1 => 'Se ha encontrado una opción de disponibilidad clara.',
                default => "Se han encontrado {$totalSlots} huecos disponibles antes de agruparlos para el cliente.",
            },
        ];
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

    private function respuestaExperienciaDinamica(
        SearchAvailabilityInput $dto,
        Negocio $negocio,
        Servicio $servicio,
        DynamicExperienceAvailabilityService $dynamicAvailability
    ): ToolResult {
        $slots = $dynamicAvailability->slotsForDate($negocio, $servicio, $dto->fecha, $dto->numero_personas);
        $summary = $dynamicAvailability->serviceSummaryForDate($negocio, $servicio, $dto->fecha, $dto->numero_personas);

        return ToolResult::ok([
            'negocio_id' => $dto->negocio_id,
            'servicio_id' => $servicio->id,
            'servicio_nombre' => $servicio->nombre,
            'fecha' => $dto->fecha,
            'duracion_minutos' => $servicio->duracion_minutos,
            'numero_personas' => $dto->numero_personas,
            'total_slots' => $summary['total_slots'],
            'slots' => $slots,
            'availability_mode' => 'experience_schedule',
            'complexity_level' => BusinessComplexityResolver::LEVEL_SCHEDULED,
            'has_precise_slots' => true,
            'occupancy_percent' => $summary['occupancy_percent'],
            'available_slots' => $summary['available_slots'],
            'capacity_total' => $summary['capacity_total'],
            'seats_reserved_total' => $summary['seats_reserved_total'],
            'seats_available_total' => $summary['seats_available_total'],
            'schedule' => [
                'is_dynamic_experience' => true,
                'capacity' => $servicio->aforo,
                'start_time' => $servicio->horaInicioCorta(),
                'end_time' => $servicio->horaFinCorta(),
            ],
        ]);
    }

    private function buscarSlotsParaRecursos(
        Collection $recursos,
        Carbon $fecha,
        int $diaSemana,
        int $duracion,
        SearchAvailabilityInput $dto,
        bool $checkExternal,
        Collection $googleBusyRanges,
        ?Servicio $servicio = null
    ): array
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
                    if ($this->slotOcupado($recurso->id, $dto->negocio_id, $slot['inicio'], $slot['fin'], $checkExternal, $googleBusyRanges)) {
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
                        'slot_key' => $this->slotKey($dto->fecha, $slot['hora_inicio'], $slot['hora_fin'], [$recurso->id]),
                        'booking_time_mode' => $servicio?->precio_por_unidad_tiempo ? 'flexible_start_within_window' : 'fixed_slot',
                        'accepts_start_time_within_slot' => (bool) ($servicio?->precio_por_unidad_tiempo),
                        'recurso_id' => $recurso->id,
                        'recurso_ids' => [$recurso->id],
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

    private function buscarSlotsParaCombinacion(
        array $combinacion,
        Carbon $fecha,
        int $diaSemana,
        int $duracion,
        SearchAvailabilityInput $dto,
        bool $checkExternal,
        Collection $googleBusyRanges,
        ?Servicio $servicio = null
    ): array
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
                    if ($this->slotOcupado($recurso->id, $dto->negocio_id, $slot['inicio'], $slot['fin'], $checkExternal, $googleBusyRanges)) {
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
        $resourceIds = $recursos->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        foreach ($slotsComunes ?? [] as $slot) {
            $result[] = [
                'fecha' => $dto->fecha,
                'hora_inicio' => $slot['hora_inicio'],
                'hora_fin' => $slot['hora_fin'],
                'inicio_datetime' => $slot['inicio']->toDateTimeString(),
                'fin_datetime' => $slot['fin']->toDateTimeString(),
                'slot_key' => $this->slotKey($dto->fecha, $slot['hora_inicio'], $slot['hora_fin'], $resourceIds),
                'booking_time_mode' => $servicio?->precio_por_unidad_tiempo ? 'flexible_start_within_window' : 'fixed_slot',
                'accepts_start_time_within_slot' => (bool) ($servicio?->precio_por_unidad_tiempo),
                'recurso_id' => $recursos->first()->id,
                'recurso_ids' => $resourceIds,
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

    private function slotKey(string $fecha, string $horaInicio, string $horaFin, array $resourceIds): string
    {
        $resourceIds = array_values(array_map('intval', $resourceIds));
        sort($resourceIds);

        return sha1($fecha.'|'.$horaInicio.'|'.$horaFin.'|'.implode(',', $resourceIds));
    }

    private function slotOcupado(
        int $recursoId,
        int $negocioId,
        Carbon $inicio,
        Carbon $fin,
        bool $checkExternal,
        Collection $googleBusyRanges
    ): bool
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

        if ($checkExternal) {
            $externalOccupancyExists = OcupacionExterna::query()
                ->where('negocio_id', $negocioId)
                ->where(function ($q) use ($recursoId) {
                    $q->where('recurso_id', $recursoId)
                        ->orWhereNull('recurso_id');
                })
                ->where('inicio_datetime', '<', $fin)
                ->where('fin_datetime', '>', $inicio)
                ->exists();

            if ($externalOccupancyExists) {
                return true;
            }
        }

        if ($googleBusyRanges->isEmpty()) {
            return false;
        }

        return app(GoogleCalendarAvailabilityService::class)
            ->slotOverlapsBusy($googleBusyRanges, $recursoId, $inicio, $fin);
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

    private function buscarSlotsDeSesiones(
        SearchAvailabilityInput $dto,
        Servicio $servicio,
        Carbon $fecha,
        Collection $googleBusyRanges
    ): array {
        $sesiones = Sesion::query()
            ->where('negocio_id', $dto->negocio_id)
            ->where('servicio_id', $servicio->id)
            ->where('fecha', $dto->fecha)
            ->where('activo', true)
            ->get();

        if ($sesiones->isEmpty()) {
            return [];
        }

        $slots = [];

        foreach ($sesiones as $sesion) {
            $reservados = Reserva::query()
                ->where('sesion_id', $sesion->id)
                ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
                ->sum('numero_personas');

            $aforoRestante = max(0, ($sesion->aforo_total ?? 0) - (int) $reservados);

            if ($aforoRestante <= 0) {
                continue;
            }

            if ($dto->numero_personas !== null && $dto->numero_personas > $aforoRestante) {
                continue;
            }

            $horaInicio = substr((string) $sesion->hora_inicio, 0, 5);
            $horaFin = substr((string) $sesion->hora_fin, 0, 5);
            $recursoId = $sesion->recurso_id;
            $recursoIds = $recursoId ? [$recursoId] : [];
            $inicioSesion = $sesion->inicio_datetime?->copy() ?? Carbon::parse($fecha->toDateString().' '.$horaInicio.':00');
            $finSesion = $sesion->fin_datetime?->copy() ?? Carbon::parse($fecha->toDateString().' '.$horaFin.':00');

            if (! $googleBusyRanges->isEmpty()
                && app(GoogleCalendarAvailabilityService::class)->slotOverlapsBusy($googleBusyRanges, $recursoId, $inicioSesion, $finSesion)
            ) {
                continue;
            }

            $slots[] = [
                'fecha' => $dto->fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'inicio_datetime' => $inicioSesion->toDateTimeString(),
                'fin_datetime' => $finSesion->toDateTimeString(),
                'slot_key' => sha1('sesion|'.$sesion->id.'|'.$dto->fecha),
                'booking_time_mode' => 'session',
                'accepts_start_time_within_slot' => false,
                'recurso_id' => $recursoId,
                'recurso_ids' => $recursoIds,
                'recurso_nombre' => $sesion->recurso?->nombre,
                'nombre_turno' => null,
                'capacidad' => $sesion->aforo_total,
                'es_combinacion' => false,
                'es_sesion' => true,
                'sesion_id' => $sesion->id,
                'aforo_total' => $sesion->aforo_total,
                'aforo_restante' => $aforoRestante,
                'notas_publicas' => $sesion->notas_publicas,
                'recursos' => $recursoId ? [['id' => $recursoId, 'nombre' => $sesion->recurso?->nombre, 'capacidad' => $sesion->aforo_total]] : [],
                'numero_recursos' => $recursoId ? 1 : 0,
                'capacidad_total' => $sesion->aforo_total,
            ];
        }

        return $slots;
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
