<?php

namespace Tests\Unit;

use App\Services\LLM\LLMClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LLMClientTest extends TestCase
{
    public function test_it_falls_back_to_a_cheaper_openrouter_model_when_prompt_is_too_expensive(): void
    {
        config([
            'services.llm.provider' => 'openrouter',
            'services.llm.model' => 'openai/gpt-4.1-mini',
            'services.llm.openrouter_fallback_models' => ['openai/gpt-4o-mini', 'openai/gpt-4.1-nano'],
            'services.llm.max_tokens' => 500,
            'services.llm.temperature' => 0.2,
            'services.llm.timeout' => 20,
            'services.openrouter.api_key' => 'test-openrouter-key',
            'services.openrouter.base_url' => 'https://openrouter.ai/api/v1',
        ]);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
                ->push([
                    'error' => [
                        'message' => 'Prompt tokens limit exceeded: 4360 > 2285.',
                        'code' => 402,
                    ],
                ], 402)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Respuesta fallback ok',
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $client = new LLMClient();
        $response = $client->chat('Sistema', 'Hola');

        $this->assertSame('Respuesta fallback ok', $response);

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && $request['model'] === 'openai/gpt-4.1-mini';
        });
        Http::assertSent(function ($request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && $request['model'] === 'openai/gpt-4o-mini';
        });
    }
}
