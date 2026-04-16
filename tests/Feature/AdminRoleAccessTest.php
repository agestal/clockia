<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Recurso;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_sees_business_dashboard_and_limited_menu(): void
    {
        $user = User::factory()->businessAdmin()->create([
            'name' => 'Admin Local',
        ]);
        $business = $this->createBusiness('Bodega Local', [
            'widget_enabled' => true,
        ]);

        $user->negocios()->attach($business);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Mi negocio')
            ->assertSeeText('Bodega Local')
            ->assertSeeText('Google Calendar')
            ->assertSeeText('Widget calendario')
            ->assertSeeText('Negocios')
            ->assertSeeText('Reservas')
            ->assertDontSeeText('Configurar la app')
            ->assertDontSeeText('Chat Test')
            ->assertDontSeeText('Ver sitio web');
    }

    public function test_business_admin_negocios_index_redirects_to_own_business_configuration(): void
    {
        $user = User::factory()->businessAdmin()->create();
        $business = $this->createBusiness('Bodega Local');

        $user->negocios()->attach($business);

        $response = $this->actingAs($user)->get(route('admin.negocios.index'));

        $response->assertRedirect(route('admin.negocios.edit', $business));
    }

    public function test_business_admin_cannot_access_platform_catalogs_or_other_businesses(): void
    {
        $user = User::factory()->businessAdmin()->create();
        $allowedBusiness = $this->createBusiness('Bodega Local');
        $otherBusiness = $this->createBusiness('Bodega Externa');

        $user->negocios()->attach($allowedBusiness);

        $this->actingAs($user)
            ->get(route('admin.tipos-negocio.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.negocios.edit', $otherBusiness))
            ->assertForbidden();
    }

    public function test_business_admin_only_sees_reservations_from_assigned_business(): void
    {
        $user = User::factory()->businessAdmin()->create();
        $business = $this->createBusiness('Bodega Local');
        $otherBusiness = $this->createBusiness('Bodega Externa');

        $user->negocios()->attach($business);

        $this->createReservation($business, 'LOCAL1234');
        $this->createReservation($otherBusiness, 'EXTERNA9');

        $response = $this->actingAs($user)->get(route('admin.reservas.index'));

        $response->assertOk()
            ->assertSeeText('LOCAL1234')
            ->assertDontSeeText('EXTERNA9')
            ->assertSeeText('Bodega Local')
            ->assertDontSeeText('Bodega Externa');
    }

    public function test_platform_admin_keeps_access_to_platform_sections(): void
    {
        $user = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Configurar la app')
            ->assertSeeText('Chat Test')
            ->assertSeeText('Ver sitio web');
    }

    public function test_clockia_domain_user_has_full_access_without_platform_role(): void
    {
        $user = User::factory()->businessAdmin()->create([
            'email' => 'jacobo@clockia.net',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Configurar la app')
            ->assertSeeText('Chat Test');
    }

    public function test_adrian_email_has_full_access_without_platform_role(): void
    {
        $user = User::factory()->businessAdmin()->create([
            'email' => 'adrian88gm@gmail.com',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Configurar la app')
            ->assertSeeText('Chat Test');
    }

    private function createBusiness(string $name, array $overrides = []): Negocio
    {
        $businessType = TipoNegocio::firstOrCreate([
            'nombre' => 'Restaurante',
        ]);

        return Negocio::create(array_merge([
            'nombre' => $name,
            'tipo_negocio_id' => $businessType->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ], $overrides));
    }

    private function createReservation(Negocio $business, string $locator): Reserva
    {
        $priceType = TipoPrecio::firstOrCreate([
            'nombre' => 'Precio fijo',
        ]);
        $resourceType = TipoRecurso::firstOrCreate([
            'nombre' => 'Sala',
        ]);
        $status = EstadoReserva::firstOrCreate([
            'nombre' => 'Confirmada',
        ]);

        $service = Servicio::create([
            'negocio_id' => $business->id,
            'nombre' => 'Visita guiada',
            'duracion_minutos' => 90,
            'precio_base' => '25.00',
            'tipo_precio_id' => $priceType->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        $resource = Recurso::create([
            'negocio_id' => $business->id,
            'nombre' => 'Sala principal',
            'tipo_recurso_id' => $resourceType->id,
            'capacidad' => 12,
            'activo' => true,
            'combinable' => false,
        ]);

        $client = Cliente::create([
            'nombre' => 'Cliente '.$locator,
            'email' => strtolower($locator).'@example.test',
        ]);

        return Reserva::withoutEvents(function () use ($business, $service, $resource, $client, $status, $locator) {
            return Reserva::create([
                'negocio_id' => $business->id,
                'servicio_id' => $service->id,
                'recurso_id' => $resource->id,
                'cliente_id' => $client->id,
                'fecha' => now()->addDay()->toDateString(),
                'hora_inicio' => '10:00:00',
                'hora_fin' => '11:30:00',
                'numero_personas' => 2,
                'precio_calculado' => '25.00',
                'precio_total' => '25.00',
                'estado_reserva_id' => $status->id,
                'localizador' => $locator,
                'documentacion_entregada' => false,
            ]);
        });
    }
}
