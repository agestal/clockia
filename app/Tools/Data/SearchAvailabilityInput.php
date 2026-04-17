<?php

namespace App\Tools\Data;

class SearchAvailabilityInput
{
    public function __construct(
        public readonly int $negocio_id,
        public readonly int $servicio_id,
        public readonly string $fecha,
        public readonly ?int $numero_personas = null,
        public readonly ?int $exclude_reserva_id = null,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
            servicio_id: (int) ($input['servicio_id'] ?? 0),
            fecha: $input['fecha'] ?? '',
            numero_personas: isset($input['numero_personas']) ? (int) $input['numero_personas'] : null,
            exclude_reserva_id: isset($input['exclude_reserva_id']) ? (int) $input['exclude_reserva_id'] : null,
        );
    }
}
