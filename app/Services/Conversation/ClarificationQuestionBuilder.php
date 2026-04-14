<?php

namespace App\Services\Conversation;

/**
 * Builds human-friendly, warm clarification questions when data is missing.
 * Deterministic — does not use LLM.
 */
class ClarificationQuestionBuilder
{
    private const FIELD_QUESTIONS = [
        'servicio_id' => 'qué servicio te interesa',
        'fecha' => 'para qué día sería',
        'numero_personas' => 'para cuántas personas',
        'hora_inicio' => 'a qué hora sería',
        'contact_name' => 'a nombre de quién dejamos la reserva',
        'contact_phone' => 'qué teléfono de contacto podemos usar',
        'contact_email' => 'qué email de contacto podemos usar',
        'document_type' => 'qué tipo de documento necesitas aportar',
        'document_value' => 'cuál es el número o referencia del documento',
    ];

    private const SINGLE_FIELD_TEMPLATES = [
        'servicio_id' => '¿Qué servicio te interesa? 😊',
        'fecha' => '¿Para qué día sería? 😊',
        'numero_personas' => '¿Para cuántas personas sería? 😊',
        'hora_inicio' => '¿A qué hora le viene bien? 😊',
        'contact_name' => '¿A nombre de quién dejamos la reserva? 😊',
        'contact_phone' => '¿Qué teléfono de contacto podemos usar para la reserva? 😊',
        'contact_email' => '¿Qué email de contacto podemos usar? 😊',
        'document_type' => '¿Qué tipo de documento necesita aportar para esta reserva? 😊',
        'document_value' => '¿Cuál es el número o referencia del documento? 😊',
    ];

    public function build(array $missingFields): string
    {
        $userFields = array_values(array_filter($missingFields, fn ($f) => $f !== 'negocio_id'));

        if (empty($userFields)) {
            return 'Hay un problema con la configuración. Contacta con el negocio.';
        }

        if (count($userFields) === 1) {
            return self::SINGLE_FIELD_TEMPLATES[$userFields[0]] ?? '¿'.ucfirst(self::FIELD_QUESTIONS[$userFields[0]] ?? $userFields[0]).'?';
        }

        $labels = array_map(fn ($f) => self::FIELD_QUESTIONS[$f] ?? $f, $userFields);
        $last = array_pop($labels);

        return 'Perfecto 😊 ¿'.implode(', ', array_map('ucfirst', $labels)).' y '.$last.'?';
    }
}
