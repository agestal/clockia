<?php

namespace App\Services\Conversation;

class ToolCall
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments = [],
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $arguments = $data['arguments'] ?? [];

        return new self(
            name: $name,
            arguments: is_array($arguments) ? $arguments : [],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }
}
