<?php

namespace App\Services\Chat;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\Conversation\ConversationBehaviorProfile;
use App\Services\Conversation\ConversationBehaviorProfileResolver;
use App\Services\Conversation\ChatbotProfileResolver;
use App\Services\Conversation\ConversationState;
use App\Services\Conversation\ConversationStatePatcher;
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

        $services = Servicio::query()
            ->where('negocio_id', $negocioId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'duracion_minutos', 'precio_base', 'requiere_pago'])
            ->map(fn (Servicio $servicio) => [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'duracion_minutos' => $servicio->duracion_minutos,
                'precio_base' => $servicio->precio_base,
                'requiere_pago' => (bool) $servicio->requiere_pago,
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
        $decision = $this->turnEngine->decide($initialPrompt, $message);
        $state = $this->statePatcher->apply($state, $decision->statePatch);

        $debug['first_decision'] = $decision->raw;
        $debug['state_after_first_decision'] = $state->toArray();

        if ($decision->toolCall === null) {
            return $this->buildResultFromDecision($decision, null, null, null, $debug, $state);
        }

        $toolValidation = $this->normalizeToolCall($decision->toolCall, $tools, $state);
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

        $finalDecision = $this->turnEngine->decide($toolPrompt, $message);
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

    private function normalizeToolCall(?ToolCall $toolCall, array $tools, ConversationState $state): array
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

        $schema = $tools[$toolCall->name]['input_schema'] ?? [];
        $required = $schema['required'] ?? [];
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
        $toolResult['data']['llm_presentation_policy'] = [
            'sector_key' => $behaviorProfile->sectorKey,
            'inventory_exposure_policy' => $behaviorProfile->inventoryExposurePolicy,
            'no_availability_policy' => $behaviorProfile->noAvailabilityPolicy,
            'customer_facing_descriptors' => $behaviorProfile->customerFacingDescriptors,
        ];

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

            $key = $start.'|'.$end;
            $resourceLabel = $slot['recurso_nombre'] ?? $slot['recurso_label'] ?? $slot['label'] ?? null;
            $descriptor = $this->inferCustomerSafeDescriptor($resourceLabel, $behaviorProfile);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'hora_inicio' => $start,
                    'hora_fin' => $end,
                    'resource_labels' => [],
                    'customer_descriptors' => [],
                    'slot_count' => 0,
                ];
            }

            if ($resourceLabel !== null && ! in_array($resourceLabel, $grouped[$key]['resource_labels'], true)) {
                $grouped[$key]['resource_labels'][] = $resourceLabel;
            }

            if ($descriptor !== null && ! in_array($descriptor, $grouped[$key]['customer_descriptors'], true)) {
                $grouped[$key]['customer_descriptors'][] = $descriptor;
            }

            $grouped[$key]['slot_count']++;

            $sanitizedSlot = $slot;

            if ($behaviorProfile->hidesInternalResourceNamesByDefault()) {
                unset($sanitizedSlot['recurso_nombre'], $sanitizedSlot['recurso_label'], $sanitizedSlot['label']);
            }

            if ($descriptor !== null) {
                $sanitizedSlot['customer_descriptor'] = $descriptor;
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

        return $toolResult;
    }

    private function inferCustomerSafeDescriptor(?string $resourceLabel, ConversationBehaviorProfile $behaviorProfile): ?string
    {
        if ($resourceLabel === null || trim($resourceLabel) === '') {
            return null;
        }

        $normalized = mb_strtolower($resourceLabel);

        return match ($behaviorProfile->sectorKey) {
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

    private function fallbackClarificationMessage(array $missingFields): string
    {
        if ($missingFields === []) {
            return 'Necesito un dato más o una instrucción más clara antes de consultar nada.';
        }

        $labels = [
            'servicio_id' => 'el servicio',
            'fecha' => 'la fecha',
            'numero_personas' => 'el número de personas',
            'negocio_id' => 'el negocio',
        ];

        $readable = collect($missingFields)
            ->map(fn (string $field) => $labels[$field] ?? $field)
            ->implode(', ');

        return "Me falta {$readable} para poder consultarlo bien.";
    }
}
