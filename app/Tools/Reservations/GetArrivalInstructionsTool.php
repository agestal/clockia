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

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario pregunta dónde está el negocio, cómo llegar o qué debe tener en cuenta antes de acudir.',
            'Cuando necesitas dar instrucciones previas ligadas a un servicio concreto.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para horarios, disponibilidad o precios.',
            'No la uses para improvisar información que no exista en la ficha del negocio o del servicio.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Prioriza dirección, contacto, URL pública e instrucciones previas útiles.',
            'No añadas indicaciones inventadas ni supongas detalles logísticos no presentes en el resultado.',
        ];
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
                'punto_encuentro' => $servicio->punto_encuentro,
                'accesibilidad_notas' => $servicio->accesibilidad_notas,
                'incluye' => $servicio->incluye,
                'no_incluye' => $servicio->no_incluye,
            ] : null,
        ]);
    }

    public function resultExplanation(array $input, \App\Tools\ToolResult $result): array
    {
        $businessName = data_get($result->data, 'negocio.nombre');

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Recupera información pública útil para acudir al negocio o preparar la visita.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $businessName !== null
                ? "Ya tienes la información de llegada de {$businessName}."
                : 'Ya tienes la información de llegada necesaria para responder.',
            'next_step_hint' => 'Da la información en orden práctico: dónde está, cómo contactar y qué tener en cuenta antes de llegar.',
            'public_summary' => $businessName !== null
                ? "Se ha recuperado información útil para llegar a {$businessName}."
                : 'Se ha recuperado información de llegada.',
        ];
    }
}
