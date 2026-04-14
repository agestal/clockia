<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\Chat\LlmFirstChatOrchestrator;
use App\Services\Conversation\ConversationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatTestController extends Controller
{
    private const CTX_KEY = 'chat_test_context';
    private const HISTORY_KEY = 'chat_test_history';
    private const STATE_KEY = 'chat_test_state';
    private const MAX_TURNS = 10;
    private const MAX_HISTORY = 30;

    public function index(): View
    {
        return view('admin.chat-test.index', [
            'negocios' => Negocio::activos()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function execute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'mode' => ['sometimes', 'string', 'in:direct,mcp'],
        ]);

        $negocioId = (int) $validated['negocio_id'];
        $execMode = $validated['mode'] ?? 'mcp';
        $context = $this->getContext($request, $negocioId);
        $state = $this->getState($request, $negocioId);

        $result = $execMode === 'mcp'
            ? $this->executeMcp($validated['message'], $negocioId, $context, $state)
            : $this->executeDirect($validated['message'], $negocioId, $context, $state);

        $result['execution_mode'] = $execMode;

        // Persist updated state from result
        $newState = isset($result['state']) ? ConversationState::fromArray($result['state']) : $state;
        $this->saveState($request, $negocioId, $newState);

        // Save context
        $context[] = [
            'message' => $validated['message'],
            'tool' => $result['tool'],
            'params' => $result['params'],
            'mode' => $result['mode'] ?? 'tool_result',
            'assistant_response' => mb_substr($result['response'] ?? '', 0, 200),
        ];
        $this->saveContext($request, $negocioId, $context);

        // Save history
        $history = $this->getHistory($request, $negocioId);
        $history[] = ['role' => 'user', 'text' => $validated['message']];
        $history[] = ['role' => 'assistant', 'text' => $result['response'], 'mode' => $result['mode'] ?? 'tool_result', 'tool' => $result['tool']];
        $this->saveHistory($request, $negocioId, $history);

        $result['context_size'] = count($context);
        $result['history'] = $history;

        return response()->json($result);
    }

    public function clearContext(Request $request): JsonResponse
    {
        $request->session()->forget(self::CTX_KEY);
        $request->session()->forget(self::HISTORY_KEY);
        $request->session()->forget(self::STATE_KEY);

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

    // ─── Session persistence ───

    private function getContext(Request $request, int $negocioId): array
    {
        return array_slice(($request->session()->get(self::CTX_KEY, [])[$negocioId] ?? []), -self::MAX_TURNS);
    }

    private function saveContext(Request $request, int $negocioId, array $context): void
    {
        $all = $request->session()->get(self::CTX_KEY, []);
        $all[$negocioId] = array_slice($context, -self::MAX_TURNS);
        $request->session()->put(self::CTX_KEY, $all);
    }

    private function getHistory(Request $request, int $negocioId): array
    {
        return array_slice(($request->session()->get(self::HISTORY_KEY, [])[$negocioId] ?? []), -self::MAX_HISTORY);
    }

    private function saveHistory(Request $request, int $negocioId, array $history): void
    {
        $all = $request->session()->get(self::HISTORY_KEY, []);
        $all[$negocioId] = array_slice($history, -self::MAX_HISTORY);
        $request->session()->put(self::HISTORY_KEY, $all);
    }

    private function getState(Request $request, int $negocioId): ConversationState
    {
        $data = ($request->session()->get(self::STATE_KEY, [])[$negocioId] ?? null);

        return $data !== null ? ConversationState::fromArray($data) : new ConversationState(negocioId: $negocioId);
    }

    private function saveState(Request $request, int $negocioId, ConversationState $state): void
    {
        $all = $request->session()->get(self::STATE_KEY, []);
        $all[$negocioId] = $state->toArray();
        $request->session()->put(self::STATE_KEY, $all);
    }
}
