<?php

namespace Tests\Feature;

use App\Models\Negocio;
use App\Models\TipoNegocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_widget_panel_instead_of_upcoming_reservations(): void
    {
        $user = User::factory()->create();
        $firstBusiness = $this->createBusiness('Bodega Norte', true);
        $secondBusiness = $this->createBusiness('Bodega Sur', false);

        $user->negocios()->attach([$firstBusiness->id, $secondBusiness->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Widgets del calendario')
            ->assertSeeText('Bodega Norte')
            ->assertSeeText('Bodega Sur')
            ->assertSeeText('Activo')
            ->assertSeeText('Inactivo')
            ->assertSee(route('admin.negocios.edit', $firstBusiness).'#widget-calendar-settings', false)
            ->assertSee(route('admin.negocios.edit', $secondBusiness).'#widget-calendar-settings', false)
            ->assertDontSeeText('Próximas reservas');
    }

    private function createBusiness(string $name, bool $widgetEnabled): Negocio
    {
        $businessType = TipoNegocio::firstOrCreate([
            'nombre' => 'Restaurante',
        ]);

        return Negocio::create([
            'nombre' => $name,
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'widget_enabled' => $widgetEnabled,
        ]);
    }
}
