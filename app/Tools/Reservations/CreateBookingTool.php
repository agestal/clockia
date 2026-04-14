<?php

namespace App\Tools\Reservations;

use App\Services\Reservations\ReservationFinalizationService;
use App\Tools\Data\CreateBookingInput;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class CreateBookingTool extends ToolDefinition
{
    public function __construct(
        private readonly ReservationFinalizationService $reservationFinalizationService,
    ) {}

    public function name(): string
    {
        return 'create_booking';
    }

    public function description(): string
    {
        return 'Crea una reserva real con validación final de disponibilidad y datos de contacto obligatorios del responsable.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario ya quiere cerrar la reserva y tienes resueltos servicio, fecha, hora, personas y contacto del responsable.',
            'Cuando ya existe un hueco real compatible y toca crear la reserva de verdad.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para buscar huecos: para eso está search_availability.',
            'No la uses si todavía faltan el nombre o el teléfono de contacto del responsable.',
            'No la uses si el servicio exige documentación y aún no tienes esos datos.',
        ];
    }

    public function argumentGuidance(): array
    {
        return [
            'negocio_id' => 'Siempre debe corresponder al negocio actual de la conversación.',
            'servicio_id' => 'Debe ser un servicio real del negocio ya elegido por el usuario.',
            'fecha' => 'Usa una fecha absoluta YYYY-MM-DD ya resuelta.',
            'hora_inicio' => 'Hora elegida por el usuario en formato H:i. Si ya vienes de search_availability, debe coincidir con un hueco real.',
            'numero_personas' => 'Inclúyelo siempre para validar capacidad y recalcular precio cuando aplique.',
            'slot_key' => 'Si dispones del identificador interno del hueco devuelto por search_availability, puedes usarlo para fijar el slot exacto.',
            'contact_name' => 'Nombre de la persona responsable de la reserva.',
            'contact_phone' => 'Teléfono real de contacto del responsable. Es obligatorio para cerrar la reserva.',
            'contact_email' => 'Email del responsable cuando el usuario lo haya dado. Si no lo tienes, puede ir vacío.',
            'document_type' => 'Tipo de documento cuando el servicio exija documentación, por ejemplo DNI, pasaporte o carné de conducir.',
            'document_value' => 'Valor del documento cuando el servicio exija documentación.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Si la reserva se crea, deja claro que ya está registrada y cita el localizador.',
            'Si falla porque el hueco ya no existe o falta contacto/documentación, explícalo con naturalidad y guía el siguiente paso.',
            'No afirmes que se ha notificado por email o SMS si no existe esa integración real.',
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
                'hora_inicio' => ['type' => 'string', 'nullable' => true, 'description' => 'Hora elegida en formato H:i'],
                'hora_fin' => ['type' => 'string', 'nullable' => true, 'description' => 'Hora fin opcional en formato H:i'],
                'numero_personas' => ['type' => 'integer', 'minimum' => 1],
                'recurso_id' => ['type' => 'integer', 'nullable' => true],
                'recurso_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'nullable' => true],
                'slot_key' => ['type' => 'string', 'nullable' => true],
                'contact_name' => ['type' => 'string'],
                'contact_phone' => ['type' => 'string'],
                'contact_email' => ['type' => 'string', 'nullable' => true],
                'document_type' => ['type' => 'string', 'nullable' => true],
                'document_value' => ['type' => 'string', 'nullable' => true],
                'notes' => ['type' => 'string', 'nullable' => true],
            ],
            'required' => ['negocio_id', 'servicio_id', 'fecha', 'numero_personas', 'contact_name', 'contact_phone'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = CreateBookingInput::fromArray($input);

        if (($dto->hora_inicio === null || trim($dto->hora_inicio) === '') && ($dto->slot_key === null || trim($dto->slot_key) === '')) {
            return ToolResult::fail('Necesito al menos la hora elegida o una referencia válida del hueco para cerrar la reserva.');
        }

        try {
            $reserva = $this->reservationFinalizationService->finalize($dto);
        } catch (\Throwable $e) {
            return ToolResult::fail($e->getMessage());
        }

        return ToolResult::ok([
            'booking' => [
                'id' => $reserva->id,
                'locator' => $reserva->localizador,
                'status' => $reserva->estadoReserva?->nombre,
                'business_id' => $reserva->negocio_id,
                'service_id' => $reserva->servicio_id,
                'service_name' => $reserva->servicio?->nombre,
                'resource_id' => $reserva->recurso_id,
                'resource_name' => $reserva->recurso?->nombre,
                'resource_ids' => $reserva->reservaRecursos->pluck('recurso_id')->values()->all(),
                'resources' => $reserva->reservaRecursos->map(fn ($item) => [
                    'id' => $item->recurso_id,
                    'name' => $item->recurso?->nombre,
                ])->values()->all(),
                'date' => $reserva->fecha?->toDateString(),
                'start_time' => substr((string) $reserva->hora_inicio, 0, 5),
                'end_time' => substr((string) $reserva->hora_fin, 0, 5),
                'party_size' => $reserva->numero_personas,
                'calculated_price' => (string) $reserva->precio_calculado,
                'total_price' => $reserva->precio_total !== null ? (string) $reserva->precio_total : null,
                'contact' => [
                    'name' => $reserva->nombre_responsable,
                    'phone' => $reserva->telefono_responsable,
                    'email' => $reserva->email_responsable,
                    'document_type' => $reserva->tipo_documento_responsable,
                    'document_value' => $reserva->documento_responsable,
                ],
                'created_at' => optional($reserva->created_at)?->toIso8601String(),
            ],
        ]);
    }

    public function resultExplanation(array $input, ToolResult $result): array
    {
        $locator = data_get($result->data, 'booking.locator');
        $status = data_get($result->data, 'booking.status');

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Crea una reserva real y persistida si el hueco y los datos de contacto son válidos.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $result->success && $locator !== null
                ? "La reserva ya está creada con localizador {$locator}. Ya no debes hablar de ella como propuesta."
                : 'La reserva no llegó a crearse y no debe presentarse como cerrada.',
            'next_step_hint' => $result->success
                ? 'Confirma al usuario que la reserva ya existe, resume los datos clave y menciona el localizador.'
                : 'Explica qué falta o qué falló y pide solo el dato o ajuste necesario para intentarlo de nuevo.',
            'public_summary' => $result->success
                ? 'La reserva se ha creado correctamente'.($locator !== null ? " con localizador {$locator}" : '').($status !== null ? " y estado {$status}" : '').'.'
                : ($result->error ?? 'No se pudo crear la reserva.'),
        ];
    }
}
