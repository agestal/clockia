<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\Recurso;
use App\Tools\ToolRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
