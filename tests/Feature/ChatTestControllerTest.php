<?php

namespace Tests\Feature;

use App\Models\Negocio;
use App\Models\TipoNegocio;
use App\Models\User;
use App\Services\Chat\LlmFirstChatOrchestrator;
use App\Services\Conversation\ConversationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Mockery;
use Tests\TestCase;

class ChatTestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_routes_mcp_requests_through_the_new_llm_first_orchestrator(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $business = $this->createBusiness();
        $user = User::factory()->create();

        $this->mock(LlmFirstChatOrchestrator::class, function ($mock) use ($business) {
            $mock->shouldReceive('handle')
                ->once()
                ->with(
                    'Hola',
                    $business->id,
                    [],
                    Mockery::on(fn ($state) => $state instanceof ConversationState && $state->negocioId === $business->id),
                    'mcp',
                )
                ->andReturn($this->fakeResult($business->id));
        });

        $token = 'csrf-test-token';
        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('admin.chat-test.execute'), [
                'message' => 'Hola',
                'negocio_id' => $business->id,
                'mode' => 'mcp',
            ], [
                'Accept' => 'application/json',
                'X-CSRF-TOKEN' => $token,
            ]);

        $response->assertOk()
            ->assertJsonPath('execution_mode', 'mcp')
            ->assertJsonPath('response', 'Respuesta ok');
    }

    public function test_it_routes_direct_requests_through_the_same_llm_first_orchestrator(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $business = $this->createBusiness();
        $user = User::factory()->create();

        $this->mock(LlmFirstChatOrchestrator::class, function ($mock) use ($business) {
            $mock->shouldReceive('handle')
                ->once()
                ->with(
                    'Hola',
                    $business->id,
                    [],
                    Mockery::on(fn ($state) => $state instanceof ConversationState && $state->negocioId === $business->id),
                    'direct',
                )
                ->andReturn($this->fakeResult($business->id));
        });

        $token = 'csrf-test-token';
        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('admin.chat-test.execute'), [
                'message' => 'Hola',
                'negocio_id' => $business->id,
                'mode' => 'direct',
            ], [
                'Accept' => 'application/json',
                'X-CSRF-TOKEN' => $token,
            ]);

        $response->assertOk()
            ->assertJsonPath('execution_mode', 'direct')
            ->assertJsonPath('response', 'Respuesta ok');
    }

    private function createBusiness(): Negocio
    {
        $businessType = TipoNegocio::create([
            'nombre' => 'Restaurante',
        ]);

        return Negocio::create([
            'nombre' => 'Restaurante Demo',
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);
    }

    private function fakeResult(int $businessId): array
    {
        return [
            'mode' => 'respond',
            'tool' => null,
            'params' => null,
            'missing_fields' => [],
            'tool_result' => null,
            'response' => 'Respuesta ok',
            'debug' => ['engine' => 'llm_first'],
            'state' => (new ConversationState(negocioId: $businessId))->toArray(),
        ];
    }
}
