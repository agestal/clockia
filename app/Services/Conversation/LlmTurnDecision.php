<?php

namespace App\Services\Conversation;

class LlmTurnDecision
{
    public function __construct(
        public readonly string $assistantMessage,
        public readonly array $statePatch = [],
        public readonly ?ToolCall $toolCall = null,
        public readonly bool $needsUserInput = false,
        public readonly string $conversationStatus = 'respond',
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            assistantMessage: trim((string) ($data['assistant_message'] ?? '')),
            statePatch: is_array($data['state_patch'] ?? null) ? $data['state_patch'] : [],
            toolCall: ToolCall::fromArray($data['tool_call'] ?? null),
            needsUserInput: (bool) ($data['needs_user_input'] ?? false),
            conversationStatus: (string) ($data['conversation_status'] ?? 'respond'),
            raw: $data,
        );
    }
}
