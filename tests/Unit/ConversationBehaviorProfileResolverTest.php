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

    public function test_it_applies_business_specific_conversation_overrides(): void
    {
        $businessType = TipoNegocio::create([
            'nombre' => 'Restaurante',
        ]);

        $business = Negocio::create([
            'nombre' => 'Restaurante Demo',
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'chat_behavior_overrides' => [
                'human_role' => 'Host de sala',
                'default_register' => 'Elegante y premium.',
                'inventory_exposure_policy' => 'allow_detailed_inventory',
                'vocabulary_hints' => ['maridaje', 'carta'],
            ],
        ]);

        $profile = app(ConversationBehaviorProfileResolver::class)->resolve($business);

        $this->assertSame('Host de sala', $profile->humanRole);
        $this->assertSame('Elegante y premium.', $profile->defaultRegister);
        $this->assertSame('allow_detailed_inventory', $profile->inventoryExposurePolicy);
        $this->assertSame(['maridaje', 'carta'], $profile->vocabularyHints);
    }
}
