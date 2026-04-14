<?php

namespace App\Tools;

abstract class ToolDefinition
{
    abstract public function name(): string;

    abstract public function description(): string;

    abstract public function inputSchema(): array;

    abstract public function execute(array $input): ToolResult;

    public function whenToUse(): array
    {
        return [];
    }

    public function whenNotToUse(): array
    {
        return [];
    }

    public function argumentGuidance(): array
    {
        return [];
    }

    public function responseGuidance(): array
    {
        return [];
    }

    public function resultExplanation(array $input, ToolResult $result): array
    {
        return [
            'tool_name' => $this->name(),
            'what_this_tool_does' => $this->description(),
            'status' => $result->success ? 'success' : 'error',
            'conversation_memory_hint' => $result->success
                ? 'Se ha ejecutado correctamente la herramienta y su resultado ya forma parte del contexto reciente.'
                : 'La herramienta falló y no debe presentarse como si hubiese resuelto la consulta.',
            'next_step_hint' => $result->success
                ? 'Usa el resultado real para responder con naturalidad, sin inventar datos ni prometer acciones no ejecutadas.'
                : 'Explica el fallo con naturalidad y guía al usuario sin inventar información.',
        ];
    }

    public function toSchema(): array
    {
        return [
            'name' => $this->name(),
            'description' => $this->description(),
            'input_schema' => $this->inputSchema(),
            'llm_guidance' => [
                'when_to_use' => $this->whenToUse(),
                'when_not_to_use' => $this->whenNotToUse(),
                'argument_guidance' => $this->argumentGuidance(),
                'response_guidance' => $this->responseGuidance(),
            ],
        ];
    }
}
