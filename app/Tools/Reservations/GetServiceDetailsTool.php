<?php

namespace App\Tools\Reservations;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\PolicyResolver;
use App\Services\Tools\BusinessComplexityResolver;
use App\Tools\Data\GetServiceDetailsInput;
use App\Tools\Exceptions\EntityNotFoundException;
use App\Tools\ToolDefinition;
use App\Tools\ToolResult;

class GetServiceDetailsTool extends ToolDefinition
{
    public function __construct(
        private readonly PolicyResolver $policyResolver,
    ) {}

    public function name(): string
    {
        return 'get_service_details';
    }

    public function description(): string
    {
        return 'Devuelve el detalle completo de un servicio, adaptando la respuesta a la complejidad del negocio.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'negocio_id' => ['type' => 'integer'],
                'servicio_id' => ['type' => 'integer'],
            ],
            'required' => ['negocio_id', 'servicio_id'],
        ];
    }

    public function execute(array $input): ToolResult
    {
        $dto = GetServiceDetailsInput::fromArray($input);

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

        $complexity = app(BusinessComplexityResolver::class);
        $nivel = $complexity->nivelComplejidad($negocio, $servicio);

        $policy = [
            'horas_minimas_cancelacion' => $this->policyResolver->horasMinimasCancelacion(null, $servicio, $negocio),
            'permite_modificacion' => $this->policyResolver->permiteModificacion(null, $servicio, $negocio),
            'es_reembolsable' => $this->policyResolver->esReembolsable(null, $servicio, $negocio),
            'porcentaje_senal' => $this->policyResolver->porcentajeSenal(null, $servicio, $negocio),
        ];

        $data = [
            'servicio' => [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'duracion_minutos' => $servicio->duracion_minutos,
                'precio_base' => (string) $servicio->precio_base,
                'tipo_precio' => $servicio->tipoPrecio?->nombre,
                'requiere_pago' => $servicio->requiere_pago,
                'notas_publicas' => $servicio->notas_publicas,
                'instrucciones_previas' => $servicio->instrucciones_previas,
                'documentacion_requerida' => $servicio->documentacion_requerida,
                'es_reembolsable' => $servicio->es_reembolsable,
                'porcentaje_senal' => $servicio->porcentaje_senal !== null ? (string) $servicio->porcentaje_senal : null,
                'precio_por_unidad_tiempo' => $servicio->precio_por_unidad_tiempo,
            ],
            'negocio' => [
                'id' => $negocio->id,
                'nombre' => $negocio->nombre,
                'direccion' => $negocio->direccion,
                'url_publica' => $negocio->url_publica,
            ],
            'politica_efectiva' => $policy,
            'complexity_level' => $nivel,
        ];

        // Only include resources if the service actually uses them
        if ($nivel >= BusinessComplexityResolver::LEVEL_RESOURCED) {
            $servicio->load(['recursos' => fn ($q) => $q->activos()->select('recursos.id', 'recursos.nombre', 'recursos.capacidad', 'recursos.capacidad_minima')]);

            $data['recursos'] = $servicio->recursos->map(fn ($r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'capacidad' => $r->capacidad,
                'capacidad_minima' => $r->capacidad_minima,
            ])->all();

            if ($nivel >= BusinessComplexityResolver::LEVEL_ADVANCED
                && $complexity->negocioTieneCombinacionesRecursos($negocio, $servicio)
            ) {
                $data['permite_combinaciones'] = true;
                $data['max_recursos_combinables'] = $negocio->maxRecursosCombinablesEfectivo();
            }
        }

        return ToolResult::ok($data);
    }
}
