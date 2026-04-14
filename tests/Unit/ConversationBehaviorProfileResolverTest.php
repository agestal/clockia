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

    public function test_it_resolves_restaurant_specific_behavior_rules(): void
    {
        $businessType = TipoNegocio::create([
            'nombre' => 'Restaurante',
        ]);

        $business = Negocio::create([
            'nombre' => 'Restaurante Demo',
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);

        $profile = app(ConversationBehaviorProfileResolver::class)->resolve($business);

        $this->assertSame('restaurant', $profile->sectorKey);
        $this->assertTrue($profile->hidesInternalResourceNamesByDefault());
        $this->assertSame('Camarero, maître o persona de sala', $profile->humanRole);
        $this->assertStringContainsString('No enumeres mesas concretas', implode(' ', $profile->specialNotes));
    }
}
