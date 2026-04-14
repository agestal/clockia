<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\Chat\LlmFirstChatOrchestrator;
use App\Services\Conversation\ConversationMemoryStore;
use App\Services\Conversation\ConversationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatTestController extends Controller
{
    private const CONVERSATION_KEY = 'chat_test_conversation_ids';

    public function index(): View
    {
        return view('admin.chat-test.index', [
            'negocios' => Negocio::activos()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function execute(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => ['required', 'string', 'min:1', 'max:1000'],
                'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
                'mode' => ['sometimes', 'string', 'in:direct,mcp'],
                'conversation_id' => ['nullable', 'string', 'max:120'],
            ]);

            $negocioId = (int) $validated['negocio_id'];
            $execMode = $validated['mode'] ?? 'mcp';
            $conversationId = $validated['conversation_id'] ?? $this->resolveConversationId($request, $negocioId);
            $memoryStore = app(ConversationMemoryStore::class);
            $memory = $memoryStore->load($conversationId, $negocioId);
            $context = $memory['context'];
            $history = $memory['history'];
            $state = ConversationState::fromArray($memory['state']);

            $result = $execMode === 'mcp'
                ? $this->executeMcp($validated['message'], $negocioId, $context, $state)
                : $this->executeDirect($validated['message'], $negocioId, $context, $state);

            $result['execution_mode'] = $execMode;
            $result['conversation_id'] = $conversationId;
            $result['conversation_ttl_minutes'] = $memoryStore->ttlMinutes();

            $newState = isset($result['state']) ? ConversationState::fromArray($result['state']) : $state;

            $context[] = [
                'message' => $validated['message'],
                'tool' => $result['tool'],
                'params' => $result['params'],
                'mode' => $result['mode'] ?? 'tool_result',
                'assistant_response' => mb_substr($result['response'] ?? '', 0, 200),
                'tool_result_summary' => data_get($result, 'tool_result.tool_result_explanation.public_summary'),
            ];

            $history[] = ['role' => 'user', 'text' => $validated['message']];
            $history[] = ['role' => 'assistant', 'text' => $result['response'], 'mode' => $result['mode'] ?? 'tool_result', 'tool' => $result['tool']];

            $memoryStore->save($conversationId, $negocioId, $context, $history, $newState);

            $result['context_size'] = count($context);
            $result['history'] = $history;

            return response()->json($result);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first() ?: 'La petición del chat no es válida.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Chat test execution failed.', [
                'message' => $request->input('message'),
                'negocio_id' => $request->input('negocio_id'),
                'mode' => $request->input('mode', 'mcp'),
                'exception' => $e,
            ]);

            return response()->json([
                'message' => $this->resolveChatErrorMessage($e),
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function clearContext(Request $request): JsonResponse
    {
        $allConversationIds = $request->session()->get(self::CONVERSATION_KEY, []);
        $memoryStore = app(ConversationMemoryStore::class);

        foreach ($allConversationIds as $negocioId => $conversationId) {
            if (is_numeric($negocioId) && is_string($conversationId) && $conversationId !== '') {
                $memoryStore->forget($conversationId, (int) $negocioId);
            }
        }

        $request->session()->forget(self::CONVERSATION_KEY);

        return response()->json(['cleared' => true]);
    }

    private function executeDirect(string $message, int $negocioId, array $context, ConversationState $state): array
    {
        return app(LlmFirstChatOrchestrator::class)->handle($message, $negocioId, $context, $state, 'direct');
    }

    private function executeMcp(string $message, int $negocioId, array $context, ConversationState $state): array
    {
        return app(LlmFirstChatOrchestrator::class)->handle($message, $negocioId, $context, $state, 'mcp');
    }

    private function resolveConversationId(Request $request, int $negocioId): string
    {
        $all = $request->session()->get(self::CONVERSATION_KEY, []);
        $conversationId = $all[$negocioId] ?? null;

        if (! is_string($conversationId) || $conversationId === '') {
            $conversationId = (string) Str::uuid();
            $all[$negocioId] = $conversationId;
            $request->session()->put(self::CONVERSATION_KEY, $all);
        }

        return $conversationId;
    }

    private function resolveChatErrorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'API error 402') && str_contains($message, 'more credits')) {
            return 'El proveedor LLM rechazó la petición por crédito insuficiente o por un límite de salida demasiado alto.';
        }

        if (app()->hasDebugModeEnabled() && $message !== '') {
            return $message;
        }

        return 'El chat no pudo procesar la petición en este momento.';
    }
}
