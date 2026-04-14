<?php

namespace App\Tools\Data;

class CheckBusinessHoursInput
{
    public function __construct(
        public readonly int $negocio_id,
        public readonly ?int $servicio_id = null,
        public readonly ?string $fecha = null,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
            servicio_id: isset($input['servicio_id']) ? (int) $input['servicio_id'] : null,
            fecha: $input['fecha'] ?? null,
        );
    }
}
