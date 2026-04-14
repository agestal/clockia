<?php

namespace App\Services\Conversation;

/**
 * Immutable value object holding the chatbot profile for a business.
 * Used by all conversation layer services.
 */
class ChatbotProfile
{
    public function __construct(
        public readonly int $negocioId,
        public readonly string $negocioNombre,
        public readonly ?string $tipoNegocioNombre,
        public readonly string $personality,
        public readonly ?string $systemRules,
        public readonly ?array $requiredFieldsOverrides,
        public readonly ?string $descripcionPublica,
        public readonly ?string $direccion,
        public readonly ?string $urlPublica,
        public readonly ?string $telefono,
        public readonly ?string $email,
        public readonly ?string $zonaHoraria,
        public readonly ?string $politicaCancelacion,
        public readonly ?int $horasMinimasCancelacion,
        public readonly ?bool $permiteModificacion,
        public readonly ?int $maxRecursosCombinables,
    ) {}

    public function requiredFieldsFor(string $toolName): ?array
    {
        if (! is_array($this->requiredFieldsOverrides) || ! isset($this->requiredFieldsOverrides[$toolName])) {
            return null;
        }

        return $this->requiredFieldsOverrides[$toolName];
    }

    public function toPromptBlock(): string
    {
        $lines = [
            "NEGOCIO: {$this->negocioNombre}",
            'TIPO DE NEGOCIO: '.($this->tipoNegocioNombre ?: 'No especificado'),
            "PERSONALIDAD: {$this->personality}",
            'OBJETIVO PRINCIPAL: Resolver la intención real del cliente, guiar la conversación con naturalidad y no inventar nunca datos, disponibilidad ni acciones no ejecutadas.',
        ];

        if ($this->zonaHoraria !== null && trim($this->zonaHoraria) !== '') {
            $lines[] = "ZONA HORARIA: {$this->zonaHoraria}";
        }

        if ($this->descripcionPublica !== null && trim($this->descripcionPublica) !== '') {
            $lines[] = "\nDESCRIPCIÓN PÚBLICA:\n{$this->descripcionPublica}";
        }

        $publicDetails = [];

        if ($this->direccion !== null && trim($this->direccion) !== '') {
            $publicDetails[] = "Dirección: {$this->direccion}";
        }

        if ($this->urlPublica !== null && trim($this->urlPublica) !== '') {
            $publicDetails[] = "URL pública: {$this->urlPublica}";
        }

        if ($this->telefono !== null && trim($this->telefono) !== '') {
            $publicDetails[] = "Teléfono: {$this->telefono}";
        }

        if ($this->email !== null && trim($this->email) !== '') {
            $publicDetails[] = "Email: {$this->email}";
        }

        if ($publicDetails !== []) {
            $lines[] = "\nDATOS PÚBLICOS RELEVANTES:\n".implode("\n", $publicDetails);
        }

        $policyLines = [];

        if ($this->politicaCancelacion !== null && trim($this->politicaCancelacion) !== '') {
            $policyLines[] = "Política de cancelación: {$this->politicaCancelacion}";
        }

        if ($this->horasMinimasCancelacion !== null) {
            $policyLines[] = "Horas mínimas de cancelación: {$this->horasMinimasCancelacion}";
        }

        if ($this->permiteModificacion !== null) {
            $policyLines[] = 'Permite modificación: '.($this->permiteModificacion ? 'sí' : 'no');
        }

        if ($this->maxRecursosCombinables !== null) {
            $policyLines[] = "Máximo de recursos combinables: {$this->maxRecursosCombinables}";
        }

        if ($policyLines !== []) {
            $lines[] = "\nPOLÍTICAS Y LÍMITES RELEVANTES:\n".implode("\n", $policyLines);
        }

        if ($this->systemRules !== null && trim($this->systemRules) !== '') {
            $lines[] = "\nPROMPT EDITABLE DEL NEGOCIO / INSTRUCCIONES MAESTRAS:\n{$this->systemRules}";
        }

        return implode("\n", $lines);
    }
}
