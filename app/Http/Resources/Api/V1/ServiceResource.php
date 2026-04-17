<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business' => new BusinessSummaryResource($this->whenLoaded('negocio')),
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'duration_minutes' => $this->duracion_minutos,
            'capacity' => $this->aforo,
            'start_time' => $this->formatTime($this->hora_inicio),
            'end_time' => $this->formatTime($this->hora_fin),
            'is_dynamic_experience' => $this->usaProgramacionDinamica(),
            'base_price' => (string) $this->precio_base,
            'price_type' => new PriceTypeSummaryResource($this->whenLoaded('tipoPrecio')),
            'requires_payment' => (bool) $this->requiere_pago,
            'is_active' => (bool) $this->activo,
            'resources' => OperationalResourceSummaryResource::collection($this->whenLoaded('recursos')),
            'resources_count' => $this->whenCounted('recursos'),
            'reservations_count' => $this->whenCounted('reservas'),
            'publicNotes' => $this->notas_publicas,
            'priorInstructions' => $this->instrucciones_previas,
            'requiredDocumentation' => $this->documentacion_requerida,
            'minCancellationHours' => $this->horas_minimas_cancelacion,
            'isRefundable' => (bool) $this->es_reembolsable,
            'depositPercentage' => $this->porcentaje_senal !== null ? (string) $this->porcentaje_senal : null,
            'pricePerTimeUnit' => (bool) $this->precio_por_unidad_tiempo,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }

    private function formatTime(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return substr($value, 0, 5);
    }
}
