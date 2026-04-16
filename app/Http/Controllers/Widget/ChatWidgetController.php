<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\Chat\LlmFirstChatOrchestrator;
use App\Services\Conversation\ConversationMemoryStore;
use App\Services\Conversation\ConversationState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatWidgetController extends Controller
{
    public function __construct(
        private readonly LlmFirstChatOrchestrator $orchestrator,
        private readonly ConversationMemoryStore $memoryStore,
    ) {}

    public function message(Request $request, Negocio $business): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:120'],
        ])->validate();

        $conversationId = $validated['conversation_id'] ?? null;
        if (! is_string($conversationId) || $conversationId === '') {
            $conversationId = (string) Str::uuid();
        }

        $memory = $this->memoryStore->load($conversationId, $business->id);
        $context = $memory['context'];
        $history = $memory['history'];
        $state = ConversationState::fromArray($memory['state']);

        try {
            $result = $this->orchestrator->handle($validated['message'], $business->id, $context, $state, 'mcp');
        } catch (\Throwable $e) {
            Log::error('Chat widget execution failed.', [
                'negocio_id' => $business->id,
                'conversation_id' => $conversationId,
                'exception' => $e,
            ]);

            return response()->json([
                'error' => 'El chat no pudo procesar la petición en este momento.',
            ], 500);
        }

        $newState = isset($result['state']) ? ConversationState::fromArray($result['state']) : $state;

        $context[] = [
            'message' => $validated['message'],
            'tool' => $result['tool'] ?? null,
            'params' => $result['params'] ?? null,
            'mode' => $result['mode'] ?? 'tool_result',
            'assistant_response' => mb_substr($result['response'] ?? '', 0, 200),
            'tool_result_summary' => data_get($result, 'tool_result.tool_result_explanation.public_summary'),
        ];

        $history[] = ['role' => 'user', 'text' => $validated['message']];
        $history[] = [
            'role' => 'assistant',
            'text' => $result['response'] ?? '',
            'mode' => $result['mode'] ?? 'tool_result',
            'tool' => $result['tool'] ?? null,
        ];

        $this->memoryStore->save($conversationId, $business->id, $context, $history, $newState);

        return response()->json([
            'conversation_id' => $conversationId,
            'reply' => $result['response'] ?? '',
            'mode' => $result['mode'] ?? null,
            'tool' => $result['tool'] ?? null,
        ]);
    }

    public function history(Request $request, Negocio $business): JsonResponse
    {
        $conversationId = $request->query('conversation_id');
        if (! is_string($conversationId) || $conversationId === '') {
            return response()->json(['conversation_id' => null, 'history' => []]);
        }

        $memory = $this->memoryStore->load($conversationId, $business->id);

        return response()->json([
            'conversation_id' => $conversationId,
            'history' => array_values(array_filter(
                $memory['history'] ?? [],
                fn ($item) => is_array($item) && isset($item['role'], $item['text'])
            )),
        ]);
    }

    public function greeting(Request $request, Negocio $business): JsonResponse
    {
        return response()->json([
            'business' => [
                'id' => $business->id,
                'name' => $business->nombre,
            ],
            'greeting' => $this->defaultGreeting($business),
        ]);
    }

    private function defaultGreeting(Negocio $business): string
    {
        return "¡Hola! Soy el asistente de {$business->nombre}. ¿En qué puedo ayudarte?";
    }
}
