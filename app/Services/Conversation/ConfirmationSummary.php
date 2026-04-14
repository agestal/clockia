<?php

namespace App\Services\Conversation;

class ConfirmationSummary
{
    public function __construct(
        public readonly string $tool,
        public readonly array $params,
        public readonly string $summaryText,
        public readonly array $dataPoints,
    ) {}

    public function toArray(): array
    {
        return [
            'tool' => $this->tool,
            'params' => $this->params,
            'summary_text' => $this->summaryText,
            'data_points' => $this->dataPoints,
        ];
    }
}
