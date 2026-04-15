<?php

namespace App\Tools\Data;

class CreateBookingInput
{
    public function __construct(
        public readonly int $negocio_id,
        public readonly int $servicio_id,
        public readonly string $fecha,
        public readonly ?string $hora_inicio = null,
        public readonly ?string $hora_fin = null,
        public readonly ?int $numero_personas = null,
        public readonly ?int $recurso_id = null,
        public readonly array $recurso_ids = [],
        public readonly ?string $slot_key = null,
        public readonly ?string $contact_name = null,
        public readonly ?string $contact_phone = null,
        public readonly ?string $contact_email = null,
        public readonly ?string $document_type = null,
        public readonly ?string $document_value = null,
        public readonly ?string $notes = null,
        public readonly ?int $sesion_id = null,
    ) {}

    public static function fromArray(array $input): self
    {
        $resourceIds = $input['recurso_ids'] ?? $input['resource_ids'] ?? [];

        return new self(
            negocio_id: (int) ($input['negocio_id'] ?? 0),
            servicio_id: (int) ($input['servicio_id'] ?? 0),
            fecha: (string) ($input['fecha'] ?? ''),
            hora_inicio: isset($input['hora_inicio']) ? (string) $input['hora_inicio'] : null,
            hora_fin: isset($input['hora_fin']) ? (string) $input['hora_fin'] : null,
            numero_personas: isset($input['numero_personas']) ? (int) $input['numero_personas'] : null,
            recurso_id: isset($input['recurso_id']) ? (int) $input['recurso_id'] : null,
            recurso_ids: is_array($resourceIds)
                ? array_values(array_filter(array_map(static fn ($value) => is_numeric($value) ? (int) $value : null, $resourceIds)))
                : [],
            slot_key: isset($input['slot_key']) ? (string) $input['slot_key'] : null,
            contact_name: isset($input['contact_name']) ? (string) $input['contact_name'] : null,
            contact_phone: isset($input['contact_phone']) ? (string) $input['contact_phone'] : null,
            contact_email: isset($input['contact_email']) ? (string) $input['contact_email'] : null,
            document_type: isset($input['document_type']) ? (string) $input['document_type'] : null,
            document_value: isset($input['document_value']) ? (string) $input['document_value'] : null,
            notes: isset($input['notes']) ? (string) $input['notes'] : null,
            sesion_id: isset($input['sesion_id']) ? (int) $input['sesion_id'] : null,
        );
    }
}
