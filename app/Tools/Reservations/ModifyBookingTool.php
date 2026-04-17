<?php

namespace App\Tools\Reservations;

use App\Services\Reservations\BookingModificationService;
use App\Tools\Data\ModifyBookingInput;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class ModifyBookingTool extends ToolDefinition
{
    public function __construct(
        private readonly BookingModificationService $bookingModificationService,
    ) {}

    public function name(): string
    {
        return 'modify_booking';
    }

    public function description(): string
    {
        return 'Modifica una reserva futura ya existente usando su localizador, valida la nueva disponibilidad y comunica los cambios al cliente y al negocio.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario ya tiene una reserva hecha y quiere cambiar fecha, hora, personas, experiencia o datos de contacto.',
            'Cuando ya dispones del localizador de la reserva y has confirmado con el usuario los nuevos datos.',
            'Si el cambio afecta a fecha, hora, personas o experiencia, usa antes search_availability para ofrecer huecos reales y despues ejecuta modify_booking con la opcion elegida.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para crear reservas nuevas: para eso esta create_booking.',
            'No la uses si aun no tienes el localizador de la reserva.',
            'No la uses si el usuario todavia no ha confirmado de forma explicita los cambios que quiere aplicar.',
        ];
    }

    public function argumentGuidance(): array
    {
        return [
            'negocio_id' => 'Siempre el negocio actual de la conversacion.',
            'locator' => 'Localizador exacto de la reserva existente. Es obligatorio.',
            'servicio_id' => 'Nueva experiencia si el cliente quiere cambiarla. Si no cambia, puedes omitirlo.',
            'fecha' => 'Nueva fecha en formato YYYY-MM-DD si se modifica.',
            'hora_inicio' => 'Nueva hora de inicio elegida por el usuario en formato H:i. Si cambias fecha, hora o personas, debe corresponder con un hueco real.',
            'numero_personas' => 'Nuevo numero de personas si cambia la reserva.',
            'contact_name' => 'Nombre actualizado de la persona responsable si el usuario lo cambia.',
            'contact_phone' => 'Telefono actualizado del responsable si el usuario lo cambia.',
            'contact_email' => 'Email actualizado del responsable si el usuario lo cambia.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Si la modificacion se guarda, deja claro que la reserva ya ha sido actualizada y resume los cambios aplicados.',
            'Menciona el email al cliente solo si el resultado indica que se ha enviado.',
            'Si el cambio no puede aplicarse por disponibilidad o por politica, explica el motivo y orienta el siguiente paso.',
        ];
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'locator' => ['type' => 'string', 'description' => 'Localizador de la reserva a modificar'],
                'servicio_id' => ['type' => 'integer', 'nullable' => true],
                'fecha' => ['type' => 'string', 'format' => 'date', 'nullable' => true],
                'hora_inicio' => ['type' => 'string', 'nullable' => true, 'description' => 'Nueva hora elegida en formato H:i'],
                'hora_fin' => ['type' => 'string', 'nullable' => true, 'description' => 'Hora fin opcional en formato H:i'],
                'numero_personas' => ['type' => 'integer', 'nullable' => true, 'minimum' => 1],
                'recurso_id' => ['type' => 'integer', 'nullable' => true],
                'recurso_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'nullable' => true],
                'slot_key' => ['type' => 'string', 'nullable' => true],
                'contact_name' => ['type' => 'string', 'nullable' => true],
                'contact_phone' => ['type' => 'string', 'nullable' => true],
                'contact_email' => ['type' => 'string', 'nullable' => true],
                'document_type' => ['type' => 'string', 'nullable' => true],
                'document_value' => ['type' => 'string', 'nullable' => true],
                'notes' => ['type' => 'string', 'nullable' => true],
                'sesion_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Nueva sesion grupal si aplica'],
            ],
            'required' => ['negocio_id', 'locator'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = ModifyBookingInput::fromArray($input);

        if ($dto->locator === '') {
            return ToolResult::fail('Necesito el localizador de la reserva para poder modificarla.');
        }

        try {
            $result = $this->bookingModificationService->modify($dto);
        } catch (\Throwable $exception) {
            return ToolResult::fail($exception->getMessage());
        }

        $reserva = $result['booking'];
        $changeSummary = $result['change_summary'];

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
                'contact' => [
                    'name' => $reserva->nombre_responsable,
                    'phone' => $reserva->telefono_responsable,
                    'email' => $reserva->email_responsable,
                ],
                'modification_email_sent_at' => optional($reserva->mail_modificacion_enviado_en)?->toIso8601String(),
                'updated_at' => optional($reserva->updated_at)?->toIso8601String(),
            ],
            'change_summary' => $changeSummary,
            'admin_notification_expected' => (bool) ($reserva->negocio?->notif_reserva_modificada),
        ]);
    }

    public function resultExplanation(array $input, ToolResult $result): array
    {
        $locator = data_get($result->data, 'booking.locator');
        $emailSentAt = data_get($result->data, 'booking.modification_email_sent_at');
        $changes = collect(data_get($result->data, 'change_summary', []))
            ->map(fn (array $item) => ($item['label'] ?? $item['field']).': '.($item['after'] ?? ''))
            ->values()
            ->all();

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Actualiza una reserva real ya existente y recalcula la disponibilidad antes de guardar.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $result->success && $locator !== null
                ? "La reserva {$locator} ya ha sido modificada y el nuevo estado es el vigente."
                : 'La reserva no se ha modificado y no debes presentarla como actualizada.',
            'next_step_hint' => $result->success
                ? 'Confirma al usuario que la reserva ya esta actualizada, resume los cambios y menciona el localizador.'
                : 'Explica el motivo del fallo y guia al usuario con la minima informacion adicional necesaria.',
            'public_summary' => $result->success
                ? 'La reserva'.($locator !== null ? " {$locator}" : '').' se ha modificado correctamente.'
                    .($changes !== [] ? ' Cambios: '.implode('; ', $changes).'.' : '')
                    .($emailSentAt ? ' Se ha enviado un email de confirmacion al cliente.' : '')
                : ($result->error ?? 'No se pudo modificar la reserva.'),
        ];
    }
}
