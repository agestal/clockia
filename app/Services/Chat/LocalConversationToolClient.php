<?php

namespace App\Services\Chat;

use App\Tools\ToolRegistry;

class LocalConversationToolClient implements ConversationToolClient
{
    public function __construct(
        private readonly ToolRegistry $toolRegistry,
    ) {}

    public function transportName(): string
    {
        return 'direct';
    }

    public function listTools(): array
    {
        return $this->toolRegistry->listTools();
    }

    public function executeTool(string $tool, array $params): array
    {
        return $this->toolRegistry->execute($tool, $params)->toArray();
    }
}
