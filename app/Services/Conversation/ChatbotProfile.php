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
        public readonly string $personality,
        public readonly ?string $systemRules,
        public readonly ?array $requiredFieldsOverrides,
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
        $lines = ["PERSONALIDAD: {$this->personality}"];

        if ($this->systemRules !== null && trim($this->systemRules) !== '') {
            $lines[] = "\nREGLAS DEL NEGOCIO:\n{$this->systemRules}";
        }

        return implode("\n", $lines);
    }
}
