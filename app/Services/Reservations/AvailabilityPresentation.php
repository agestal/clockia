<?php

namespace App\Services\Reservations;

class AvailabilityPresentation
{
    public function __construct(
        public readonly bool $hasOptions,
        public readonly array $options,
        public readonly ?string $message,
        public readonly ?string $fallbackReason,
        public readonly string $servicio,
        public readonly string $fecha,
        public readonly ?int $personas,
        public readonly int $rawSlotCount,
    ) {}

    public function toPromptBlock(): string
    {
        if (! $this->hasOptions) {
            return "SIN DISPONIBILIDAD: {$this->message}".($this->fallbackReason ? " (motivo: {$this->fallbackReason})" : '');
        }

        $personasText = $this->personas ? " para {$this->personas} personas" : '';
        $lines = ["OPCIONES DISPONIBLES{$personasText} para {$this->servicio} el {$this->fecha}:"];

        foreach ($this->options as $opt) {
            $line = "• {$opt['hora_inicio']}";
            if ($opt['zone_label']) {
                $line .= " ({$opt['zone_label']})";
            }
            $lines[] = $line;
        }

        $lines[] = "Total opciones mostradas: ".count($this->options)." (de {$this->rawSlotCount} recursos disponibles)";

        return implode("\n", $lines);
    }

    public function toArray(): array
    {
        return [
            'has_options' => $this->hasOptions,
            'options_count' => count($this->options),
            'options' => array_map(fn ($o) => [
                'hora_inicio' => $o['hora_inicio'],
                'hora_fin' => $o['hora_fin'],
                'zone_label' => $o['zone_label'],
                'resource_count' => $o['resource_count'],
            ], $this->options),
            'message' => $this->message,
            'fallback_reason' => $this->fallbackReason,
            'servicio' => $this->servicio,
            'fecha' => $this->fecha,
            'personas' => $this->personas,
            'raw_slot_count' => $this->rawSlotCount,
        ];
    }
}
