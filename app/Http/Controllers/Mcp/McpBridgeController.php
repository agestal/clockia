<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\Conversation\ChatbotProfileResolver;
use App\Services\Conversation\ClarificationQuestionBuilder;
use App\Services\Conversation\ConfirmationSummaryBuilder;
use App\Services\Conversation\ConversationRequirementsResolver;
use App\Tools\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class McpBridgeController extends Controller
{
    public function __construct(
        private readonly ToolRegistry $toolRegistry,
        private readonly ChatbotProfileResolver $profileResolver,
        private readonly ConversationRequirementsResolver $requirementsResolver,
        private readonly ClarificationQuestionBuilder $clarificationBuilder,
        private readonly ConfirmationSummaryBuilder $confirmationBuilder,
    ) {}

    // ─── Tools ───

    public function listTools(): JsonResponse
    {
        return response()->json([
            'tools' => $this->toolRegistry->listTools(),
        ]);
    }

    public function executeTool(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tool' => ['required', 'string'],
            'params' => ['required', 'array'],
        ]);

        $toolName = $validated['tool'];
        $params = $validated['params'];

        if (! $this->toolRegistry->has($toolName)) {
            return response()->json(['error' => "Tool '{$toolName}' not found."], 404);
        }

        try {
            $result = $this->toolRegistry->executeForConversation($toolName, $params);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($result);
    }

    // ─── Resources ───

    public function getChatbotProfile(Negocio $negocio): JsonResponse
    {
        $profile = $this->profileResolver->resolve($negocio);

        $negocio->load('tipoNegocio:id,nombre');

        return response()->json([
            'negocio_id' => $profile->negocioId,
            'negocio_nombre' => $profile->negocioNombre,
            'tipo_negocio' => $negocio->tipoNegocio?->nombre,
            'personality' => $profile->personality,
            'system_rules' => $profile->systemRules,
            'required_fields_overrides' => $profile->requiredFieldsOverrides,
            'conversation_behavior_overrides' => $negocio->chat_behavior_overrides,
            'zona_horaria' => $negocio->zona_horaria,
            'telefono' => $negocio->telefono,
            'email' => $negocio->email,
            'direccion' => $negocio->direccion,
            'descripcion_publica' => $negocio->descripcion_publica,
            'politica_cancelacion' => $negocio->politica_cancelacion,
            'horas_minimas_cancelacion' => $negocio->horas_minimas_cancelacion,
            'permite_modificacion' => $negocio->permite_modificacion,
            'max_recursos_combinables' => $negocio->max_recursos_combinables,
        ]);
    }

    public function getConversationRequirements(Request $request): JsonResponse
    {
        $negocioId = $request->integer('negocio_id');
        $negocio = $negocioId > 0 ? Negocio::find($negocioId) : null;
        $profile = $negocio ? $this->profileResolver->resolve($negocio) : null;

        $tools = $this->toolRegistry->names();
        $requirements = [];

        foreach ($tools as $toolName) {
            $requirements[$toolName] = [
                'required_fields' => $this->requirementsResolver->requiredFieldsFor($toolName, $profile),
                'requires_confirmation' => $this->requirementsResolver->requiresConfirmation($toolName),
            ];
        }

        // Also include future tools that require confirmation
        foreach (['create_booking', 'cancel_booking', 'modify_booking'] as $futureTool) {
            if (! isset($requirements[$futureTool])) {
                $requirements[$futureTool] = [
                    'required_fields' => $this->requirementsResolver->requiredFieldsFor($futureTool, $profile),
                    'requires_confirmation' => true,
                ];
            }
        }

        return response()->json([
            'requirements' => $requirements,
        ]);
    }

    public function buildConfirmationSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tool' => ['required', 'string'],
            'params' => ['required', 'array'],
        ]);

        $summary = $this->confirmationBuilder->build($validated['tool'], $validated['params']);

        return response()->json($summary->toArray());
    }

    public function buildClarificationQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'missing_fields' => ['required', 'array'],
        ]);

        return response()->json([
            'question' => $this->clarificationBuilder->build($validated['missing_fields']),
        ]);
    }
}
