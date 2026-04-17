<?php

namespace Tests\Feature;

use App\Mail\ReservaConfirmada;
use App\Models\Bloqueo;
use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\TipoBloqueo;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\Recurso;
use App\Tools\ToolRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateBookingToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_contact_phone_before_creating_a_booking(): void
    {
        [$negocio, $servicio] = $this->createBookingFixture();

        $result = app(ToolRegistry::class)->executeForConversation('create_booking', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'hora_inicio' => '12:30',
            'numero_personas' => 2,
            'contact_name' => 'Ana Prueba',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Falta el teléfono de contacto para cerrar la reserva.', $result['error']);
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_it_creates_a_real_booking_and_persists_contact_snapshot(): void
    {
        [$negocio, $servicio, $recurso] = $this->createBookingFixture();

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 2,
        ]);

        $slot = data_get($availability, 'data.slots.0');

        $result = app(ToolRegistry::class)->executeForConversation('create_booking', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'slot_key' => $slot['slot_key'],
            'hora_inicio' => $slot['hora_inicio'],
            'numero_personas' => 2,
            'contact_name' => 'Lucía Test',
            'contact_phone' => '600111222',
            'contact_email' => 'lucia@example.com',
            'notes' => 'Reserva de prueba automatizada',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('Confirmada', data_get($result, 'data.booking.status'));
        $this->assertSame('Lucía Test', data_get($result, 'data.booking.contact.name'));
        $this->assertSame('600111222', data_get($result, 'data.booking.contact.phone'));
        $this->assertSame([$recurso->id], data_get($result, 'data.booking.resource_ids'));

        $reservaId = data_get($result, 'data.booking.id');

        $this->assertDatabaseHas('reservas', [
            'id' => $reservaId,
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'recurso_id' => $recurso->id,
            'nombre_responsable' => 'Lucía Test',
            'telefono_responsable' => '600111222',
            'email_responsable' => 'lucia@example.com',
        ]);

        $reserva = Reserva::with(['cliente', 'reservaRecursos'])->findOrFail($reservaId);

        $this->assertSame('Lucía Test', $reserva->cliente?->nombre);
        $this->assertSame('600111222', $reserva->cliente?->telefono);
        $this->assertSame('lucia@example.com', $reserva->cliente?->email);
        $this->assertSame([$recurso->id], $reserva->reservaRecursos->pluck('recurso_id')->all());
        $this->assertNotNull($reserva->localizador);
    }

    public function test_it_allows_a_flexible_start_within_an_available_window_for_rental_style_services(): void
    {
        [$negocio, $servicio, $recurso] = $this->createRentalFixture();

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
        ]);

        $this->assertTrue($availability['success']);
        $this->assertSame('08:30', data_get($availability, 'data.slots.0.hora_inicio'));
        $this->assertSame('19:30', data_get($availability, 'data.slots.0.hora_fin'));
        $this->assertTrue((bool) data_get($availability, 'data.slots.0.accepts_start_time_within_slot'));

        $result = app(ToolRegistry::class)->executeForConversation('create_booking', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'hora_inicio' => '10:00',
            'numero_personas' => 1,
            'contact_name' => 'Adrián Test',
            'contact_phone' => '600222333',
            'contact_email' => 'adrian@example.com',
            'document_type' => 'carné de conducir',
            'document_value' => '77416801C',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('10:00', data_get($result, 'data.booking.start_time'));
        $this->assertSame('19:30', data_get($result, 'data.booking.end_time'));

        $this->assertDatabaseHas('reservas', [
            'id' => data_get($result, 'data.booking.id'),
            'recurso_id' => $recurso->id,
            'hora_inicio' => '10:00:00',
            'hora_fin' => '19:30:00',
            'tipo_documento_responsable' => 'carné de conducir',
            'documento_responsable' => '77416801C',
        ]);
    }

    public function test_it_sends_confirmation_email_when_enabled_and_contact_email_exists(): void
    {
        Mail::fake();

        [$negocio, $servicio] = $this->createBookingFixture();
        $negocio->update(['mail_confirmacion_activo' => true]);

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 2,
        ]);

        $slot = data_get($availability, 'data.slots.0');

        $result = app(ToolRegistry::class)->executeForConversation('create_booking', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'slot_key' => $slot['slot_key'],
            'hora_inicio' => $slot['hora_inicio'],
            'numero_personas' => 2,
            'contact_name' => 'Lucía Mail',
            'contact_phone' => '600333444',
            'contact_email' => 'lucia.mail@example.com',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotNull(data_get($result, 'data.booking.confirmation_email_sent_at'));
        $this->assertTrue((bool) data_get($result, 'data.booking.internal_calendar_visible'));

        Mail::assertSent(ReservaConfirmada::class, function (ReservaConfirmada $mail): bool {
            return $mail->hasTo('lucia.mail@example.com');
        });

        $this->assertDatabaseHas('reservas', [
            'id' => data_get($result, 'data.booking.id'),
        ]);

        $reserva = Reserva::findOrFail(data_get($result, 'data.booking.id'));
        $this->assertNotNull($reserva->mail_confirmacion_enviado_en);
    }

    public function test_it_returns_dynamic_experience_slots_with_remaining_capacity(): void
    {
        [$negocio, $servicio] = $this->createDynamicExperienceFixture();
        $cliente = Cliente::create(['nombre' => 'Cliente previo']);

        Reserva::create([
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'cliente_id' => $cliente->id,
            'fecha' => '2026-04-16',
            'hora_inicio' => '11:00:00',
            'hora_fin' => '12:00:00',
            'numero_personas' => 3,
            'precio_calculado' => 60,
            'estado_reserva_id' => EstadoReserva::where('nombre', 'Confirmada')->value('id'),
            'nombre_responsable' => 'Reserva previa',
            'telefono_responsable' => '600000001',
        ]);

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 4,
        ]);

        $this->assertTrue($availability['success']);
        $this->assertSame('experience_schedule', data_get($availability, 'data.availability_mode'));
        $this->assertSame(2, data_get($availability, 'data.total_slots'));
        $this->assertCount(2, data_get($availability, 'data.slots'));
        $this->assertSame(9, data_get($availability, 'data.slots.0.aforo_total'));
        $this->assertSame(6, data_get($availability, 'data.slots.0.aforo_restante'));
        $this->assertSame(33, data_get($availability, 'data.slots.0.ocupacion_porcentaje'));
        $this->assertSame(9, data_get($availability, 'data.slots.1.aforo_restante'));
    }

    public function test_service_blocks_remove_dynamic_experience_slots(): void
    {
        [$negocio, $servicio] = $this->createDynamicExperienceFixture();
        $tipoBloqueo = TipoBloqueo::create(['nombre' => 'Evento']);

        Bloqueo::create([
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'tipo_bloqueo_id' => $tipoBloqueo->id,
            'fecha' => '2026-04-16',
            'hora_inicio' => '12:00:00',
            'hora_fin' => '13:00:00',
            'activo' => true,
        ]);

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 2,
        ]);

        $this->assertTrue($availability['success']);
        $this->assertCount(1, data_get($availability, 'data.slots'));
        $this->assertSame('11:00', data_get($availability, 'data.slots.0.hora_inicio'));
    }

    public function test_it_creates_a_booking_for_a_dynamic_experience_slot_without_session_or_resource(): void
    {
        [$negocio, $servicio] = $this->createDynamicExperienceFixture();

        $availability = app(ToolRegistry::class)->executeForConversation('search_availability', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'numero_personas' => 4,
        ]);

        $slot = data_get($availability, 'data.slots.0');

        $result = app(ToolRegistry::class)->executeForConversation('create_booking', [
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-04-16',
            'slot_key' => $slot['slot_key'],
            'hora_inicio' => $slot['hora_inicio'],
            'numero_personas' => 4,
            'contact_name' => 'Reserva Dinámica',
            'contact_phone' => '600444555',
            'contact_email' => 'dinamica@example.com',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame([], data_get($result, 'data.booking.resource_ids'));

        $this->assertDatabaseHas('reservas', [
            'id' => data_get($result, 'data.booking.id'),
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'recurso_id' => null,
            'sesion_id' => null,
            'hora_inicio' => '11:00:00',
            'hora_fin' => '12:00:00',
            'numero_personas' => 4,
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

    private function createRentalFixture(): array
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Rent a car']);
        $tipoPrecio = TipoPrecio::create(['nombre' => 'Por día']);
        $tipoRecurso = TipoRecurso::create(['nombre' => 'Vehículo']);

        EstadoReserva::create(['nombre' => 'Pendiente']);
        EstadoReserva::create(['nombre' => 'Confirmada']);

        $negocio = Negocio::create([
            'nombre' => 'Autos Test',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);

        $servicio = Servicio::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Turismo compacto',
            'duracion_minutos' => 660,
            'precio_base' => 35,
            'tipo_precio_id' => $tipoPrecio->id,
            'requiere_pago' => true,
            'activo' => true,
            'documentacion_requerida' => 'DNI o pasaporte + carné de conducir vigente.',
            'precio_por_unidad_tiempo' => true,
        ]);

        $recurso = Recurso::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Peugeot 208',
            'tipo_recurso_id' => $tipoRecurso->id,
            'capacidad' => 1,
            'activo' => true,
        ]);

        $servicio->recursos()->sync([$recurso->id]);

        Disponibilidad::create([
            'recurso_id' => $recurso->id,
            'dia_semana' => Carbon::parse('2026-04-16')->dayOfWeek,
            'hora_inicio' => '08:30:00',
            'hora_fin' => '19:30:00',
            'activo' => true,
            'nombre_turno' => 'Horario de oficina',
            'buffer_minutos' => 30,
        ]);

        return [$negocio, $servicio, $recurso];
    }

    private function createDynamicExperienceFixture(): array
    {
        $tipoNegocio = TipoNegocio::create(['nombre' => 'Bodega']);
        $tipoPrecio = TipoPrecio::create(['nombre' => 'Por persona']);

        EstadoReserva::create(['nombre' => 'Pendiente']);
        EstadoReserva::create(['nombre' => 'Confirmada']);
        EstadoReserva::create(['nombre' => 'Cancelada']);
        EstadoReserva::create(['nombre' => 'No presentada']);

        $negocio = Negocio::create([
            'nombre' => 'Bodega Dinámica Test',
            'tipo_negocio_id' => $tipoNegocio->id,
            'zona_horaria' => 'Europe/Madrid',
            'dias_apertura' => [(int) Carbon::parse('2026-04-16')->dayOfWeek],
            'activo' => true,
        ]);

        $servicio = Servicio::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Cata Atlántica',
            'duracion_minutos' => 60,
            'numero_personas_minimo' => 2,
            'numero_personas_maximo' => 9,
            'aforo' => 9,
            'hora_inicio' => '11:00:00',
            'hora_fin' => '13:00:00',
            'precio_base' => 20,
            'tipo_precio_id' => $tipoPrecio->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        return [$negocio, $servicio];
    }
}
