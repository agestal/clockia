<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Cliente;
use App\Models\ConceptoPago;
use App\Models\Disponibilidad;
use App\Models\EstadoPago;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\Pago;
use App\Models\Recurso;
use App\Models\RecursoCombinacion;
use App\Models\Reserva;
use App\Models\ReservaIntegracion;
use App\Models\ReservaRecurso;
use App\Models\Servicio;
use App\Models\Sesion;
use App\Models\TipoBloqueo;
use App\Models\TipoNegocio;
use App\Models\TipoPago;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoPacoLolaSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Paco & Lola',
        ]);

        $tipoNegocioBodega = TipoNegocio::firstOrCreate(
            ['nombre' => 'Bodega'],
            ['descripcion' => 'Bodega o proyecto de enoturismo con experiencias, visitas y catas reservables.']
        );

        $tipoPrecioPorPersona = TipoPrecio::where('nombre', 'Por persona')->firstOrFail();
        $tipoRecursoSalaCatas = TipoRecurso::firstOrCreate(
            ['nombre' => 'Sala de catas'],
            ['descripcion' => 'Espacio preparado para visitas guiadas, degustaciones y grupos de enoturismo.']
        );

        $estadoReservaPendiente = EstadoReserva::where('nombre', 'Pendiente')->firstOrFail();
        $estadoReservaConfirmada = EstadoReserva::where('nombre', 'Confirmada')->firstOrFail();
        $estadoReservaCancelada = EstadoReserva::where('nombre', 'Cancelada')->firstOrFail();
        $estadoReservaCompletada = EstadoReserva::where('nombre', 'Completada')->firstOrFail();
        $estadoReservaNoPresentada = EstadoReserva::where('nombre', 'No presentada')->firstOrFail();

        $tipoPagoTarjeta = TipoPago::where('nombre', 'Tarjeta')->firstOrFail();
        $tipoPagoTransferencia = TipoPago::where('nombre', 'Transferencia')->firstOrFail();
        $tipoPagoTpv = TipoPago::where('nombre', 'TPV online')->firstOrFail();
        $estadoPagoPendiente = EstadoPago::where('nombre', 'Pendiente')->firstOrFail();
        $estadoPagoPagado = EstadoPago::where('nombre', 'Pagado')->firstOrFail();
        $estadoPagoReembolsado = EstadoPago::where('nombre', 'Reembolsado')->firstOrFail();
        $conceptoPagoSenal = ConceptoPago::where('nombre', 'Señal')->firstOrFail();
        $conceptoPagoFinal = ConceptoPago::where('nombre', 'Pago final')->firstOrFail();
        $conceptoPagoReembolso = ConceptoPago::where('nombre', 'Reembolso')->firstOrFail();

        $tipoBloqueoMantenimiento = TipoBloqueo::where('nombre', 'Mantenimiento')->firstOrFail();
        $tipoBloqueoEvento = TipoBloqueo::where('nombre', 'Evento especial')->firstOrFail();
        $tipoBloqueoCierre = TipoBloqueo::where('nombre', 'Cierre puntual')->firstOrFail();

        $negocio = Negocio::updateOrCreate(
            ['nombre' => 'Paco & Lola'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'enoturismo@pacolola.example',
                'telefono' => '+34 986 747 779',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Cooperativa moderna fundada en 2005 por viticultores independientes del Valle del Salnes, con mas de 430 socios, la mayor cooperativa de la D.O. Rias Baixas. Su llamativa bodega de lunares combina tecnologia sostenible de vanguardia con generaciones de tradicion viticola para producir Albariños vibrantes.',
                'direccion' => 'Valdamor 18, 36968 Xil-Meaño, Pontevedra',
                'url_publica' => 'https://pacolola.example',
                'politica_cancelacion' => 'Cancelaciones y modificaciones requieren al menos 48 horas de antelacion.',
                'horas_minimas_cancelacion' => 48,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Eres un anfitrion de una cooperativa moderna y vibrante, experto en Albariño y enoturismo del Valle del Salnes. Hablas con cercania, entusiasmo y orgullo por el proyecto cooperativo. Ajustas el tono al visitante: divulgativo para quien empieza, mas tecnico si lo piden.',
                'chat_required_fields' => [
                    'search_availability' => ['servicio_id', 'fecha', 'numero_personas'],
                    'create_booking' => ['servicio_id', 'fecha', 'hora_inicio', 'numero_personas', 'contact_name', 'contact_phone', 'contact_email'],
                    'create_quote' => ['servicio_id', 'numero_personas'],
                    'get_service_details' => ['servicio_id'],
                    'get_cancellation_policy' => [],
                    'get_arrival_instructions' => [],
                    'check_business_hours' => [],
                    'list_bookable_services' => [],
                ],
                'chat_system_rules' => implode("\n", [
                    'La oferta publica se presenta como experiencias y visitas guiadas; no como inventario interno de salas o recursos.',
                    'Esta bodega es la mayor cooperativa de la D.O. Rias Baixas, ubicada en Meaño, Valle del Salnes. El Albariño es la referencia natural.',
                    'Si el usuario pregunta por denominaciones de origen, vinos gallegos, Albariño o maridajes, responde con naturalidad y conocimiento real.',
                    'Los visitantes deben llegar 10 minutos antes de la hora de inicio de la experiencia.',
                    'No se permiten anillos, pendientes ni objetos personales en las zonas de produccion.',
                    'Para cerrar una reserva, intenta recoger nombre, telefono y email en el mismo bloque. El email es importante para la confirmacion.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                    'No inventes bodegas, vinos concretos, añadas o premios que no esten respaldados por el contexto del negocio.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrion de bodega cooperativa, guia de enoturismo',
                    'default_register' => 'Cercano, entusiasta y moderno, con orgullo cooperativo sin resultar corporativo.',
                    'question_style' => 'Haz preguntas breves y utiles. Si faltan varios datos para cerrar una visita, intenta pedirlos juntos en uno o dos turnos como maximo.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias realmente distintas. Si hay una sola opcion clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de experiencias, visitas, catas y degustaciones; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesion proxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'Albariño', 'cooperativa', 'Valle del Salnes', 'maridaje', 'plazas'],
                ],
                'mail_confirmacion_activo' => true,
                'mail_recordatorio_activo' => true,
                'mail_recordatorio_horas_antes' => 24,
                'mail_encuesta_activo' => false,
            ]
        );

        User::query()->each(function (User $user) use ($negocio): void {
            $user->negocios()->syncWithoutDetaching([$negocio->id]);
        });

        $clientes = $this->seedClientes();
        $servicios = $this->seedServicios($negocio, $tipoPrecioPorPersona);
        $recursos = $this->seedRecursos($negocio, $tipoRecursoSalaCatas);

        $this->syncServicioRecursos($servicios, $recursos);
        $this->seedDisponibilidades($recursos);
        $this->seedSesiones($negocio, $servicios, $recursos);
        $this->seedBloqueos($negocio, $recursos, [
            'mantenimiento' => $tipoBloqueoMantenimiento,
            'evento' => $tipoBloqueoEvento,
            'cierre' => $tipoBloqueoCierre,
        ]);

        $this->seedReservasYPagos(
            $negocio,
            $clientes,
            $servicios,
            $recursos,
            [
                'pendiente' => $estadoReservaPendiente,
                'confirmada' => $estadoReservaConfirmada,
                'cancelada' => $estadoReservaCancelada,
                'completada' => $estadoReservaCompletada,
                'no_presentada' => $estadoReservaNoPresentada,
            ],
            [
                'tarjeta' => $tipoPagoTarjeta,
                'transferencia' => $tipoPagoTransferencia,
                'tpv' => $tipoPagoTpv,
            ],
            [
                'pendiente' => $estadoPagoPendiente,
                'pagado' => $estadoPagoPagado,
                'reembolsado' => $estadoPagoReembolsado,
            ],
            [
                'senal' => $conceptoPagoSenal,
                'final' => $conceptoPagoFinal,
                'reembolso' => $conceptoPagoReembolso,
            ]
        );
    }

    private function purgeLegacyDemoBusinesses(array $names): void
    {
        foreach ($names as $name) {
            $business = Negocio::where('nombre', $name)->first();

            if (! $business) {
                continue;
            }

            DB::transaction(function () use ($business): void {
                $business->users()->detach();

                $serviceIds = $business->servicios()->pluck('id');
                $resourceIds = $business->recursos()->pluck('id');
                $reservationIds = $business->reservas()->pluck('id');

                if ($reservationIds->isNotEmpty()) {
                    Pago::whereIn('reserva_id', $reservationIds)->delete();
                    ReservaIntegracion::whereIn('reserva_id', $reservationIds)->delete();
                    ReservaRecurso::whereIn('reserva_id', $reservationIds)->delete();
                    Reserva::whereIn('id', $reservationIds)->delete();
                }

                Sesion::where('negocio_id', $business->id)->delete();
                OcupacionExterna::where('negocio_id', $business->id)->delete();
                Bloqueo::where('negocio_id', $business->id)->delete();

                if ($resourceIds->isNotEmpty()) {
                    Bloqueo::whereIn('recurso_id', $resourceIds)->delete();
                    Disponibilidad::whereIn('recurso_id', $resourceIds)->delete();
                    RecursoCombinacion::where(function ($query) use ($resourceIds) {
                        $query->whereIn('recurso_id', $resourceIds)
                            ->orWhereIn('recurso_combinado_id', $resourceIds);
                    })->delete();
                    DB::table('servicio_recurso')->whereIn('recurso_id', $resourceIds)->delete();
                }

                if ($serviceIds->isNotEmpty()) {
                    DB::table('servicio_recurso')->whereIn('servicio_id', $serviceIds)->delete();
                    Servicio::whereIn('id', $serviceIds)->delete();
                }

                if ($resourceIds->isNotEmpty()) {
                    Recurso::whereIn('id', $resourceIds)->delete();
                }

                $business->delete();
            });
        }
    }

    private function seedClientes(): Collection
    {
        return collect([
            ['nombre' => 'Iria Nogueira', 'email' => 'iria.nogueira@example.com', 'telefono' => '600 520 001', 'notas' => 'Viene con amigos desde Santiago.'],
            ['nombre' => 'Xoan Dominguez', 'email' => 'xoan.dominguez@example.com', 'telefono' => '600 520 002', 'notas' => null],
            ['nombre' => 'Carme Piñeiro', 'email' => 'carme.pineiro@example.com', 'telefono' => '600 520 003', 'notas' => 'Interesada en quesos artesanos y maridaje.'],
            ['nombre' => 'Brais Carballo', 'email' => null, 'telefono' => '600 520 004', 'notas' => 'Pregunta por visitas en grupo grande.'],
            ['nombre' => 'Antia Rivas', 'email' => 'antia.rivas@example.com', 'telefono' => '600 520 005', 'notas' => null],
            ['nombre' => 'Hugo Sestelo', 'email' => 'hugo.sestelo@example.com', 'telefono' => '600 520 006', 'notas' => null],
            ['nombre' => 'Nerea Fontan', 'email' => null, 'telefono' => '600 520 007', 'notas' => 'Viene en familia durante puentes festivos.'],
            ['nombre' => 'Marcos Davila', 'email' => 'marcos.davila@example.com', 'telefono' => '600 520 008', 'notas' => null],
        ])->mapWithKeys(function (array $data) {
            $cliente = Cliente::updateOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );

            return [$data['nombre'] => $cliente];
        });
    }

    private function seedServicios(Negocio $negocio, TipoPrecio $tipoPrecioPorPersona): Collection
    {
        $services = [
            [
                'nombre' => 'Visita Clasica',
                'descripcion' => 'Visita guiada a la bodega de lunares con recorrido por las instalaciones y cata comentada de 3 vinos D.O. Rias Baixas.',
                'duracion_minutos' => 60,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 30,
                'precio_base' => 15.00,
                'precio_menor' => 9.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Menores de 13 a 17 años pagan tarifa reducida (sin degustacion). Menores de 13 años gratis.',
                'instrucciones_previas' => 'Recomendamos llegar 10 minutos antes para comenzar puntuales. No se permiten anillos, pendientes ni objetos personales en las zonas de produccion.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita guiada a la bodega', 'Cata de 3 vinos D.O. Rias Baixas'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Bodega accesible en planta baja; consultar para necesidades especificas.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Visita Gourmet',
                'descripcion' => 'Recorrido completo por la bodega con cata de 3 vinos (al menos una edicion especial) y maridaje con queso artesano gallego.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 30,
                'precio_base' => 22.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Experiencia exclusiva para mayores de 18 años. Incluye al menos un vino de edicion especial.',
                'instrucciones_previas' => 'Recomendamos llegar 10 minutos antes. No se permiten anillos, pendientes ni objetos personales en las zonas de produccion. Si hay alergias alimentarias, conviene avisar con antelacion.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita completa a la bodega', 'Cata de 3 vinos (edicion especial incluida)', 'Maridaje con queso artesano gallego'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Bodega accesible en planta baja; consultar para necesidades especificas.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
        ];

        return collect($services)->mapWithKeys(function (array $data) use ($negocio) {
            $servicio = Servicio::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $servicio];
        });
    }

    private function seedRecursos(Negocio $negocio, TipoRecurso $tipoRecursoSalaCatas): Collection
    {
        $resources = [
            [
                'nombre' => 'Sala de Catas',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 30,
                'capacidad_minima' => 1,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala principal de catas en el interior de la bodega de lunares.',
            ],
            [
                'nombre' => 'Terraza Polka',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 20,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Terraza exterior con vistas, disponible en condiciones meteorologicas favorables.',
            ],
        ];

        return collect($resources)->mapWithKeys(function (array $data) use ($negocio) {
            $recurso = Recurso::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $recurso];
        });
    }

    private function syncServicioRecursos(Collection $servicios, Collection $recursos): void
    {
        $servicios['Visita Clasica']->recursos()->sync([
            $recursos['Sala de Catas']->id,
            $recursos['Terraza Polka']->id,
        ]);

        $servicios['Visita Gourmet']->recursos()->sync([
            $recursos['Sala de Catas']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        // Sala de Catas: Mon-Fri (1,2,3,4,5) at 11:00-12:00, 13:00-14:00, 16:00-17:00
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [1, 2, 3, 4, 5], '11:00', '12:00', 'Visita Clasica manana');
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [1, 2, 3, 4, 5], '13:00', '14:00', 'Visita Clasica mediodia');
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [1, 2, 3, 4, 5], '16:00', '17:00', 'Visita Clasica tarde');

        // Sala de Catas: Sat-Sun (6,0) at 11:00-12:00, 13:00-14:00 (Visita Clasica)
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [6, 0], '11:00', '12:00', 'Visita Clasica manana');
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [6, 0], '13:00', '14:00', 'Visita Clasica mediodia');

        // Sala de Catas: Sat-Sun (6,0) at 13:00-14:30 (Visita Gourmet)
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [6, 0], '13:00', '14:30', 'Visita Gourmet');

        // Terraza Polka: Sat-Sun (6,0) at 11:00-12:00 (Visita Clasica)
        $this->seedWeeklyAvailability($recursos['Terraza Polka'], [6, 0], '11:00', '12:00', 'Visita Clasica manana');
    }

    private function seedWeeklyAvailability(Recurso $recurso, array $weekdays, string $start, string $end, string $turnName): void
    {
        foreach ($weekdays as $weekday) {
            Disponibilidad::updateOrCreate(
                [
                    'recurso_id' => $recurso->id,
                    'dia_semana' => $weekday,
                    'hora_inicio' => $start.':00',
                    'hora_fin' => $end.':00',
                ],
                [
                    'activo' => true,
                    'nombre_turno' => $turnName,
                    'buffer_minutos' => 0,
                ]
            );
        }
    }

    private function seedSesiones(Negocio $negocio, Collection $servicios, Collection $recursos): void
    {
        $period = CarbonPeriod::create(
            Carbon::today()->subDays(21),
            Carbon::today()->addDays(42)
        );

        $sessionPatterns = [
            // Sala de Catas: Mon-Fri Visita Clasica at 11:00, 13:00, 16:00
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '11:00',
                'end' => '12:00',
                'note' => 'Visita clasica de manana entre semana.',
            ],
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '13:00',
                'end' => '14:00',
                'note' => 'Visita clasica de mediodia entre semana.',
            ],
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '16:00',
                'end' => '17:00',
                'note' => 'Visita clasica de tarde entre semana.',
            ],
            // Sala de Catas: Sat-Sun Visita Clasica at 11:00, 13:00
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [6, 0],
                'start' => '11:00',
                'end' => '12:00',
                'note' => 'Visita clasica de manana fin de semana.',
            ],
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [6, 0],
                'start' => '13:00',
                'end' => '14:00',
                'note' => 'Visita clasica de mediodia fin de semana.',
            ],
            // Sala de Catas: Sat-Sun Visita Gourmet at 13:00
            [
                'service' => $servicios['Visita Gourmet'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [6, 0],
                'start' => '13:00',
                'end' => '14:30',
                'note' => 'Visita gourmet con maridaje de fin de semana.',
            ],
            // Terraza Polka: Sat-Sun Visita Clasica at 11:00
            [
                'service' => $servicios['Visita Clasica'],
                'resources' => [$recursos['Terraza Polka']],
                'weekdays' => [6, 0],
                'start' => '11:00',
                'end' => '12:00',
                'note' => 'Visita clasica en terraza exterior fin de semana.',
            ],
        ];

        foreach ($period as $date) {
            $currentDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
            $weekday = (int) $currentDate->dayOfWeek;

            foreach ($sessionPatterns as $pattern) {
                if (! in_array($weekday, $pattern['weekdays'], true)) {
                    continue;
                }

                foreach ($pattern['resources'] as $resource) {
                    Sesion::updateOrCreate(
                        [
                            'negocio_id' => $negocio->id,
                            'servicio_id' => $pattern['service']->id,
                            'recurso_id' => $resource->id,
                            'fecha' => $currentDate->toDateString(),
                            'hora_inicio' => $pattern['start'].':00',
                        ],
                        [
                            'hora_fin' => $pattern['end'].':00',
                            'aforo_total' => min(
                                (int) ($pattern['service']->numero_personas_maximo ?? $resource->capacidad ?? 0),
                                (int) ($resource->capacidad ?? $pattern['service']->numero_personas_maximo ?? 0)
                            ) ?: null,
                            'activo' => true,
                            'notas_publicas' => $pattern['note'],
                        ]
                    );
                }
            }
        }
    }

    private function seedBloqueos(Negocio $negocio, Collection $recursos, array $tiposBloqueo): void
    {
        $maintenanceDate = Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString();
        $eventDate = Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Terraza Polka']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $maintenanceDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Mantenimiento de la terraza y revision del mobiliario exterior.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala de Catas']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $eventDate,
                'hora_inicio' => '16:00:00',
                'hora_fin' => '17:00:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Evento privado de presentacion de nueva cosecha.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );
    }

    private function seedReservasYPagos(
        Negocio $negocio,
        Collection $clientes,
        Collection $servicios,
        Collection $recursos,
        array $estadosReserva,
        array $tiposPago,
        array $estadosPago,
        array $conceptosPago
    ): void {
        $reservas = [
            [
                'localizador' => 'PL-CLA-001',
                'cliente' => $clientes['Iria Nogueira'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 60.00,
                'precio_total' => 60.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo de amigas desde Santiago, primera visita a la bodega.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 60.00,
                        'referencia_externa' => 'DEMO-PL-CLA-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(18, 30, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PL-CLA-002',
                'cliente' => $clientes['Xoan Dominguez'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 30.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de confirmacion, viene con su pareja.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PL-GOU-001',
                'cliente' => $clientes['Carme Piñeiro'],
                'servicio' => $servicios['Visita Gourmet'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:30:00',
                'numero_personas' => 6,
                'precio_calculado' => 132.00,
                'precio_total' => 132.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo interesado en quesos artesanos y maridaje.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 132.00,
                        'referencia_externa' => 'DEMO-PL-GOU-001',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(11, 10, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PL-CLA-003',
                'cliente' => $clientes['Brais Carballo'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Terraza Polka'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:00:00',
                'numero_personas' => 10,
                'precio_calculado' => 150.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo grande, prefiere terraza si el tiempo lo permite.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PL-CLA-004',
                'cliente' => $clientes['Antia Rivas'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '17:00:00',
                'numero_personas' => 3,
                'precio_calculado' => 45.00,
                'precio_total' => 45.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Visita de tarde entre semana.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 45.00,
                        'referencia_externa' => 'DEMO-PL-CLA-004',
                        'fecha_pago' => Carbon::today()->setTime(10, 15, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PL-CLA-090',
                'cliente' => $clientes['Hugo Sestelo'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->previous(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:00:00',
                'numero_personas' => 5,
                'precio_calculado' => 75.00,
                'precio_total' => 75.00,
                'estado_reserva_id' => $estadosReserva['completada']->id,
                'notas' => 'Visita completada sin incidencias.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 75.00,
                        'referencia_externa' => 'DEMO-PL-CLA-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::FRIDAY)->setTime(13, 0, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PL-GOU-090',
                'cliente' => $clientes['Nerea Fontan'],
                'servicio' => $servicios['Visita Gourmet'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->previous(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 88.00,
                'precio_total' => 88.00,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'La familia no llego a presentarse.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PL-CLA-091',
                'cliente' => $clientes['Marcos Davila'],
                'servicio' => $servicios['Visita Clasica'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 30.00,
                'precio_total' => 30.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por cambio de planes del visitante.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 30.00,
                        'referencia_externa' => 'DEMO-PL-CLA-091',
                        'fecha_pago' => Carbon::today()->subDays(3)->setTime(16, 25, 0),
                    ],
                ],
            ],
        ];

        foreach ($reservas as $spec) {
            $sesion = Sesion::query()
                ->where('negocio_id', $negocio->id)
                ->where('servicio_id', $spec['servicio']->id)
                ->where('recurso_id', $spec['recurso']->id)
                ->whereDate('fecha', $spec['fecha'])
                ->where('hora_inicio', $spec['hora_inicio'])
                ->firstOrFail();

            $cliente = $spec['cliente'];

            $reserva = Reserva::updateOrCreate(
                ['localizador' => $spec['localizador']],
                [
                    'negocio_id' => $negocio->id,
                    'servicio_id' => $spec['servicio']->id,
                    'sesion_id' => $sesion->id,
                    'recurso_id' => $spec['recurso']->id,
                    'cliente_id' => $cliente->id,
                    'nombre_responsable' => $cliente->nombre,
                    'email_responsable' => $cliente->email,
                    'telefono_responsable' => $cliente->telefono,
                    'tipo_documento_responsable' => $spec['tipo_documento_responsable'],
                    'documento_responsable' => $spec['documento_responsable'],
                    'fecha' => $spec['fecha'],
                    'hora_inicio' => $spec['hora_inicio'],
                    'hora_fin' => $spec['hora_fin'],
                    'numero_personas' => $spec['numero_personas'],
                    'precio_calculado' => $spec['precio_calculado'],
                    'precio_total' => $spec['precio_total'],
                    'estado_reserva_id' => $spec['estado_reserva_id'],
                    'notas' => $spec['notas'],
                    'horas_minimas_cancelacion' => $spec['servicio']->horas_minimas_cancelacion,
                    'permite_modificacion' => true,
                    'es_reembolsable' => $spec['servicio']->es_reembolsable,
                    'porcentaje_senal' => $spec['servicio']->porcentaje_senal,
                    'origen_reserva' => $spec['origen_reserva'],
                    'importada_externamente' => false,
                    'documentacion_entregada' => false,
                ]
            );

            ReservaRecurso::updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'recurso_id' => $spec['recurso']->id,
                ],
                [
                    'fecha' => $reserva->fecha,
                    'hora_inicio' => $reserva->hora_inicio,
                    'hora_fin' => $reserva->hora_fin,
                    'fecha_inicio_datetime' => $reserva->inicio_datetime,
                    'fecha_fin_datetime' => $reserva->fin_datetime,
                    'notas' => null,
                ]
            );

            foreach ($spec['pagos'] as $paymentSpec) {
                Pago::updateOrCreate(
                    [
                        'reserva_id' => $reserva->id,
                        'concepto_pago_id' => $paymentSpec['concepto_pago_id'],
                        'referencia_externa' => $paymentSpec['referencia_externa'],
                    ],
                    [
                        'tipo_pago_id' => $paymentSpec['tipo_pago_id'],
                        'estado_pago_id' => $paymentSpec['estado_pago_id'],
                        'importe' => $paymentSpec['importe'],
                        'fecha_pago' => $paymentSpec['fecha_pago'],
                        'enlace_pago_externo' => null,
                        'iniciado_por_bot' => false,
                    ]
                );
            }
        }
    }
}
