<?php

namespace App\Services\Chat;

use App\Models\Negocio;

class ChatToolRequirements
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

    private const FIELD_LABELS = [
        'negocio_id' => 'el negocio',
        'servicio_id' => 'qué servicio te interesa',
        'fecha' => 'para qué día',
        'numero_personas' => 'para cuántas personas',
    ];

    public function requiredFieldsFor(string $toolName, ?Negocio $negocio = null): array
    {
        if ($negocio !== null) {
            $override = $negocio->chatRequiredFieldsFor($toolName);
            if ($override !== null) {
                return $override;
            }
        }

        return self::DEFAULTS[$toolName] ?? [];
    }

    public function missingFields(string $toolName, array $params, ?Negocio $negocio = null): array
    {
        $required = $this->requiredFieldsFor($toolName, $negocio);
        $missing = [];

        foreach ($required as $field) {
            if (! isset($params[$field]) || $params[$field] === null || $params[$field] === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    public function canExecute(string $toolName, array $params, ?Negocio $negocio = null): bool
    {
        return empty($this->missingFields($toolName, $params, $negocio));
    }

    public function buildClarificationResponse(array $missingFields, ?Negocio $negocio = null): string
    {
        // Filter out negocio_id since the system always provides it
        $userMissing = array_values(array_filter($missingFields, fn ($f) => $f !== 'negocio_id'));

        if (empty($userMissing)) {
            return 'Hay un problema con la configuración. Contacta con el negocio.';
        }

        $labels = array_map(fn ($f) => self::FIELD_LABELS[$f] ?? $f, $userMissing);

        if (count($labels) === 1) {
            return "¿{$this->ucfirst($labels[0])}?";
        }

        $last = array_pop($labels);

        return '¿'.implode(', ', array_map(fn ($l) => $this->ucfirst($l), $labels)).' y '.$last.'?';
    }

    private function ucfirst(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($s, 1, null, 'UTF-8');
    }
}
