<?php

namespace Tests\Feature;

use App\Jobs\ImportGoogleCalendarEventsJob;
use App\Models\Integracion;
use App\Models\IntegracionCuenta;
use App\Models\IntegracionMapeo;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\TipoNegocio;
use App\Models\TipoRecurso;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GoogleCalendarBackofficeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        config()->set('services.google_calendar.client_id', 'google-client-id');
        config()->set('services.google_calendar.client_secret', 'google-client-secret');
        config()->set('services.google_calendar.auth_base_url', 'https://accounts.google.com/o/oauth2/v2/auth');
        config()->set('services.google_calendar.token_url', 'https://oauth2.googleapis.com/token');
        config()->set('services.google_calendar.api_base_url', 'https://www.googleapis.com/calendar/v3');
        config()->set('services.google_calendar.import_days', 30);
        config()->set('services.google_calendar.scopes', [
            'https://www.googleapis.com/auth/calendar',
        ]);
    }

    public function test_admin_connect_redirects_to_google_and_enables_the_integration(): void
    {
        $user = User::factory()->create();
        $negocio = $this->createBusiness();
        $user->negocios()->attach($negocio);

        $response = $this->actingAs($user)
            ->get(route('admin.negocios.google-calendar.connect', $negocio));

        $response->assertRedirect();
        $this->assertStringStartsWith(
            'https://accounts.google.com/o/oauth2/v2/auth?',
            (string) $response->headers->get('Location')
        );

        $this->assertDatabaseHas('integraciones', [
            'negocio_id' => $negocio->id,
            'proveedor' => 'google_calendar',
            'activo' => true,
        ]);
    }

    public function test_admin_callback_persists_the_connected_account_and_available_calendars(): void
    {
        $negocio = $this->createBusiness();
        $state = Crypt::encryptString(json_encode([
            'business_id' => $negocio->id,
            'issued_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
                'refresh_token' => 'google-refresh-token',
                'expires_in' => 3600,
                'scope' => 'https://www.googleapis.com/auth/calendar',
                'token_type' => 'Bearer',
            ]),
            'https://www.googleapis.com/calendar/v3/users/me/calendarList*' => Http::response([
                'items' => [
                    [
                        'id' => 'primary@example.com',
                        'summary' => 'Agenda principal',
                        'timeZone' => 'Europe/Madrid',
                        'primary' => true,
                        'accessRole' => 'owner',
                    ],
                    [
                        'id' => 'equipo@example.com',
                        'summary' => 'Equipo',
                        'timeZone' => 'Europe/Madrid',
                        'primary' => false,
                        'accessRole' => 'writer',
                    ],
                ],
            ]),
        ]);

        $response = $this->get(route('admin.integraciones.google.callback', [
            'code' => 'oauth-code',
            'state' => $state,
        ]));

        $response->assertRedirect(route('admin.negocios.edit', $negocio));

        $this->assertDatabaseHas('integraciones', [
            'negocio_id' => $negocio->id,
            'proveedor' => 'google_calendar',
            'estado' => 'conectada',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('integracion_cuentas', [
            'email_externo' => 'primary@example.com',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('integracion_mapeos', [
            'external_id' => 'primary@example.com',
            'nombre_externo' => 'Agenda principal',
            'es_primario' => true,
            'seleccionado' => true,
        ]);
        $this->assertDatabaseHas('integracion_mapeos', [
            'external_id' => 'equipo@example.com',
            'nombre_externo' => 'Equipo',
            'seleccionado' => false,
        ]);
    }

    public function test_admin_can_review_the_panel_and_save_calendar_selection(): void
    {
        $user = User::factory()->create();
        [$negocio, $recursoPrincipal, $recursoSecundario] = $this->createBusinessWithResources();
        $user->negocios()->attach($negocio);
        $integracion = $this->createConnectedGoogleIntegration($negocio, $recursoPrincipal);

        $secondaryMapping = IntegracionMapeo::create([
            'integracion_id' => $integracion->id,
            'tipo_origen' => 'calendario',
            'external_id' => 'equipo@example.com',
            'nombre_externo' => 'Equipo',
            'timezone' => 'Europe/Madrid',
            'es_primario' => false,
            'seleccionado' => false,
            'negocio_id' => $negocio->id,
            'recurso_id' => null,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.negocios.edit', $negocio))
            ->assertOk()
            ->assertSeeText('Google Calendar')
            ->assertSeeText('primary@example.com')
            ->assertSeeText('Agenda principal')
            ->assertSeeText('Importar ahora');

        $response = $this->actingAs($user)
            ->put(route('admin.negocios.google-calendar.calendars.update', $negocio), [
                'selected' => [
                    $secondaryMapping->id => '1',
                ],
                'resource_ids' => [
                    $secondaryMapping->id => $recursoSecundario->id,
                ],
            ]);

        $response->assertRedirect(route('admin.negocios.edit', $negocio));

        $this->assertDatabaseHas('integracion_mapeos', [
            'id' => $secondaryMapping->id,
            'seleccionado' => true,
            'recurso_id' => $recursoSecundario->id,
        ]);
        $this->assertDatabaseHas('integracion_mapeos', [
            'integracion_id' => $integracion->id,
            'external_id' => 'primary@example.com',
            'seleccionado' => false,
        ]);
    }

    public function test_admin_can_queue_an_initial_import_from_the_backoffice(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        [$negocio, $recursoPrincipal] = $this->createBusinessWithResources();
        $user->negocios()->attach($negocio);
        $this->createConnectedGoogleIntegration($negocio, $recursoPrincipal);

        $response = $this->actingAs($user)
            ->post(route('admin.negocios.google-calendar.import', $negocio), [
                'days_ahead' => 45,
            ]);

        $response->assertRedirect(route('admin.negocios.edit', $negocio));

        Queue::assertPushed(ImportGoogleCalendarEventsJob::class, function (ImportGoogleCalendarEventsJob $job) use ($negocio) {
            return $job->businessId === $negocio->id
                && $job->daysAhead === 45;
        });
    }

    private function createBusiness(): Negocio
    {
        $tipoNegocio = TipoNegocio::create([
            'nombre' => 'Restaurante',
        ]);

        return Negocio::create([
            'nombre' => 'Restaurante Demo',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);
    }

    private function createBusinessWithResources(): array
    {
        $negocio = $this->createBusiness();
        $tipoRecurso = TipoRecurso::create([
            'nombre' => 'Mesa',
        ]);

        $recursoPrincipal = Recurso::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Mesa 1',
            'tipo_recurso_id' => $tipoRecurso->id,
            'capacidad' => 4,
            'activo' => true,
        ]);

        $recursoSecundario = Recurso::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Mesa 2',
            'tipo_recurso_id' => $tipoRecurso->id,
            'capacidad' => 4,
            'activo' => true,
        ]);

        return [$negocio, $recursoPrincipal, $recursoSecundario];
    }

    private function createConnectedGoogleIntegration(Negocio $negocio, ?Recurso $recurso = null): Integracion
    {
        $integracion = Integracion::create([
            'negocio_id' => $negocio->id,
            'proveedor' => 'google_calendar',
            'nombre' => 'Google Calendar',
            'modo_operacion' => 'coexistencia',
            'estado' => 'conectada',
            'activo' => true,
        ]);

        IntegracionCuenta::create([
            'integracion_id' => $integracion->id,
            'email_externo' => 'primary@example.com',
            'nombre_externo' => 'Cuenta principal',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expira_en' => now()->addHour(),
            'activo' => true,
        ]);

        IntegracionMapeo::create([
            'integracion_id' => $integracion->id,
            'tipo_origen' => 'calendario',
            'external_id' => 'primary@example.com',
            'nombre_externo' => 'Agenda principal',
            'timezone' => 'Europe/Madrid',
            'es_primario' => true,
            'seleccionado' => true,
            'negocio_id' => $negocio->id,
            'recurso_id' => $recurso?->id,
            'activo' => true,
        ]);

        return $integracion;
    }
}
