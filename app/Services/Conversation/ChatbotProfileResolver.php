<?php

namespace App\Services\Conversation;

use App\Models\Negocio;

/**
 * Loads and resolves the complete chatbot profile for a business.
 * This is the single source of truth for how the bot should behave per negocio.
 */
class ChatbotProfileResolver
{
    private const DEFAULT_PERSONALITY = 'Amable, profesional y conciso. Trata al cliente de usted con cercanía.';

    public function resolve(Negocio $negocio): ChatbotProfile
    {
        return new ChatbotProfile(
            negocioId: $negocio->id,
            negocioNombre: $negocio->nombre,
            personality: $this->resolvePersonality($negocio),
            systemRules: $negocio->chat_system_rules,
            requiredFieldsOverrides: $negocio->chat_required_fields,
        );
    }

    private function resolvePersonality(Negocio $negocio): string
    {
        $p = $negocio->chat_personality;

        return ($p !== null && trim($p) !== '') ? trim($p) : self::DEFAULT_PERSONALITY;
    }
}
