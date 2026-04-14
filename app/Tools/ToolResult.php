<?php

namespace App\Tools;

class ToolResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $data = [],
        public readonly ?string $error = null,
    ) {}

    public static function ok(array $data): self
    {
        return new self(success: true, data: $data);
    }

    public static function fail(string $error): self
    {
        return new self(success: false, error: $error);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
