<?php

namespace App\Services\Chat;

use App\Services\Mcp\McpClient;

class McpConversationToolClient implements ConversationToolClient
{
    public function __construct(
        private readonly McpClient $mcpClient,
    ) {}

    public function transportName(): string
    {
        return 'mcp';
    }

    public function listTools(): array
    {
        return $this->mcpClient->listTools()['tools'] ?? [];
    }

    public function executeTool(string $tool, array $params): array
    {
        return $this->mcpClient->executeTool($tool, $params);
    }
}
