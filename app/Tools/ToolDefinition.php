<?php

namespace App\Tools;

abstract class ToolDefinition
{
    abstract public function name(): string;

    abstract public function description(): string;

    abstract public function inputSchema(): array;

    abstract public function execute(array $input): ToolResult;

    public function toSchema(): array
    {
        return [
            'name' => $this->name(),
            'description' => $this->description(),
            'input_schema' => $this->inputSchema(),
        ];
    }
}
