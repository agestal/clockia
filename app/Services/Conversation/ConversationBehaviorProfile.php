<?php

namespace App\Services\Conversation;

class ConversationBehaviorProfile
{
    public function __construct(
        public readonly string $sectorKey,
        public readonly string $sectorLabel,
        public readonly string $humanRole,
        public readonly string $defaultRegister,
        public readonly string $questionStyle,
        public readonly string $optionStyle,
        public readonly string $offerNamingStyle,
        public readonly string $inventoryExposurePolicy,
        public readonly string $noAvailabilityPolicy,
        public readonly array $vocabularyHints = [],
        public readonly array $customerFacingDescriptors = [],
        public readonly array $specialNotes = [],
    ) {}

    public function hidesInternalResourceNamesByDefault(): bool
    {
        return in_array($this->inventoryExposurePolicy, [
            'hide_internal_resources',
            'show_only_customer_safe_descriptors',
        ], true);
    }

    public function toPromptBlock(): string
    {
        $lines = [
            "SECTOR: {$this->sectorLabel}",
            "ROL HUMANO A IMITAR: {$this->humanRole}",
            "REGISTRO BASE: {$this->defaultRegister}",
            "ESTILO DE PREGUNTA: {$this->questionStyle}",
            "ESTILO DE OPCIONES: {$this->optionStyle}",
            "CÓMO NOMBRAR LA OFERTA: {$this->offerNamingStyle}",
            "POLÍTICA DE EXPOSICIÓN DE INVENTARIO: {$this->inventoryExposurePolicy}",
            "POLÍTICA SIN DISPONIBILIDAD: {$this->noAvailabilityPolicy}",
        ];

        if ($this->customerFacingDescriptors !== []) {
            $lines[] = 'DESCRIPTORES ORIENTADOS AL CLIENTE: '.implode(', ', $this->customerFacingDescriptors);
        }

        if ($this->vocabularyHints !== []) {
            $lines[] = 'VOCABULARIO O EXPRESIONES PREFERIDAS: '.implode(', ', $this->vocabularyHints);
        }

        if ($this->specialNotes !== []) {
            $lines[] = 'NOTAS ESPECIALES: '.implode(' | ', $this->specialNotes);
        }

        return implode("\n", $lines);
    }
}
