<?php

namespace App\Tools\Data;

class CreateQuoteInput
{
    public function __construct(
        public readonly int $negocio_id,
        public readonly int $servicio_id,
        public readonly ?int $numero_personas = null,
        public readonly ?int $numero_menores = null,
        public readonly ?string $inicio_datetime = null,
        public readonly ?string $fin_datetime = null,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
            servicio_id: (int) ($input['servicio_id'] ?? 0),
            numero_personas: isset($input['numero_personas']) ? (int) $input['numero_personas'] : null,
            numero_menores: isset($input['numero_menores']) ? (int) $input['numero_menores'] : null,
            inicio_datetime: $input['inicio_datetime'] ?? null,
            fin_datetime: $input['fin_datetime'] ?? null,
        );
    }
}
