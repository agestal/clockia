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
        $negocio->loadMissing('tipoNegocio:id,nombre');

        return new ChatbotProfile(
            negocioId: $negocio->id,
            negocioNombre: $negocio->nombre,
            tipoNegocioNombre: $negocio->tipoNegocio?->nombre,
            personality: $this->resolvePersonality($negocio),
            systemRules: $negocio->chat_system_rules,
            requiredFieldsOverrides: $negocio->chat_required_fields,
            descripcionPublica: $negocio->descripcion_publica,
            direccion: $negocio->direccion,
            urlPublica: $negocio->url_publica,
            telefono: $negocio->telefono,
            email: $negocio->email,
            zonaHoraria: $negocio->zona_horaria,
            politicaCancelacion: $negocio->politica_cancelacion,
            horasMinimasCancelacion: $negocio->horas_minimas_cancelacion,
            permiteModificacion: $negocio->permite_modificacion,
            maxRecursosCombinables: $negocio->max_recursos_combinables,
        );
    }

    private function resolvePersonality(Negocio $negocio): string
    {
        $p = $negocio->chat_personality;

        return ($p !== null && trim($p) !== '') ? trim($p) : self::DEFAULT_PERSONALITY;
    }
}
