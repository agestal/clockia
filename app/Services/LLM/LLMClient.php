<?php

namespace App\Services\LLM;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LLMClient
{
    private string $provider;
    private string $model;
    private array $openRouterFallbackModels;
    private float $temperature;
    private int $maxTokens;
    private int $timeout;

    public function __construct()
    {
        $this->provider = config('services.llm.provider', 'openrouter');
        $this->model = config('services.llm.model', 'openai/gpt-4o-mini');
        $this->openRouterFallbackModels = config('services.llm.openrouter_fallback_models', ['openai/gpt-4o-mini', 'openai/gpt-4.1-nano']);
        $this->temperature = (float) config('services.llm.temperature', 0.15);
        $this->maxTokens = (int) config('services.llm.max_tokens', 700);
        $this->timeout = (int) config('services.llm.timeout', 20);
    }

    public function chat(string $systemPrompt, string $userMessage): string
    {
        $config = $this->resolveProviderConfig();

        if ($config['api_key'] === '') {
            throw new RuntimeException("API key not configured for provider '{$this->provider}'. Check .env");
        }

        return match ($this->provider) {
            'anthropic' => $this->callAnthropic($systemPrompt, $userMessage, $config),
            default => $this->callOpenAICompatible($systemPrompt, $userMessage, $config),
        };
    }

    private function resolveProviderConfig(): array
    {
        return match ($this->provider) {
            'openrouter' => [
                'api_key' => config('services.openrouter.api_key', ''),
                'base_url' => config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'),
            ],
            'groq' => [
                'api_key' => config('services.groq.api_key', ''),
                'base_url' => config('services.groq.base_url', 'https://api.groq.com/openai/v1'),
            ],
            'openai' => [
                'api_key' => config('services.openai.api_key', ''),
                'base_url' => config('services.openai.base_url', 'https://api.openai.com/v1'),
            ],
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY', ''),
                'base_url' => 'https://api.anthropic.com',
            ],
            default => throw new RuntimeException("LLM provider '{$this->provider}' not supported."),
        };
    }

    private function callOpenAICompatible(string $systemPrompt, string $userMessage, array $config): string
    {
        $url = rtrim($config['base_url'], '/').'/chat/completions';

        $headers = [
            'Authorization' => 'Bearer '.$config['api_key'],
            'Content-Type' => 'application/json',
        ];

        if ($this->provider === 'openrouter') {
            $headers['HTTP-Referer'] = config('app.url', 'http://localhost');
            $headers['X-Title'] = 'Clockia';
        }

        $attempts = [[
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
        ]];
        $queuedAttempts = [];
        $lastError = null;

        while ($attempt = array_shift($attempts)) {
            $attemptKey = $attempt['model'].'|'.$attempt['max_tokens'];

            if (isset($queuedAttempts[$attemptKey])) {
                continue;
            }

            $queuedAttempts[$attemptKey] = true;

            $payload = [
                'model' => $attempt['model'],
                'temperature' => $this->temperature,
                'max_tokens' => $attempt['max_tokens'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', '');
            }

            $lastError = "[{$this->provider}] API error {$response->status()}: {$response->body()}";

            if ($this->provider !== 'openrouter' || $response->status() !== 402) {
                break;
            }

            foreach ($this->buildOpenRouterRetryAttempts($response->body(), $attempt) as $retryAttempt) {
                $retryKey = $retryAttempt['model'].'|'.$retryAttempt['max_tokens'];

                if (! isset($queuedAttempts[$retryKey])) {
                    $attempts[] = $retryAttempt;
                }
            }
        }

        throw new RuntimeException($lastError ?? "[{$this->provider}] API request failed.");
    }

    private function callAnthropic(string $systemPrompt, string $userMessage, array $config): string
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("[anthropic] API error {$response->status()}: {$response->body()}");
        }

        return $response->json('content.0.text', '');
    }

    private function resolveAffordableRetryMaxTokens(string $responseBody, int $currentMaxTokens): ?int
    {
        if (! preg_match('/can only afford\s+(\d+)/i', $responseBody, $matches)) {
            return null;
        }

        $affordableMaxTokens = (int) ($matches[1] ?? 0);

        if ($affordableMaxTokens <= 0) {
            return null;
        }

        $retryMaxTokens = min($currentMaxTokens - 1, max(128, $affordableMaxTokens - 64));

        return $retryMaxTokens > 0 && $retryMaxTokens < $currentMaxTokens
            ? $retryMaxTokens
            : null;
    }

    /**
     * @return array<int, array{model: string, max_tokens: int}>
     */
    private function buildOpenRouterRetryAttempts(string $responseBody, array $attempt): array
    {
        $retries = [];
        $currentModel = (string) ($attempt['model'] ?? $this->model);
        $currentMaxTokens = (int) ($attempt['max_tokens'] ?? $this->maxTokens);
        $fallbackMaxTokens = $this->resolveAffordableRetryMaxTokens($responseBody, $currentMaxTokens);
        $isPromptAffordabilityError = str_contains(strtolower($responseBody), 'prompt tokens limit exceeded');

        if ($fallbackMaxTokens !== null && ! $isPromptAffordabilityError) {
            $retries[] = [
                'model' => $currentModel,
                'max_tokens' => $fallbackMaxTokens,
            ];
        }

        foreach ($this->resolveOpenRouterFallbackModels($currentModel) as $fallbackModel) {
            $retries[] = [
                'model' => $fallbackModel,
                'max_tokens' => $fallbackMaxTokens ?? min($currentMaxTokens, 500),
            ];
        }

        return $retries;
    }

    /**
     * @return array<int, string>
     */
    private function resolveOpenRouterFallbackModels(string $currentModel): array
    {
        return array_values(array_filter(
            array_unique(array_map(
                static fn ($value) => trim((string) $value),
                $this->openRouterFallbackModels
            )),
            static fn (string $model) => $model !== '' && $model !== $currentModel
        ));
    }
}
