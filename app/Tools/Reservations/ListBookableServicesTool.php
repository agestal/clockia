<?php

namespace App\Tools\Reservations;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Tools\Data\ListBookableServicesInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class ListBookableServicesTool extends ToolDefinition
{
    public function name(): string
    {
        return 'list_bookable_services';
    }

    public function description(): string
    {
        return 'Lista los servicios activos y reservables de un negocio.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario pregunta qué ofrece el negocio o qué opciones reservables existen.',
            'Cuando necesitas ayudarle a elegir entre varios servicios antes de hablar de fechas o disponibilidad.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para comprobar huecos disponibles en una fecha concreta.',
            'No la uses para crear ni confirmar una reserva.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Resume la oferta en lenguaje de cliente y evita volcar el catálogo completo si no hace falta.',
            'Si un servicio encaja claramente con la petición del usuario, puedes destacarlo en vez de enumerarlo todo.',
        ];
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer', 'description' => 'ID del negocio'],
            ],
            'required' => ['negocio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = ListBookableServicesInput::fromArray($input);

        $negocio = Negocio::find($dto->negocio_id);

        if (! $negocio) {
            throw new EntityNotFoundException('Negocio', $dto->negocio_id);
        }

        $servicios = Servicio::query()
            ->where('negocio_id', $dto->negocio_id)
            ->activos()
            ->with('tipoPrecio:id,nombre')
            ->orderBy('nombre')
            ->get();

        $items = $servicios->map(fn (Servicio $s) => [
            'id' => $s->id,
            'nombre' => $s->nombre,
            'descripcion' => $s->descripcion,
            'duracion_minutos' => $s->duracion_minutos,
            'precio_base' => (string) $s->precio_base,
            'tipo_precio' => $s->tipoPrecio?->nombre,
            'requiere_pago' => $s->requiere_pago,
            'activo' => $s->activo,
            'notas_publicas' => $s->notas_publicas,
            'instrucciones_previas' => $s->instrucciones_previas,
            'documentacion_requerida' => $s->documentacion_requerida,
            'es_reembolsable' => $s->es_reembolsable,
            'porcentaje_senal' => $s->porcentaje_senal !== null ? (string) $s->porcentaje_senal : null,
            'precio_por_unidad_tiempo' => $s->precio_por_unidad_tiempo,
            'numero_personas_minimo' => $s->numero_personas_minimo,
            'numero_personas_maximo' => $s->numero_personas_maximo,
            'permite_menores' => $s->permite_menores,
            'edad_minima' => $s->edad_minima,
            'precio_menor' => $s->precio_menor !== null ? (string) $s->precio_menor : null,
            'idiomas' => $s->idiomas,
            'punto_encuentro' => $s->punto_encuentro,
            'incluye' => $s->incluye,
            'no_incluye' => $s->no_incluye,
            'accesibilidad_notas' => $s->accesibilidad_notas,
            'requiere_aprobacion_manual' => $s->requiere_aprobacion_manual,
        ])->all();

        return ToolResult::ok([
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'total_servicios' => count($items),
            'servicios' => $items,
        ]);
    }

    public function resultExplanation(array $input, \App\Tools\ToolResult $result): array
    {
        $services = $result->data['servicios'] ?? [];
        $total = is_array($services) ? count($services) : 0;

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Devuelve la oferta reservable real del negocio.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $total > 0
                ? "Ya conoces {$total} opciones reservables del negocio para seguir la conversación sin pedir al usuario que repita qué quiere."
                : 'No se encontraron servicios activos en este momento.',
            'next_step_hint' => 'Traduce el catálogo a lenguaje de cliente. Si el usuario ya insinuó una intención concreta, úsala para recomendar en vez de recitar.',
            'public_summary' => $total > 0
                ? "Hay {$total} servicios activos disponibles para explicar u ofrecer."
                : 'No hay servicios activos para mostrar.',
        ];
    }
}
