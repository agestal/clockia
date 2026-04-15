<?php

namespace App\Services\Conversation;

class ConversationStatePatcher
{
    public function apply(ConversationState $state, array $patch): ConversationState
    {
        if ($patch === []) {
            return $state;
        }

        $normalized = $this->normalizePatch($patch);

        if (array_key_exists('servicio_id', $normalized)) {
            $state->servicioId = $this->toNullableInt($normalized['servicio_id']);
        }

        if (array_key_exists('servicio_nombre', $normalized)) {
            $state->servicioNombre = $this->toNullableString($normalized['servicio_nombre']);
        }

        if (array_key_exists('nivel_conocimiento_usuario', $normalized)) {
            $state->nivelConocimientoUsuario = $this->toNullableString($normalized['nivel_conocimiento_usuario']);
        }

        if (array_key_exists('fase_conversacional', $normalized)) {
            $state->faseConversacional = $this->toNullableString($normalized['fase_conversacional']);
        }

        if (array_key_exists('fecha', $normalized)) {
            $state->fecha = $this->toNullableString($normalized['fecha']);
        }

        if (array_key_exists('numero_personas', $normalized)) {
            $state->numeroPersonas = $this->toNullableInt($normalized['numero_personas']);
        }

        if (array_key_exists('hora_preferida', $normalized)) {
            $state->horaPreferida = $this->toNullableString($normalized['hora_preferida']);
        }

        if (array_key_exists('contact_name', $normalized)) {
            $state->contactName = $this->toNullableString($normalized['contact_name']);
        }

        if (array_key_exists('contact_phone', $normalized)) {
            $state->contactPhone = $this->toNullableString($normalized['contact_phone']);
        }

        if (array_key_exists('contact_email', $normalized)) {
            $state->contactEmail = $this->toNullableString($normalized['contact_email']);
        }

        if (array_key_exists('document_type', $normalized)) {
            $state->documentType = $this->toNullableString($normalized['document_type']);
        }

        if (array_key_exists('document_value', $normalized)) {
            $state->documentValue = $this->toNullableString($normalized['document_value']);
        }

        if (array_key_exists('ultima_intencion', $normalized)) {
            $state->ultimaIntencion = $this->toNullableString($normalized['ultima_intencion']);
        }

        if (array_key_exists('fecha_es_pasada', $normalized)) {
            $state->fechaEsPasada = (bool) $normalized['fecha_es_pasada'];
        }

        if (array_key_exists('necesita_confirmacion', $normalized)) {
            $state->necesitaConfirmacion = (bool) $normalized['necesita_confirmacion'];
        }

        if (array_key_exists('datos_confirmados', $normalized) && is_array($normalized['datos_confirmados'])) {
            foreach ($normalized['datos_confirmados'] as $key => $value) {
                if ($value === null || $value === '') {
                    unset($state->datosConfirmados[$key]);
                    continue;
                }

                $state->datosConfirmados[$key] = $value;
            }
        }

        if (array_key_exists('ultima_propuesta', $normalized)) {
            $state->ultimaPropuesta = is_array($normalized['ultima_propuesta'])
                ? $this->normalizeProposal($normalized['ultima_propuesta'])
                : null;
        }

        return $state;
    }

    private function normalizePatch(array $patch): array
    {
        $normalized = $patch;

        $aliases = [
            'service_id' => 'servicio_id',
            'service_name' => 'servicio_nombre',
            'user_familiarity' => 'nivel_conocimiento_usuario',
            'knowledge_level' => 'nivel_conocimiento_usuario',
            'conversation_stage' => 'fase_conversacional',
            'date' => 'fecha',
            'party_size' => 'numero_personas',
            'preferred_time' => 'hora_preferida',
            'start_time' => 'hora_preferida',
            'intent' => 'ultima_intencion',
            'contact_person_name' => 'contact_name',
            'customer_name' => 'contact_name',
            'responsible_name' => 'contact_name',
            'reservation_holder_name' => 'contact_name',
            'contact_person_phone' => 'contact_phone',
            'customer_phone' => 'contact_phone',
            'telephone' => 'contact_phone',
            'phone' => 'contact_phone',
            'contact_person_email' => 'contact_email',
            'customer_email' => 'contact_email',
            'email' => 'contact_email',
            'document' => 'document_value',
            'date_is_past' => 'fecha_es_pasada',
            'awaiting_confirmation' => 'necesita_confirmacion',
            'confirmed_facts' => 'datos_confirmados',
            'last_proposal' => 'ultima_propuesta',
        ];

        foreach ($aliases as $from => $to) {
            if (array_key_exists($from, $normalized) && ! array_key_exists($to, $normalized)) {
                $normalized[$to] = $normalized[$from];
            }
        }

        $datosConfirmados = is_array($normalized['datos_confirmados'] ?? null)
            ? $normalized['datos_confirmados']
            : [];

        foreach ([
            'preferred_zone',
            'zone',
            'zona',
            'numero_personas',
            'party_size',
            'contact_name',
            'contact_phone',
            'contact_email',
            'document_type',
            'document_value',
        ] as $key) {
            if (array_key_exists($key, $normalized)) {
                if ($normalized[$key] === null || $normalized[$key] === '') {
                    unset($datosConfirmados[$key]);
                } else {
                    $datosConfirmados[$key] = $normalized[$key];
                }
            }
        }

        if ($datosConfirmados !== []) {
            $normalized['datos_confirmados'] = $datosConfirmados;
        }

        return $normalized;
    }

    private function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeProposal(array $proposal): array
    {
        $aliases = [
            'start_time' => 'hora_inicio',
            'end_time' => 'hora_fin',
            'resource_id' => 'recurso_id',
            'resource_ids' => 'recurso_ids',
        ];

        foreach ($aliases as $from => $to) {
            if (array_key_exists($from, $proposal) && ! array_key_exists($to, $proposal)) {
                $proposal[$to] = $proposal[$from];
            }
        }

        return $proposal;
    }
}
