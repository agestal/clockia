<?php

namespace Tests\Unit;

use App\Models\Negocio;
use App\Models\TipoNegocio;
use App\Services\Conversation\ChatbotProfileResolver;
use App\Services\Conversation\ConversationBehaviorProfileResolver;
use App\Services\Conversation\ConversationState;
use App\Services\Conversation\TurnPromptBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TurnPromptBuilderBodegaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_includes_winery_knowledge_and_denomination_hints_in_the_prompt(): void
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Bodega']);

        $negocio = Negocio::create([
            'nombre' => 'Bodegas Viña Atlántica',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'direccion' => 'Lugar de Castrelo, 18, 36639 Cambados, Pontevedra',
            'descripcion_publica' => 'Bodega de Albariño en Rías Baixas con experiencias y catas comentadas.',
        ]);

        $profile = (new ChatbotProfileResolver())->resolve($negocio);
        $behavior = (new ConversationBehaviorProfileResolver())->resolve($negocio);

        $prompt = (new TurnPromptBuilder())->buildInitialPrompt(
            $profile,
            $behavior,
            new ConversationState(negocioId: $negocio->id),
            [],
            [],
            [],
            Carbon::parse('2026-04-15 12:00:00', 'Europe/Madrid'),
        );

        $this->assertStringContainsString('CONOCIMIENTO SECTORIAL EXTRA - BODEGA Y ENOLOGIA', $prompt);
        $this->assertStringContainsString('D.O. Rias Baixas', $prompt);
    }
}
