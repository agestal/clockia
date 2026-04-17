<?php

namespace Tests\Feature;

use App\Mail\Admin\ReservaModificadaAdmin;
use App\Mail\ReservaModificada;
use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\ReservaRecurso;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Tools\ToolRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ModifyBookingToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_modifies_an_existing_booking_and_sends_customer_and_admin_emails(): void
    {
        Mail::fake();

        [$negocio, $reserva] = $this->createBookingToModify();

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $reserva->servicio_id,
            'fecha' => '2026-04-20',
            'numero_personas' => 3,
            'exclude_reserva_id' => $reserva->id,
        ]);

        $this->assertTrue($availability['success'], json_encode($availability));
        $this->assertNotEmpty(data_get($availability, 'data.slots'), json_encode($availability));

        $result = app(ToolRegistry::class)->executeForConversation('modify_booking', [
            'negocio_id' => $negocio->id,
            'locator' => $reserva->localizador,
            'numero_personas' => 3,
        ]);

        $this->assertTrue($result['success'], (string) ($result['error'] ?? ''));
        $this->assertSame(3, data_get($result, 'data.booking.party_size'));
        $this->assertNotNull(data_get($result, 'data.booking.modification_email_sent_at'));
        $this->assertTrue((bool) data_get($result, 'data.admin_notification_expected'));

        $changes = data_get($result, 'data.change_summary', []);
        $this->assertNotEmpty($changes);
        $this->assertTrue(collect($changes)->contains(
            fn (array $item) => ($item['field'] ?? null) === 'party_size' && ($item['before'] ?? null) === '2' && ($item['after'] ?? null) === '3'
        ));

        Mail::assertSent(ReservaModificada::class, function (ReservaModificada $mail): bool {
            return $mail->hasTo('cliente@example.com');
        });

        Mail::assertSent(ReservaModificadaAdmin::class, function (ReservaModificadaAdmin $mail): bool {
            return $mail->hasTo('admin.negocio@example.com');
        });

        $reserva->refresh();

        $this->assertSame(3, $reserva->numero_personas);
        $this->assertNotNull($reserva->mail_modificacion_enviado_en);
    }

    public function test_modify_booking_is_exposed_through_the_mcp_bridge(): void
    {
        config(['services.mcp.bridge_token' => 'test-bridge-token']);

        $response = $this->withHeader('Authorization', 'Bearer test-bridge-token')
            ->getJson('/api/mcp/tools');

        $response->assertOk();
        $response->assertJsonPath('tools.modify_booking.name', 'modify_booking');
        $response->assertJsonPath('tools.modify_booking.llm_guidance.when_to_use.0', 'Cuando el usuario ya tiene una reserva hecha y quiere cambiar fecha, hora, personas, experiencia o datos de contacto.');
    }

    private function createBookingToModify(): array
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Bodega']);
        $tipoPrecio = TipoPrecio::create(['nombre' => 'Fijo']);
        $tipoRecurso = TipoRecurso::create(['nombre' => 'Sala']);

        EstadoReserva::create(['nombre' => 'Pendiente']);
        $estadoConfirmada = EstadoReserva::create(['nombre' => 'Confirmada']);
        EstadoReserva::create(['nombre' => 'Cancelada']);
        EstadoReserva::create(['nombre' => 'No presentada']);

        $negocio = Negocio::create([
            'nombre' => 'Bodega Test',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'mail_confirmacion_activo' => true,
            'notif_email_destino' => 'admin.negocio@example.com',
            'notif_reserva_nueva' => false,
            'notif_reserva_modificada' => true,
        ]);

        $servicio = Servicio::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Visita con cata',
            'duracion_minutos' => 120,
            'precio_base' => 35,
            'tipo_precio_id' => $tipoPrecio->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        $recurso = Recurso::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Sala Atlantica',
            'tipo_recurso_id' => $tipoRecurso->id,
            'capacidad' => 4,
            'activo' => true,
        ]);

        $servicio->recursos()->sync([$recurso->id]);

        Disponibilidad::create([
            'recurso_id' => $recurso->id,
            'dia_semana' => Carbon::parse('2026-04-20')->dayOfWeek,
            'hora_inicio' => '12:30:00',
            'hora_fin' => '16:30:00',
            'activo' => true,
            'nombre_turno' => 'Visita con cata',
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Cliente Test',
            'email' => 'cliente@example.com',
            'telefono' => '600123123',
        ]);

        $reserva = Reserva::withoutEvents(function () use ($negocio, $servicio, $recurso, $cliente, $estadoConfirmada) {
            return Reserva::create([
                'negocio_id' => $negocio->id,
                'servicio_id' => $servicio->id,
                'recurso_id' => $recurso->id,
                'cliente_id' => $cliente->id,
                'nombre_responsable' => 'Cliente Test',
                'email_responsable' => 'cliente@example.com',
                'telefono_responsable' => '600123123',
                'fecha' => '2026-04-20',
                'hora_inicio' => '12:30:00',
                'hora_fin' => '14:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 70,
                'estado_reserva_id' => $estadoConfirmada->id,
                'notas' => 'Reserva inicial',
                'localizador' => 'MOD-TEST-01',
                'horas_minimas_cancelacion' => 24,
                'permite_modificacion' => true,
                'es_reembolsable' => true,
                'origen_reserva' => 'chat',
                'importada_externamente' => false,
            ]);
        });

        ReservaRecurso::create([
            'reserva_id' => $reserva->id,
            'recurso_id' => $recurso->id,
            'fecha' => $reserva->fecha,
            'hora_inicio' => $reserva->hora_inicio,
            'hora_fin' => $reserva->hora_fin,
            'fecha_inicio_datetime' => $reserva->inicio_datetime,
            'fecha_fin_datetime' => $reserva->fin_datetime,
            'notas' => null,
        ]);

        return [$negocio, $reserva->fresh(['negocio', 'servicio', 'cliente', 'recurso', 'reservaRecursos.recurso'])];
    }
}
