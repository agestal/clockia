<?php

namespace App\Services\Conversation;

/**
 * Single source of truth for the current conversation state.
 * Serializable to/from session and updated through LLM state patches.
 */
class ConversationState
{
    public function __construct(
        public int $negocioId,
        public ?int $servicioId = null,
        public ?string $servicioNombre = null,
        public ?string $fecha = null,
        public ?int $numeroPersonas = null,
        public ?string $horaPreferida = null,
        public ?string $contactName = null,
        public ?string $contactPhone = null,
        public ?string $contactEmail = null,
        public ?string $documentType = null,
        public ?string $documentValue = null,
        public ?string $ultimaIntencion = null,
        public bool $fechaEsPasada = false,
        public bool $necesitaConfirmacion = false,
        public array $datosConfirmados = [],
        public ?array $ultimaPropuesta = null,
    ) {}

    public function toArray(): array
    {
        return [
            'negocio_id' => $this->negocioId,
            'servicio_id' => $this->servicioId,
            'servicio_nombre' => $this->servicioNombre,
            'fecha' => $this->fecha,
            'numero_personas' => $this->numeroPersonas,
            'hora_preferida' => $this->horaPreferida,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'contact_email' => $this->contactEmail,
            'document_type' => $this->documentType,
            'document_value' => $this->documentValue,
            'ultima_intencion' => $this->ultimaIntencion,
            'fecha_es_pasada' => $this->fechaEsPasada,
            'necesita_confirmacion' => $this->necesitaConfirmacion,
            'datos_confirmados' => $this->datosConfirmados,
            'ultima_propuesta' => $this->ultimaPropuesta,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            negocioId: (int) ($data['negocio_id'] ?? 0),
            servicioId: $data['servicio_id'] ?? null,
            servicioNombre: $data['servicio_nombre'] ?? null,
            fecha: $data['fecha'] ?? null,
            numeroPersonas: isset($data['numero_personas']) ? (int) $data['numero_personas'] : null,
            horaPreferida: $data['hora_preferida'] ?? null,
            contactName: $data['contact_name'] ?? null,
            contactPhone: $data['contact_phone'] ?? null,
            contactEmail: $data['contact_email'] ?? null,
            documentType: $data['document_type'] ?? null,
            documentValue: $data['document_value'] ?? null,
            ultimaIntencion: $data['ultima_intencion'] ?? null,
            fechaEsPasada: (bool) ($data['fecha_es_pasada'] ?? false),
            necesitaConfirmacion: (bool) ($data['necesita_confirmacion'] ?? false),
            datosConfirmados: $data['datos_confirmados'] ?? [],
            ultimaPropuesta: $data['ultima_propuesta'] ?? null,
        );
    }

    public function buildToolParams(): array
    {
        $params = ['negocio_id' => $this->negocioId];

        if ($this->servicioId !== null) {
            $params['servicio_id'] = $this->servicioId;
        }
        if ($this->fecha !== null) {
            $params['fecha'] = $this->fecha;
        }
        if ($this->numeroPersonas !== null) {
            $params['numero_personas'] = $this->numeroPersonas;
        }
        if ($this->horaPreferida !== null) {
            $params['hora_inicio'] = $this->horaPreferida;
        }
        if ($this->contactName !== null) {
            $params['contact_name'] = $this->contactName;
        }
        if ($this->contactPhone !== null) {
            $params['contact_phone'] = $this->contactPhone;
        }
        if ($this->contactEmail !== null) {
            $params['contact_email'] = $this->contactEmail;
        }
        if ($this->documentType !== null) {
            $params['document_type'] = $this->documentType;
        }
        if ($this->documentValue !== null) {
            $params['document_value'] = $this->documentValue;
        }

        if (is_array($this->ultimaPropuesta)) {
            foreach (['slot_key', 'hora_inicio', 'hora_fin', 'recurso_id', 'recurso_ids'] as $key) {
                if (array_key_exists($key, $this->ultimaPropuesta) && $this->ultimaPropuesta[$key] !== null && $this->ultimaPropuesta[$key] !== '') {
                    $params[$key] = $this->ultimaPropuesta[$key];
                }
            }
        }

        return $params;
    }

    public function summary(): string
    {
        $parts = [];
        if ($this->servicioNombre) {
            $parts[] = "servicio: {$this->servicioNombre}";
        }
        if ($this->fecha) {
            $parts[] = "fecha: {$this->fecha}";
        }
        if ($this->numeroPersonas) {
            $parts[] = "personas: {$this->numeroPersonas}";
        }
        if ($this->horaPreferida) {
            $parts[] = "hora: {$this->horaPreferida}";
        }
        if ($this->contactName) {
            $parts[] = "responsable: {$this->contactName}";
        }
        if ($this->contactPhone) {
            $parts[] = "teléfono: {$this->contactPhone}";
        }

        return $parts ? implode(', ', $parts) : 'sin datos aún';
    }
}
