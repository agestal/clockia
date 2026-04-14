<?php

namespace App\Tools\Data;

class ListBookableServicesInput
{
    public function __construct(
        public readonly int $negocio_id,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
        );
    }
}
