<?php

namespace App\Services\Conversation;

use Illuminate\Support\Facades\Cache;

class ConversationMemoryStore
{
    public function load(string $conversationId, int $negocioId): array
    {
        $payload = Cache::get($this->key($conversationId, $negocioId));

        if (! is_array($payload)) {
            return $this->emptyPayload($negocioId);
        }

        return [
            'context' => is_array($payload['context'] ?? null) ? $payload['context'] : [],
            'history' => is_array($payload['history'] ?? null) ? $payload['history'] : [],
            'state' => is_array($payload['state'] ?? null)
                ? $payload['state']
                : (new ConversationState(negocioId: $negocioId))->toArray(),
            'updated_at' => $payload['updated_at'] ?? null,
        ];
    }

    public function save(string $conversationId, int $negocioId, array $context, array $history, ConversationState $state): void
    {
        Cache::put(
            $this->key($conversationId, $negocioId),
            [
                'context' => array_slice($context, -$this->maxTurns()),
                'history' => array_slice($history, -$this->maxHistory()),
                'state' => $state->toArray(),
                'updated_at' => now()->toIso8601String(),
            ],
            now()->addMinutes($this->ttlMinutes()),
        );
    }

    public function forget(string $conversationId, int $negocioId): void
    {
        Cache::forget($this->key($conversationId, $negocioId));
    }

    public function ttlMinutes(): int
    {
        return max(5, (int) config('services.llm.conversation_ttl_minutes', 120));
    }

    public function maxTurns(): int
    {
        return max(4, (int) config('services.llm.conversation_max_turns', 12));
    }

    public function maxHistory(): int
    {
        return max(10, (int) config('services.llm.conversation_max_history', 40));
    }

    private function key(string $conversationId, int $negocioId): string
    {
        return "conversation.memory.{$negocioId}.{$conversationId}";
    }

    private function emptyPayload(int $negocioId): array
    {
        return [
            'context' => [],
            'history' => [],
            'state' => (new ConversationState(negocioId: $negocioId))->toArray(),
            'updated_at' => null,
        ];
    }
}
