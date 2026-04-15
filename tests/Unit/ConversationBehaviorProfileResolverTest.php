<?php

namespace Tests\Unit;

use App\Models\Negocio;
use App\Models\TipoNegocio;
use App\Services\Conversation\ConversationBehaviorProfileResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationBehaviorProfileResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_a_winery_profile_for_bodega_businesses(): void
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Bodega']);

        $negocio = Negocio::create([
            'nombre' => 'Bodega Test',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);

        $profile = (new ConversationBehaviorProfileResolver())->resolve($negocio);

        $this->assertSame('winery', $profile->sectorKey);
        $this->assertStringContainsString('bodega', mb_strtolower($profile->sectorLabel, 'UTF-8'));
        $this->assertTrue(
            collect($profile->vocabularyHints)->contains(fn (string $hint) => str_contains(mb_strtolower($hint, 'UTF-8'), 'vino'))
        );
    }
}
