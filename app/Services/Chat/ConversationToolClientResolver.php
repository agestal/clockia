<?php

namespace App\Services\Chat;

use InvalidArgumentException;

class ConversationToolClientResolver
{
    public function __construct(
        private readonly McpConversationToolClient $mcpClient,
        private readonly LocalConversationToolClient $localClient,
    ) {}

    public function resolve(string $mode): ConversationToolClient
    {
        return match ($mode) {
            'mcp' => $this->mcpClient,
            'direct' => $this->localClient,
            default => throw new InvalidArgumentException("Conversation tool mode '{$mode}' no soportado."),
        };
    }
}
