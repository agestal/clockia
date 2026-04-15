<?php

namespace App\Services\Chat;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\Conversation\ConversationBehaviorProfile;
use App\Services\Conversation\ConversationBehaviorProfileResolver;
use App\Services\Conversation\ChatbotProfileResolver;
use App\Services\Conversation\ConversationState;
use App\Services\Conversation\ConversationStatePatcher;
use App\Services\Conversation\ConversationUserMessageNormalizer;
use App\Services\Conversation\LlmTurnDecision;
use App\Services\Conversation\LlmTurnEngine;
use App\Services\Conversation\TurnPromptBuilder;
use App\Services\Conversation\ToolCall;
use Illuminate\Support\Carbon;

class LlmFirstChatOrchestrator
{
    public function __construct(
        private readonly ConversationToolClientResolver $toolClientResolver,
        private readonly ChatbotProfileResolver $profileResolver,
        private readonly ConversationBehaviorProfileResolver $behaviorProfileResolver,
        private readonly TurnPromptBuilder $promptBuilder,
        private readonly LlmTurnEngine $turnEngine,
        private readonly ConversationStatePatcher $statePatcher,
        private readonly ConversationUserMessageNormalizer $messageNormalizer,
    ) {}

    public function handle(
        string $message,
        int $negocioId,
        array $context = [],
        ?ConversationState $state = null,
        string $toolMode = 'mcp',
    ): array {
        $negocio = Negocio::findOrFail($negocioId);
        $profile = $this->profileResolver->resolve($negocio);
        $behaviorProfile = $this->behaviorProfileResolver->resolve($negocio);
        $state ??= new ConversationState(negocioId: $negocioId);
        $toolClient = $this->toolClientResolver->resolve($toolMode);
        $state = $this->applyMessageFactsToState($state, $message);
        $normalizedMessage = $this->messageNormalizer->normalize($message);
        $llmMessage = $normalizedMessage === trim($message)
            ? $message
            : "Mensaje original del usuario:\n{$message}\n\nVersión normalizada para facilitar la interpretación:\n{$normalizedMessage}";

        $services = Servicio::query()
            ->where('negocio_id', $negocioId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'duracion_minutos',
                'precio_base',
                'precio_menor',
                'numero_personas_minimo',
                'numero_personas_maximo',
                'requiere_pago',
                'notas_publicas',
            ])
            ->map(fn (Servicio $servicio) => [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'duracion_minutos' => $servicio->duracion_minutos,
                'precio_base' => $servicio->precio_base,
                'precio_menor' => $servicio->precio_menor,
                'numero_personas_minimo' => $servicio->numero_personas_minimo,
                'numero_personas_maximo' => $servicio->numero_personas_maximo,
                'requiere_pago' => (bool) $servicio->requiere_pago,
                'notas_publicas' => $servicio->notas_publicas,
            ])
            ->values()
            ->all();
        $now = Carbon::now($negocio->zona_horaria ?: config('app.timezone'));

        $debug = [
            'engine' => 'llm_first',
            'tool_transport' => $toolClient->transportName(),
            'timestamp' => $now->toIso8601String(),
            'negocio_id' => $negocioId,
            'behavior_profile' => [
                'sector_key' => $behaviorProfile->sectorKey,
                'human_role' => $behaviorProfile->humanRole,
                'inventory_exposure_policy' => $behaviorProfile->inventoryExposurePolicy,
            ],
            'normalized_message' => $normalizedMessage,
            'state_before' => $state->toArray(),
        ];

        try {
            $tools = $toolClient->listTools();
        } catch (\Throwable $e) {
            $debug['tool_list_error'] = $e->getMessage();

            return $this->buildErrorResult(
                'No he podido cargar las herramientas disponibles en este momento. Inténtelo de nuevo en unos segundos.',
                $debug,
                $state,
            );
        }

        $debug['tool_names'] = array_keys($tools);

        $initialPrompt = $this->promptBuilder->buildInitialPrompt($profile, $behaviorProfile, $state, $context, $tools, $services, $now);
        $decision = $this->turnEngine->decide($initialPrompt, $llmMessage);
        $state = $this->statePatcher->apply($state, $decision->statePatch);

        $debug['first_decision'] = $decision->raw;
        $debug['state_after_first_decision'] = $state->toArray();

        if ($decision->toolCall === null) {
            return $this->buildResultFromDecision($decision, null, null, null, $debug, $state);
        }

        $toolValidation = $this->normalizeToolCall($decision->toolCall, $tools, $state, $profile);
        $toolCall = $toolValidation['tool_call'];

        if ($toolCall === null) {
            $debug['tool_rejected'] = true;
            $debug['missing_fields'] = $toolValidation['missing_fields'];

            return $this->buildResultFromDecision(
                new LlmTurnDecision(
                    assistantMessage: $decision->assistantMessage !== ''
                        ? $decision->assistantMessage
                        : $this->fallbackClarificationMessage($toolValidation['missing_fields']),
                    statePatch: $decision->statePatch,
                    toolCall: null,
                    needsUserInput: true,
                    conversationStatus: 'clarify',
                    raw: $decision->raw,
                ),
                null,
                null,
                null,
                $debug,
                $state,
            );
        }

