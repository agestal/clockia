<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business' => new BusinessSummaryResource($this->whenLoaded('negocio')),
            'service' => new ServiceSummaryResource($this->whenLoaded('servicio')),
            'resource' => new OperationalResourceSummaryResource($this->whenLoaded('recurso')),
            'block_type' => new BlockTypeSummaryResource($this->whenLoaded('tipoBloqueo')),
            'date' => optional($this->fecha)?->toDateString(),
            'start_date' => optional($this->fecha_inicio)?->toDateString(),
            'end_date' => optional($this->fecha_fin)?->toDateString(),
            'is_recurring' => (bool) $this->es_recurrente,
            'weekday' => $this->dia_semana,
            'start_time' => $this->formatTime($this->hora_inicio),
            'end_time' => $this->formatTime($this->hora_fin),
            'reason' => $this->motivo,
            'is_full_day' => $this->esDiaCompleto(),
            'is_range' => $this->esRango(),
            'is_business_wide' => $this->esNegocioCompleto(),
            'is_active' => (bool) $this->activo,
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
