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
use Illuminate\Support\Facades\DB;

class DemoRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $tipoNegocioRestaurante = TipoNegocio::where('nombre', 'Restaurante')->firstOrFail();
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

        $negocio = Negocio::query()
            ->whereIn('nombre', ['Culuca Cociña-bar', 'Restaurante Marea Alta'])
            ->first();

        DB::transaction(function () use (
            &$negocio,
            $tipoNegocioRestaurante,
            $tipoPrecioPersonalizado,
            $tipoRecursoMesa,
            $tipoRecursoSala,
            $tipoBloqueoMantenimiento,
            $tipoBloqueoEventoEspecial,
            $tipoBloqueoCierrePuntual,
            $estadoReservaPendiente,
            $estadoReservaConfirmada,
            $estadoReservaCancelada,
            $estadoReservaCompletada,
            $estadoReservaNoPresentada,
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
        ): void {
            if ($negocio instanceof Negocio) {
                $this->purgeExistingRestaurantDemoData($negocio);
            } else {
                $negocio = new Negocio();
            }

            $negocio->fill([
                'nombre' => 'Culuca Cociña-bar',
                'tipo_negocio_id' => $tipoNegocioRestaurante->id,
                'email' => 'info@culuca.com',
                'telefono' => '981 97 88 98',
                'zona_horaria' => 'Europe/Madrid',
                'dias_apertura' => [0, 1, 2, 3, 4, 5, 6],
                'activo' => true,
                'descripcion_publica' => 'Restaurante coruñés de cocina de temporada y producto gallego con carta de sala, selección de vinos, reservado para celebraciones y menús de grupo a medida. La propuesta combina platos clásicos de la casa, mariscos y pescado, carnes a la brasa y opciones para compartir.',
                'direccion' => 'Avenida Arteixo 10 Bajo, 15004 A Coruña',
                'url_publica' => 'https://culuca.com',
                'politica_cancelacion' => 'La web pública no muestra una política cerrada de cancelación. Las reservas se solicitan por formulario o contacto directo y quedan sujetas a confirmación del restaurante; para cambios, cancelaciones, reservado o grupos, se debe contactar por teléfono o correo.',
                'horas_minimas_cancelacion' => null,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 2,
                'chat_personality' => 'Tono cercano, seguro y nada ceremonioso. Habla como un equipo de sala profesional de un restaurante urbano con cocina gallega contemporánea. Sé claro, breve y útil. Si preguntan por la carta, orienta con naturalidad hacia platos, vinos, grupos y reservado sin sonar a formulario.',
                'chat_required_fields' => [
                    'search_availability' => ['servicio_id', 'fecha', 'numero_personas'],
                    'create_quote' => ['servicio_id', 'numero_personas'],
                    'get_service_details' => ['servicio_id'],
                    'get_cancellation_policy' => [],
                    'get_arrival_instructions' => [],
                    'check_business_hours' => [],
                    'list_bookable_services' => [],
                ],
                'chat_system_rules' => implode("\n", [
                    'El restaurante trabaja principalmente con reservas de mesa para comida y cena, además de reservado y menús de grupo bajo petición.',
                    'No inventes precios cerrados para comida o cena a la carta: el consumo depende de lo que se pida en la carta y en la bodega.',
                    'Si preguntan por precios de una reserva estándar, explica que no hay menú cerrado salvo en grupos o presupuestos a medida.',
                    'La comida se sirve todos los días entre las 12:30 y las 16:30, con cierre de cocina a las 15:45.',
                    'La cena se sirve solo de martes a sábado. El domingo y el lunes por la noche no hay servicio.',
                    'De martes a jueves la cocina cierra a las 23:00. Viernes y sábado cierra a las 23:30.',
                    'El reservado y los menús de grupo requieren antelación y confirmación manual por parte del restaurante.',
                    'Si el cliente pregunta por eventos fuera del local o servicio en domicilio, indícale que se gestiona como evento a medida y por contacto directo.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Equipo de sala de Culuca',
                    'default_register' => 'Cercano, hospitalario, ágil y natural, como una recomendación de sala bien llevada.',
                    'question_style' => 'Haz preguntas cortas y naturales. Si solo falta un dato, pide solo ese dato. Si el cliente ya dejó clara la intención, no reinicies la conversación.',
                    'option_style' => 'Si solo hay una opción útil, propónla directamente. Si hay varias parecidas, resume por franja o por tipo de experiencia en vez de enumerar inventario interno.',
                    'offer_naming_style' => 'Habla de comida, cena, carta, reservado o menú de grupo en lenguaje de cliente. Evita nombres internos de mesas salvo que el cliente lo pida.',
                    'inventory_exposure_policy' => 'hide_internal_resources',
                    'no_availability_policy' => 'Si no hay hueco exacto, dilo con claridad y ofrece una alternativa cercana de hora, fecha o formato, como comida, cena o reservado, cuando encaje.',
                    'vocabulary_hints' => ['carta', 'vino', 'reservado', 'grupo', 'comida', 'cena'],
                ],
            ]);
            $negocio->save();

            User::query()->each(function (User $user) use ($negocio): void {
                $user->negocios()->syncWithoutDetaching([$negocio->id]);
            });

            $clientes = collect([
                ['nombre' => 'Andrea Freire', 'email' => 'andrea.freire@example.com', 'telefono' => '600 210 001', 'notas' => 'Prefiere mesa tranquila si hay disponibilidad.'],
                ['nombre' => 'Pablo Puga', 'email' => 'pablo.puga@example.com', 'telefono' => '600 210 002', 'notas' => null],
                ['nombre' => 'Lucía Veiga', 'email' => null, 'telefono' => '600 210 003', 'notas' => 'Pregunta a menudo por vinos por copa.'],
                ['nombre' => 'Marcos Loureiro', 'email' => 'marcos.loureiro@example.com', 'telefono' => null, 'notas' => null],
                ['nombre' => 'Sabela Castro', 'email' => 'sabela.castro@example.com', 'telefono' => '600 210 005', 'notas' => 'Prefiere comida de mediodía.'],
                ['nombre' => 'Noa Vázquez', 'email' => null, 'telefono' => '600 210 006', 'notas' => 'Avisa si trae carrito de bebé.'],
                ['nombre' => 'Miguel Becerra', 'email' => 'miguel.becerra@example.com', 'telefono' => '600 210 007', 'notas' => null],
                ['nombre' => 'Iria Souto', 'email' => null, 'telefono' => null, 'notas' => 'Suele reservar para grupos de amigos.'],
                ['nombre' => 'Álvaro Varela', 'email' => 'alvaro.varela@example.com', 'telefono' => '600 210 009', 'notas' => null],
                ['nombre' => 'Carla Lage', 'email' => 'carla.lage@example.com', 'telefono' => '600 210 010', 'notas' => 'Solicita factura cuando procede.'],
                ['nombre' => 'Brais Seoane', 'email' => null, 'telefono' => '600 210 011', 'notas' => null],
                ['nombre' => 'Nerea Fandiño', 'email' => 'nerea.fandino@example.com', 'telefono' => '600 210 012', 'notas' => 'Le interesa el reservado para celebraciones pequeñas.'],
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
                    'descripcion' => 'Reserva de mesa para el servicio de mediodía a la carta.',
                    'duracion_minutos' => 120,
                    'precio_base' => 0.00,
                    'tipo_precio_id' => $tipoPrecioPersonalizado->id,
                    'requiere_pago' => false,
                    'activo' => true,
                    'notas_publicas' => 'Servicio a la carta con cocina abierta de 12:30 a 15:45 y sala abierta hasta las 16:30. La cuenta final depende de lo pedido en carta y bodega.',
                    'instrucciones_previas' => 'Si vienes con carrito, necesidades de accesibilidad o prefieres una mesa amplia, indícalo al reservar.',
                    'documentacion_requerida' => null,
                    'horas_minimas_cancelacion' => null,
                    'es_reembolsable' => true,
                    'porcentaje_senal' => null,
                    'precio_por_unidad_tiempo' => false,
                ],
                [
                    'nombre' => 'Cena',
                    'descripcion' => 'Reserva de mesa para el servicio de noche a la carta.',
                    'duracion_minutos' => 120,
                    'precio_base' => 0.00,
                    'tipo_precio_id' => $tipoPrecioPersonalizado->id,
                    'requiere_pago' => false,
                    'activo' => true,
                    'notas_publicas' => 'Servicio a la carta de martes a sábado desde las 19:30. La cocina cierra a las 23:00 de martes a jueves y a las 23:30 viernes y sábado.',
                    'instrucciones_previas' => 'Si quieres una recomendación de vinos o una mesa más apartada, puedes indicarlo en la reserva.',
                    'documentacion_requerida' => null,
                    'horas_minimas_cancelacion' => null,
                    'es_reembolsable' => true,
                    'porcentaje_senal' => null,
                    'precio_por_unidad_tiempo' => false,
                ],
                [
                    'nombre' => 'Menú de grupo',
                    'descripcion' => 'Servicio para grupos con presupuesto cerrado confeccionado a medida.',
                    'duracion_minutos' => 150,
                    'precio_base' => 0.00,
                    'tipo_precio_id' => $tipoPrecioPersonalizado->id,
                    'requiere_pago' => false,
                    'activo' => true,
                    'notas_publicas' => 'Los menús de grupo se preparan a medida y se sirven en la zona reservada del comedor. El precio se define según propuesta y número de asistentes.',
                    'instrucciones_previas' => 'Conviene solicitarlo con antelación suficiente y comunicar alergias, restricciones alimentarias y si se desea copa de bienvenida.',
                    'documentacion_requerida' => null,
                    'horas_minimas_cancelacion' => null,
                    'es_reembolsable' => true,
                    'porcentaje_senal' => null,
                    'precio_por_unidad_tiempo' => false,
                ],
                [
                    'nombre' => 'Reservado',
                    'descripcion' => 'Reserva del espacio privado del comedor para pequeñas celebraciones o comidas de empresa.',
                    'duracion_minutos' => 150,
                    'precio_base' => 0.00,
                    'tipo_precio_id' => $tipoPrecioPersonalizado->id,
                    'requiere_pago' => false,
                    'activo' => true,
                    'notas_publicas' => 'Espacio más íntimo del comedor pensado para celebraciones familiares, grupos de amigos o comidas de empresa. La disponibilidad y las condiciones se confirman manualmente.',
                    'instrucciones_previas' => 'Indica el tamaño del grupo, si necesitas menú cerrado y cualquier necesidad especial antes de confirmar.',
                    'documentacion_requerida' => null,
                    'horas_minimas_cancelacion' => null,
                    'es_reembolsable' => true,
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
                ['nombre' => 'Mesa comedor A', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 2, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
                ['nombre' => 'Mesa comedor B', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 2, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
                ['nombre' => 'Mesa comedor C', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
                ['nombre' => 'Mesa comedor D', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => true, 'notas_publicas' => null],
                ['nombre' => 'Mesa comedor E', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 4, 'activo' => true, 'capacidad_minima' => null, 'combinable' => false, 'notas_publicas' => null],
                ['nombre' => 'Mesa comedor F', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 6, 'activo' => true, 'capacidad_minima' => 4, 'combinable' => false, 'notas_publicas' => 'Mesa amplia para grupos medianos.'],
                ['nombre' => 'Mesa comedor G', 'tipo_recurso_id' => $tipoRecursoMesa->id, 'capacidad' => 8, 'activo' => true, 'capacidad_minima' => 5, 'combinable' => false, 'notas_publicas' => 'Mesa pensada para grupos grandes en sala.'],
                ['nombre' => 'Reservado Culuca', 'tipo_recurso_id' => $tipoRecursoSala->id, 'capacidad' => 12, 'activo' => true, 'capacidad_minima' => 6, 'combinable' => false, 'notas_publicas' => 'Espacio reservado del comedor para celebraciones y comidas de empresa.'],
            ])->mapWithKeys(function (array $data) use ($negocio) {
                $recurso = Recurso::updateOrCreate(
                    ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                    $data + ['negocio_id' => $negocio->id]
                );

                return [$data['nombre'] => $recurso];
            });

            $servicios['Comida']->recursos()->sync([
                $recursos['Mesa comedor A']->id,
                $recursos['Mesa comedor B']->id,
                $recursos['Mesa comedor C']->id,
                $recursos['Mesa comedor D']->id,
                $recursos['Mesa comedor E']->id,
                $recursos['Mesa comedor F']->id,
                $recursos['Mesa comedor G']->id,
                $recursos['Reservado Culuca']->id,
            ]);

            $servicios['Cena']->recursos()->sync([
                $recursos['Mesa comedor A']->id,
                $recursos['Mesa comedor B']->id,
                $recursos['Mesa comedor C']->id,
                $recursos['Mesa comedor D']->id,
                $recursos['Mesa comedor E']->id,
                $recursos['Mesa comedor F']->id,
                $recursos['Mesa comedor G']->id,
                $recursos['Reservado Culuca']->id,
            ]);

            $servicios['Menú de grupo']->recursos()->sync([
                $recursos['Mesa comedor F']->id,
                $recursos['Mesa comedor G']->id,
                $recursos['Reservado Culuca']->id,
            ]);

            $servicios['Reservado']->recursos()->sync([
                $recursos['Reservado Culuca']->id,
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
        });
    }

    private function purgeExistingRestaurantDemoData(Negocio $negocio): void
    {
        $serviceIds = $negocio->servicios()->pluck('id');
        $resourceIds = $negocio->recursos()->pluck('id');
        $reservationIds = $negocio->reservas()->pluck('id');

        if ($reservationIds->isNotEmpty()) {
            DB::table('pagos')->whereIn('reserva_id', $reservationIds)->delete();
            DB::table('reserva_integraciones')->whereIn('reserva_id', $reservationIds)->delete();
            DB::table('reserva_recursos')->whereIn('reserva_id', $reservationIds)->delete();
            DB::table('reservas')->whereIn('id', $reservationIds)->delete();
        }

        if ($resourceIds->isNotEmpty()) {
            DB::table('bloqueos')
                ->whereIn('recurso_id', $resourceIds)
                ->orWhere('negocio_id', $negocio->id)
                ->delete();
            DB::table('disponibilidades')->whereIn('recurso_id', $resourceIds)->delete();
            DB::table('ocupaciones_externas')
                ->where('negocio_id', $negocio->id)
                ->orWhereIn('recurso_id', $resourceIds)
                ->delete();
            DB::table('integracion_mapeos')
                ->where('negocio_id', $negocio->id)
                ->orWhereIn('recurso_id', $resourceIds)
                ->orWhereIn('servicio_id', $serviceIds)
                ->delete();
            DB::table('recurso_combinaciones')
                ->whereIn('recurso_id', $resourceIds)
                ->orWhereIn('recurso_combinado_id', $resourceIds)
                ->delete();
        }

        if ($serviceIds->isNotEmpty() || $resourceIds->isNotEmpty()) {
            DB::table('servicio_recurso')
                ->when($serviceIds->isNotEmpty(), fn ($query) => $query->whereIn('servicio_id', $serviceIds))
                ->when($resourceIds->isNotEmpty(), fn ($query) => $query->orWhereIn('recurso_id', $resourceIds))
                ->delete();
        }

        DB::table('integraciones')->where('negocio_id', $negocio->id)->delete();

        if ($serviceIds->isNotEmpty()) {
            Servicio::query()->whereIn('id', $serviceIds)->delete();
        }

        if ($resourceIds->isNotEmpty()) {
            Recurso::query()->whereIn('id', $resourceIds)->delete();
        }
    }

    private function seedRecursoCombinaciones($recursos): void
    {
        $pares = [
            ['Mesa comedor A', 'Mesa comedor B'],
            ['Mesa comedor C', 'Mesa comedor D'],
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
        foreach (range(0, 6) as $diaSemana) {
            foreach ([
                'Mesa comedor A',
                'Mesa comedor B',
                'Mesa comedor C',
                'Mesa comedor D',
                'Mesa comedor E',
                'Mesa comedor F',
                'Mesa comedor G',
                'Reservado Culuca',
            ] as $nombreRecurso) {
                $recurso = $recursos[$nombreRecurso];
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '12:30:00', '16:30:00', true, 'Servicio de comida', 15);
            }
        }

        foreach ([2, 3, 4] as $diaSemana) {
            foreach ([
                'Mesa comedor A',
                'Mesa comedor B',
                'Mesa comedor C',
                'Mesa comedor D',
                'Mesa comedor E',
                'Mesa comedor F',
                'Mesa comedor G',
                'Reservado Culuca',
            ] as $nombreRecurso) {
                $recurso = $recursos[$nombreRecurso];
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '19:30:00', '23:00:00', true, 'Servicio de cena', 15);
            }
        }

        foreach ([5, 6] as $diaSemana) {
            foreach ([
                'Mesa comedor A',
                'Mesa comedor B',
                'Mesa comedor C',
                'Mesa comedor D',
                'Mesa comedor E',
                'Mesa comedor F',
                'Mesa comedor G',
                'Reservado Culuca',
            ] as $nombreRecurso) {
                $recurso = $recursos[$nombreRecurso];
                $this->upsertDisponibilidad($recurso->id, $diaSemana, '19:30:00', '23:30:00', true, 'Servicio de cena', 15);
            }
        }
    }

    private function seedBloqueos($recursos, TipoBloqueo $mantenimiento, TipoBloqueo $eventoEspecial, TipoBloqueo $cierrePuntual): void
    {
        $today = Carbon::today();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Mesa comedor E']->id,
                'tipo_bloqueo_id' => $mantenimiento->id,
                'fecha' => $today->copy()->addDays(3)->toDateString(),
            ],
            [
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Revisión de mobiliario de sala.',
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Reservado Culuca']->id,
                'tipo_bloqueo_id' => $eventoEspecial->id,
                'fecha' => $today->copy()->next(Carbon::FRIDAY)->toDateString(),
            ],
            [
                'hora_inicio' => '20:00:00',
                'hora_fin' => '23:30:00',
                'motivo' => 'Cena privada cerrada al público.',
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Mesa comedor B']->id,
                'tipo_bloqueo_id' => $cierrePuntual->id,
                'fecha' => $today->copy()->addDays(1)->toDateString(),
            ],
            [
                'hora_inicio' => '13:00:00',
                'hora_fin' => '15:00:00',
                'motivo' => 'Uso interno de sala.',
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
        $lastFriday = $today->copy()->previous(Carbon::FRIDAY);
        $lastSaturday = $today->copy()->previous(Carbon::SATURDAY);
        $nextWednesday = $today->copy()->next(Carbon::WEDNESDAY);
        $nextThursday = $today->copy()->next(Carbon::THURSDAY);
        $nextFriday = $today->copy()->next(Carbon::FRIDAY);
        $nextSaturday = $today->copy()->next(Carbon::SATURDAY);
        $nextSunday = $today->copy()->next(Carbon::SUNDAY);
        $nextMonday = $today->copy()->next(Carbon::MONDAY);

        $items = [
            [
                'cliente' => 'Andrea Freire',
                'servicio' => 'Comida',
                'recurso' => 'Mesa comedor C',
                'fecha' => $lastFriday,
                'hora_inicio' => '14:00:00',
                'hora_fin' => '16:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 118.00,
                'precio_total' => 126.00,
                'estado' => $completada,
                'notas' => 'Pidieron varios platos para compartir y vino por copa.',
            ],
            [
                'cliente' => 'Pablo Puga',
                'servicio' => 'Cena',
                'recurso' => 'Mesa comedor A',
                'fecha' => $lastSaturday,
                'hora_inicio' => '21:00:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 74.00,
                'precio_total' => 74.00,
                'estado' => $completada,
                'notas' => null,
            ],
            [
                'cliente' => 'Lucía Veiga',
                'servicio' => 'Comida',
                'recurso' => 'Mesa comedor D',
                'fecha' => $nextWednesday,
                'hora_inicio' => '13:30:00',
                'hora_fin' => '15:30:00',
                'numero_personas' => 3,
                'precio_calculado' => 92.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => 'Interesada en recomendaciones de vino por copa.',
            ],
            [
                'cliente' => 'Marcos Loureiro',
                'servicio' => 'Cena',
                'recurso' => 'Mesa comedor E',
                'fecha' => $nextThursday,
                'hora_inicio' => '21:15:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 78.00,
                'precio_total' => null,
                'estado' => $pendiente,
                'notas' => null,
            ],
            [
                'cliente' => 'Sabela Castro',
                'servicio' => 'Menú de grupo',
                'recurso' => 'Reservado Culuca',
                'fecha' => $nextFriday,
                'hora_inicio' => '21:00:00',
                'hora_fin' => '23:30:00',
                'numero_personas' => 10,
                'precio_calculado' => 520.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => 'Grupo de empresa con presupuesto cerrado pendiente de último ajuste.',
                'instrucciones_llegada' => 'Indicar en sala que la reserva es para el reservado del comedor.',
                'fecha_estimada_fin' => $nextFriday->copy()->setTime(23, 30)->format('Y-m-d H:i:s'),
            ],
            [
                'cliente' => 'Noa Vázquez',
                'servicio' => 'Comida',
                'recurso' => 'Mesa comedor F',
                'fecha' => $nextSaturday,
                'hora_inicio' => '14:15:00',
                'hora_fin' => '16:15:00',
                'numero_personas' => 5,
                'precio_calculado' => 158.00,
                'precio_total' => null,
                'estado' => $confirmada,
                'notas' => 'Va con carrito de bebé.',
            ],
            [
                'cliente' => 'Miguel Becerra',
                'servicio' => 'Cena',
                'recurso' => 'Mesa comedor G',
                'fecha' => $nextSaturday,
                'hora_inicio' => '20:30:00',
                'hora_fin' => '23:00:00',
                'numero_personas' => 7,
                'precio_calculado' => 245.00,
                'precio_total' => null,
                'estado' => $pendiente,
                'notas' => 'Pide confirmar si pueden compartir raciones y algún vino grande.',
            ],
            [
                'cliente' => 'Iria Souto',
                'servicio' => 'Reservado',
                'recurso' => 'Reservado Culuca',
                'fecha' => $nextSunday,
                'hora_inicio' => '14:00:00',
                'hora_fin' => '16:30:00',
                'numero_personas' => 8,
                'precio_calculado' => 0.00,
                'precio_total' => null,
                'estado' => $pendiente,
                'notas' => 'Celebración familiar pendiente de confirmar menú.',
            ],
            [
                'cliente' => 'Álvaro Varela',
                'servicio' => 'Cena',
                'recurso' => 'Mesa comedor C',
                'fecha' => $nextFriday,
                'hora_inicio' => '20:00:00',
                'hora_fin' => '22:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 144.00,
                'precio_total' => null,
                'estado' => $cancelada,
                'notas' => 'Canceló por cambio de agenda.',
                'fecha_cancelacion' => $nextThursday->copy()->setTime(11, 15)->format('Y-m-d H:i:s'),
                'motivo_cancelacion' => 'Cambio de agenda.',
                'cancelada_por' => 'cliente',
            ],
            [
                'cliente' => 'Carla Lage',
                'servicio' => 'Comida',
                'recurso' => 'Mesa comedor B',
                'fecha' => $nextMonday,
                'hora_inicio' => '13:15:00',
                'hora_fin' => '15:15:00',
                'numero_personas' => 2,
                'precio_calculado' => 64.00,
                'precio_total' => 69.00,
                'estado' => $confirmada,
                'notas' => 'Solicita factura.',
            ],
            [
                'cliente' => 'Brais Seoane',
                'servicio' => 'Cena',
                'recurso' => 'Mesa comedor D',
                'fecha' => $lastFriday,
                'hora_inicio' => '22:00:00',
                'hora_fin' => '23:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 68.00,
                'precio_total' => 68.00,
                'estado' => $noPresentada,
                'notas' => null,
            ],
            [
                'cliente' => 'Nerea Fandiño',
                'servicio' => 'Menú de grupo',
                'recurso' => 'Reservado Culuca',
                'fecha' => $nextThursday,
                'hora_inicio' => '14:00:00',
                'hora_fin' => '16:30:00',
                'numero_personas' => 12,
                'precio_calculado' => 660.00,
                'precio_total' => 720.00,
                'estado' => $confirmada,
                'notas' => 'Comida de empresa con copa de bienvenida.',
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
                'importe' => 126.00,
                'referencia_externa' => 'CUL-TPV-0001',
                'fecha_pago' => Carbon::parse($reservas->values()[0]->fecha->toDateString().' 15:55:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[1],
                'tipo_pago_id' => $efectivo->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 74.00,
                'referencia_externa' => null,
                'fecha_pago' => Carbon::parse($reservas->values()[1]->fecha->toDateString().' 22:45:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[4],
                'tipo_pago_id' => $transferencia->id,
                'estado_pago_id' => $pendiente->id,
                'importe' => 150.00,
                'referencia_externa' => 'CUL-GRUPO-001',
                'fecha_pago' => null,
            ],
            [
                'reserva' => $reservas->values()[8],
                'tipo_pago_id' => $tpvOnline->id,
                'estado_pago_id' => $reembolsado->id,
                'importe' => 40.00,
                'referencia_externa' => 'CUL-REF-009',
                'fecha_pago' => Carbon::parse(now()->subDays(1)->format('Y-m-d 10:30:00'))->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[9],
                'tipo_pago_id' => $bizum->id,
                'estado_pago_id' => $pagado->id,
                'importe' => 69.00,
                'referencia_externa' => 'CUL-BIZ-010',
                'fecha_pago' => Carbon::parse($reservas->values()[9]->fecha->toDateString().' 15:10:00')->format('Y-m-d H:i:s'),
            ],
            [
                'reserva' => $reservas->values()[11],
                'tipo_pago_id' => $tarjeta->id,
                'estado_pago_id' => $fallido->id,
                'importe' => 180.00,
                'referencia_externa' => 'CUL-FAIL-012',
                'fecha_pago' => null,
            ],
            [
                'reserva' => $reservas->values()[7],
                'tipo_pago_id' => $bizum->id,
                'estado_pago_id' => $cancelado->id,
                'importe' => 50.00,
                'referencia_externa' => 'CUL-CAN-008',
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
