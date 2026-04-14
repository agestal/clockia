<?php

namespace App\Tools;

class ToolRegistry
{
    /** @var array<string, ToolDefinition> */
    private array $tools = [];

    public function register(ToolDefinition $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    public function get(string $name): ?ToolDefinition
    {
        return $this->tools[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function execute(string $name, array $input): ToolResult
    {
        $tool = $this->get($name);

        if ($tool === null) {
            return ToolResult::fail("Tool '{$name}' no encontrada.");
        }

        return $tool->execute($input);
    }

    public function executeForConversation(string $name, array $input): array
    {
        $tool = $this->get($name);

        if ($tool === null) {
            return ToolResult::fail("Tool '{$name}' no encontrada.")->toArray();
        }

        $result = $tool->execute($input);

        return array_merge($result->toArray(), [
            'tool_metadata' => $tool->toSchema(),
            'tool_input' => $input,
            'tool_result_explanation' => $tool->resultExplanation($input, $result),
        ]);
    }

    /** @return array<string, array{name: string, description: string, input_schema: array}> */
    public function listTools(): array
    {
        $list = [];

        foreach ($this->tools as $name => $tool) {
            $list[$name] = $tool->toSchema();
        }

        return $list;
    }

    /** @return string[] */
    public function names(): array
    {
        return array_keys($this->tools);
    }
}
