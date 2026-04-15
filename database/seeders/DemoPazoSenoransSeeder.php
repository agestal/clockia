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

class DemoPazoSenoransSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Pazo de Señoráns',
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
            ['nombre' => 'Pazo de Señoráns'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'eventos@pazodesenorans.example',
                'telefono' => '+34 986 715 373',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Pazo historico del siglo XVI en el corazon del Valle del Salnes, elaborando algunos de los Albariños mas aclamados de Rias Baixas desde 1989. Combina siglos de tradicion viticola con su propia destileria, produciendo vinos excepcionales y aguardientes artesanos entre una arquitectura de epoca impresionante.',
                'direccion' => 'Vilanoviña s/n, 36637 Meis, Pontevedra',
                'url_publica' => 'https://pazodesenorans.example',
                'politica_cancelacion' => 'Reserva obligatoria con al menos 24 horas de antelacion. Cancelaciones directamente con la finca.',
                'horas_minimas_cancelacion' => 24,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Eres un anfitrion en un pazo historico gallego dedicado al enoturismo y la destilacion artesana. Conoces a fondo el Albariño, la D.O. Rias Baixas, y la tradicion de los aguardientes gallegos. Hablas con cercania, elegancia y un toque de orgullo por la historia del lugar. Ajustas el nivel tecnico al cliente.',
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
                    'La oferta publica se presenta como experiencias, visitas guiadas y catas; no como inventario interno de salas o recursos.',
                    'Este pazo esta en Meis, en el Valle del Salnes, corazon de la D.O. Rias Baixas, y el Albariño es la referencia natural del territorio.',
                    'El pazo tiene su propia destileria artesana donde se elaboran orujo blanco y orujo de hierbas, que forman parte de las visitas guiadas.',
                    'Si el usuario pregunta por denominaciones de origen, vinos gallegos, Albariño, aguardientes o tipos de cata, responde con naturalidad y conocimiento real.',
                    'Si el cliente pregunta por la historia del pazo, puedes mencionar que data del siglo XVI y que elabora vinos desde 1989.',
                    'La Seleccion de Añada es un vino con 30 meses de crianza sobre lias, una de las joyas de la casa.',
                    'Todas las experiencias requieren reserva previa y aprobacion manual. Explicalo con naturalidad cuando sea relevante.',
                    'Para cerrar una reserva, intenta recoger nombre, telefono y email en el mismo bloque. Como enviamos confirmacion por email, el email es importante.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                    'No inventes vinos, añadas o premios que no esten respaldados por el contexto del negocio.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrion de pazo historico, guia de enoturismo y destileria',
                    'default_register' => 'Amable, elegante y cercano, con conocimiento del vino y los aguardientes sin sonar estirado.',
                    'question_style' => 'Haz preguntas breves y utiles. Si faltan varios datos para cerrar una visita, intenta pedirlos juntos en uno o dos turnos como maximo.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias realmente distintas. Si hay una sola opcion clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de experiencias, visitas, catas y recorridos por el pazo; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesion proxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'pazo', 'destileria', 'Albariño', 'orujo', 'aguardiente', 'Seleccion de Añada'],
                ],
                'mail_confirmacion_activo' => true,
                'mail_recordatorio_activo' => true,
                'mail_recordatorio_horas_antes' => 48,
                'mail_encuesta_activo' => true,
                'mail_encuesta_horas_despues' => 24,
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
            ['nombre' => 'Elena Pazos', 'email' => 'elena.pazos@example.com', 'telefono' => '600 520 001', 'notas' => 'Habitual del Valle del Salnes, le interesa la historia del pazo.'],
            ['nombre' => 'Carlos Abal', 'email' => 'carlos.abal@example.com', 'telefono' => '600 520 002', 'notas' => null],
            ['nombre' => 'Inés Carballo', 'email' => 'ines.carballo@example.com', 'telefono' => '600 520 003', 'notas' => 'Interesada en catas premium y vinos de guarda.'],
            ['nombre' => 'Xosé Doval', 'email' => null, 'telefono' => '600 520 004', 'notas' => 'Pregunta siempre por visitas en gallego.'],
            ['nombre' => 'Beatriz Senra', 'email' => 'beatriz.senra@example.com', 'telefono' => '600 520 005', 'notas' => null],
            ['nombre' => 'Pablo Meijide', 'email' => 'pablo.meijide@example.com', 'telefono' => '600 520 006', 'notas' => null],
            ['nombre' => 'Rosa Vilar', 'email' => null, 'telefono' => '600 520 007', 'notas' => 'Viene con familia en verano.'],
            ['nombre' => 'Marcos Fontán', 'email' => 'marcos.fontan@example.com', 'telefono' => '600 520 008', 'notas' => null],
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
                'nombre' => 'Visita Guiada al Pazo',
                'descripcion' => 'Recorrido completo por los viñedos, la destileria artesana, el pazo historico del siglo XVI y la bodega, culminando con una cata comentada de 3 vinos Albariño y 2 aguardientes (orujo blanco y orujo de hierbas).',
                'duracion_minutos' => 120,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 16,
                'precio_base' => 20.00,
                'precio_menor' => 0.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Los menores son bienvenidos con entrada gratuita. No se sirve alcohol a menores de edad.',
                'instrucciones_previas' => 'Reserva obligatoria. Recomendamos llegar 10 minutos antes para comenzar puntuales.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'en', 'gl'],
                'punto_encuentro' => 'Entrada principal del Pazo',
                'incluye' => ['Visita guiada a viñedos', 'Visita a la destileria', 'Recorrido por el pazo historico', 'Visita a la bodega', 'Cata de 3 vinos Albariño', 'Degustacion de orujo blanco y orujo de hierbas'],
                'no_incluye' => ['Transporte hasta la finca'],
                'accesibilidad_notas' => 'El recorrido incluye zonas exteriores con terreno irregular. Consultar accesibilidad al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cata Premium con Seleccion de Añada',
                'descripcion' => 'Cata intima de 4 vinos incluyendo la Seleccion de Añada (30 meses de crianza sobre lias) y Sol de Señoráns, acompañados de una seleccion de quesos gallegos.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 8,
                'precio_base' => 35.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Experiencia exclusiva de aforo reducido para amantes del vino. Solo mayores de 18 años.',
                'instrucciones_previas' => 'Reserva obligatoria. Plazas muy limitadas, recomendamos reservar con antelacion.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'en'],
                'punto_encuentro' => 'Entrada principal del Pazo',
                'incluye' => ['Cata comentada de 4 vinos', 'Seleccion de Añada', 'Sol de Señoráns', 'Maridaje con quesos gallegos'],
                'no_incluye' => ['Transporte hasta la finca', 'Visita a viñedos o destileria'],
                'accesibilidad_notas' => 'La cata se realiza integramente en interior, en sala accesible.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 24,
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
                'nombre' => 'Salon del Pazo',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 16,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Salon historico del pazo del siglo XVI, utilizado para las visitas guiadas en grupo.',
            ],
            [
                'nombre' => 'Sala de Catas Privada',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 8,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala intima reservada para catas premium y experiencias exclusivas.',
            ],
            [
                'nombre' => 'Destileria',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 12,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Destileria artesana donde se elaboran los orujos y aguardientes de la casa.',
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
        $servicios['Visita Guiada al Pazo']->recursos()->sync([
            $recursos['Salon del Pazo']->id,
            $recursos['Destileria']->id,
        ]);

        $servicios['Cata Premium con Seleccion de Añada']->recursos()->sync([
            $recursos['Sala de Catas Privada']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        $this->seedWeeklyAvailability($recursos['Salon del Pazo'], [1, 2, 3, 4, 5], '10:30', '12:30', 'Visita Guiada Mañana');
        $this->seedWeeklyAvailability($recursos['Salon del Pazo'], [1, 2, 3, 4, 5], '16:00', '18:00', 'Visita Guiada Tarde');
        $this->seedWeeklyAvailability($recursos['Sala de Catas Privada'], [4, 5], '12:30', '14:00', 'Cata Premium');
        $this->seedWeeklyAvailability($recursos['Destileria'], [1, 2, 3, 4, 5], '10:30', '12:30', 'Visita Guiada Mañana');
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
                'service' => $servicios['Visita Guiada al Pazo'],
                'resources' => [$recursos['Salon del Pazo'], $recursos['Destileria']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '10:30',
                'end' => '12:30',
                'note' => 'Visita guiada completa por la mañana.',
            ],
            [
                'service' => $servicios['Visita Guiada al Pazo'],
                'resources' => [$recursos['Salon del Pazo']],
                'weekdays' => [1, 2, 3, 4, 5],
                'start' => '16:00',
                'end' => '18:00',
                'note' => 'Visita guiada completa por la tarde.',
            ],
            [
                'service' => $servicios['Cata Premium con Seleccion de Añada'],
                'resources' => [$recursos['Sala de Catas Privada']],
                'weekdays' => [4, 5],
                'start' => '12:30',
                'end' => '14:00',
                'note' => 'Cata premium con Seleccion de Añada y quesos gallegos.',
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
        $maintenanceDate = Carbon::today()->next(Carbon::WEDNESDAY)->addWeek()->toDateString();
        $eventDate = Carbon::today()->next(Carbon::THURSDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Destileria']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $maintenanceDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Revision y mantenimiento de los alambiques de la destileria.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala de Catas Privada']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $eventDate,
                'hora_inicio' => '12:30:00',
                'hora_fin' => '14:00:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Cata privada para prensa especializada ya cerrada.',
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
                'localizador' => 'PDS-VIS-001',
                'cliente' => $clientes['Elena Pazos'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->next(Carbon::WEDNESDAY)->toDateString(),
                'hora_inicio' => '10:30:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 80.00,
                'precio_total' => 80.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo familiar interesado en la historia del pazo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 80.00,
                        'referencia_externa' => 'DEMO-PDS-VIS-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(18, 30, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PDS-VIS-002',
                'cliente' => $clientes['Pablo Meijide'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->next(Carbon::THURSDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '18:00:00',
                'numero_personas' => 6,
                'precio_calculado' => 120.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Grupo de amigos de Santiago, pendiente de confirmacion.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PDS-VIS-003',
                'cliente' => $clientes['Xosé Doval'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '10:30:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 40.00,
                'precio_total' => 40.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Solicita visita en gallego si es posible.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 40.00,
                        'referencia_externa' => 'DEMO-PDS-VIS-003',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(11, 10, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PDS-CAT-001',
                'cliente' => $clientes['Inés Carballo'],
                'servicio' => $servicios['Cata Premium con Seleccion de Añada'],
                'recurso' => $recursos['Sala de Catas Privada'],
                'fecha' => Carbon::today()->next(Carbon::THURSDAY)->addWeek()->toDateString(),
                'hora_inicio' => '12:30:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 140.00,
                'precio_total' => 140.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Muy interesada en la Seleccion de Añada y los quesos locales.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 140.00,
                        'referencia_externa' => 'DEMO-PDS-CAT-001',
                        'fecha_pago' => Carbon::today()->setTime(10, 15, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PDS-CAT-002',
                'cliente' => $clientes['Beatriz Senra'],
                'servicio' => $servicios['Cata Premium con Seleccion de Añada'],
                'recurso' => $recursos['Sala de Catas Privada'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString(),
                'hora_inicio' => '12:30:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 70.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de confirmacion, pareja de fin de semana.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PDS-VIS-090',
                'cliente' => $clientes['Rosa Vilar'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->previous(Carbon::WEDNESDAY)->toDateString(),
                'hora_inicio' => '10:30:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 5,
                'precio_calculado' => 100.00,
                'precio_total' => 100.00,
                'estado_reserva_id' => $estadosReserva['completada']->id,
                'notas' => 'Visita familiar completada sin incidencias.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 100.00,
                        'referencia_externa' => 'DEMO-PDS-VIS-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::TUESDAY)->setTime(13, 0, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'PDS-VIS-091',
                'cliente' => $clientes['Marcos Fontán'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->previous(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '18:00:00',
                'numero_personas' => 3,
                'precio_calculado' => 60.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'El grupo no llego a presentarse.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'PDS-VIS-092',
                'cliente' => $clientes['Carlos Abal'],
                'servicio' => $servicios['Visita Guiada al Pazo'],
                'recurso' => $recursos['Salon del Pazo'],
                'fecha' => Carbon::today()->next(Carbon::MONDAY)->addWeek()->toDateString(),
                'hora_inicio' => '10:30:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 80.00,
                'precio_total' => 80.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por cambio de planes del grupo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 80.00,
                        'referencia_externa' => 'DEMO-PDS-VIS-092',
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
