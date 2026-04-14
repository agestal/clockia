<?php

namespace App\Tools\Reservations;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\PolicyResolver;
use App\Tools\Data\GetCancellationPolicyInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class GetCancellationPolicyTool extends ToolDefinition
{
    public function __construct(
        private readonly PolicyResolver $policyResolver,
    ) {}

    public function name(): string
    {
        return 'get_cancellation_policy';
    }

    public function description(): string
    {
        return 'Devuelve la política de cancelación aplicable a un negocio y/o servicio.';
    }

    public function whenToUse(): array
    {
        return [
            'Cuando el usuario pregunta si puede cancelar, modificar o recuperar un pago.',
            'Cuando necesitas explicar condiciones de señal, reembolso o antelación mínima.',
        ];
    }

    public function whenNotToUse(): array
    {
        return [
            'No la uses para disponibilidad ni para listar servicios.',
            'No la uses para crear una reserva.',
        ];
    }

    public function responseGuidance(): array
    {
        return [
            'Resume primero la política en lenguaje claro.',
            'Si hay detalles técnicos, añádelos solo si el usuario los necesita.',
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
        $dto = GetCancellationPolicyInput::fromArray($input);

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

        $horas = $this->policyResolver->horasMinimasCancelacion(null, $servicio, $negocio);
        $permiteModificacion = $this->policyResolver->permiteModificacion(null, $servicio, $negocio);
        $esReembolsable = $this->policyResolver->esReembolsable(null, $servicio, $negocio);
        $porcentajeSenal = $this->policyResolver->porcentajeSenal(null, $servicio, $negocio);

        return ToolResult::ok([
            'negocio_id' => $negocio->id,
            'negocio_nombre' => $negocio->nombre,
            'servicio_id' => $servicio?->id,
            'servicio_nombre' => $servicio?->nombre,
            'politica_cancelacion_texto' => $negocio->politica_cancelacion,
            'horas_minimas_cancelacion' => $horas,
            'permite_modificacion' => $permiteModificacion,
            'es_reembolsable' => $esReembolsable,
            'porcentaje_senal' => $porcentajeSenal,
            'resumen_humano' => $this->generarResumen($horas, $permiteModificacion, $esReembolsable, $porcentajeSenal),
        ]);
    }

    private function generarResumen(int $horas, bool $permiteModificacion, bool $esReembolsable, ?string $porcentajeSenal): string
    {
        $partes = [];

        if ($horas > 0) {
            $partes[] = "Cancelación gratuita hasta {$horas} horas antes.";
        } else {
            $partes[] = 'Cancelación sin restricción de tiempo.';
        }

        $partes[] = $esReembolsable ? 'El pago es reembolsable.' : 'El pago no es reembolsable.';
        $partes[] = $permiteModificacion ? 'Se permite modificar la reserva.' : 'No se permite modificar la reserva.';

        if ($porcentajeSenal !== null) {
            $partes[] = "Se requiere una señal del {$porcentajeSenal}% del precio.";
        }

        return implode(' ', $partes);
    }

    public function resultExplanation(array $input, \App\Tools\ToolResult $result): array
    {
        $summary = data_get($result->data, 'resumen_humano');

        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => 'Recupera la política real de cancelación, modificación y reembolso.',
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => 'Ya conoces la política de cancelación relevante para esta conversación.',
            'next_step_hint' => 'Empieza por el resumen humano y desarrolla solo si el usuario pide más detalle.',
            'public_summary' => $summary ?: 'Se ha recuperado la política de cancelación.',
        ];
    }
}
