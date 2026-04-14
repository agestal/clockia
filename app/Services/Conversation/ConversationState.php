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

        return $parts ? implode(', ', $parts) : 'sin datos aún';
    }
}
