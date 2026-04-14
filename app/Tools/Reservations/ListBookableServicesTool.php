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
        ])->all();

        return ToolResult::ok([
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'total_servicios' => count($items),
            'servicios' => $items,
        ]);
    }
}
