<?php

namespace App\Services\Conversation;

use App\Services\LLM\LLMClient;

class LlmTurnEngine
{
    public function __construct(
        private readonly LLMClient $client,
    ) {}

    public function decide(string $systemPrompt, string $userPrompt): LlmTurnDecision
    {
        $raw = $this->client->chat($systemPrompt, $this->wrapUserPrompt($userPrompt));
        $parsed = $this->extractJson($raw);

        if (! is_array($parsed)) {
            $repairPrompt = <<<PROMPT
Último mensaje del usuario:
{$userPrompt}

Tu respuesta anterior no respetó el formato requerido. Reescríbela como JSON válido y solo JSON.
No añadas texto fuera del JSON.
PROMPT;

            $retryRaw = $this->client->chat($systemPrompt, $repairPrompt);
            $retryParsed = $this->extractJson($retryRaw);

            if (is_array($retryParsed)) {
                $parsed = $retryParsed;
            } else {
                $raw = $retryRaw;
            }
        }

        if (! is_array($parsed)) {
            return new LlmTurnDecision(
                assistantMessage: trim($raw),
                raw: ['raw_output' => $raw, 'json_parse_failed' => true],
            );
        }

        return LlmTurnDecision::fromArray($parsed);
    }

    private function wrapUserPrompt(string $userPrompt): string
    {
        return <<<PROMPT
Último mensaje del usuario:
{$userPrompt}

Devuelve únicamente JSON válido. Sin markdown. Sin comentarios. Sin texto extra.
PROMPT;
    }

    private function extractJson(string $raw): ?array
    {
        $trimmed = trim($raw);
        $decoded = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $trimmed, $matches) !== 1) {
            return null;
        }

        $decoded = json_decode($matches[0], true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded)
            ? $decoded
            : null;
    }
}
