<?php

namespace Tests\Unit;

use App\Models\Negocio;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Services\Chat\ConversationToolClient;
use App\Services\Chat\ConversationToolClientResolver;
use App\Services\Chat\LlmFirstChatOrchestrator;
use App\Services\Conversation\ChatbotProfileResolver;
use App\Services\Conversation\ConversationBehaviorProfileResolver;
use App\Services\Conversation\ConversationState;
use App\Services\Conversation\ConversationStatePatcher;
use App\Services\Conversation\ConversationUserMessageNormalizer;
use App\Services\Conversation\LlmTurnEngine;
use App\Services\Conversation\TurnPromptBuilder;
use App\Services\LLM\LLMClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LlmFirstChatOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_the_llm_first_core_with_the_requested_tool_transport(): void
    {
        [$business, $service] = $this->createBusinessFixture();

        $llmClient = Mockery::mock(LLMClient::class);
        $llmClient->shouldReceive('chat')
            ->twice()
            ->andReturn(
                json_encode([
                    'assistant_message' => 'Voy a revisar disponibilidad.',
                    'state_patch' => [
                        'servicio_id' => $service->id,
                        'servicio_nombre' => $service->nombre,
                        'fecha' => '2026-04-17',
                        'numero_personas' => 5,
                        'hora_preferida' => '21:00',
                        'ultima_intencion' => 'reservar',
                        'datos_confirmados' => ['preferred_zone' => 'interior'],
                    ],
                    'tool_call' => [
                        'name' => 'search_availability',
                        'arguments' => [
                            'servicio_id' => $service->id,
                            'fecha' => '2026-04-17',
                            'numero_personas' => 5,
                        ],
                    ],
                    'needs_user_input' => true,
                    'conversation_status' => 'tool_call',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode([
                    'assistant_message' => 'He encontrado disponibilidad para cenar el viernes 17 de abril a las 21:00 para 5 personas en el interior.',
                    'state_patch' => [
                        'servicio_id' => $service->id,
                        'servicio_nombre' => $service->nombre,
                        'fecha' => '2026-04-17',
                        'numero_personas' => 5,
                        'hora_preferida' => '21:00',
                        'ultima_intencion' => 'reservar',
                        'datos_confirmados' => ['preferred_zone' => 'interior'],
                    ],
                    'tool_call' => null,
                    'needs_user_input' => true,
                    'conversation_status' => 'respond',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            );

        $fakeToolClient = new class implements ConversationToolClient
        {
            public array $executions = [];

            public function transportName(): string
            {
                return 'direct';
            }

            public function listTools(): array
            {
                return [
                    'search_availability' => [
                        'name' => 'search_availability',
                        'description' => 'Busca disponibilidad.',
                        'input_schema' => [
                            'type' => 'object',
                            'properties' => [
                                'negocio_id' => ['type' => 'integer'],
                                'servicio_id' => ['type' => 'integer'],
                                'fecha' => ['type' => 'string'],
                                'numero_personas' => ['type' => 'integer'],
                            ],
                            'required' => ['negocio_id', 'servicio_id', 'fecha'],
                        ],
                    ],
                ];
            }

            public function executeTool(string $tool, array $params): array
            {
                $this->executions[] = compact('tool', 'params');

                return [
                    'success' => true,
                    'data' => [
                        'availability_mode' => 'precise',
                        'slots' => [
                            [
                                'hora_inicio' => '21:00',
                                'hora_fin' => '23:00',
                                'recurso_nombre' => 'Mesa interior 3',
                            ],
                        ],
                    ],
                ];
            }
        };

        $resolver = Mockery::mock(ConversationToolClientResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->with('direct')
            ->andReturn($fakeToolClient);

        $orchestrator = new LlmFirstChatOrchestrator(
            $resolver,
            new ChatbotProfileResolver(),
            new ConversationBehaviorProfileResolver(),
            new TurnPromptBuilder(),
            new LlmTurnEngine($llmClient),
            new ConversationStatePatcher(),
            new ConversationUserMessageNormalizer(),
        );

        $result = $orchestrator->handle(
            'Quiero reservar una mesa para cenar el viernes a las 21:00, somos 5 y preferimos interior',
            $business->id,
            [],
            new ConversationState(negocioId: $business->id),
            'direct',
        );

        $this->assertSame('tool_result', $result['mode']);
        $this->assertSame('search_availability', $result['tool']);
        $this->assertSame('direct', $result['debug']['tool_transport']);
        $this->assertSame($business->id, $result['params']['negocio_id']);
        $this->assertSame($service->id, $result['params']['servicio_id']);
        $this->assertSame('2026-04-17', $result['state']['fecha']);
        $this->assertSame(5, $result['state']['numero_personas']);
        $this->assertSame('21:00', $result['state']['hora_preferida']);
        $this->assertSame('interior', $result['state']['datos_confirmados']['preferred_zone']);
        $this->assertCount(1, $fakeToolClient->executions);
    }

    public function test_it_returns_clarification_when_the_llm_requests_a_tool_without_required_fields(): void
    {
        [$business, $service] = $this->createBusinessFixture();

        $llmClient = Mockery::mock(LLMClient::class);
        $llmClient->shouldReceive('chat')
            ->once()
            ->andReturn(json_encode([
                'assistant_message' => '',
                'state_patch' => [
                    'servicio_id' => $service->id,
                    'servicio_nombre' => $service->nombre,
                    'ultima_intencion' => 'reservar',
                ],
                'tool_call' => [
                    'name' => 'search_availability',
                    'arguments' => [
                        'servicio_id' => $service->id,
                    ],
                ],
                'needs_user_input' => true,
                'conversation_status' => 'tool_call',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $fakeToolClient = new class implements ConversationToolClient
        {
            public function transportName(): string
            {
                return 'direct';
            }

            public function listTools(): array
            {
                return [
                    'search_availability' => [
                        'name' => 'search_availability',
                        'description' => 'Busca disponibilidad.',
                        'input_schema' => [
                            'type' => 'object',
                            'properties' => [
                                'negocio_id' => ['type' => 'integer'],
                                'servicio_id' => ['type' => 'integer'],
                                'fecha' => ['type' => 'string'],
                            ],
                            'required' => ['negocio_id', 'servicio_id', 'fecha'],
                        ],
                    ],
                ];
            }

            public function executeTool(string $tool, array $params): array
            {
                throw new \RuntimeException('No debería ejecutarse.');
            }
        };

        $resolver = Mockery::mock(ConversationToolClientResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->with('direct')
            ->andReturn($fakeToolClient);

        $orchestrator = new LlmFirstChatOrchestrator(
            $resolver,
            new ChatbotProfileResolver(),
            new ConversationBehaviorProfileResolver(),
            new TurnPromptBuilder(),
            new LlmTurnEngine($llmClient),
            new ConversationStatePatcher(),
            new ConversationUserMessageNormalizer(),
        );

        $result = $orchestrator->handle(
            'Quiero reservar',
            $business->id,
            [],
            new ConversationState(negocioId: $business->id),
            'direct',
        );

        $this->assertSame('clarification', $result['mode']);
        $this->assertNull($result['tool']);
        $this->assertSame(['fecha'], $result['debug']['missing_fields']);
        $this->assertStringContainsString('fecha', $result['response']);
    }

    public function test_it_returns_a_controlled_error_when_tool_execution_fails(): void
    {
        [$business, $service] = $this->createBusinessFixture();

        $llmClient = Mockery::mock(LLMClient::class);
        $llmClient->shouldReceive('chat')
            ->once()
            ->andReturn(json_encode([
                'assistant_message' => 'Voy a consultarlo.',
                'state_patch' => [
                    'servicio_id' => $service->id,
                    'servicio_nombre' => $service->nombre,
                    'fecha' => '2026-04-17',
                ],
                'tool_call' => [
                    'name' => 'search_availability',
                    'arguments' => [
                        'servicio_id' => $service->id,
                        'fecha' => '2026-04-17',
                    ],
                ],
                'needs_user_input' => true,
                'conversation_status' => 'tool_call',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $fakeToolClient = new class implements ConversationToolClient
        {
            public function transportName(): string
            {
                return 'mcp';
            }

            public function listTools(): array
            {
                return [
                    'search_availability' => [
                        'name' => 'search_availability',
                        'description' => 'Busca disponibilidad.',
                        'input_schema' => [
                            'type' => 'object',
                            'properties' => [
                                'negocio_id' => ['type' => 'integer'],
                                'servicio_id' => ['type' => 'integer'],
                                'fecha' => ['type' => 'string'],
                            ],
                            'required' => ['negocio_id', 'servicio_id', 'fecha'],
                        ],
                    ],
                ];
            }

            public function executeTool(string $tool, array $params): array
            {
                throw new \RuntimeException('Fallo MCP');
            }
        };

        $resolver = Mockery::mock(ConversationToolClientResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->with('mcp')
            ->andReturn($fakeToolClient);

        $orchestrator = new LlmFirstChatOrchestrator(
            $resolver,
            new ChatbotProfileResolver(),
            new ConversationBehaviorProfileResolver(),
            new TurnPromptBuilder(),
            new LlmTurnEngine($llmClient),
            new ConversationStatePatcher(),
            new ConversationUserMessageNormalizer(),
        );

        $result = $orchestrator->handle(
            'Busca disponibilidad',
            $business->id,
            [],
            new ConversationState(negocioId: $business->id),
            'mcp',
        );

        $this->assertSame('error', $result['mode']);
        $this->assertSame('search_availability', $result['tool']);
        $this->assertSame('Fallo MCP', $result['debug']['tool_execution_error']);
    }

    public function test_it_prepares_customer_safe_winery_availability_for_the_llm(): void
    {
        [$business, $service] = $this->createWineryFixture();

        $systemPrompts = [];
        $call = 0;

        $llmClient = Mockery::mock(LLMClient::class);
        $llmClient->shouldReceive('chat')
            ->twice()
            ->andReturnUsing(function (string $systemPrompt) use (&$systemPrompts, &$call, $service) {
                $systemPrompts[] = $systemPrompt;
                $call++;

                if ($call === 1) {
                    return json_encode([
                        'assistant_message' => 'Voy a revisar plazas para esa experiencia.',
                        'state_patch' => [
                            'servicio_id' => $service->id,
                            'servicio_nombre' => $service->nombre,
                            'fecha' => '2026-04-18',
                            'numero_personas' => 4,
                            'ultima_intencion' => 'reservar',
                        ],
                        'tool_call' => [
                            'name' => 'search_availability',
                            'arguments' => [
                                'servicio_id' => $service->id,
                                'fecha' => '2026-04-18',
                                'numero_personas' => 4,
                            ],
                        ],
                        'needs_user_input' => true,
                        'conversation_status' => 'tool_call',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return json_encode([
                    'assistant_message' => 'Tengo una sesión muy buena para ese día.',
                    'state_patch' => [
                        'servicio_id' => $service->id,
                        'servicio_nombre' => $service->nombre,
                        'fecha' => '2026-04-18',
                        'numero_personas' => 4,
                        'ultima_intencion' => 'reservar',
                    ],
                    'tool_call' => null,
                    'needs_user_input' => true,
                    'conversation_status' => 'respond',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            });

        $fakeToolClient = new class implements ConversationToolClient
        {
            public function transportName(): string
            {
                return 'direct';
            }

            public function listTools(): array
            {
                return [
                    'search_availability' => [
                        'name' => 'search_availability',
                        'description' => 'Busca disponibilidad.',
                        'input_schema' => [
                            'type' => 'object',
                            'properties' => [
                                'negocio_id' => ['type' => 'integer'],
                                'servicio_id' => ['type' => 'integer'],
                                'fecha' => ['type' => 'string'],
                                'numero_personas' => ['type' => 'integer'],
                            ],
                            'required' => ['negocio_id', 'servicio_id', 'fecha'],
                        ],
                    ],
                ];
            }

            public function executeTool(string $tool, array $params): array
            {
                return [
                    'success' => true,
                    'data' => [
                        'availability_mode' => 'precise',
                        'total_slots' => 2,
                        'slots' => [
                            [
                                'hora_inicio' => '12:30',
                                'hora_fin' => '14:00',
                                'recurso_nombre' => 'Sala Lagar 1',
                                'notas_publicas' => 'Cata comentada con maridaje de quesos gallegos.',
                                'es_sesion' => true,
                                'aforo_restante' => 8,
                            ],
                            [
                                'hora_inicio' => '12:30',
                                'hora_fin' => '14:00',
                                'recurso_nombre' => 'Sala Lagar 2',
                                'notas_publicas' => 'Cata comentada con maridaje de quesos gallegos.',
                                'es_sesion' => true,
                                'aforo_restante' => 6,
                            ],
                        ],
                    ],
                ];
            }
        };

        $resolver = Mockery::mock(ConversationToolClientResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->with('direct')
            ->andReturn($fakeToolClient);

        $orchestrator = new LlmFirstChatOrchestrator(
            $resolver,
            new ChatbotProfileResolver(),
            new ConversationBehaviorProfileResolver(),
            new TurnPromptBuilder(),
            new LlmTurnEngine($llmClient),
            new ConversationStatePatcher(),
            new ConversationUserMessageNormalizer(),
        );

        $result = $orchestrator->handle(
            'Quiero una cata para 4 personas el sábado',
            $business->id,
            [],
            new ConversationState(negocioId: $business->id),
            'direct',
        );

        $this->assertSame('tool_result', $result['mode']);
        $this->assertCount(2, $systemPrompts);
        $this->assertStringContainsString('llm_customer_safe_options', $systemPrompts[1]);
        $this->assertStringContainsString('llm_reply_strategy', $systemPrompts[1]);
        $this->assertStringContainsString('cata con maridaje', $systemPrompts[1]);
        $this->assertStringNotContainsString('Sala Lagar 1', $systemPrompts[1]);
        $this->assertStringNotContainsString('Sala Lagar 2', $systemPrompts[1]);
    }

    public function test_it_detects_when_a_winery_user_looks_like_a_first_timer(): void
    {
        [$business] = $this->createWineryFixture();

        $llmClient = Mockery::mock(LLMClient::class);
        $llmClient->shouldReceive('chat')
            ->once()
            ->andReturn(json_encode([
                'assistant_message' => 'Claro, te explico cómo funcionan nuestras experiencias.',
                'state_patch' => [
                    'fase_conversacional' => 'orientacion',
                ],
                'tool_call' => null,
                'needs_user_input' => true,
                'conversation_status' => 'respond',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $fakeToolClient = new class implements ConversationToolClient
        {
            public function transportName(): string
            {
                return 'direct';
            }

            public function listTools(): array
            {
                return [];
            }

            public function executeTool(string $tool, array $params): array
            {
                throw new \RuntimeException('No debería ejecutarse ninguna tool.');
            }
        };

        $resolver = Mockery::mock(ConversationToolClientResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->with('direct')
            ->andReturn($fakeToolClient);

        $orchestrator = new LlmFirstChatOrchestrator(
            $resolver,
            new ChatbotProfileResolver(),
            new ConversationBehaviorProfileResolver(),
            new TurnPromptBuilder(),
            new LlmTurnEngine($llmClient),
            new ConversationStatePatcher(),
            new ConversationUserMessageNormalizer(),
        );

        $result = $orchestrator->handle(
            'No tengo ni idea, es nuestra primera vez en una experiencia así',
            $business->id,
            [],
            new ConversationState(negocioId: $business->id),
            'direct',
        );

        $this->assertSame('novato', $result['debug']['state_before']['nivel_conocimiento_usuario']);
        $this->assertSame('orientacion', $result['state']['fase_conversacional']);
    }

    private function createBusinessFixture(): array
    {
        $businessType = TipoNegocio::create([
            'nombre' => 'Restaurante',
        ]);

        $priceType = TipoPrecio::create([
            'nombre' => 'Fijo',
        ]);

        $business = Negocio::create([
            'nombre' => 'Restaurante Demo',
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);

        $service = Servicio::create([
            'negocio_id' => $business->id,
            'nombre' => 'Cena',
            'duracion_minutos' => 120,
            'precio_base' => 30,
            'tipo_precio_id' => $priceType->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        return [$business, $service];
    }

    private function createWineryFixture(): array
    {
        $businessType = TipoNegocio::create([
            'nombre' => 'Bodega',
        ]);

        $priceType = TipoPrecio::create([
            'nombre' => 'Por persona',
        ]);

        $business = Negocio::create([
            'nombre' => 'Bodega Demo',
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'direccion' => 'Cambados, Pontevedra',
            'descripcion_publica' => 'Bodega de Albariño con visitas y catas en Rías Baixas.',
        ]);

        $service = Servicio::create([
            'negocio_id' => $business->id,
            'nombre' => 'Cata Atlántica',
            'duracion_minutos' => 90,
            'precio_base' => 28,
            'tipo_precio_id' => $priceType->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        return [$business, $service];
    }
}
