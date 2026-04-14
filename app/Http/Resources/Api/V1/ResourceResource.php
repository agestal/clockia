<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business' => new BusinessSummaryResource($this->whenLoaded('negocio')),
            'name' => $this->nombre,
            'resource_type' => new ResourceTypeSummaryResource($this->whenLoaded('tipoRecurso')),
            'capacity' => $this->capacidad,
            'minCapacity' => $this->capacidad_minima,
            'isCombinable' => (bool) $this->combinable,
            'publicNotes' => $this->notas_publicas,
            'combinableWith' => $this->whenLoaded('recursosCombinables', function () {
                return $this->recursosCombinables->map(function ($combinacion) {
                    return [
                        'id' => $combinacion->recursoCombinado->id,
                        'name' => $combinacion->recursoCombinado->nombre,
                    ];
                })->values();
            }),
            'is_active' => (bool) $this->activo,
            'services' => ServiceSummaryResource::collection($this->whenLoaded('servicios')),
            'availabilities_count' => $this->whenCounted('disponibilidades'),
            'blocks_count' => $this->whenCounted('bloqueos'),
            'reservations_count' => $this->whenCounted('reservas'),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
