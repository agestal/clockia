<?php

namespace App\Tools\Reservations;

use App\Services\Reservations\BookingCancellationService;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class RequestBookingCancellationTool extends ToolDefinition
{
    public function __construct(
        private readonly BookingCancellationService $cancellationService,
    ) {}

    public function name(): string
    {
        return 'request_booking_cancellation';
    }

    public function description(): string
    {
        return 'Busca una reserva activa por localizador o email y envía un email al cliente para que confirme la cancelación desde su correo.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario quiere cancelar una reserva existente.',
            'Cuando el usuario pregunta cómo cancelar o dice que no puede asistir.',
            'Cuando ya tienes el localizador o el email del usuario para buscar la reserva.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para crear reservas: para eso está create_booking.',
            'No la uses si el usuario solo pregunta por la política de cancelación sin querer cancelar: para eso está get_cancellation_policy.',
            'No la uses si todavía no tienes ni localizador ni email del usuario.',
        ];
    }

    public function argumentGuidance(): array
    {
        return [
            'negocio_id' => 'Siempre el negocio actual de la conversación.',
            'locator' => 'El código de localizador de la reserva. Prioriza este campo si el usuario lo tiene.',
            'email' => 'El email del cliente. Úsalo si no tiene el localizador. Permite buscar todas las reservas activas del cliente.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Si se envió el email de cancelación, dile al usuario que revise su correo y confirme desde allí.',
            'Si la reserva no es cancelable porque está fuera de plazo, explícalo con claridad y menciona las horas mínimas.',
            'Si no se encontró ninguna reserva, pregunta si el dato es correcto o pide el otro (localizador o email).',
            'No canceles directamente: esta herramienta solo envía el email de confirmación. La cancelación real la hace el usuario desde su correo.',
        ];
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'locator' => ['type' => 'string', 'nullable' => true, 'description' => 'Localizador de la reserva'],
                'email' => ['type' => 'string', 'nullable' => true, 'description' => 'Email del cliente para buscar reservas activas'],
            ],
            'required' => ['negocio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $negocioId = (int) ($input['negocio_id'] ?? 0);
        $locator = isset($input['locator']) && trim((string) $input['locator']) !== '' ? trim((string) $input['locator']) : null;
        $email = isset($input['email']) && trim((string) $input['email']) !== '' ? trim((string) $input['email']) : null;

        if ($locator === null && $email === null) {
            return ToolResult::fail('Necesito el localizador de la reserva o el email del cliente para poder buscarla.');
        }

        if ($locator !== null) {
            return $this->handleByLocator($negocioId, $locator);
        }

        return $this->handleByEmail($negocioId, $email);
    }

    private function handleByLocator(int $negocioId, string $locator): ToolResult
    {
        $reserva = $this->cancellationService->lookupByLocator($negocioId, $locator);

        if ($reserva === null) {
            return ToolResult::fail('No se encontró ninguna reserva activa con ese localizador.');
        }

        if (! $this->cancellationService->isCancellable($reserva)) {
            $hours = $this->cancellationService->hoursUntilDeadline($reserva);
            $minHours = $reserva->horas_minimas_cancelacion;

            return ToolResult::fail(
                "La reserva {$reserva->localizador} no se puede cancelar en este momento."
                .($minHours ? " La política requiere cancelar con al menos {$minHours} horas de antelación." : '')
                .($hours !== null ? " Faltan {$hours} horas para la experiencia." : '')
            );
        }

        try {
            $this->cancellationService->requestCancellation($reserva);
        } catch (\Throwable $e) {
            return ToolResult::fail($e->getMessage());
        }

        $email = $reserva->email_responsable ?? $reserva->cliente?->email;

        return ToolResult::ok([
            'action' => 'cancellation_email_sent',
            'locator' => $reserva->localizador,
            'service_name' => $reserva->servicio?->nombre,
            'date' => $reserva->fecha?->toDateString(),
            'time' => substr((string) $reserva->hora_inicio, 0, 5),
            'participants' => $reserva->numero_personas,
            'email_sent_to' => $email,
            'message' => 'Se ha enviado un email al cliente para que confirme la cancelación.',
        ]);
    }

    private function handleByEmail(int $negocioId, string $email): ToolResult
    {
        $reservas = $this->cancellationService->lookupByEmail($negocioId, $email);

        if ($reservas->isEmpty()) {
            return ToolResult::fail('No se encontraron reservas activas con ese email.');
        }

        $cancellable = $reservas->filter(fn ($r) => $this->cancellationService->isCancellable($r));

        if ($cancellable->count() === 1) {
            $reserva = $cancellable->first();

            try {
                $this->cancellationService->requestCancellation($reserva);
            } catch (\Throwable $e) {
                return ToolResult::fail($e->getMessage());
            }

            return ToolResult::ok([
                'action' => 'cancellation_email_sent',
                'locator' => $reserva->localizador,
                'service_name' => $reserva->servicio?->nombre,
                'date' => $reserva->fecha?->toDateString(),
                'time' => substr((string) $reserva->hora_inicio, 0, 5),
                'participants' => $reserva->numero_personas,
                'email_sent_to' => $email,
                'message' => 'Se ha enviado un email para confirmar la cancelación.',
            ]);
        }

        $list = $reservas->map(fn ($r) => [
            'locator' => $r->localizador,
            'service_name' => $r->servicio?->nombre,
            'date' => $r->fecha?->toDateString(),
            'time' => substr((string) $r->hora_inicio, 0, 5),
            'participants' => $r->numero_personas,
            'cancellable' => $this->cancellationService->isCancellable($r),
        ])->values()->all();

        return ToolResult::ok([
            'action' => 'multiple_bookings_found',
            'bookings' => $list,
            'message' => 'Se encontraron varias reservas. Pregunta al usuario cuál quiere cancelar indicando el localizador.',
        ]);
    }

    public function resultExplanation(array $input, ToolResult $result): array
    {
        $action = data_get($result->data, 'action');

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Busca reservas activas y envía un email de confirmación de cancelación. No cancela directamente.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => match ($action) {
                'cancellation_email_sent' => 'Se ha enviado el email de cancelación. No es necesario volver a llamar a esta tool para esta reserva.',
                'multiple_bookings_found' => 'Hay varias reservas; necesitas que el usuario elija una por localizador.',
                default => $result->error ?? 'La cancelación no se completó.',
            },
            'next_step_hint' => match ($action) {
                'cancellation_email_sent' => 'Dile al usuario que revise su correo y haga clic en el enlace para confirmar. La cancelación no es efectiva hasta que confirme desde el email.',
                'multiple_bookings_found' => 'Presenta las reservas encontradas y pregunta cuál quiere cancelar. Usa el localizador para la siguiente llamada.',
                default => 'Explica el problema y ofrece alternativas.',
            },
            'public_summary' => $result->success
                ? data_get($result->data, 'message', 'Proceso de cancelación iniciado.')
                : ($result->error ?? 'No se pudo procesar la cancelación.'),
        ];
    }
}
