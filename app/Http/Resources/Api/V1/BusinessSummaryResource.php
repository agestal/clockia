<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->nombre,
            'is_active' => (bool) $this->activo,
            'publicDescription' => $this->descripcion_publica,
            'address' => $this->direccion,
            'website' => $this->url_publica,
            'cancellationPolicy' => $this->politica_cancelacion,
            'minCancellationHours' => $this->horas_minimas_cancelacion,
            'allowsModification' => (bool) $this->permite_modificacion,
        ];
    }
}
