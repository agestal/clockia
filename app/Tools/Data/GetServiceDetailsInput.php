<?php

namespace App\Tools\Data;

class GetServiceDetailsInput
{
    public function __construct(
        public readonly int $negocio_id,
        public readonly int $servicio_id,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
            servicio_id: (int) ($input['servicio_id'] ?? 0),
        );
    }
}