        try {
            $toolResult = $toolClient->executeTool($toolCall->name, $toolCall->arguments);
        } catch (\Throwable $e) {
            $debug['executed_tool'] = $toolCall->toArray();
            $debug['tool_execution_error'] = $e->getMessage();

            return $this->buildErrorResult(
                'He intentado consultar la información, pero la herramienta ha fallado. Si quiere, podemos volver a intentarlo.',
                $debug,
                $state,
                $toolCall->name,
                $toolCall->arguments,
            );
        }

        $debug['executed_tool'] = $toolCall->toArray();
        $debug['tool_success'] = (bool) ($toolResult['success'] ?? false);

        $toolPrompt = $this->promptBuilder->buildToolResultPrompt(
            $profile,
            $behaviorProfile,
            $state,
            $context,
            $tools,
            $services,
            $toolCall->toArray(),
            $this->sanitizeToolResultForLlm($toolResult, $behaviorProfile),
            $now,
        );

        $finalDecision = $this->turnEngine->decide($toolPrompt, $llmMessage);
        $finalDecision = new LlmTurnDecision(
            assistantMessage: $finalDecision->assistantMessage !== '' ? $finalDecision->assistantMessage : $decision->assistantMessage,
            statePatch: $finalDecision->statePatch,
            toolCall: null,
            needsUserInput: $finalDecision->needsUserInput,
            conversationStatus: $finalDecision->conversationStatus,
            raw: $finalDecision->raw,
        );

        $state = $this->statePatcher->apply($state, $finalDecision->statePatch);
        $debug['final_decision'] = $finalDecision->raw;
        $debug['state_after_final_decision'] = $state->toArray();

