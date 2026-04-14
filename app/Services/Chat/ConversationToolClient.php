<?php

namespace App\Services\Chat;

interface ConversationToolClient
{
    public function transportName(): string;

    /**
     * @return array<string, array{name: string, description: string, input_schema: array}>
     */
    public function listTools(): array;

    public function executeTool(string $tool, array $params): array;
}
