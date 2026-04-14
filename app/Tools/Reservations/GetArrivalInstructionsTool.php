<?php

namespace App\Tools\Reservations;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Tools\Data\GetArrivalInstructionsInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class GetArrivalInstructionsTool extends ToolDefinition
{
    public function name(): string
    {
        return 'get_arrival_instructions';
    }

    public function description(): string
    {
        return 'Devuelve información útil para llegar al negocio y preparar la reserva.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'servicio_id' => ['type' => 'integer', 'nullable' => true],
            ],
            'required' => ['negocio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = GetArrivalInstructionsInput::fromArray($input);

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

            if (! $servicio) {
                throw new EntityNotFoundException('Servicio', $dto->servicio_id);
            }
        }

        return ToolResult::ok([
            'negocio' => [
                'nombre' => $negocio->nombre,
                'direccion' => $negocio->direccion,
                'url_publica' => $negocio->url_publica,
                'descripcion_publica' => $negocio->descripcion_publica,
                'telefono' => $negocio->telefono,
                'email' => $negocio->email,
            ],
            'servicio' => $servicio ? [
                'nombre' => $servicio->nombre,
                'instrucciones_previas' => $servicio->instrucciones_previas,
                'documentacion_requerida' => $servicio->documentacion_requerida,
                'notas_publicas' => $servicio->notas_publicas,
            ] : null,
        ]);
    }
}
