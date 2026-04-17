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

class DemoFefinanesSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Palacio de Fefiñanes',
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
            ['nombre' => 'Palacio de Fefiñanes'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'visitas@fefinanes.example',
                'telefono' => '+34 986 542 204',
                'zona_horaria' => 'Europe/Madrid',
                'dias_apertura' => [1, 2, 3, 4, 5, 6],
                'activo' => true,
                'descripcion_publica' => 'Bodega instalada en un magnifico palacio del siglo XVII en el centro historico de Cambados, declarado Bien de Interes Cultural. Pioneros en la produccion comercial de Albariño desde 1928, representan el origen mismo del Albariño embotellado, con su iconica etiqueta diseñada hace casi un siglo.',
                'direccion' => 'Plaza de Fefiñanes s/n, 36630 Cambados, Pontevedra',
                'url_publica' => 'https://fefinanes.example',
                'politica_cancelacion' => 'Reserva obligatoria. Contactar con visitas@fefinanes.example para cancelaciones.',
                'horas_minimas_cancelacion' => 24,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Eres un anfitrion experto en enologia y enoturismo en una bodega historica dentro de un palacio del siglo XVII. Conoces bien el Albariño, la D.O. Rias Baixas y la historia de Cambados. Hablas con elegancia, educacion y cercania, transmitiendo el prestigio y la tradicion del lugar.',
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
                    'La oferta publica se presenta como experiencias, visitas guiadas y catas en un palacio historico; no como inventario interno de salas.',
                    'Esta bodega esta en la Plaza de Fefiñanes, en el centro historico de Cambados, D.O. Rias Baixas. El Albariño es la referencia absoluta.',
                    'Si el usuario pregunta por la historia, el palacio, el Albariño o la D.O. Rias Baixas, responde con naturalidad y conocimiento real.',
                    'Todas las visitas requieren reserva previa y aprobacion manual. Explicalo con naturalidad cuando sea relevante.',
                    'No hay aparcamiento propio; se puede aparcar en las calles cercanas a la plaza.',
                    'No se admiten mascotas en las visitas.',
                    'Puedes explicar una cata de forma pedagogica: vista, nariz, boca, final y contexto del vino, adaptando el nivel tecnico al cliente.',
                    'Si el cliente quiere algo exclusivo, orienta hacia la Visita Especial Exclusiva con su cata de 4 vinos.',
                    'Para cerrar una reserva, intenta recoger nombre, telefono y email en el mismo bloque. Como enviamos confirmacion por email, el email es importante.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                    'No inventes vinos, añadas o premios que no esten respaldados por el contexto del negocio.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrion de bodega historica, guia cultural y enologico',
                    'default_register' => 'Elegante, culto y cercano, con conocimiento profundo del vino y la historia del palacio sin sonar pedante.',
                    'question_style' => 'Haz preguntas breves y utiles. Si faltan varios datos para cerrar una visita, intenta pedirlos juntos en uno o dos turnos como maximo.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias realmente distintas. Si hay una sola opcion clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de visitas, catas, experiencias y recorridos por el palacio; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesion proxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'palacio', 'Albariño', 'Patio de Armas', 'denominacion de origen', 'plazas'],
                ],
                'mail_confirmacion_activo' => true,
                'mail_recordatorio_activo' => true,
                'mail_recordatorio_horas_antes' => 24,
                'mail_encuesta_activo' => true,
                'mail_encuesta_horas_despues' => 48,
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
        $this->seedBloqueos($negocio, $servicios, [
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
            ['nombre' => 'Carmen Nogueira', 'email' => 'carmen.nogueira@example.com', 'telefono' => '600 520 001', 'notas' => 'Interesada en la historia del palacio.'],
            ['nombre' => 'Pablo Doval', 'email' => 'pablo.doval@example.com', 'telefono' => '600 520 002', 'notas' => null],
            ['nombre' => 'Elena Amoedo', 'email' => 'elena.amoedo@example.com', 'telefono' => '600 520 003', 'notas' => 'Viene con grupo desde Santiago.'],
            ['nombre' => 'Xosé Piñeiro', 'email' => null, 'telefono' => '600 520 004', 'notas' => 'Pregunta por visitas privadas.'],
            ['nombre' => 'Inés Fontenla', 'email' => 'ines.fontenla@example.com', 'telefono' => '600 520 005', 'notas' => null],
            ['nombre' => 'Rubén Troncoso', 'email' => 'ruben.troncoso@example.com', 'telefono' => '600 520 006', 'notas' => null],
            ['nombre' => 'Antía Barreiro', 'email' => null, 'telefono' => '600 520 007', 'notas' => 'Viene con familia en verano.'],
            ['nombre' => 'Fernando Cores', 'email' => 'fernando.cores@example.com', 'telefono' => '600 520 008', 'notas' => 'Coleccionista de Albariño, interesado en añadas antiguas.'],
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
                'nombre' => 'Visita Estandar',
                'descripcion' => 'Visita guiada al palacio, Patio de Armas, bodega y viñedo-jardin con cata de 1 vino (Albariño de Fefiñanes).',
                'duracion_minutos' => 45,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 20,
                'aforo' => 20,
                'hora_inicio' => '10:00:00',
                'hora_fin' => '12:15:00',
                'precio_base' => 13.00,
                'precio_menor' => 0.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Menores de 18 años gratis. Acceso adaptado para sillas de ruedas. No se admiten mascotas.',
                'instrucciones_previas' => 'Reserva obligatoria. Acudir a la entrada del palacio en la Plaza de Fefiñanes unos minutos antes de la hora.',
                'documentacion_requerida' => null,
                'idiomas' => ['es'],
                'punto_encuentro' => 'Plaza de Fefiñanes (entrada del palacio)',
                'incluye' => ['Visita guiada al palacio', 'Patio de Armas', 'Bodega', 'Viñedo-jardin', 'Cata de 1 vino (Albariño de Fefiñanes)'],
                'no_incluye' => ['Transporte', 'Aparcamiento (no disponible, aparcar en calles cercanas)'],
                'accesibilidad_notas' => 'Acceso adaptado para sillas de ruedas.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Visita Especial',
                'descripcion' => 'Visita guiada completa con cata comentada de 3 vinos: Albariño de Fefiñanes, 1583 y III Año.',
                'duracion_minutos' => 60,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 16,
                'aforo' => 16,
                'hora_inicio' => '11:00:00',
                'hora_fin' => '13:00:00',
                'precio_base' => 25.00,
                'precio_menor' => 0.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Menores de 18 años gratis. No se admiten mascotas.',
                'instrucciones_previas' => 'Reserva obligatoria. Acudir a la entrada del palacio en la Plaza de Fefiñanes.',
                'documentacion_requerida' => null,
                'idiomas' => ['es'],
                'punto_encuentro' => 'Plaza de Fefiñanes (entrada del palacio)',
                'incluye' => ['Visita guiada completa', 'Cata de 3 vinos (Albariño de Fefiñanes, 1583, III Año)'],
                'no_incluye' => ['Transporte', 'Aparcamiento'],
                'accesibilidad_notas' => 'Consultar accesibilidad al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Visita Especial Exclusiva',
                'descripcion' => 'Visita privada exclusiva con recorrido completo y cata de 4 vinos: Albariño de Fefiñanes, 1583, III Año y Armas de Lanzos.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 3,
                'numero_personas_maximo' => 10,
                'aforo' => 10,
                'hora_inicio' => '12:00:00',
                'hora_fin' => '15:00:00',
                'precio_base' => 70.00,
                'precio_menor' => 0.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Menores de 18 años gratis. Experiencia privada y exclusiva. Requiere deposito del 50%. No se admiten mascotas.',
                'instrucciones_previas' => 'Reserva obligatoria con deposito del 50%. Acudir a la entrada del palacio en la Plaza de Fefiñanes.',
                'documentacion_requerida' => null,
                'idiomas' => ['es'],
                'punto_encuentro' => 'Plaza de Fefiñanes (entrada del palacio)',
                'incluye' => ['Visita privada exclusiva', 'Recorrido completo del palacio', 'Cata de 4 vinos (Albariño de Fefiñanes, 1583, III Año, Armas de Lanzos)'],
                'no_incluye' => ['Transporte', 'Aparcamiento'],
                'accesibilidad_notas' => 'Consultar accesibilidad al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => 50.00,
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
                'nombre' => 'Patio de Armas',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 20,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Patio historico del palacio, punto de inicio de las visitas estandar.',
            ],
            [
                'nombre' => 'Sala Noble',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 16,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala principal de catas en el interior del palacio.',
            ],
            [
                'nombre' => 'Sala Privada del Palacio',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 10,
                'capacidad_minima' => 3,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala exclusiva y privada para visitas premium de aforo reducido.',
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
        $servicios['Visita Estandar']->recursos()->sync([
            $recursos['Patio de Armas']->id,
        ]);

        $servicios['Visita Especial']->recursos()->sync([
            $recursos['Sala Noble']->id,
        ]);

        $servicios['Visita Especial Exclusiva']->recursos()->sync([
            $recursos['Sala Privada del Palacio']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        // Patio de Armas: Mon-Sat (1,2,3,4,5,6) four slots for Visita Estandar
        foreach (['10:00' => '10:45', '12:00' => '12:45', '16:00' => '16:45', '18:00' => '18:45'] as $start => $end) {
            $this->seedWeeklyAvailability($recursos['Patio de Armas'], [1, 2, 3, 4, 5, 6], $start, $end, 'Visita Estandar');
        }

        // Sala Noble: Mon-Fri (1,2,3,4,5) two slots for Visita Especial
        $this->seedWeeklyAvailability($recursos['Sala Noble'], [1, 2, 3, 4, 5], '11:00', '12:00', 'Visita Especial');
        $this->seedWeeklyAvailability($recursos['Sala Noble'], [1, 2, 3, 4, 5], '17:00', '18:00', 'Visita Especial');

        // Sala Privada del Palacio: Fri-Sat (5,6) one slot for Visita Especial Exclusiva
        $this->seedWeeklyAvailability($recursos['Sala Privada del Palacio'], [5, 6], '12:00', '13:30', 'Visita Especial Exclusiva');
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
            [
                'service' => $servicios['Visita Estandar'],
                'resources' => [$recursos['Patio de Armas']],
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'start' => '10:00',
                'end' => '10:45',
                'note' => 'Visita estandar de mañana.',
            ],
            [
                'service' => $servicios['Visita Estandar'],
                'resources' => [$recursos['Patio de Armas']],
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'start' => '12:00',
                'end' => '12:45',
                'note' => 'Visita estandar de mediodia.',
            ],
            [
                'service' => $servicios['Visita Estandar'],
                'resources' => [$recursos['Patio de Armas']],
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'start' => '16:00',
                'end' => '16:45',
                'note' => 'Visita estandar de tarde.',
            ],
            [
                'service' => $servicios['Visita Estandar'],
                'resources' => [$recursos['Patio de Armas']],
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'start' => '18:00',
                'end' => '18:45',
                'note' => 'Visita estandar de ultima hora.',
            ],
            [
                'service' => $servicios['Visita Especial'],
                'resources' => [$recursos['Sala Noble']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '11:00',
                'end' => '12:00',
                'note' => 'Visita especial de mañana con cata de 3 vinos.',
            ],
            [
                'service' => $servicios['Visita Especial'],
                'resources' => [$recursos['Sala Noble']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '17:00',
                'end' => '18:00',
                'note' => 'Visita especial de tarde con cata de 3 vinos.',
            ],
            [
                'service' => $servicios['Visita Especial Exclusiva'],
                'resources' => [$recursos['Sala Privada del Palacio']],
                'weekdays' => [5, 6],
                'start' => '12:00',
                'end' => '13:30',
                'note' => 'Visita exclusiva privada con cata de 4 vinos.',
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

    private function seedBloqueos(Negocio $negocio, Collection $servicios, array $tiposBloqueo): void
    {
        $fullDayDate = Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString();
        $partialDate = Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'servicio_id' => $servicios['Visita Estandar']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $fullDayDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'recurso_id' => null,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Mantenimiento del Patio de Armas y revision de instalaciones historicas.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'servicio_id' => $servicios['Visita Especial Exclusiva']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $partialDate,
                'hora_inicio' => '12:00:00',
                'hora_fin' => '13:30:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'recurso_id' => null,
                'motivo' => 'Evento privado institucional ya cerrado.',
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
                'localizador' => 'FEF-EST-001',
                'cliente' => $clientes['Carmen Nogueira'],
                'servicio' => $servicios['Visita Estandar'],
                'recurso' => $recursos['Patio de Armas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '10:00:00',
                'hora_fin' => '10:45:00',
                'numero_personas' => 4,
                'precio_calculado' => 52.00,
                'precio_total' => 52.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo interesado en la historia del palacio.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 52.00,
                        'referencia_externa' => 'DEMO-FEF-EST-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(18, 30, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'FEF-EST-002',
                'cliente' => $clientes['Pablo Doval'],
                'servicio' => $servicios['Visita Estandar'],
                'recurso' => $recursos['Patio de Armas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '12:45:00',
                'numero_personas' => 2,
                'precio_calculado' => 26.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de aprobacion manual.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'FEF-ESP-001',
                'cliente' => $clientes['Elena Amoedo'],
                'servicio' => $servicios['Visita Especial'],
                'recurso' => $recursos['Sala Noble'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:00:00',
                'numero_personas' => 6,
                'precio_calculado' => 150.00,
                'precio_total' => 150.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo desde Santiago, celebracion de cumpleaños.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 150.00,
                        'referencia_externa' => 'DEMO-FEF-ESP-001',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(11, 10, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'FEF-EXC-001',
                'cliente' => $clientes['Xosé Piñeiro'],
                'servicio' => $servicios['Visita Especial Exclusiva'],
                'recurso' => $recursos['Sala Privada del Palacio'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '13:30:00',
                'numero_personas' => 5,
                'precio_calculado' => 350.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Reserva exclusiva pendiente de completar deposito del 50%.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pendiente']->id,
                        'concepto_pago_id' => $conceptosPago['senal']->id,
                        'importe' => 175.00,
                        'referencia_externa' => 'DEMO-FEF-EXC-001',
                        'fecha_pago' => null,
                    ],
                ],
            ],
            [
                'localizador' => 'FEF-EST-090',
                'cliente' => $clientes['Inés Fontenla'],
                'servicio' => $servicios['Visita Estandar'],
                'recurso' => $recursos['Patio de Armas'],
                'fecha' => Carbon::today()->previous(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '16:45:00',
                'numero_personas' => 3,
                'precio_calculado' => 39.00,
                'precio_total' => 39.00,
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
                        'importe' => 39.00,
                        'referencia_externa' => 'DEMO-FEF-EST-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::FRIDAY)->setTime(13, 0, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'FEF-ESP-090',
                'cliente' => $clientes['Rubén Troncoso'],
                'servicio' => $servicios['Visita Especial'],
                'recurso' => $recursos['Sala Noble'],
                'fecha' => Carbon::today()->previous(Carbon::WEDNESDAY)->toDateString(),
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 50.00,
                'precio_total' => 50.00,
                'estado_reserva_id' => $estadosReserva['completada']->id,
                'notas' => 'Pareja disfrutando de la cata de 3 vinos.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 50.00,
                        'referencia_externa' => 'DEMO-FEF-ESP-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::MONDAY)->setTime(9, 45, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'FEF-EST-091',
                'cliente' => $clientes['Antía Barreiro'],
                'servicio' => $servicios['Visita Estandar'],
                'recurso' => $recursos['Patio de Armas'],
                'fecha' => Carbon::today()->previous(Carbon::THURSDAY)->toDateString(),
                'hora_inicio' => '18:00:00',
                'hora_fin' => '18:45:00',
                'numero_personas' => 5,
                'precio_calculado' => 65.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'El grupo no llego a presentarse.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'FEF-EXC-090',
                'cliente' => $clientes['Fernando Cores'],
                'servicio' => $servicios['Visita Especial Exclusiva'],
                'recurso' => $recursos['Sala Privada del Palacio'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '13:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 280.00,
                'precio_total' => 280.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por cambio de agenda del grupo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 140.00,
                        'referencia_externa' => 'DEMO-FEF-EXC-090',
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
