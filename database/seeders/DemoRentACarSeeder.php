<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EstadoPago;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPago;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Demo: Rent-a-car inspirado en Autos Castiñeira (Pontecaldelas, Pontevedra).
 * Solo la parte de alquiler de vehículos — sin taller ni otros servicios.
 * Datos basados en información pública real.
 */
class DemoRentACarSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Ensure catalog types ───

        $tipoNegocioTaller = TipoNegocio::firstOrCreate(
            ['nombre' => 'Rent a car'],
            ['descripcion' => 'Empresa de alquiler de vehículos.']
        );

        $tipoRecursoVehiculo = TipoRecurso::where('nombre', 'Vehículo')->firstOrFail();

        $tipoPrecioPorDia = TipoPrecio::firstOrCreate(
            ['nombre' => 'Por día'],
            ['descripcion' => 'Precio por día de alquiler.']
        );

        $tipoPrecioFijo = TipoPrecio::where('nombre', 'Fijo')->firstOrFail();

        $estadoReservaPendiente = EstadoReserva::where('nombre', 'Pendiente')->firstOrFail();
        $estadoReservaConfirmada = EstadoReserva::where('nombre', 'Confirmada')->firstOrFail();
        $estadoReservaCompletada = EstadoReserva::where('nombre', 'Completada')->firstOrFail();

        $tipoPagoTarjeta = TipoPago::where('nombre', 'Tarjeta')->firstOrFail();
        $tipoPagoTransferencia = TipoPago::where('nombre', 'Transferencia')->firstOrFail();
        $estadoPagoPagado = EstadoPago::where('nombre', 'Pagado')->firstOrFail();
        $estadoPagoPendiente = EstadoPago::where('nombre', 'Pendiente')->firstOrFail();

        // ─── Negocio ───

        $negocio = Negocio::updateOrCreate(
            ['nombre' => 'Autos Castiñeira'],
            [
                'tipo_negocio_id' => $tipoNegocioTaller->id,
                'email' => 'contacto@autoscastineira.es',
                'telefono' => '886 160 902',
                'zona_horaria' => 'Europe/Madrid',
                'dias_apertura' => [1, 2, 3, 4, 5],
                'activo' => true,
                'descripcion_publica' => 'Empresa de alquiler de vehículos con más de 30 años de experiencia en Pontecaldelas, Pontevedra. Alquiler de turismos, furgonetas y vehículos comerciales por día, semana o larga duración. Todos nuestros vehículos incluyen seguro a todo riesgo con franquicia de 300€ y 350 km/día incluidos.',
                'direccion' => 'Pazos nº13 A, 36829 Pontecaldelas, Pontevedra',
                'url_publica' => 'https://autoscastineira.com',
                'politica_cancelacion' => 'Cancelación gratuita hasta 24 horas antes de la recogida. Cancelaciones con menos de 24 horas de antelación perderán el importe de la señal si la hubiera. Para alquileres de larga duración (más de 7 días), cancelar con 48 horas de antelación.',
                'horas_minimas_cancelacion' => 24,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Tono directo, profesional y cercano. Habla como un negocio familiar gallego de confianza con 30 años de experiencia. Sé claro con precios y condiciones. Si el cliente tiene dudas sobre qué vehículo necesita, ayúdale a elegir según su necesidad (mudanza, viaje, trabajo). No seas excesivamente formal.',
                'chat_required_fields' => [
                    'search_availability' => ['servicio_id', 'fecha'],
                    'create_quote' => ['servicio_id'],
                    'get_service_details' => ['servicio_id'],
                    'get_cancellation_policy' => [],
                    'get_arrival_instructions' => [],
                    'check_business_hours' => [],
                    'list_bookable_services' => [],
                ],
                'chat_system_rules' => implode("\n", [
                    'Todos los alquileres incluyen seguro a todo riesgo con franquicia de 300€.',
                    'Kilometraje incluido: 350 km/día para turismos, 400 km/día para furgonetas grandes. Km extra a 0,20€/km.',
                    'Para alquileres de más de 7 días, ofrecer tarifa reducida y sugerir contactar por teléfono.',
                    'El cliente debe tener carné de conducir vigente y ser mayor de 21 años (25 para furgonetas grandes).',
                    'Horario de recogida y devolución: lunes a viernes 8:30 a 19:30.',
                    'Si preguntan por maquinaria de obra, remolques o taller mecánico, indicar que contacten directamente por teléfono.',
                    'No alquilamos los fines de semana — la recogida y devolución solo es en horario de oficina.',
                    'Segundo teléfono de contacto: 652 803 013.',
                ]),
            ]
        );

        User::query()->each(function (User $user) use ($negocio): void {
            $user->negocios()->syncWithoutDetaching([$negocio->id]);
        });

        // ─── Clientes demo ───

        $clientes = collect([
            ['nombre' => 'David Fernández', 'email' => 'david.fernandez@example.com', 'telefono' => '600 300 001', 'notas' => 'Cliente habitual, empresa de reformas.'],
            ['nombre' => 'Laura Martínez', 'email' => 'laura.martinez@example.com', 'telefono' => '600 300 002', 'notas' => null],
            ['nombre' => 'Óscar Pérez', 'email' => null, 'telefono' => '600 300 003', 'notas' => 'Siempre alquila furgoneta para mudanzas.'],
            ['nombre' => 'Nuria Castro', 'email' => 'nuria.castro@example.com', 'telefono' => '600 300 004', 'notas' => null],
        ])->mapWithKeys(function (array $data) {
            $cliente = Cliente::updateOrCreate(['nombre' => $data['nombre']], $data);

            return [$data['nombre'] => $cliente];
        });

        // ─── Servicios (tipos de alquiler) ───

        $servicios = collect([
            [
                'nombre' => 'Turismo compacto',
                'descripcion' => 'Alquiler de turismo compacto ideal para ciudad y desplazamientos cortos. Modelos tipo Peugeot 208, Citroën C3 o Renault Clio. 5 plazas, diésel, consumo económico.',
                'duracion_minutos' => 660, // ventana de oficina (11h)
                'precio_base' => 35.00,
                'tipo_precio_id' => $tipoPrecioPorDia->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Incluye seguro a todo riesgo (franquicia 300€) y 350 km/día. Km extra: 0,20€/km. Carné de conducir obligatorio. Mayores de 21 años.',
                'instrucciones_previas' => 'Traer DNI/pasaporte y carné de conducir vigente. Tarjeta de crédito para la fianza.',
                'documentacion_requerida' => 'DNI o pasaporte + carné de conducir vigente.',
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => true,
            ],
            [
                'nombre' => 'Turismo familiar',
                'descripcion' => 'Turismo familiar o tipo SUV compacto para viajes con más espacio. Modelos tipo Peugeot 2008, Renault Mégane Sport Tourer o Skoda Fabia. 5 plazas, maletero amplio.',
                'duracion_minutos' => 1440,
                'precio_base' => 45.00,
                'tipo_precio_id' => $tipoPrecioPorDia->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Incluye seguro a todo riesgo (franquicia 300€) y 350 km/día. Ideal para familias o viajes largos con equipaje.',
                'instrucciones_previas' => 'Traer DNI/pasaporte y carné de conducir vigente. Tarjeta de crédito para la fianza.',
                'documentacion_requerida' => 'DNI o pasaporte + carné de conducir vigente.',
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => true,
            ],
            [
                'nombre' => 'Furgoneta pequeña',
                'descripcion' => 'Furgoneta tipo Peugeot Partner para mudanzas pequeñas, transporte de mercancía o trabajos puntuales. 2 plazas, zona de carga: 1,50m x 1,20m x 1,17m.',
                'duracion_minutos' => 1440,
                'precio_base' => 35.00,
                'tipo_precio_id' => $tipoPrecioPorDia->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Incluye seguro a todo riesgo (franquicia 300€) y 350 km/día. 2 plazas. Ideal para entregas urbanas y pequeñas mudanzas.',
                'instrucciones_previas' => 'Traer DNI/pasaporte y carné de conducir vigente (B). Tarjeta de crédito para la fianza.',
                'documentacion_requerida' => 'DNI o pasaporte + carné de conducir B vigente.',
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => true,
            ],
            [
                'nombre' => 'Furgoneta mediana',
                'descripcion' => 'Furgoneta de carga mediana para mudanzas y transporte profesional. 3 plazas, mayor volumen de carga que la pequeña.',
                'duracion_minutos' => 1440,
                'precio_base' => 55.00,
                'tipo_precio_id' => $tipoPrecioPorDia->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Incluye seguro a todo riesgo (franquicia 300€) y 400 km/día. 3 plazas. Para mudanzas y transporte de volumen medio.',
                'instrucciones_previas' => 'Traer DNI/pasaporte y carné de conducir vigente. Mayores de 25 años.',
                'documentacion_requerida' => 'DNI o pasaporte + carné de conducir B vigente. Mayores de 25 años.',
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => true,
            ],
            [
                'nombre' => 'Furgoneta grande',
                'descripcion' => 'Furgoneta extra larga tipo Renault Máster L4H2 para grandes mudanzas y transporte profesional. 3 plazas, máxima capacidad de carga.',
                'duracion_minutos' => 1440,
                'precio_base' => 95.00,
                'tipo_precio_id' => $tipoPrecioPorDia->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Incluye seguro a todo riesgo (franquicia 300€) y 400 km/día. 3 plazas. Para grandes mudanzas y transporte industrial. Mayores de 25 años obligatorio.',
                'instrucciones_previas' => 'Traer DNI/pasaporte y carné de conducir vigente. Obligatorio ser mayor de 25 años. Experiencia previa con vehículos grandes recomendada.',
                'documentacion_requerida' => 'DNI o pasaporte + carné de conducir B vigente. Mayores de 25 años.',
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => 20.00,
                'precio_por_unidad_tiempo' => true,
            ],
        ])->mapWithKeys(function (array $data) use ($negocio) {
            $servicio = Servicio::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $servicio];
        });

        // ─── Recursos (vehículos concretos de la flota) ───

        $recursos = collect([
            ['nombre' => 'Peugeot 208', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Turismo compacto, 5 plazas, diésel.'],
            ['nombre' => 'Citroën C3', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Turismo compacto, 5 plazas, diésel.'],
            ['nombre' => 'Renault Clio', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Turismo compacto, 5 plazas, diésel.'],
            ['nombre' => 'Peugeot 2008', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'SUV compacto, 5 plazas, diésel.'],
            ['nombre' => 'Renault Mégane ST', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Familiar, 5 plazas, maletero amplio.'],
            ['nombre' => 'Skoda Fabia', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Turismo compacto, 5 plazas.'],
            ['nombre' => 'Peugeot Partner', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Furgoneta pequeña, 2 plazas, carga: 1,50×1,20×1,17m.'],
            ['nombre' => 'Furgoneta mediana 1', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Furgoneta de carga mediana, 3 plazas.'],
            ['nombre' => 'Renault Máster L4H2', 'tipo_recurso_id' => $tipoRecursoVehiculo->id, 'capacidad' => 1, 'activo' => true, 'notas_publicas' => 'Furgoneta extra larga, 3 plazas, máxima capacidad.'],
        ])->mapWithKeys(function (array $data) use ($negocio) {
            $recurso = Recurso::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $recurso];
        });

        // ─── Servicio ↔ Recurso ───

        $servicios['Turismo compacto']->recursos()->sync([
            $recursos['Peugeot 208']->id,
            $recursos['Citroën C3']->id,
            $recursos['Renault Clio']->id,
            $recursos['Skoda Fabia']->id,
        ]);

        $servicios['Turismo familiar']->recursos()->sync([
            $recursos['Peugeot 2008']->id,
            $recursos['Renault Mégane ST']->id,
        ]);

        $servicios['Furgoneta pequeña']->recursos()->sync([
            $recursos['Peugeot Partner']->id,
        ]);

        $servicios['Furgoneta mediana']->recursos()->sync([
            $recursos['Furgoneta mediana 1']->id,
        ]);

        $servicios['Furgoneta grande']->recursos()->sync([
            $recursos['Renault Máster L4H2']->id,
        ]);

        // ─── Disponibilidades: Lunes a Viernes 8:30-19:30 ───

        foreach ($recursos as $recurso) {
            foreach (range(1, 5) as $dia) { // Lunes(1) a Viernes(5)
                Disponibilidad::updateOrCreate(
                    ['recurso_id' => $recurso->id, 'dia_semana' => $dia, 'hora_inicio' => '08:30:00', 'hora_fin' => '19:30:00'],
                    ['activo' => true, 'nombre_turno' => 'Horario de oficina', 'buffer_minutos' => 30]
                );
            }
        }

        // ─── Reservas demo ───

        $today = Carbon::today();

        $reservaItems = [
            ['cliente' => 'David Fernández', 'servicio' => 'Furgoneta pequeña', 'recurso' => 'Peugeot Partner', 'fecha' => $today->copy()->addDays(2), 'hora_inicio' => '09:00:00', 'hora_fin' => '19:00:00', 'numero_personas' => 1, 'precio_calculado' => 35.00, 'estado' => $estadoReservaConfirmada, 'notas' => 'Alquiler 1 día para mudanza pequeña.'],
            ['cliente' => 'Laura Martínez', 'servicio' => 'Turismo compacto', 'recurso' => 'Peugeot 208', 'fecha' => $today->copy()->addDays(3), 'hora_inicio' => '08:30:00', 'hora_fin' => '19:30:00', 'numero_personas' => 1, 'precio_calculado' => 105.00, 'estado' => $estadoReservaConfirmada, 'notas' => 'Alquiler 3 días para viaje.'],
            ['cliente' => 'Óscar Pérez', 'servicio' => 'Furgoneta grande', 'recurso' => 'Renault Máster L4H2', 'fecha' => $today->copy()->addDays(5), 'hora_inicio' => '08:30:00', 'hora_fin' => '19:30:00', 'numero_personas' => 1, 'precio_calculado' => 190.00, 'estado' => $estadoReservaPendiente, 'notas' => 'Mudanza completa, 2 días.'],
            ['cliente' => 'Nuria Castro', 'servicio' => 'Turismo familiar', 'recurso' => 'Peugeot 2008', 'fecha' => $today->copy()->addDays(1), 'hora_inicio' => '09:00:00', 'hora_fin' => '19:00:00', 'numero_personas' => 1, 'precio_calculado' => 45.00, 'estado' => $estadoReservaCompletada, 'notas' => null],
        ];

        $reservas = collect($reservaItems)->mapWithKeys(function (array $item) use ($negocio, $clientes, $servicios, $recursos) {
            $reserva = Reserva::updateOrCreate(
                [
                    'negocio_id' => $negocio->id,
                    'servicio_id' => $servicios[$item['servicio']]->id,
                    'recurso_id' => $recursos[$item['recurso']]->id,
                    'cliente_id' => $clientes[$item['cliente']]->id,
                    'fecha' => $item['fecha']->toDateString(),
                    'hora_inicio' => $item['hora_inicio'],
                ],
                [
                    'hora_fin' => $item['hora_fin'],
                    'numero_personas' => $item['numero_personas'],
                    'precio_calculado' => number_format((float) $item['precio_calculado'], 2, '.', ''),
                    'estado_reserva_id' => $item['estado']->id,
                    'notas' => $item['notas'],
                    'localizador' => Reserva::generarLocalizador(),
                ]
            );

            return ['reserva_'.$reserva->id => $reserva];
        });

        // ─── Pagos demo ───

        Pago::updateOrCreate(
            ['reserva_id' => $reservas->values()[0]->id, 'tipo_pago_id' => $tipoPagoTarjeta->id, 'importe' => '35.00'],
            ['estado_pago_id' => $estadoPagoPagado->id, 'referencia_externa' => 'TPV-CAR-001', 'fecha_pago' => now()->format('Y-m-d H:i:s')]
        );

        Pago::updateOrCreate(
            ['reserva_id' => $reservas->values()[1]->id, 'tipo_pago_id' => $tipoPagoTarjeta->id, 'importe' => '105.00'],
            ['estado_pago_id' => $estadoPagoPagado->id, 'referencia_externa' => 'TPV-CAR-002', 'fecha_pago' => now()->format('Y-m-d H:i:s')]
        );

        Pago::updateOrCreate(
            ['reserva_id' => $reservas->values()[2]->id, 'tipo_pago_id' => $tipoPagoTransferencia->id, 'importe' => '38.00'],
            ['estado_pago_id' => $estadoPagoPendiente->id, 'referencia_externa' => null, 'fecha_pago' => null]
        );
    }
}
