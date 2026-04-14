<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EstadoPago;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Recurso;
use App\Models\RecursoCombinacion;
use App\Models\Servicio;
use App\Models\TipoBloqueo;
use App\Models\TipoNegocio;
use App\Models\TipoPago;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $tipoNegocioRestaurante = TipoNegocio::where('nombre', 'Restaurante')->firstOrFail();
        $tipoPrecioPorPersona = TipoPrecio::where('nombre', 'Por persona')->firstOrFail();
        $tipoPrecioFijo = TipoPrecio::where('nombre', 'Fijo')->firstOrFail();
        $tipoPrecioPersonalizado = TipoPrecio::where('nombre', 'Personalizado')->firstOrFail();
        $tipoRecursoMesa = TipoRecurso::where('nombre', 'Mesa')->firstOrFail();
        $tipoRecursoSala = TipoRecurso::where('nombre', 'Sala')->firstOrFail();
        $tipoBloqueoMantenimiento = TipoBloqueo::where('nombre', 'Mantenimiento')->firstOrFail();
        $tipoBloqueoEventoEspecial = TipoBloqueo::where('nombre', 'Evento especial')->firstOrFail();
        $tipoBloqueoCierrePuntual = TipoBloqueo::where('nombre', 'Cierre puntual')->firstOrFail();
        $estadoReservaPendiente = EstadoReserva::where('nombre', 'Pendiente')->firstOrFail();
        $estadoReservaConfirmada = EstadoReserva::where('nombre', 'Confirmada')->firstOrFail();
        $estadoReservaCancelada = EstadoReserva::where('nombre', 'Cancelada')->firstOrFail();
        $estadoReservaCompletada = EstadoReserva::where('nombre', 'Completada')->firstOrFail();
        $estadoReservaNoPresentada = EstadoReserva::where('nombre', 'No presentada')->firstOrFail();
        $tipoPagoEfectivo = TipoPago::where('nombre', 'Efectivo')->firstOrFail();
        $tipoPagoTarjeta = TipoPago::where('nombre', 'Tarjeta')->firstOrFail();
        $tipoPagoBizum = TipoPago::where('nombre', 'Bizum')->firstOrFail();
        $tipoPagoTransferencia = TipoPago::where('nombre', 'Transferencia')->firstOrFail();
        $tipoPagoTpvOnline = TipoPago::where('nombre', 'TPV online')->firstOrFail();
        $estadoPagoPendiente = EstadoPago::where('nombre', 'Pendiente')->firstOrFail();
        $estadoPagoPagado = EstadoPago::where('nombre', 'Pagado')->firstOrFail();
        $estadoPagoReembolsado = EstadoPago::where('nombre', 'Reembolsado')->firstOrFail();
        $estadoPagoFallido = EstadoPago::where('nombre', 'Fallido')->firstOrFail();
        $estadoPagoCancelado = EstadoPago::where('nombre', 'Cancelado')->firstOrFail();

        $negocio = Negocio::updateOrCreate(
            ['nombre' => 'Restaurante Marea Alta'],
            [
                'tipo_negocio_id' => $tipoNegocioRestaurante->id,
                'email' => 'reservas@mareaalta.demo',
                'telefono' => '981 245 678',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Restaurante de cocina gallega contemporánea con vistas al puerto. Producto de temporada, carta de vinos seleccionada y ambiente acogedor para todo tipo de ocasiones.',
                'direccion' => 'Avenida de la Marina, 42, 15001 A Coruña',
                'url_publica' => null,
                'politica_cancelacion' => 'Cancelaciones gratuitas hasta 24 horas antes. Cancelaciones con menos de 24 horas de antelación perderán el importe de la señal si la hubiera.',
                'horas_minimas_cancelacion' => 24,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 2,
                'chat_personality' => 'Tono cercano, amable y claro. Responde como un restaurante profesional pero accesible. Sé breve, educado y orientado a ayudar. Si falta información, pregunta de forma natural antes de responder. No uses tecnicismos ni respuestas demasiado largas.',
                'chat_required_fields' => [
                    'search_availability' => ['servicio_id', 'fecha', 'numero_personas'],
                    'create_quote' => ['servicio_id', 'numero_personas'],
                    'get_service_details' => ['servicio_id'],
                    'get_cancellation_policy' => [],
                    'get_arrival_instructions' => [],
                    'check_business_hours' => [],
                    'list_bookable_services' => [],
                ],
                'chat_system_rules' => 'No ofrezcas reservas para más de 8 personas sin indicar que requiere confirmación especial. Para el menú degustación, recuerda siempre que requiere señal del 20%. Pregunta siempre por alergias o intolerancias alimentarias si el servicio incluye comida.',
            ]
        );

        User::query()->each(function (User $user) use ($negocio): void {
            $user->negocios()->syncWithoutDetaching([$negocio->id]);
        });

        $clientes = collect([
            ['nombre' => 'Lucía Gómez', 'email' => 'lucia.gomez@example.com', 'telefono' => '600 123 001', 'notas' => 'Prefiere mesa tranquila.'],
            ['nombre' => 'Álvaro Seoane', 'email' => 'alvaro.seoane@example.com', 'telefono' => '600 123 002', 'notas' => null],
            ['nombre' => 'Marta Varela', 'email' => null, 'telefono' => '600 123 003', 'notas' => 'Suele venir con niños.'],
            ['nombre' => 'Javier Otero', 'email' => 'jotero@example.com', 'telefono' => null, 'notas' => null],
            ['nombre' => 'Sofía Rey', 'email' => 'sofia.rey@example.com', 'telefono' => '600 123 005', 'notas' => null],
            ['nombre' => 'Daniel Patiño', 'email' => null, 'telefono' => '600 123 006', 'notas' => 'Avisa si se retrasa.'],
            ['nombre' => 'Paula Ríos', 'email' => 'paula.rios@example.com', 'telefono' => '600 123 007', 'notas' => null],
            ['nombre' => 'Nerea Santos', 'email' => null, 'telefono' => null, 'notas' => 'Cliente habitual de brunch.'],
            ['nombre' => 'Carlos Méndez', 'email' => 'carlos.mendez@example.com', 'telefono' => '600 123 009', 'notas' => null],
            ['nombre' => 'Irene Vidal', 'email' => 'irene.vidal@example.com', 'telefono' => null, 'notas' => 'Solicita factura cuando procede.'],
            ['nombre' => 'Hugo Peña', 'email' => null, 'telefono' => '600 123 011', 'notas' => null],
            ['nombre' => 'Laura Ferreiro', 'email' => 'laura.ferreiro@example.com', 'telefono' => '600 123 012', 'notas' => 'Le gusta terraza si el tiempo acompaña.'],
        ])->mapWithKeys(function (array $data) {
            $cliente = Cliente::updateOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );

            return [$data['nombre'] => $cliente];
        });

        $servicios = collect([
            [
                'nombre' => 'Comida',
                'descripcion' => 'Servicio de comida principal en horario de mediodía.',
                'duracion_minutos' => 120,
                'precio_base' => 28.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'activo' => true,
                'notas_publicas' => 'Carta de mediodía con menú del día disponible de lunes a viernes. Posibilidad de adaptar platos para intolerancias bajo petición previa.',
                'instrucciones_previas' => null,
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 12,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cena',
                'descripcion' => 'Servicio de cena en horario nocturno.',
                'duracion_minutos' => 120,
                'precio_base' => 32.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'activo' => true,
                'notas_publicas' => 'Carta completa con selección de entrantes, principales y postres. Maridaje de vinos disponible por copa o botella.',
                'instrucciones_previas' => null,
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 12,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Brunch',
                'descripcion' => 'Servicio de brunch de fin de semana.',
                'duracion_minutos' => 90,
                'precio_base' => 22.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'activo' => true,
                'notas_publicas' => 'Brunch servido solo sábados y domingos. Incluye bebida caliente, zumo natural, tostadas, huevos y pieza de bollería.',
                'instrucciones_previas' => null,
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 6,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Menú degustación',
                'descripcion' => 'Experiencia gastronómica cerrada con reserva previa.',
                'duracion_minutos' => 150,
                'precio_base' => 65.00,
                'tipo_precio_id' => $tipoPrecioFijo->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Menú de 7 pases con productos de temporada. Maridaje de vinos incluido. No se permiten modificaciones sobre el menú.',
                'instrucciones_previas' => 'Se recomienda llegar 10 minutos antes para disfrutar de un aperitivo de bienvenida. Infórmenos de alergias alimentarias con al menos 48 horas de antelación.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => false,
                'porcentaje_senal' => 20.00,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Evento privado',
                'descripcion' => 'Servicio para reuniones y celebraciones en sala privada.',
                'duracion_minutos' => 180,
                'precio_base' => 320.00,
                'tipo_precio_id' => $tipoPrecioPersonalizado->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Sala privada con capacidad hasta 12 personas. Incluye equipo de sonido, proyector y menú personalizable. Decoración bajo consulta.',
                'instrucciones_previas' => 'Contactar al menos una semana antes para acordar el menú. Si necesita decoración especial, consultar disponibilidad.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 72,
                'es_reembolsable' => false,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
        ])->mapWithKeys(function (array $data) use ($negocio) {
            $servicio = Servicio::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $servicio];
        });

        $recursos = collect([
            ['nombre' => 'Mesa interior 1', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 2, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
            ['nombre' => 'Mesa interior 2', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 2, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
            ['nombre' => 'Mesa interior 3', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
            ['nombre' => 'Mesa interior 4', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
            ['nombre' => 'Mesa terraza 1', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 2, 'activo' => true, 'capacidad_minima' => null, 'combinable' => false, 'notas_publicas' => 'Terraza cubierta, disponible con buen tiempo.'],
            ['nombre' => 'Mesa terraza 2', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => false, 'notas_publicas' => 'Terraza cubierta, disponible con buen tiempo.'],
            ['nombre' => 'Mesa grupo 8', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 8, 'activo' => true, 'capacidad_minima' => 4, 'combinable' => false, 'notas_publicas' => 'Mesa redonda ideal para grupos grandes. Requiere reserva con al menos 4 comensales.'],
            ['nombre' => 'Sala privada Atlántico', 'tipo_recurso_id' => $tipoRecursoSala->id, 'capacidad' => 12, 'activo' => true, 'capacidad_minima' => 6, 'combinable' => false, 'notas_publicas' => 'Sala privada con vistas al puerto. Equipada con proyector y equipo de sonido.'],
        ])->mapWithKeys(function (array $data) use ($negocio) {
            $recurso = Recurso::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $recurso];
        });

        $servicios['Comida']->recursos()->sync([
            $recursos['Mesa interior 1']->id,
            $recursos['Mesa interior 2']->id,
            $recursos['Mesa interior 3']->id,
            $recursos['Mesa interior 4']->id,
            $recursos['Mesa terraza 1']->id,
            $recursos['Mesa terraza 2']->id,
            $recursos['Mesa grupo 8']->id,
            $recursos['Sala privada Atlántico']->id,
        ]);

        $servicios['Cena']->recursos()->sync([
            $recursos['Mesa interior 1']->id,
            $recursos['Mesa interior 2']->id,
            $recursos['Mesa interior 3']->id,
            $recursos['Mesa interior 4']->id,
            $recursos['Mesa terraza 1']->id,
            $recursos['Mesa terraza 2']->id,
            $recursos['Mesa grupo 8']->id,
            $recursos['Sala privada Atlántico']->id,
        ]);

        $servicios['Brunch']->recursos()->sync([
            $recursos['Mesa interior 3']->id,
            $recursos['Mesa interior 4']->id,
            $recursos['Mesa terraza 1']->id,
            $recursos['Mesa terraza 2']->id,
            $recursos['Mesa grupo 8']->id,
        ]);

        $servicios['Menú degustación']->recursos()->sync([
            $recursos['Mesa interior 1']->id,
            $recursos['Mesa interior 3']->id,
            $recursos['Mesa grupo 8']->id,
            $recursos['Sala privada Atlántico']->id,
        ]);

        $servicios['Evento privado']->recursos()->sync([
            $recursos['Sala privada Atlántico']->id,
        ]);

        $this->seedRecursoCombinaciones($recursos);
        $this->seedDisponibilidades($recursos);
        $this->seedBloqueos($recursos, $tipoBloqueoMantenimiento, $tipoBloqueoEventoEspecial, $tipoBloqueoCierrePuntual);

        $reservas = $this->seedReservas(
            $negocio,
            $clientes,
            $servicios,
            $recursos,
            $estadoReservaPendiente,
            $estadoReservaConfirmada,
            $estadoReservaCancelada,
            $estadoReservaCompletada,
            $estadoReservaNoPresentada
        );

        $this->seedPagos(
            $reservas,
            $tipoPagoEfectivo,
            $tipoPagoTarjeta,
            $tipoPagoBizum,
            $tipoPagoTransferencia,
            $tipoPagoTpvOnline,
            $estadoPagoPendiente,
            $estadoPagoPagado,
            $estadoPagoReembolsado,
            $estadoPagoFallido,
            $estadoPagoCancelado
        );
    }

    private function seedRecursoCombinaciones($recursos): void
    {
        $pares = [
            ['Mesa interior 1', 'Mesa interior 2'],
            ['Mesa interior 3', 'Mesa interior 4'],
        ];

        foreach ($pares as [$nombre1, $nombre2]) {
            RecursoCombinacion::updateOrCreate(
                [
                    'recurso_id' => $recursos[$nombre1]->id,
                    'recurso_combinado_id' => $recursos[$nombre2]->id,
                ]
            );
            RecursoCombinacion::updateOrCreate(
                [
                    'recurso_id' => $recursos[$nombre2]->id,
                    'recurso_combinado_id' => $recursos[$nombre1]->id,
                ]
            );
        }
    }

    private function seedDisponibilidades($recursos): void
    {
        foreach (range(2, 6) as $diaSemana) {
            foreach ([
                'Mesa interior 1',
                'Mesa interior 2',
                'Mesa interior 3',
                'Mesa interior 4',
                'Mesa terraza 1',
                'Mesa terraza 2',
                'Mesa grupo 8',
            ] as $nombreRecurso) {
                $recurso = $recursos[$nombreRecurso];

                $this->upsertDisponibilidad($recurso->id, $diaSemana, '13:00:00', '16:00:00', true, 'Turno de comida', 15);
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '20:00:00', '23:30:00', true, 'Turno de cena', 15);
            }
        }

        foreach ([0, 6] as $diaSemana) {
            foreach ([
                'Mesa interior 3',
                'Mesa interior 4',
                'Mesa terraza 1',
                'Mesa terraza 2',
                'Mesa grupo 8',
            ] as $nombreRecurso) {
                $recurso = $recursos[$nombreRecurso];

                $this->upsertDisponibilidad($recurso->id, $diaSemana, '11:00:00', '13:00:00', true, 'Turno de brunch', 15);
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '13:00:00', '16:00:00', true, 'Turno de comida', 15);
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '20:00:00', '23:30:00', true, 'Turno de cena', 15);
            }
        }

        foreach ([4, 5, 6, 0] as $diaSemana) {
            $recurso = $recursos['Sala privada Atlántico'];
            $this->upsertDisponibilidad($recurso->id, $diaSemana, '13:00:00', '16:00:00', true, 'Turno de comida', 30);
            $this->upsertDisponibilidad($recurso->id, $diaSemana, '20:00:00', '23:30:00', true, 'Turno de cena', 30);
        }
    }

    private function seedBloqueos($recursos, TipoBloqueo $mantenimiento, TipoBloqueo $eventoEspecial, TipoBloqueo $cierrePuntual): void
    {
        $today = Carbon::today();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Mesa terraza 2']->id,
                'tipo_bloqueo_id' => $mantenimiento->id,
                'fecha' => $today->copy()->addDays(3)->toDateString(),
            ],
            [
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Revisión de estructura y pintura.',
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala privada Atlántico']->id,
                'tipo_bloqueo_id' => $eventoEspecial->id,
                'fecha' => $today->copy()->addDays(5)->toDateString(),
            ],
            [
                'hora_inicio' => '19:00:00',
                'hora_fin' => '23:30:00',
                'motivo' => 'Presentación de producto cerrada.',
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Mesa interior 2']->id,
                'tipo_bloqueo_id' => $cierrePuntual->id,
                'fecha' => $today->copy()->addDays(1)->toDateString(),
            ],
            [
                'hora_inicio' => '13:00:00',
                'hora_fin' => '15:00:00',
                'motivo' => 'Reserva interna de formación.',
            ]
        );
    }

    private function seedReservas(
        Negocio $negocio,
        $clientes,
        $servicios,
        $recursos,
        EstadoReserva $pendiente,
        EstadoReserva $confirmada,
        EstadoReserva $cancelada,
        EstadoReserva $completada,
        EstadoReserva $noPresentada
    ) {
        $today = Carbon::today();

        $items = [
            [
                'cliente' => 'Lucía Gómez',
                'servicio' => 'Comida',
                'recurso' => 'Mesa interior 3',
                'fecha' => $today->copy()->subDays(2),
                'hora_inicio' => '13:30:00',
                'hora_fin' => '15:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 112.00,
                'precio_total' => 118.00,
                'estado' => $completada,
                'notas' => 'Solicitó pan sin gluten.',
            ],
            [
                'cliente' => 'Álvaro Seoane',
                'servicio' => 'Cena',
                'recurso' => 'Mesa interior 1',
                'fecha' => $today->copy()->subDay(),
                'hora_inicio' => '21:00:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 64.00,
                'precio_total' => 64.00,
                'estado' => $completada,
                'notas' => null,
            ],
            [
                'cliente' => 'Marta Varela',
                'servicio' => 'Brunch',
                'recurso' => 'Mesa terraza 1',
                'fecha' => $today->copy()->addDays(1),
                'hora_inicio' => '11:15:00',
                'hora_fin' => '12:45:00',
                'numero_personas' => 2,
                'precio_calculado' => 44.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => 'Lleva carrito de bebé.',
            ],
            [
                'cliente' => 'Javier Otero',
                'servicio' => 'Comida',
                'recurso' => 'Mesa interior 4',
                'fecha' => $today->copy()->addDays(1),
                'hora_inicio' => '14:00:00',
                'hora_fin' => '16:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 112.00,
                'precio_total' => 120.00,
                'estado' => $pendiente,
                'notas' => null,
            ],
            [
                'cliente' => 'Sofía Rey',
                'servicio' => 'Menú degustación',
                'recurso' => 'Mesa grupo 8',
                'fecha' => $today->copy()->addDays(2),
                'hora_inicio' => '20:30:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 65.00,
                'precio_total' => 65.00,
                'estado' => $confirmada,
                'notas' => 'Celebración de aniversario.',
            ],
            [
                'cliente' => 'Daniel Patiño',
                'servicio' => 'Cena',
                'recurso' => 'Mesa interior 2',
                'fecha' => $today->copy()->addDays(2),
                'hora_inicio' => '20:15:00',
                'hora_fin' => '22:15:00',
                'numero_personas' => 2,
                'precio_calculado' => 64.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => null,
            ],
            [
                'cliente' => 'Paula Ríos',
                'servicio' => 'Comida',
                'recurso' => 'Mesa terraza 2',
                'fecha' => $today->copy()->addDays(4),
                'hora_inicio' => '13:45:00',
                'hora_fin' => '15:45:00',
                'numero_personas' => 4,
                'precio_calculado' => 112.00,
                'precio_total' => null,
                'estado' => $pendiente,
                'notas' => 'Si llueve, mover a interior.',
            ],
            [
                'cliente' => 'Nerea Santos',
                'servicio' => 'Brunch',
                'recurso' => 'Mesa interior 3',
                'fecha' => $today->copy()->addDays(5),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 3,
                'precio_calculado' => 66.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => null,
            ],
            [
                'cliente' => 'Carlos Méndez',
                'servicio' => 'Evento privado',
                'recurso' => 'Sala privada Atlántico',
                'fecha' => $today->copy()->addDays(7),
                'hora_inicio' => '20:00:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 10,
                'precio_calculado' => 320.00,
                'precio_total' => 380.00,
                'estado' => $confirmada,
                'notas' => 'Incluye decoración básica.',
                'instrucciones_llegada' => 'Acceder por la entrada lateral de la Avenida de la Marina. Preguntar en recepción por la Sala Atlántico.',
                'fecha_estimada_fin' => $today->copy()->addDays(7)->setHour(23)->setMinute(0)->format('Y-m-d H:i:s'),
            ],
            [
                'cliente' => 'Irene Vidal',
                'servicio' => 'Cena',
                'recurso' => 'Mesa interior 4',
                'fecha' => $today->copy()->addDays(8),
                'hora_inicio' => '21:30:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 64.00,
                'precio_total' => null,
                'estado' => $cancelada,
                'notas' => 'Canceló por cambio de planes.',
                'fecha_cancelacion' => $today->copy()->addDays(6)->setHour(10)->setMinute(15)->format('Y-m-d H:i:s'),
                'motivo_cancelacion' => 'Cambio de planes personales.',
                'cancelada_por' => 'cliente',
            ],
            [
                'cliente' => 'Hugo Peña',
                'servicio' => 'Comida',
                'recurso' => 'Mesa interior 1',
                'fecha' => $today->copy()->subDays(3),
                'hora_inicio' => '14:30:00',
                'hora_fin' => '16:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 56.00,
                'precio_total' => 56.00,
                'estado' => $noPresentada,
                'notas' => null,
            ],
            [
                'cliente' => 'Laura Ferreiro',
                'servicio' => 'Menú degustación',
                'recurso' => 'Sala privada Atlántico',
                'fecha' => $today->copy()->addDays(10),
                'hora_inicio' => '20:00:00',
                'hora_fin' => '22:30:00',
                'numero_personas' => 6,
                'precio_calculado' => 65.00,
                'precio_total' => 420.00,
                'estado' => $confirmada,
                'notas' => 'Grupo de empresa.',
            ],
        ];

        return collect($items)->mapWithKeys(function (array $item) use ($negocio, $clientes, $servicios, $recursos) {
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
                    'precio_total' => $item['precio_total'] !== null ? number_format((float) $item['precio_total'], 2, '.', '') : null,
                    'estado_reserva_id' => $item['estado']->id,
                    'notas' => $item['notas'],
                    'localizador' => $item['localizador'] ?? Reserva::generarLocalizador(),
                    'fecha_cancelacion' => $item['fecha_cancelacion'] ?? null,
                    'motivo_cancelacion' => $item['motivo_cancelacion'] ?? null,
                    'cancelada_por' => $item['cancelada_por'] ?? null,
                    'instrucciones_llegada' => $item['instrucciones_llegada'] ?? null,
                    'fecha_estimada_fin' => $item['fecha_estimada_fin'] ?? null,
                    'documentacion_entregada' => $item['documentacion_entregada'] ?? false,
                ]
            );

            return ['reserva_'.$reserva->id => $reserva];
        });
    }

    private function seedPagos(
        $reservas,
        TipoPago $efectivo,
        TipoPago $tarjeta,
        TipoPago $bizum,
        TipoPago $transferencia,
        TipoPago $tpvOnline,
        EstadoPago $pendiente,
        EstadoPago $pagado,
        EstadoPago $reembolsado,
        EstadoPago $fallido,
        EstadoPago $cancelado
    ): void {
        $items = [
            [
                'reserva' => $reservas->values()[0],
                'tipo_pago_id' => $tarjeta->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 118.00,
                'referencia_externa' => 'TPV-RES-0001',
                'fecha_pago' => Carbon::parse($reservas->values()[0]->fecha->toDateString().' 15:40:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[1],
                'tipo_pago_id' => $efectivo->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 64.00,
                'referencia_externa' => null,
                'fecha_pago' => Carbon::parse($reservas->values()[1]->fecha->toDateString().' 22:50:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[4],
                'tipo_pago_id' => $tpvOnline->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 65.00,
                'referencia_externa' => 'ONL-MENU-2026-01',
                'fecha_pago' => Carbon::parse($reservas->values()[4]->fecha->toDateString().' 18:05:00')->format('Y-m-d H:i:s'),
                'enlace_pago_externo' => 'https://pay.example.com/checkout/ONL-MENU-2026-01',
                'iniciado_por_bot' => true,
            ],
            [
                'reserva' => $reservas->values()[6],
                'tipo_pago_id' => $bizum->id,
                'estado_pago_id' => $pendiente->id,
                'importe' => 112.00,
                'referencia_externa' => 'BZM-PEND-004',
                'fecha_pago' => null,
                'iniciado_por_bot' => true,
            ],
            [
                'reserva' => $reservas->values()[8],
                'tipo_pago_id' => $transferencia->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 380.00,
                'referencia_externa' => 'TRF-EVENTO-077',
                'fecha_pago' => Carbon::parse($reservas->values()[8]->fecha->toDateString().' 12:00:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[9],
                'tipo_pago_id' => $tarjeta->id,
                'estado_pago_id' => $reembolsado->id,
                'importe' => 20.00,
                'referencia_externa' => 'REF-CANCEL-010',
                'fecha_pago' => Carbon::parse($reservas->values()[9]->fecha->toDateString().' 10:30:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[10],
                'tipo_pago_id' => $tpvOnline->id,
                'estado_pago_id' => $fallido->id,
                'importe' => 80.00,
                'referencia_externa' => 'ERR-DEG-011',
                'fecha_pago' => null,
            ],
            [
                'reserva' => $reservas->values()[11],
                'tipo_pago_id' => $bizum->id,
                'estado_pago_id' => $cancelado->id,
                'importe' => 50.00,
                'referencia_externa' => 'BZM-CANCEL-012',
                'fecha_pago' => null,
            ],
        ];

        foreach ($items as $item) {
            Pago::updateOrCreate(
                [
                    'reserva_id' => $item['reserva']->id,
                    'tipo_pago_id' => $item['tipo_pago_id'],
                    'importe' => number_format((float) $item['importe'], 2, '.', ''),
                ],
                [
                    'estado_pago_id' => $item['estado_pago_id'],
                    'referencia_externa' => $item['referencia_externa'],
                    'fecha_pago' => $item['fecha_pago'],
                    'enlace_pago_externo' => $item['enlace_pago_externo'] ?? null,
                    'iniciado_por_bot' => $item['iniciado_por_bot'] ?? false,
                ]
            );
        }
    }

    private function upsertDisponibilidad(int $recursoId, int $diaSemana, string $horaInicio, string $horaFin, bool $activo, ?string $nombreTurno = null, ?int $bufferMinutos = null): void
    {
        Disponibilidad::updateOrCreate(
            [
                'recurso_id' => $recursoId,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ],
            [
                'activo' => $activo,
                'nombre_turno' => $nombreTurno,
                'buffer_minutos' => $bufferMinutos,
            ]
        );
    }
}