        return $this->buildResultFromDecision($finalDecision, $toolCall->name, $toolCall->arguments, $toolResult, $debug, $state);
    }

    private function buildResultFromDecision(
        LlmTurnDecision $decision,
        ?string $tool,
        ?array $params,
        ?array $toolResult,
        array $debug,
        ConversationState $state,
    ): array {
        $mode = match (true) {
            $tool !== null => 'tool_result',
            $decision->conversationStatus === 'error' => 'error',
            $decision->needsUserInput || $decision->conversationStatus === 'clarify' => 'clarification',
            default => 'respond',
        };

        return [
            'mode' => $mode,
            'tool' => $tool,
            'params' => $params,
            'missing_fields' => [],
            'tool_result' => $toolResult,
            'response' => $decision->assistantMessage,
            'debug' => $debug,
            'state' => $state->toArray(),
        ];
    }

    private function buildErrorResult(
        string $message,
        array $debug,
        ConversationState $state,
        ?string $tool = null,
        ?array $params = null,
    ): array {
        return [
            'mode' => 'error',
            'tool' => $tool,
            'params' => $params,
            'missing_fields' => [],
            'tool_result' => null,
            'response' => $message,
            'debug' => $debug,
            'state' => $state->toArray(),
        ];
    }

    private function normalizeToolCall(?ToolCall $toolCall, array $tools, ConversationState $state, ?\App\Services\Conversation\ChatbotProfile $profile = null): array
    {
        if ($toolCall === null || ! isset($tools[$toolCall->name])) {
            return ['tool_call' => null, 'missing_fields' => []];
        }

        $arguments = $toolCall->arguments;
        $aliases = [
            'business_id' => 'negocio_id',
            'service_id' => 'servicio_id',
            'date' => 'fecha',
            'party_size' => 'numero_personas',
            'start_time' => 'hora_inicio',
            'end_time' => 'hora_fin',
            'resource_id' => 'recurso_id',
            'resource_ids' => 'recurso_ids',
            'customer_name' => 'contact_name',
            'responsible_name' => 'contact_name',
            'phone' => 'contact_phone',
            'telephone' => 'contact_phone',
            'customer_phone' => 'contact_phone',
            'email' => 'contact_email',
            'customer_email' => 'contact_email',
        ];

        foreach ($aliases as $from => $to) {
            if (array_key_exists($from, $arguments) && ! array_key_exists($to, $arguments)) {
                $arguments[$to] = $arguments[$from];
            }
        }

        $arguments['negocio_id'] = $state->negocioId;

        if (! empty($state->servicioId) && ! array_key_exists('servicio_id', $arguments)) {
            $arguments['servicio_id'] = $state->servicioId;
        }

        if (! empty($state->fecha) && ! array_key_exists('fecha', $arguments)) {
            $arguments['fecha'] = $state->fecha;
        }

        if (! empty($state->numeroPersonas) && ! array_key_exists('numero_personas', $arguments)) {
            $arguments['numero_personas'] = $state->numeroPersonas;
        }

        if (! empty($state->horaPreferida) && ! array_key_exists('hora_inicio', $arguments)) {
            $arguments['hora_inicio'] = $state->horaPreferida;
        }

        if (! empty($state->contactName) && ! array_key_exists('contact_name', $arguments)) {
            $arguments['contact_name'] = $state->contactName;
        }

        if (! empty($state->contactPhone) && ! array_key_exists('contact_phone', $arguments)) {
            $arguments['contact_phone'] = $state->contactPhone;
        }

        if (! empty($state->contactEmail) && ! array_key_exists('contact_email', $arguments)) {
            $arguments['contact_email'] = $state->contactEmail;
        }

        if (! empty($state->documentType) && ! array_key_exists('document_type', $arguments)) {
            $arguments['document_type'] = $state->documentType;
        }

        if (! empty($state->documentValue) && ! array_key_exists('document_value', $arguments)) {
            $arguments['document_value'] = $state->documentValue;
        }

        if (is_array($state->ultimaPropuesta)) {
            foreach (['slot_key', 'hora_inicio', 'hora_fin', 'recurso_id', 'recurso_ids', 'sesion_id'] as $key) {
                if (
                    ! array_key_exists($key, $arguments)
                    && array_key_exists($key, $state->ultimaPropuesta)
                    && $state->ultimaPropuesta[$key] !== null
                    && $state->ultimaPropuesta[$key] !== ''
                ) {
                    $arguments[$key] = $state->ultimaPropuesta[$key];
                }
            }
        }

        $schema = $tools[$toolCall->name]['input_schema'] ?? [];
        $required = $profile?->requiredFieldsFor($toolCall->name) ?? ($schema['required'] ?? []);
        $missingFields = [];

        foreach ($required as $requiredKey) {
            if (! array_key_exists($requiredKey, $arguments) || $arguments[$requiredKey] === null || $arguments[$requiredKey] === '') {
                $missingFields[] = $requiredKey;
            }
        }

        if ($missingFields !== []) {
            return ['tool_call' => null, 'missing_fields' => $missingFields];
        }

        return [
            'tool_call' => new ToolCall($toolCall->name, $arguments),
            'missing_fields' => [],
        ];
    }

    private function sanitizeToolResultForLlm(array $toolResult, ConversationBehaviorProfile $behaviorProfile): array
    {
        $toolResult['tool_result_explanation'] = is_array($toolResult['tool_result_explanation'] ?? null)
            ? $toolResult['tool_result_explanation']
            : [];

        $toolResult['data']['llm_presentation_policy'] = [
            'sector_key' => $behaviorProfile->sectorKey,
            'inventory_exposure_policy' => $behaviorProfile->inventoryExposurePolicy,
            'no_availability_policy' => $behaviorProfile->noAvailabilityPolicy,
            'customer_facing_descriptors' => $behaviorProfile->customerFacingDescriptors,
            'vocabulary_hints' => $behaviorProfile->vocabularyHints,
        ];

        if (isset($toolResult['data']['servicios']) && is_array($toolResult['data']['servicios'])) {
            $toolResult = $this->sanitizeServiceCatalogForLlm($toolResult, $behaviorProfile);
        }

        if (isset($toolResult['data']['servicio']) && is_array($toolResult['data']['servicio'])) {
            $toolResult = $this->sanitizeServiceDetailForLlm($toolResult, $behaviorProfile);
        }

        if (isset($toolResult['data']['booking']) && is_array($toolResult['data']['booking'])) {
            $toolResult = $this->sanitizeBookingForLlm($toolResult, $behaviorProfile);
        }

        if (($toolResult['data']['availability_mode'] ?? null) === 'simple') {
            $toolResult['data']['llm_no_availability_guidance'] = $this->buildNoAvailabilityGuidance($behaviorProfile, true);
            $toolResult['tool_result_explanation']['customer_safe_status'] = 'simple_mode_followup_required';

            return $toolResult;
        }

        if (($toolResult['data']['availability_mode'] ?? null) !== 'precise' || ! isset($toolResult['data']['slots']) || ! is_array($toolResult['data']['slots'])) {
            return $toolResult;
        }

        $grouped = [];
        $sanitizedSlots = [];

        foreach ($toolResult['data']['slots'] as $slot) {
            $start = $slot['hora_inicio'] ?? null;
            $end = $slot['hora_fin'] ?? null;

            if ($start === null || $end === null) {
                continue;
            }

            $descriptor = $this->inferCustomerSafeDescriptor($slot, $behaviorProfile);
            $publicNote = $this->inferCustomerSafeSlotNote($slot, $behaviorProfile);
            $publicLabel = $this->buildCustomerSafeSlotLabel($slot, $descriptor, $behaviorProfile);
            $resourceLabel = $slot['recurso_nombre'] ?? $slot['recurso_label'] ?? $slot['label'] ?? null;
            $key = implode('|', [$start, $end, $descriptor ?? '', $publicNote ?? '', $slot['booking_time_mode'] ?? 'fixed_slot']);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'hora_inicio' => $start,
                    'hora_fin' => $end,
                    'booking_time_mode' => $slot['booking_time_mode'] ?? 'fixed_slot',
                    'accepts_start_time_within_slot' => (bool) ($slot['accepts_start_time_within_slot'] ?? false),
                    'resource_labels' => [],
                    'customer_descriptors' => [],
                    'public_label' => $publicLabel,
                    'public_note' => $publicNote,
                    'remaining_capacity' => $this->toNullableInt($slot['aforo_restante'] ?? null),
                    'remaining_capacity_label' => $this->buildRemainingCapacityLabel($slot, $behaviorProfile),
                    'slot_count' => 0,
                ];
            }

            if ($resourceLabel !== null && ! in_array($resourceLabel, $grouped[$key]['resource_labels'], true)) {
                $grouped[$key]['resource_labels'][] = $resourceLabel;
            }

            if ($descriptor !== null && ! in_array($descriptor, $grouped[$key]['customer_descriptors'], true)) {
                $grouped[$key]['customer_descriptors'][] = $descriptor;
            }

            $remainingCapacity = $this->toNullableInt($slot['aforo_restante'] ?? null);
            if ($remainingCapacity !== null) {
                $grouped[$key]['remaining_capacity'] = ($grouped[$key]['remaining_capacity'] ?? 0) + $remainingCapacity;
                $grouped[$key]['remaining_capacity_label'] = $this->buildRemainingCapacityLabelFromNumber(
                    $grouped[$key]['remaining_capacity'],
                    $behaviorProfile,
                );
            }

            $grouped[$key]['slot_count']++;

            $sanitizedSlot = $slot;

            if ($behaviorProfile->hidesInternalResourceNamesByDefault()) {
                unset($sanitizedSlot['recurso_nombre'], $sanitizedSlot['recurso_label'], $sanitizedSlot['label']);
            }

            if ($descriptor !== null) {
                $sanitizedSlot['customer_descriptor'] = $descriptor;
            }

            if ($publicLabel !== null) {
                $sanitizedSlot['public_label'] = $publicLabel;
            }

            if ($publicNote !== null) {
                $sanitizedSlot['public_note'] = $publicNote;
            }

            if (($remainingCapacityLabel = $this->buildRemainingCapacityLabel($slot, $behaviorProfile)) !== null) {
                $sanitizedSlot['remaining_capacity_label'] = $remainingCapacityLabel;
            }

            $sanitizedSlots[] = $sanitizedSlot;
        }

        foreach ($grouped as &$group) {
            if ($behaviorProfile->hidesInternalResourceNamesByDefault()) {
                unset($group['resource_labels']);
            }
        }

        $toolResult['data']['slots'] = $sanitizedSlots;
        $toolResult['data']['llm_slot_summary'] = array_values($grouped);
        $toolResult['data']['llm_customer_safe_options'] = array_values($grouped);
        $toolResult['data']['llm_reply_strategy'] = $this->buildAvailabilityReplyStrategy(
            (int) ($toolResult['data']['total_slots'] ?? 0),
            array_values($grouped),
            $behaviorProfile,
        );
        $toolResult['tool_result_explanation']['customer_safe_option_groups'] = array_values($grouped);
        $toolResult['tool_result_explanation']['public_summary'] = $this->buildAvailabilityPublicSummary(
            (int) ($toolResult['data']['total_slots'] ?? 0),
            array_values($grouped),
        );

        if (($toolResult['data']['total_slots'] ?? 0) === 0) {
            $toolResult['data']['llm_no_availability_guidance'] = $this->buildNoAvailabilityGuidance($behaviorProfile, false);
            $toolResult['tool_result_explanation']['customer_safe_status'] = 'no_availability';
        }

        return $toolResult;
    }

    private function sanitizeServiceCatalogForLlm(array $toolResult, ConversationBehaviorProfile $behaviorProfile): array
    {
        $toolResult['data']['llm_catalog_term'] = $this->resolveCatalogTerm($behaviorProfile);
        $services = collect($toolResult['data']['servicios']);

        $toolResult['data']['llm_customer_safe_services'] = $services
            ->map(function (array $service) use ($behaviorProfile) {
                return [
                    'id' => $service['id'] ?? null,
                    'name' => $service['nombre'] ?? null,
                    'description' => $service['descripcion'] ?? null,
                    'duration_label' => isset($service['duracion_minutos']) ? ((int) $service['duracion_minutos']).' minutos' : null,
                    'price_label' => isset($service['precio_base']) ? number_format((float) $service['precio_base'], 2, ',', '.').' €' : null,
                    'price_from_label' => isset($service['precio_menor']) && $service['precio_menor'] !== null
                        ? 'Desde '.number_format((float) $service['precio_menor'], 2, ',', '.').' €'
                        : null,
                    'group_size_label' => $this->buildPartySizeLabel($service),
                    'payment_required' => (bool) ($service['requiere_pago'] ?? false),
                    'public_notes' => $service['notas_publicas'] ?? null,
                    'commercial_hook' => $this->buildServiceCommercialHook($service, $behaviorProfile),
                ];
            })
            ->values()
            ->all();

        $durations = $services->pluck('duracion_minutos')->filter(fn ($value) => is_numeric($value))->map(fn ($value) => (int) $value);
        $prices = $services
            ->flatMap(fn (array $service) => [$service['precio_menor'] ?? null, $service['precio_base'] ?? null])
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value);
        $mins = $services->pluck('numero_personas_minimo')->filter(fn ($value) => is_numeric($value))->map(fn ($value) => (int) $value);
        $maxs = $services->pluck('numero_personas_maximo')->filter(fn ($value) => is_numeric($value))->map(fn ($value) => (int) $value);

        $toolResult['data']['llm_customer_safe_catalog_overview'] = [
            'services_count' => $services->count(),
            'duration_range_label' => $durations->isNotEmpty() ? $durations->min().' a '.$durations->max().' minutos' : null,
            'price_range_label' => $prices->isNotEmpty()
                ? number_format($prices->min(), 2, ',', '.').' a '.number_format($prices->max(), 2, ',', '.').' €'
                : null,
            'group_size_range_label' => ($mins->isNotEmpty() || $maxs->isNotEmpty())
                ? ($mins->isNotEmpty() ? 'desde '.$mins->min() : 'sin mínimo claro')
                    .' / '
                    .($maxs->isNotEmpty() ? 'hasta '.$maxs->max().' personas' : 'sin máximo claro')
                : null,
            'orientation_hint' => $behaviorProfile->sectorKey === 'winery'
                ? 'Si el usuario es novato, primero explica cómo suelen funcionar las experiencias de la bodega y luego entra en las opciones concretas.'
                : 'Usa este resumen para orientar antes de entrar a detalle si el usuario lo necesita.',
        ];

        $toolResult['data']['llm_explanation_plan'] = [
            'goal' => $behaviorProfile->sectorKey === 'winery'
                ? 'Orientar al cliente antes de pedirle que elija una experiencia concreta.'
                : 'Explicar la oferta de forma útil antes de pedir una elección concreta.',
            'steps' => [
                'Explica brevemente cómo funciona la experiencia o la oferta en términos generales.',
                'Resume duración, precio y tamaño de grupo cuando sean relevantes.',
                'Presenta las opciones concretas disponibles con nombre y una frase útil de cada una.',
                'Cierra con una pregunta que ayude a elegir según estilo o preferencia, no con una pregunta genérica vacía.',
            ],
            'avoid' => [
                'No pidas que elija una experiencia que aún no has nombrado.',
                'No conviertas la respuesta en un listado técnico plano.',
                'No empujes todavía el cierre de reserva si el usuario sigue en modo orientación.',
            ],
        ];

        return $toolResult;
    }

    private function sanitizeServiceDetailForLlm(array $toolResult, ConversationBehaviorProfile $behaviorProfile): array
    {
        $service = $toolResult['data']['servicio'];

        $toolResult['data']['llm_customer_safe_service_detail'] = [
            'name' => $service['nombre'] ?? null,
            'summary' => $service['notas_publicas'] ?? $service['descripcion'] ?? null,
            'duration_label' => isset($service['duracion_minutos']) ? ((int) $service['duracion_minutos']).' minutos' : null,
            'price_label' => isset($service['precio_base']) ? number_format((float) $service['precio_base'], 2, ',', '.').' €' : null,
            'price_from_label' => isset($service['precio_menor']) && $service['precio_menor'] !== null
                ? 'Desde '.number_format((float) $service['precio_menor'], 2, ',', '.').' €'
                : null,
            'group_size_label' => $this->buildPartySizeLabel($service),
            'languages_label' => $this->buildLanguagesLabel($service['idiomas'] ?? null),
            'meeting_point' => $service['punto_encuentro'] ?? null,
            'includes' => array_values(array_filter($service['incluye'] ?? [])),
            'excludes' => array_values(array_filter($service['no_incluye'] ?? [])),
            'commercial_hook' => $this->buildServiceCommercialHook($service, $behaviorProfile),
            'age_policy_label' => $this->buildAgePolicyLabel($service),
            'booking_hint' => $this->buildServiceBookingHint($service, $behaviorProfile),
        ];

        if (
            $behaviorProfile->hidesInternalResourceNamesByDefault()
            && isset($toolResult['data']['recursos'])
            && is_array($toolResult['data']['recursos'])
        ) {
            unset($toolResult['data']['recursos']);
        }

        return $toolResult;
    }

    private function sanitizeBookingForLlm(array $toolResult, ConversationBehaviorProfile $behaviorProfile): array
    {
        $booking = $toolResult['data']['booking'];

        if ($behaviorProfile->hidesInternalResourceNamesByDefault()) {
            unset($toolResult['data']['booking']['resource_name'], $toolResult['data']['booking']['resources']);
        }

        $confirmationSent = filled($booking['confirmation_email_sent_at'] ?? null);

        $toolResult['data']['llm_customer_safe_booking'] = [
            'locator' => $booking['locator'] ?? null,
            'service_name' => $booking['service_name'] ?? null,
            'date' => $booking['date'] ?? null,
            'start_time' => $booking['start_time'] ?? null,
            'end_time' => $booking['end_time'] ?? null,
            'party_size' => $booking['party_size'] ?? null,
            'status' => $booking['status'] ?? null,
            'contact_name' => data_get($booking, 'contact.name'),
            'confirmation_email_sent' => $confirmationSent,
            'confirmation_email_sent_at' => $booking['confirmation_email_sent_at'] ?? null,
            'internal_calendar_visible' => (bool) ($booking['internal_calendar_visible'] ?? false),
            'public_summary' => $this->buildBookingPublicSummary($booking, $confirmationSent),
        ];

        $toolResult['tool_result_explanation']['public_summary'] = $toolResult['data']['llm_customer_safe_booking']['public_summary'];

        return $toolResult;
    }

    private function buildNoAvailabilityGuidance(ConversationBehaviorProfile $behaviorProfile, bool $isSimpleMode): array
    {
        $alternativeDimensions = match ($behaviorProfile->sectorKey) {
            'winery' => ['otra sesión', 'otro día', 'otra experiencia'],
            'restaurant' => ['hora cercana', 'otra fecha', 'otra zona'],
            'hotel' => ['otras fechas', 'otra categoría', 'otra duración de estancia'],
            'appointment_based' => ['otra hora', 'otro día', 'otro profesional'],
            'coworking' => ['otra franja', 'otro espacio', 'otro día'],
            'gym' => ['otra clase', 'otra franja', 'otro día'],
            default => ['otra hora', 'otra fecha'],
        };

        return [
            'status' => 'no_availability',
            'simple_mode' => $isSimpleMode,
            'policy' => $behaviorProfile->noAvailabilityPolicy,
            'preferred_strategy' => 'Sé claro, evita sonar técnico y ofrece flexibilidad razonable si procede.',
            'alternative_dimensions' => $alternativeDimensions,
            'avoid_internal_details' => true,
        ];
    }

    private function resolveCatalogTerm(ConversationBehaviorProfile $behaviorProfile): string
    {
        return match ($behaviorProfile->sectorKey) {
            'winery' => 'experiencias',
            'restaurant' => 'lo que ofrecemos',
            'hotel' => 'tipos de estancia',
            'appointment_based' => 'servicios y citas',
            'coworking' => 'espacios y opciones',
            default => 'servicios',
        };
    }

    private function inferCustomerSafeDescriptor(array $slot, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        $resourceLabel = $slot['recurso_nombre'] ?? $slot['recurso_label'] ?? $slot['label'] ?? null;

        if ($resourceLabel === null || trim($resourceLabel) === '') {
            if ($behaviorProfile->sectorKey === 'winery' && ! empty($slot['es_sesion'])) {
                return 'sesión guiada';
            }

            return match ($behaviorProfile->sectorKey) {
                'winery' => 'experiencia disponible',
                default => null,
            };
        }

        $normalized = mb_strtolower(implode(' ', array_filter([
            (string) $resourceLabel,
            (string) ($slot['nombre_turno'] ?? ''),
            (string) ($slot['notas_publicas'] ?? ''),
        ])));

        return match ($behaviorProfile->sectorKey) {
            'winery' => match (true) {
                str_contains($normalized, 'marid') => 'cata con maridaje',
                str_contains($normalized, 'viñedo') || str_contains($normalized, 'vinedo') => 'visita y cata',
                str_contains($normalized, 'visita') => 'visita guiada',
                str_contains($normalized, 'premium') || str_contains($normalized, 'reserva') => 'experiencia premium',
                str_contains($normalized, 'comentad') || str_contains($normalized, 'cata') => 'cata comentada',
                str_contains($normalized, 'atardecer') => 'sesión al atardecer',
                ! empty($slot['es_sesion']) => 'sesión guiada',
                default => 'experiencia disponible',
            },
            'restaurant' => match (true) {
                str_contains($normalized, 'terraza') => 'terraza',
                str_contains($normalized, 'interior') => 'interior',
                str_contains($normalized, 'privada') || str_contains($normalized, 'sala') => 'sala privada',
                str_contains($normalized, 'grupo') => 'mesa para grupo',
                default => 'mesa disponible',
            },
            'hotel' => trim((string) preg_replace('/\b\d+\b/u', '', $resourceLabel)) ?: 'habitación disponible',
            'appointment_based' => match (true) {
                str_contains($normalized, 'cabina') => 'cabina disponible',
                str_contains($normalized, 'box') => 'box disponible',
                str_contains($normalized, 'profesional') => 'profesional disponible',
                default => 'cita disponible',
            },
            'coworking' => match (true) {
                str_contains($normalized, 'sala') => 'sala disponible',
                str_contains($normalized, 'puesto') => 'puesto disponible',
                default => 'espacio disponible',
            },
            'gym' => 'plaza disponible',
            default => null,
        };
    }

    private function inferCustomerSafeSlotNote(array $slot, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        $publicNote = trim((string) ($slot['notas_publicas'] ?? ''));

        if ($publicNote !== '') {
            return mb_substr($publicNote, 0, 140);
        }

        if ($behaviorProfile->sectorKey === 'winery' && ! empty($slot['es_sesion'])) {
            return 'Sesión con plazas limitadas para mantener una experiencia cuidada.';
        }

        return null;
    }

    private function buildCustomerSafeSlotLabel(array $slot, ?string $descriptor, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        if ($descriptor !== null) {
            return $descriptor;
        }

        return match ($behaviorProfile->sectorKey) {
            'winery' => ! empty($slot['es_sesion']) ? 'sesión disponible' : 'experiencia disponible',
            'restaurant' => 'opción disponible',
            default => null,
        };
    }

    private function buildRemainingCapacityLabel(array $slot, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        $remaining = $this->toNullableInt($slot['aforo_restante'] ?? null);

        return $this->buildRemainingCapacityLabelFromNumber($remaining, $behaviorProfile);
    }

    private function buildRemainingCapacityLabelFromNumber(?int $remaining, ConversationBehaviorProfile $behaviorProfile): ?string
    {

        if ($remaining === null || $remaining <= 0) {
            return null;
        }

        return match ($behaviorProfile->sectorKey) {
            'winery' => $remaining === 1 ? 'Queda 1 plaza.' : "Quedan {$remaining} plazas.",
            default => null,
        };
    }

    private function buildAvailabilityReplyStrategy(int $rawSlotCount, array $groupedOptions, ConversationBehaviorProfile $behaviorProfile): array
    {
        if ($rawSlotCount <= 0) {
            return [
                'presentation_mode' => 'no_availability',
                'should_offer_directly' => false,
                'should_hide_internal_inventory' => true,
                'customer_option_count' => 0,
                'note' => 'Explica que no hay disponibilidad con claridad y ofrece una alternativa razonable si existe.',
            ];
        }

        if (count($groupedOptions) === 1) {
            return [
                'presentation_mode' => 'single_direct_proposal',
                'should_offer_directly' => true,
                'should_hide_internal_inventory' => $behaviorProfile->hidesInternalResourceNamesByDefault(),
                'customer_option_count' => 1,
                'note' => 'Hay una sola opción realmente útil para el cliente. Propónla directamente.',
            ];
        }

        return [
            'presentation_mode' => 'grouped_choice',
            'should_offer_directly' => false,
            'should_hide_internal_inventory' => $behaviorProfile->hidesInternalResourceNamesByDefault(),
            'customer_option_count' => count($groupedOptions),
            'note' => 'Resume solo las opciones realmente distintas para el cliente y evita repetir estructura interna.',
        ];
    }

    private function buildPartySizeLabel(array $service): ?string
    {
        $min = $this->toNullableInt($service['numero_personas_minimo'] ?? null);
        $max = $this->toNullableInt($service['numero_personas_maximo'] ?? null);

        if ($min !== null && $max !== null) {
            return "De {$min} a {$max} personas";
        }

        if ($min !== null) {
            return "Desde {$min} personas";
        }

        if ($max !== null) {
            return "Hasta {$max} personas";
        }

        return null;
    }

    private function buildLanguagesLabel(mixed $languages): ?string
    {
        if (! is_array($languages) || $languages === []) {
            return null;
        }

        $mapped = collect($languages)
            ->map(fn ($lang) => match ((string) $lang) {
                'es' => 'español',
                'gl' => 'gallego',
                'en' => 'inglés',
                default => (string) $lang,
            })
            ->filter()
            ->values()
            ->all();

        return $mapped !== [] ? implode(', ', $mapped) : null;
    }

    private function buildAgePolicyLabel(array $service): ?string
    {
        $permiteMenores = $service['permite_menores'] ?? null;
        $edadMinima = $this->toNullableInt($service['edad_minima'] ?? null);

        if ($permiteMenores === false && $edadMinima !== null) {
            return "Solo para mayores de {$edadMinima} años";
        }

        if ($permiteMenores === true) {
            return 'Apta para menores';
        }

        return null;
    }

    private function buildServiceCommercialHook(array $service, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        $summary = trim((string) ($service['notas_publicas'] ?? $service['descripcion'] ?? ''));

        if ($summary !== '') {
            return mb_substr($summary, 0, 180);
        }

        return match ($behaviorProfile->sectorKey) {
            'winery' => 'Experiencia pensada para descubrir la bodega y sus vinos con un tono cercano y cuidado.',
            default => null,
        };
    }

    private function buildServiceBookingHint(array $service, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        $requiresApproval = (bool) ($service['requiere_aprobacion_manual'] ?? false);
        $requiresPayment = (bool) ($service['requiere_pago'] ?? false);

        if ($requiresApproval) {
            return 'La reserva puede quedar pendiente de validación final por parte del negocio.';
        }

        if ($requiresPayment) {
            return $behaviorProfile->sectorKey === 'winery'
                ? 'Puede requerir señal o pago previo para quedar completamente confirmada.'
                : 'Puede requerir pago previo para quedar completamente confirmada.';
        }

        return 'Si hay plazas libres, se puede cerrar la reserva directamente.';
    }

    private function buildBookingPublicSummary(array $booking, bool $confirmationSent): string
    {
        $service = $booking['service_name'] ?? 'la experiencia';
        $date = $booking['date'] ?? null;
        $time = $booking['start_time'] ?? null;
        $locator = $booking['locator'] ?? null;

        $parts = ["La reserva de {$service} ya está creada"];

        if ($date !== null && $time !== null) {
            $parts[] = "para el {$date} a las {$time}";
        }

        if ($locator !== null) {
            $parts[] = "con localizador {$locator}";
        }

        $summary = implode(' ', $parts).'.';

        if ($confirmationSent) {
            $summary .= ' Se ha enviado el email de confirmación.';
        }

        return $summary;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function fallbackClarificationMessage(array $missingFields): string
    {
        if ($missingFields === []) {
            return 'Necesito un dato más o una instrucción más clara antes de consultar nada.';
        }

        $labels = [
            'servicio_id' => 'el servicio',
            'fecha' => 'la fecha',
            'numero_personas' => 'el número de personas',
            'hora_inicio' => 'la hora',
            'contact_name' => 'el nombre del responsable',
            'contact_phone' => 'el teléfono de contacto',
            'contact_email' => 'el email de contacto',
            'document_type' => 'el tipo de documento',
            'document_value' => 'el número o referencia del documento',
            'negocio_id' => 'el negocio',
        ];

        $contactFields = array_values(array_intersect($missingFields, ['contact_name', 'contact_phone', 'contact_email']));
        $documentFields = array_values(array_intersect($missingFields, ['document_type', 'document_value']));

        if ($contactFields !== [] || $documentFields !== []) {
            $parts = [];

            if ($contactFields !== []) {
                $parts[] = 'los datos de contacto del responsable';
            }

            if ($documentFields !== []) {
                $parts[] = 'la documentación necesaria';
            }

            return 'Para seguir necesito '.implode(' y ', $parts).'.';
        }

        $readable = collect($missingFields)
            ->map(fn (string $field) => $labels[$field] ?? $field)
            ->implode(', ');

        return "Me falta {$readable} para poder consultarlo bien.";
    }

    private function buildAvailabilityPublicSummary(int $totalSlots, array $groupedOptions): string
    {
        if ($totalSlots <= 0) {
            return 'No se han encontrado huecos disponibles con el criterio consultado.';
        }

        if (count($groupedOptions) === 1) {
            $group = $groupedOptions[0];
            $start = $group['hora_inicio'] ?? null;
            $end = $group['hora_fin'] ?? null;
            $isFlexibleWindow = (bool) ($group['accepts_start_time_within_slot'] ?? false);

            if ($start !== null && $end !== null) {
                return $isFlexibleWindow
                    ? "Hay disponibilidad dentro de la franja {$start} - {$end}."
                    : "Hay una única franja clara disponible: {$start} - {$end}.";
            }

            return 'Hay una única opción clara disponible.';
        }

        return 'Hay varias alternativas disponibles, pero ya están agrupadas en opciones comprensibles para el cliente.';
    }

    private function applyMessageFactsToState(ConversationState $state, string $message): ConversationState
    {
        if ($state->contactEmail === null && preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $message, $matches) === 1) {
            $state->contactEmail = mb_strtolower($matches[0], 'UTF-8');
        }

        if ($state->contactPhone === null) {
            preg_match_all('/(?<!\d)(?:\+34\s*)?(?:\d[\s-]*){9,12}(?!\d)/', $message, $matches);

            foreach ($matches[0] ?? [] as $candidate) {
                $digits = preg_replace('/\D+/', '', $candidate);
                if ($digits !== null && strlen($digits) >= 9 && strlen($digits) <= 12) {
                    $state->contactPhone = trim($candidate);
                    break;
                }
            }
        }

        if ($state->numeroPersonas === null && preg_match('/\b(para mí|para mi|yo solo|yo sola|solo yo|solo 1|una persona|uno solo|una sola)\b/ui', $message) === 1) {
            $state->numeroPersonas = 1;
        }

        $knowledgeLevel = $this->detectKnowledgeLevel($message);
        if ($knowledgeLevel !== null) {
            $state->nivelConocimientoUsuario = $knowledgeLevel;
        }

        if ($this->messageNeedsOfferOrientation($message) && $state->servicioId === null) {
            $state->faseConversacional = 'orientacion';

            if ($state->nivelConocimientoUsuario === null) {
                $state->nivelConocimientoUsuario = 'desconocido';
            }
        }

        $detectedDocumentType = $this->detectDocumentType($message);

        if ($state->documentType === null && $detectedDocumentType !== null) {
            $state->documentType = $detectedDocumentType;
        }

        if ($state->documentValue === null) {
            $documentValue = $this->extractDocumentValue($message);
            if ($documentValue !== null && ($detectedDocumentType !== null || $state->documentType !== null)) {
                $state->documentValue = $documentValue;
            }
        }

        return $state;
    }

    private function detectKnowledgeLevel(string $message): ?string
    {
        $normalized = mb_strtolower($message, 'UTF-8');

        $novicePatterns = [
            'es mi primera vez',
            'es nuestra primera vez',
            'nunca he ido',
            'nunca hemos ido',
            'no tengo ni idea',
            'no sabemos cómo funciona',
            'no sé cómo funciona',
            'como funcionan las experiencias',
            'cómo funcionan las experiencias',
            'explícame cómo va',
        ];

        foreach ($novicePatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return 'novato';
            }
        }

        $experiencedPatterns = [
            'ya conozco',
            'ya sabemos cómo va',
            'ya sé cómo funciona',
            'ya he hecho',
            'ya hemos hecho',
            'hemos ido a otras bodegas',
            'he ido a otras bodegas',
            'ya estuve en una cata',
            'ya hemos hecho catas',
        ];

        foreach ($experiencedPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return 'familiarizado';
            }
        }

        return null;
    }

    private function messageNeedsOfferOrientation(string $message): bool
    {
        $normalized = mb_strtolower($message, 'UTF-8');

        foreach ([
            'quiero que me expliques',
            'explícame',
            'explicame',
            'como funciona',
            'cómo funciona',
            'como son las experiencias',
            'cómo son las experiencias',
            'que experiencias teneis',
            'qué experiencias tenéis',
            'que hacéis',
            'qué hacéis',
            'que ofrecéis',
            'qué ofrecéis',
            'informame',
            'infórmame',
        ] as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function detectDocumentType(string $message): ?string
    {
        $normalized = mb_strtolower($message, 'UTF-8');

        return match (true) {
            str_contains($normalized, 'carnet de conducir'),
            str_contains($normalized, 'carné de conducir'),
            str_contains($normalized, 'permiso de conducir') => 'carné de conducir',
            str_contains($normalized, 'pasaporte') => 'pasaporte',
            str_contains($normalized, 'dni') => 'DNI',
            default => null,
        };
    }

    private function extractDocumentValue(string $message): ?string
    {
        if (preg_match('/\b\d{7,8}[A-Z]\b/i', $message, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        if (preg_match('/\b[A-Z]\d{7}[A-Z]\b/i', $message, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        if (preg_match('/\b[A-Z0-9-]{6,20}\b/i', $message, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        return null;
    }
}
