<?php

namespace App\Services\Conversation;

/**
 * Decides what data is needed for a tool, combining system defaults with business overrides.
 * Replaces and supersedes ChatToolRequirements for use outside chat test.
 */
class ConversationRequirementsResolver
{
    private const DEFAULTS = [
        'list_bookable_services' => ['negocio_id'],
        'get_service_details' => ['negocio_id', 'servicio_id'],
        'check_business_hours' => ['negocio_id'],
        'search_availability' => ['negocio_id', 'servicio_id', 'fecha', 'numero_personas'],
        'create_quote' => ['negocio_id', 'servicio_id', 'numero_personas'],
        'get_cancellation_policy' => ['negocio_id'],
        'get_arrival_instructions' => ['negocio_id'],
    ];

    /** Tools that require explicit confirmation before execution */
    private const REQUIRES_CONFIRMATION = [
        'create_booking',
        'cancel_booking',
        'modify_booking',
    ];

    public function requiredFieldsFor(string $tool, ?ChatbotProfile $profile = null): array
    {
        if ($profile !== null) {
            $override = $profile->requiredFieldsFor($tool);
            if ($override !== null) {
                return $override;
            }
        }

        return self::DEFAULTS[$tool] ?? [];
    }

    public function missingFields(string $tool, array $params, ?ChatbotProfile $profile = null): array
    {
        $missing = [];

        foreach ($this->requiredFieldsFor($tool, $profile) as $field) {
            if (! isset($params[$field]) || $params[$field] === null || $params[$field] === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    public function canExecute(string $tool, array $params, ?ChatbotProfile $profile = null): bool
    {
        return empty($this->missingFields($tool, $params, $profile));
    }

    public function requiresConfirmation(string $tool): bool
    {
        return in_array($tool, self::REQUIRES_CONFIRMATION, true);
    }
}
