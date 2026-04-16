<?php

namespace Tests\Feature;

use App\Models\Negocio;
use App\Models\TipoNegocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessShortcutTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_calendar_shortcut_redirects_directly_when_user_has_one_business(): void
    {
        $user = User::factory()->create();
        $negocio = $this->createBusiness('Bodega Uno');
        $user->negocios()->attach($negocio);

        $response = $this->actingAs($user)
            ->get(route('admin.negocios.shortcuts.google-calendar'));

        $response->assertRedirect(
            route('admin.negocios.edit', $negocio).'#google-calendar-settings'
        );
    }

    public function test_widget_shortcut_shows_selector_when_user_has_multiple_businesses(): void
    {
        $user = User::factory()->create();
        $firstBusiness = $this->createBusiness('Bodega Norte');
        $secondBusiness = $this->createBusiness('Bodega Sur');

        $user->negocios()->attach([$firstBusiness->id, $secondBusiness->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.negocios.shortcuts.widget'));

        $response->assertOk()
            ->assertSeeText('Widget Calendario')
            ->assertSeeText('Bodega Norte')
            ->assertSeeText('Bodega Sur')
            ->assertSee(route('admin.negocios.edit', $firstBusiness).'#widget-calendar-settings', false)
            ->assertSee(route('admin.negocios.edit', $secondBusiness).'#widget-calendar-settings', false);
    }

    private function createBusiness(string $name): Negocio
    {
        $businessType = TipoNegocio::firstOrCreate([
            'nombre' => 'Restaurante',
        ]);

        return Negocio::create([
            'nombre' => $name,
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);
    }
}
