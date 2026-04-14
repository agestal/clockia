<?php

namespace App\Tools\Reservations;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\PolicyResolver;
use App\Tools\Data\CreateQuoteInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;
use Carbon\Carbon;

class CreateQuoteTool extends ToolDefinition
{
    public function __construct(
        private readonly PolicyResolver $policyResolver,
    ) {}

    public function name(): string
    {
        return 'create_quote';
    }

    public function description(): string
    {
        return 'Calcula una estimación de precio para un servicio antes de reservar.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'servicio_id' => ['type' => 'integer'],
                'numero_personas' => ['type' => 'integer', 'nullable' => true, 'minimum' => 1],
                'inicio_datetime' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                'fin_datetime' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
            ],
            'required' => ['negocio_id', 'servicio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = CreateQuoteInput::fromArray($input);

        $negocio = Negocio::find($dto->negocio_id);
        if (! $negocio) {
            throw new EntityNotFoundException('Negocio', $dto->negocio_id);
        }

        $servicio = Servicio::query()
            ->where('negocio_id', $dto->negocio_id)
            ->where('id', $dto->servicio_id)
            ->with('tipoPrecio:id,nombre')
            ->first();

        if (! $servicio) {
            throw new EntityNotFoundException('Servicio', $dto->servicio_id);
        }

        $precioBase = (float) $servicio->precio_base;
        $tipoPrecio = $servicio->tipoPrecio?->nombre ?? 'Fijo';
        $personas = $dto->numero_personas ?? 1;

        $precioCalculado = $this->calcularPrecio($precioBase, $tipoPrecio, $personas, $servicio, $dto);

        $policy = [
            'horas_minimas_cancelacion' => $this->policyResolver->horasMinimasCancelacion(null, $servicio, $negocio),
            'permite_modificacion' => $this->policyResolver->permiteModificacion(null, $servicio, $negocio),
            'es_reembolsable' => $this->policyResolver->esReembolsable(null, $servicio, $negocio),
            'porcentaje_senal' => $this->policyResolver->porcentajeSenal(null, $servicio, $negocio),
        ];

        $importeSenal = null;
        if ($policy['porcentaje_senal'] !== null) {
            $importeSenal = number_format($precioCalculado * ((float) $policy['porcentaje_senal'] / 100), 2, '.', '');
        }

        return ToolResult::ok([
            'servicio_id' => $servicio->id,
            'servicio_nombre' => $servicio->nombre,
            'precio_base' => number_format($precioBase, 2, '.', ''),
            'precio_calculado' => number_format($precioCalculado, 2, '.', ''),
            'tipo_precio' => $tipoPrecio,
            'numero_personas' => $personas,
            'duracion_minutos' => $servicio->duracion_minutos,
            'desglose' => $this->generarDesglose($precioBase, $tipoPrecio, $personas, $precioCalculado),
            'requiere_pago' => $servicio->requiere_pago,
            'es_reembolsable' => $policy['es_reembolsable'],
            'porcentaje_senal' => $policy['porcentaje_senal'],
            'importe_senal_estimado' => $importeSenal,
            'horas_minimas_cancelacion' => $policy['horas_minimas_cancelacion'],
            'permite_modificacion' => $policy['permite_modificacion'],
        ]);
    }

    private function calcularPrecio(float $precioBase, string $tipoPrecio, int $personas, Servicio $servicio, CreateQuoteInput $dto): float
    {
        return match ($tipoPrecio) {
            'Por persona' => $precioBase * $personas,
            'Por tramo' => $this->calcularPorTramo($precioBase, $servicio, $dto),
            default => $precioBase,
        };
    }

    private function calcularPorTramo(float $precioBase, Servicio $servicio, CreateQuoteInput $dto): float
    {
        if ($dto->inicio_datetime === null || $dto->fin_datetime === null) {
            return $precioBase;
        }

        $inicio = Carbon::parse($dto->inicio_datetime);
        $fin = Carbon::parse($dto->fin_datetime);
        $minutosReales = max(1, (int) $inicio->diffInMinutes($fin));
        $tramos = max(1, (int) ceil($minutosReales / max(1, $servicio->duracion_minutos)));

        return $precioBase * $tramos;
    }

    private function generarDesglose(float $precioBase, string $tipoPrecio, int $personas, float $precioCalculado): string
    {
        return match ($tipoPrecio) {
            'Por persona' => number_format($precioBase, 2, '.', '').' x '.$personas.' personas = '.number_format($precioCalculado, 2, '.', ''),
            'Fijo' => 'Precio fijo: '.number_format($precioCalculado, 2, '.', ''),
            default => $tipoPrecio.': '.number_format($precioCalculado, 2, '.', ''),
        };
    }
}
