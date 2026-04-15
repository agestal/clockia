<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EstadoReserva;
use App\Models\Integracion;
use App\Models\IntegracionCuenta;
use App\Models\IntegracionMapeo;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\Recurso;
use App\Services\Integrations\GoogleCalendarImportService;
use App\Tools\ToolRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_free_busy_filters_out_slots_that_are_busy_in_google_calendar(): void
    {
        [$negocio, $servicio, $recurso] = $this->createBookingFixture();
        $this->createConnectedGoogleIntegration($negocio, $recurso);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/freeBusy' => Http::response([
                'calendars' => [
                    'primary@example.com' => [
                        'busy' => [
                            [
                                'start' => '2026-04-16T12:30:00+02:00',
                                'end' => '2026-04-16T14:30:00+02:00',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 2,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, data_get($result, 'data.total_slots'));
        $this->assertSame('14:30', data_get($result, 'data.slots.0.hora_inicio'));
        $this->assertSame('16:30', data_get($result, 'data.slots.0.hora_fin'));
    }

    public function test_booking_creation_syncs_to_google_calendar_without_breaking_the_booking(): void
    {
        [$negocio, $servicio, $recurso] = $this->createBookingFixture();
        $this->createConnectedGoogleIntegration($negocio, $recurso);
        $cliente = Cliente::create([
            'nombre' => 'Cliente Sync',
            'telefono' => '600999888',
            'email' => 'sync@example.com',
        ]);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/*/events' => Http::response([
                'id' => 'google-event-123',
                'status' => 'confirmed',
                'htmlLink' => 'https://calendar.google.com/event?eid=123',
            ], 200),
        ]);

        $reserva = Reserva::create([
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'recurso_id' => $recurso->id,
            'cliente_id' => $cliente->id,
            'nombre_responsable' => 'Cliente Sync',
            'email_responsable' => 'sync@example.com',
            'telefono_responsable' => '600999888',
            'fecha' => '2026-04-16',
            'hora_inicio' => '12:30',
            'hora_fin' => '14:30',
            'numero_personas' => 2,
            'precio_calculado' => 50,
            'estado_reserva_id' => EstadoReserva::where('nombre', 'Confirmada')->value('id'),
            'localizador' => Reserva::generarLocalizador(),
            'importada_externamente' => false,
        ]);

        $this->assertDatabaseHas('reservas', [
            'id' => $reserva->id,
        ]);

        $this->assertDatabaseHas('reserva_integraciones', [
            'reserva_id' => $reserva->id,
            'proveedor' => 'google_calendar',
            'external_id' => 'google-event-123',
            'external_calendar_id' => 'primary@example.com',
            'direccion_sync' => 'clockia_to_google',
            'estado_sync' => 'synced',
        ]);
    }

    public function test_initial_import_avoids_collisions_between_calendars_with_the_same_event_id(): void
    {
        [$negocio, , $recurso] = $this->createBookingFixture();
        $integracion = $this->createConnectedGoogleIntegration($negocio, $recurso);

        IntegracionMapeo::create([
            'integracion_id' => $integracion->id,
            'tipo_origen' => 'calendario',
            'external_id' => 'secondary@example.com',
            'nombre_externo' => 'Secondary',
            'timezone' => 'Europe/Madrid',
            'es_primario' => false,
            'seleccionado' => true,
            'negocio_id' => $negocio->id,
            'recurso_id' => null,
            'activo' => true,
        ]);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary%40example.com/events*' => Http::response([
                'items' => [
                    [
                        'id' => 'shared-event',
                        'status' => 'confirmed',
                        'summary' => 'Evento primario',
                        'start' => ['dateTime' => '2026-04-16T10:00:00+02:00'],
                        'end' => ['dateTime' => '2026-04-16T11:00:00+02:00'],
                    ],
                ],
            ]),
            'https://www.googleapis.com/calendar/v3/calendars/secondary%40example.com/events*' => Http::response([
                'items' => [
                    [
                        'id' => 'shared-event',
                        'status' => 'confirmed',
                        'summary' => 'Evento secundario',
                        'start' => ['dateTime' => '2026-04-16T12:00:00+02:00'],
                        'end' => ['dateTime' => '2026-04-16T13:00:00+02:00'],
                    ],
                ],
            ]),
        ]);

        $imported = app(GoogleCalendarImportService::class)->importUpcomingEvents($negocio->id, 30);

        $this->assertCount(2, $imported);
        $this->assertDatabaseHas('ocupaciones_externas', [
            'proveedor' => 'google_calendar',
            'external_calendar_id' => 'primary@example.com',
            'external_id' => 'shared-event',
        ]);
        $this->assertDatabaseHas('ocupaciones_externas', [
            'proveedor' => 'google_calendar',
            'external_calendar_id' => 'secondary@example.com',
            'external_id' => 'shared-event',
        ]);
    }

    private function createBookingFixture(): array
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Restaurante']);
        $tipoPrecio = TipoPrecio::create(['nombre' => 'Fijo']);
        $tipoRecurso = TipoRecurso::create(['nombre' => 'Mesa']);

        EstadoReserva::create(['nombre' => 'Pendiente']);
        EstadoReserva::create(['nombre' => 'Confirmada']);

        $negocio = Negocio::create([
            'nombre' => 'Restaurante Test',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);

        $servicio = Servicio::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Comida',
            'duracion_minutos' => 120,
            'precio_base' => 25,
            'tipo_precio_id' => $tipoPrecio->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        $recurso = Recurso::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Mesa 1',
            'tipo_recurso_id' => $tipoRecurso->id,
            'capacidad' => 4,
            'activo' => true,
        ]);

        $servicio->recursos()->sync([$recurso->id]);

        Disponibilidad::create([
            'recurso_id' => $recurso->id,
            'dia_semana' => Carbon::parse('2026-04-16')->dayOfWeek,
            'hora_inicio' => '12:30:00',
            'hora_fin' => '16:30:00',
            'activo' => true,
            'nombre_turno' => 'Servicio de comida',
        ]);

        return [$negocio, $servicio, $recurso];
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
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expira_en' => now()->addHour(),
            'activo' => true,
        ]);

        IntegracionMapeo::create([
            'integracion_id' => $integracion->id,
            'tipo_origen' => 'calendario',
            'external_id' => 'primary@example.com',
            'nombre_externo' => 'Primary',
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
