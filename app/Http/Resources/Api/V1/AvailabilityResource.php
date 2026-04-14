<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'resource' => new OperationalResourceSummaryResource($this->whenLoaded('recurso')),
            'weekday' => $this->dia_semana,
            'start_time' => $this->formatTime($this->hora_inicio),
            'end_time' => $this->formatTime($this->hora_fin),
            'is_active' => (bool) $this->activo,
            'shiftName' => $this->nombre_turno,
            'bufferMinutes' => $this->buffer_minutos,
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
