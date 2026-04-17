<?php

namespace App\Tools\Reservations;

use App\Models\Bloqueo;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\Reservations\DynamicExperienceAvailabilityService;
use App\Services\Tools\BusinessComplexityResolver;
use App\Tools\Data\CheckBusinessHoursInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CheckBusinessHoursTool extends ToolDefinition
{
    private const DAY_NAMES = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];

    public function name(): string
    {
        return 'check_business_hours';
    }

    public function description(): string
    {
        return 'Devuelve los horarios de un negocio y/o servicio, adaptándose al nivel de detalle disponible.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario pregunta por horarios, apertura o disponibilidad general de un día sin buscar todavía un hueco exacto.',
            'Cuando necesitas aclarar si un negocio o servicio opera en una fecha concreta.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para sustituir la búsqueda real de disponibilidad si el usuario ya está intentando reservar.',
            'No la uses para crear o confirmar reservas.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Presenta los horarios de forma clara y breve.',
            'Si la herramienta solo ofrece nivel simple, dilo con naturalidad y no aparentes agenda detallada.',
        ];
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'servicio_id' => ['type' => 'integer', 'nullable' => true],
                'fecha' => ['type' => 'string', 'format' => 'date', 'nullable' => true],
            ],
            'required' => ['negocio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = CheckBusinessHoursInput::fromArray($input);

        $negocio = Negocio::find($dto->negocio_id);
        if (! $negocio) {
            throw new EntityNotFoundException('Negocio', $dto->negocio_id);
        }

        $servicio = null;
        if ($dto->servicio_id !== null) {
            $servicio = Servicio::query()
                ->where('negocio_id', $dto->negocio_id)
                ->where('id', $dto->servicio_id)
                ->first();
        }

        $dynamicAvailability = app(DynamicExperienceAvailabilityService::class);
        if ($servicio !== null && $dynamicAvailability->supports($servicio)) {
            return $this->respuestaHorariosDinamicos(
                $dto,
                $negocio,
                collect([$servicio]),
                $dynamicAvailability,
                $servicio
            );
        }

        if ($servicio === null) {
            $dynamicServices = $negocio->servicios()
                ->activos()
                ->get()
                ->filter(fn (Servicio $item) => $dynamicAvailability->supports($item))
                ->values();

            if ($dynamicServices->isNotEmpty()) {
                return $this->respuestaHorariosDinamicos(
                    $dto,
                    $negocio,
                    $dynamicServices,
                    $dynamicAvailability
                );
            }
        }

        $complexity = app(BusinessComplexityResolver::class);
        $tieneDisponibilidades = $complexity->negocioTieneDisponibilidadesOperativas($negocio, $servicio);

        // Simple business — no operational schedules
        if (! $tieneDisponibilidades) {
            return $this->respuestaSinHorarios($dto, $negocio, $servicio);
        }

        // Has schedules — return detailed hours
        $recursoIds = $this->resolveRecursoIds($dto);

        $disponibilidades = Disponibilidad::query()
            ->whereIn('recurso_id', $recursoIds)
            ->activos()
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        $horariosPorDia = [];
        foreach ($disponibilidades as $d) {
            $horariosPorDia[$d->dia_semana][] = [
                'hora_inicio' => substr((string) $d->hora_inicio, 0, 5),
                'hora_fin' => substr((string) $d->hora_fin, 0, 5),
                'nombre_turno' => $d->nombre_turno,
                'buffer_minutos' => $d->buffer_minutos,
            ];
        }

        $resumen = [];
        for ($dia = 0; $dia <= 6; $dia++) {
            $turnos = $horariosPorDia[$dia] ?? [];
            $resumen[] = [
                'dia_semana' => $dia,
                'dia_nombre' => self::DAY_NAMES[$dia],
                'abierto' => count($turnos) > 0,
                'turnos' => $turnos,
            ];
        }

        $result = [
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'zona_horaria' => $negocio->zona_horaria,
            'horarios' => $resumen,
            'availability_mode' => 'scheduled',
            'has_precise_slots' => true,
        ];

        if ($dto->fecha !== null) {
            $result['fecha_consultada'] = $this->consultarFecha($dto, $negocio, $horariosPorDia, $recursoIds);
        }

        return ToolResult::ok($result);
    }

    /**
     * @param  Collection<int, Servicio>  $servicios
     */
    private function respuestaHorariosDinamicos(
        CheckBusinessHoursInput $dto,
        Negocio $negocio,
        Collection $servicios,
        DynamicExperienceAvailabilityService $dynamicAvailability,
        ?Servicio $servicio = null
    ): ToolResult {
        $schedule = $dynamicAvailability->weeklySchedule($negocio, $servicio);
        $horarios = collect($schedule)->map(function (array $day) {
            return [
                'dia_semana' => $day['dia_semana'],
                'dia_nombre' => self::DAY_NAMES[$day['dia_semana']],
                'abierto' => $day['abierto'],
                'turnos' => collect($day['turnos'])->map(fn (array $turno) => [
                    'hora_inicio' => $turno['hora_inicio'],
                    'hora_fin' => $turno['hora_fin'],
                    'servicio_id' => $turno['service_id'],
                    'servicio_nombre' => $turno['service_name'],
                    'duracion_minutos' => $turno['duracion_minutos'],
                    'aforo' => $turno['aforo'],
                ])->values()->all(),
            ];
        })->all();

        $result = [
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'zona_horaria' => $negocio->zona_horaria,
            'horarios' => $horarios,
            'availability_mode' => 'experience_schedule',
            'has_precise_slots' => true,
            'dias_apertura' => $negocio->diasAperturaEfectivos(),
        ];

        if ($servicio !== null) {
            $result['servicio'] = [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'duracion_minutos' => $servicio->duracion_minutos,
                'aforo' => $servicio->aforo,
                'hora_inicio' => $servicio->horaInicioCorta(),
                'hora_fin' => $servicio->horaFinCorta(),
            ];
        }

        if ($dto->fecha !== null) {
            $result['fecha_consultada'] = $this->consultarFechaDinamica(
                $dto->fecha,
                $negocio,
                $servicios,
                $dynamicAvailability
            );
        }

        return ToolResult::ok($result);
    }

    private function respuestaSinHorarios(CheckBusinessHoursInput $dto, Negocio $negocio, ?Servicio $servicio): ToolResult
    {
        $result = [
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'zona_horaria' => $negocio->zona_horaria,
            'horarios' => [],
            'availability_mode' => 'simple',
            'has_precise_slots' => false,
            'mensaje' => 'Este negocio no tiene horarios operativos detallados. Contacta directamente para consultar disponibilidad.',
        ];

        if ($servicio !== null && $servicio->duracion_minutos) {
            $result['servicio_duracion_minutos'] = $servicio->duracion_minutos;
        }

        if ($dto->fecha !== null) {
            $bloqueadoNegocio = $this->negocioBloqueadoEnFecha($negocio, $dto->fecha);
            $result['fecha_consultada'] = [
                'fecha' => $dto->fecha,
                'dia_semana' => (int) Carbon::parse($dto->fecha)->dayOfWeek,
                'dia_nombre' => self::DAY_NAMES[(int) Carbon::parse($dto->fecha)->dayOfWeek],
                'abierto' => ! $bloqueadoNegocio,
                'turnos' => [],
                'bloqueos' => $bloqueadoNegocio ? [['motivo' => 'Bloqueo de negocio', 'dia_completo' => true, 'tipo' => 'negocio']] : [],
            ];
        }

        return ToolResult::ok($result);
    }

    private function consultarFecha(CheckBusinessHoursInput $dto, Negocio $negocio, array $horariosPorDia, array $recursoIds): array
    {
        $fechaCarbon = Carbon::parse($dto->fecha);
        $diaSemana = (int) $fechaCarbon->dayOfWeek;
        $turnosDia = $horariosPorDia[$diaSemana] ?? [];

        $bloqueosActivos = Bloqueo::query()
            ->where('activo', true)
            ->where(function ($q) use ($negocio, $recursoIds) {
                $q->where('negocio_id', $negocio->id)
                    ->orWhereIn('recurso_id', $recursoIds);
            })
            ->where(function ($q) use ($dto, $diaSemana) {
                $q->where('fecha', $dto->fecha)
                    ->orWhere(function ($inner) use ($dto) {
                        $inner->whereNotNull('fecha_inicio')
                            ->whereNotNull('fecha_fin')
                            ->where('fecha_inicio', '<=', $dto->fecha)
                            ->where('fecha_fin', '>=', $dto->fecha);
                    })
                    ->orWhere(function ($inner) use ($diaSemana) {
                        $inner->where('es_recurrente', true)
                            ->where('dia_semana', $diaSemana);
                    });
            })
            ->get();

        return [
            'fecha' => $dto->fecha,
            'dia_semana' => $diaSemana,
            'dia_nombre' => self::DAY_NAMES[$diaSemana],
            'abierto' => count($turnosDia) > 0 && $bloqueosActivos->where('hora_inicio', null)->where('hora_fin', null)->isEmpty(),
            'turnos' => $turnosDia,
            'bloqueos' => $bloqueosActivos->map(fn (Bloqueo $b) => [
                'motivo' => $b->motivo,
                'hora_inicio' => $b->hora_inicio ? substr((string) $b->hora_inicio, 0, 5) : null,
                'hora_fin' => $b->hora_fin ? substr((string) $b->hora_fin, 0, 5) : null,
                'dia_completo' => $b->esDiaCompleto(),
                'tipo' => $b->esNegocioCompleto() ? 'negocio' : 'recurso',
            ])->values()->all(),
        ];
    }

    private function negocioBloqueadoEnFecha(Negocio $negocio, string $fecha): bool
    {
        $diaSemana = (int) Carbon::parse($fecha)->dayOfWeek;

        return Bloqueo::query()
            ->where('activo', true)
            ->where('negocio_id', $negocio->id)
            ->whereNull('recurso_id')
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

    /**
     * @param  Collection<int, Servicio>  $servicios
     */
    private function consultarFechaDinamica(
        string $fecha,
        Negocio $negocio,
        Collection $servicios,
        DynamicExperienceAvailabilityService $dynamicAvailability
    ): array {
        $fechaCarbon = Carbon::parse($fecha);
        $summary = $dynamicAvailability->daySummaryForServices($negocio, $servicios, $fechaCarbon);

        return [
            'fecha' => $fecha,
            'dia_semana' => (int) $fechaCarbon->dayOfWeek,
            'dia_nombre' => self::DAY_NAMES[(int) $fechaCarbon->dayOfWeek],
            'abierto' => collect($summary['service_occupancy'] ?? [])
                ->contains(fn (array $item) => (int) ($item['total_slots'] ?? 0) > 0),
            'turnos' => collect($summary['service_occupancy'] ?? [])
                ->map(fn (array $item) => [
                    'servicio_id' => $item['service_id'],
                    'servicio_nombre' => $item['service_name'],
                    'hora_inicio' => $item['start_time'],
                    'hora_fin' => $item['end_time'],
                    'duracion_minutos' => $item['duration_minutes'],
                    'aforo' => $item['capacity'],
                    'total_slots' => $item['total_slots'],
                    'available_slots' => $item['available_slots'],
                    'occupancy_percent' => $item['occupancy_percent'],
                ])
                ->values()
                ->all(),
            'occupancy_percent' => $summary['occupancy_percent'] ?? null,
            'available_slots' => $summary['available_slots'] ?? 0,
            'total_slots' => $summary['total_slots'] ?? 0,
            'bloqueos' => [],
        ];
    }

    public function resultExplanation(array $input, \App\Tools\ToolResult $result): array
    {
        $mode = data_get($result->data, 'availability_mode', 'simple');
        $date = data_get($result->data, 'fecha_consultada.fecha');

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Aporta el contexto horario operativo del negocio o servicio consultado.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $date !== null
                ? "Ya tienes contexto horario para {$date}."
                : 'Ya tienes el contexto general de horarios del negocio.',
            'next_step_hint' => in_array($mode, ['scheduled', 'experience_schedule'], true)
                ? 'Si el usuario quiere reservar, usa este resultado como contexto pero sigue necesitando la tool de disponibilidad para huecos reales.'
                : 'Aclara que hay información general, no una agenda operativa cerrada.',
            'public_summary' => $date !== null
                ? "Se ha consultado el horario operativo para {$date}."
                : 'Se han consultado los horarios generales del negocio.',
        ];
    }

    private function resolveRecursoIds(CheckBusinessHoursInput $dto): array
    {
        if ($dto->servicio_id !== null) {
            $servicio = Servicio::query()
                ->where('negocio_id', $dto->negocio_id)
                ->where('id', $dto->servicio_id)
                ->first();

            if ($servicio) {
                return $servicio->recursos()->activos()->pluck('recursos.id')->all();
            }
        }

        return \App\Models\Recurso::query()
            ->where('negocio_id', $dto->negocio_id)
            ->activos()
            ->pluck('id')
            ->all();
    }
}
