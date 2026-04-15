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

class DemoBodegaSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Culuca Cociña-bar',
            'Autos Castiñeira',
            'Bodegas Viña Atlántica',
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
            ['nombre' => 'Bodegas Viña Atlántica'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'reservas@vinaatlantica.example',
                'telefono' => '+34 986 52 40 80',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Bodega de Albariño en Rías Baixas con experiencias guiadas, catas comentadas y recorridos entre viñedos pensados para grupos pequeños y escapadas de fin de semana.',
                'direccion' => 'Lugar de Castrelo, 18, 36639 Cambados, Pontevedra',
                'url_publica' => 'https://vinaatlantica.example',
                'politica_cancelacion' => 'Cancelación gratuita hasta 48 horas antes de la experiencia. Entre 48 y 24 horas se reembolsa el 50 %. Con menos de 24 horas no se garantiza reembolso salvo causa justificada.',
                'horas_minimas_cancelacion' => 48,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Tono cálido, sereno y experto en enoturismo. Habla como el equipo de acogida de una bodega gallega que conoce bien el Albariño y acompaña sin sonar a folleto.',
                'chat_required_fields' => [
                    'search_availability' => ['servicio_id', 'fecha', 'numero_personas'],
                    'create_booking' => ['servicio_id', 'fecha', 'hora_inicio', 'numero_personas', 'contact_name', 'contact_phone'],
                    'create_quote' => ['servicio_id', 'numero_personas'],
                    'get_service_details' => ['servicio_id'],
                    'get_cancellation_policy' => [],
                    'get_arrival_instructions' => [],
                    'check_business_hours' => [],
                    'list_bookable_services' => [],
                ],
                'chat_system_rules' => implode("\n", [
                    'La oferta pública se presenta como experiencias, visitas guiadas o catas; no como inventario interno de salas.',
                    'Si el usuario pide algo para este fin de semana, intenta orientar hacia sábado o domingo antes de enfriar la conversación.',
                    'No conviertas la respuesta en un catálogo técnico de vinos salvo que el usuario lo pida.',
                    'Si una experiencia premium requiere señal, explícalo con naturalidad solo cuando sea relevante.',
                    'Si faltan datos de contacto para cerrar una reserva, intenta pedir nombre, teléfono y email en el mismo turno.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrión de enoturismo y visitas a bodega',
                    'default_register' => 'Cercano, elegante y natural, con conocimiento del vino sin resultar pedante.',
                    'question_style' => 'Haz preguntas breves y útiles. Si faltan varios datos para cerrar una visita, pídelos juntos.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias o sesiones realmente distintas. Si hay una sola opción clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de experiencias, visitas, catas, recorridos o degustaciones; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesión próxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'viñedo', 'Albariño', 'plazas'],
                ],
                'mail_confirmacion_activo' => false,
                'mail_recordatorio_activo' => false,
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
            ['nombre' => 'Adrián Gómez', 'email' => 'adrian.gomez@example.com', 'telefono' => '600 410 001', 'notas' => 'Suele venir con amigos desde Pontevedra.'],
            ['nombre' => 'Lucía Varela', 'email' => 'lucia.varela@example.com', 'telefono' => '600 410 002', 'notas' => null],
            ['nombre' => 'Marta Otero', 'email' => 'marta.otero@example.com', 'telefono' => '600 410 003', 'notas' => 'Le interesan experiencias con maridaje.'],
            ['nombre' => 'Diego Casal', 'email' => null, 'telefono' => '600 410 004', 'notas' => 'Pregunta mucho por grupos privados.'],
            ['nombre' => 'Sara Lema', 'email' => 'sara.lema@example.com', 'telefono' => '600 410 005', 'notas' => null],
            ['nombre' => 'Miguel Vázquez', 'email' => 'miguel.vazquez@example.com', 'telefono' => '600 410 006', 'notas' => null],
            ['nombre' => 'Alba Touriñán', 'email' => null, 'telefono' => '600 410 007', 'notas' => 'Viene con familia en verano.'],
            ['nombre' => 'Raúl Fariña', 'email' => 'raul.farina@example.com', 'telefono' => '600 410 008', 'notas' => null],
            ['nombre' => 'Noelia Rey', 'email' => 'noelia.rey@example.com', 'telefono' => null, 'notas' => 'Interesada en experiencias premium.'],
            ['nombre' => 'Claudia Ríos', 'email' => 'claudia.rios@example.com', 'telefono' => '600 410 010', 'notas' => null],
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
                'nombre' => 'Orixe',
                'descripcion' => 'Visita guiada a la bodega histórica con paseo corto por viñedo y cata comentada de tres Albariños de la casa.',
                'duracion_minutos' => 75,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 18,
                'precio_base' => 22.00,
                'precio_menor' => 12.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Una forma muy cómoda de conocer la bodega si es tu primera visita a Rías Baixas.',
                'instrucciones_previas' => 'Recomendamos llegar 10 minutos antes para comenzar puntuales.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepción principal de la bodega',
                'incluye' => ['Visita guiada', 'Cata de 3 vinos', 'Aperitivo gallego'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Acceso adaptado en la zona de bodega y sala principal; el exterior tiene algunos tramos irregulares.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Ondas do Mar',
                'descripcion' => 'Experiencia de maridaje de Albariño con conservas premium y producto del mar, pensada para una visita relajada al mediodía.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 16,
                'precio_base' => 32.00,
                'precio_menor' => 18.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Ideal para quien quiere una experiencia gastronómica breve pero muy representativa de la ría.',
                'instrucciones_previas' => 'Si hay alergias alimentarias, conviene avisar con antelación.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Patio del lagar',
                'incluye' => ['Cata comentada', 'Maridaje con producto del mar', 'Visita corta a zona de elaboración'],
                'no_incluye' => ['Comida completa'],
                'accesibilidad_notas' => 'Sala accesible; consultar si se necesita apoyo adicional para grupos grandes.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cata Cantiga de Amigo',
                'descripcion' => 'Cata desenfadada de cuatro vinos con relato del territorio, perfecta para grupos de amigos y celebraciones suaves.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 4,
                'numero_personas_maximo' => 18,
                'precio_base' => 28.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Muy solicitada para grupos que quieren una experiencia cuidada pero informal.',
                'instrucciones_previas' => 'Conviene avisar si el grupo viene con margen justo para adaptar la acogida.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl'],
                'punto_encuentro' => 'Sala Cantiga',
                'incluye' => ['Cata de 4 vinos', 'Picoteo salado', 'Presentación guiada del viñedo'],
                'no_incluye' => ['Transporte', 'Menú largo'],
                'accesibilidad_notas' => 'Se desarrolla íntegramente en interior.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cata Cantiga de Amor',
                'descripcion' => 'Sesión premium en espacio privado con recorrido comentado, selección especial de botellas y maridaje delicado.',
                'duracion_minutos' => 105,
                'numero_personas_minimo' => 4,
                'numero_personas_maximo' => 10,
                'precio_base' => 48.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Formato íntimo, muy recomendable para escapadas especiales o pequeños grupos privados.',
                'instrucciones_previas' => 'La señal asegura la plaza y ayuda a preparar el maridaje especial.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'en'],
                'punto_encuentro' => 'Recepción principal de la bodega',
                'incluye' => ['Visita guiada', 'Cata premium', 'Maridaje especial', 'Acceso a sala privada'],
                'no_incluye' => ['Transporte'],
                'accesibilidad_notas' => 'Espacio accesible; si el grupo necesita apoyos extra, es mejor indicarlo al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 72,
                'es_reembolsable' => true,
                'porcentaje_senal' => 25.00,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Creaciones Singulares',
                'descripcion' => 'Taller guiado de edición limitada con cata comparada y conversación pausada sobre parcelas, suelos y añadas.',
                'duracion_minutos' => 120,
                'numero_personas_minimo' => 4,
                'numero_personas_maximo' => 8,
                'precio_base' => 58.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Experiencia de aforo muy reducido pensada para público especialmente interesado en el vino.',
                'instrucciones_previas' => 'Plazas muy limitadas; recomendamos cerrar la reserva con antelación.',
                'documentacion_requerida' => null,
                'idiomas' => ['es'],
                'punto_encuentro' => 'Aula Singular',
                'incluye' => ['Taller comentado', 'Cata comparada', 'Pequeño maridaje', 'Material de apoyo'],
                'no_incluye' => ['Envío de botellas a domicilio'],
                'accesibilidad_notas' => 'Actividad en interior, con mesas altas y apoyo del equipo si hace falta.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 72,
                'es_reembolsable' => true,
                'porcentaje_senal' => 30.00,
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
                'nombre' => 'Sala Orixe',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 18,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala luminosa junto al antiguo lagar, pensada para grupos medios.',
            ],
            [
                'nombre' => 'Mirador Atlántico',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 12,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Espacio con vistas a la ría, muy agradable para grupos pequeños.',
            ],
            [
                'nombre' => 'Sala Ondas',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 16,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala de catas con mesas corridas y apoyo para maridajes.',
            ],
            [
                'nombre' => 'Sala Cantiga',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 18,
                'capacidad_minima' => 4,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala cómoda para grupos y encuentros más animados.',
            ],
            [
                'nombre' => 'Sala Privada do Lagar',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 10,
                'capacidad_minima' => 4,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Espacio reservado para sesiones premium y grupos privados.',
            ],
            [
                'nombre' => 'Aula Singular',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 8,
                'capacidad_minima' => 4,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala pequeña de trabajo y cata avanzada para aforos muy reducidos.',
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
        $servicios['Orixe']->recursos()->sync([
            $recursos['Sala Orixe']->id,
            $recursos['Mirador Atlántico']->id,
        ]);

        $servicios['Ondas do Mar']->recursos()->sync([
            $recursos['Sala Ondas']->id,
        ]);

        $servicios['Cata Cantiga de Amigo']->recursos()->sync([
            $recursos['Sala Cantiga']->id,
        ]);

        $servicios['Cata Cantiga de Amor']->recursos()->sync([
            $recursos['Sala Privada do Lagar']->id,
        ]);

        $servicios['Creaciones Singulares']->recursos()->sync([
            $recursos['Aula Singular']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        $this->seedWeeklyAvailability($recursos['Sala Orixe'], [3, 4, 5, 6, 0], '11:00', '12:15', 'Orixe');
        $this->seedWeeklyAvailability($recursos['Mirador Atlántico'], [3, 4, 5, 6, 0], '11:00', '12:15', 'Orixe');
        $this->seedWeeklyAvailability($recursos['Sala Ondas'], [4, 5, 6, 0], '13:00', '14:30', 'Ondas do Mar');
        $this->seedWeeklyAvailability($recursos['Sala Cantiga'], [5, 6, 0], '17:00', '18:30', 'Cata Cantiga de Amigo');
        $this->seedWeeklyAvailability($recursos['Sala Privada do Lagar'], [5, 6], '18:45', '20:30', 'Cata Cantiga de Amor');
        $this->seedWeeklyAvailability($recursos['Aula Singular'], [6], '12:00', '14:00', 'Creaciones Singulares');
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
                'service' => $servicios['Orixe'],
                'resources' => [$recursos['Sala Orixe'], $recursos['Mirador Atlántico']],
                'weekdays' => [3, 4, 5, 6, 0],
                'start' => '11:00',
                'end' => '12:15',
                'note' => 'Visita y cata en horario de mañana.',
            ],
            [
                'service' => $servicios['Ondas do Mar'],
                'resources' => [$recursos['Sala Ondas']],
                'weekdays' => [4, 5, 6, 0],
                'start' => '13:00',
                'end' => '14:30',
                'note' => 'Maridaje del mediodía.',
            ],
            [
                'service' => $servicios['Cata Cantiga de Amigo'],
                'resources' => [$recursos['Sala Cantiga']],
                'weekdays' => [5, 6, 0],
                'start' => '17:00',
                'end' => '18:30',
                'note' => 'Formato ideal para grupos y escapadas de tarde.',
            ],
            [
                'service' => $servicios['Cata Cantiga de Amor'],
                'resources' => [$recursos['Sala Privada do Lagar']],
                'weekdays' => [5, 6],
                'start' => '18:45',
                'end' => '20:30',
                'note' => 'Sesión premium con sala privada.',
            ],
            [
                'service' => $servicios['Creaciones Singulares'],
                'resources' => [$recursos['Aula Singular']],
                'weekdays' => [6],
                'start' => '12:00',
                'end' => '14:00',
                'note' => 'Taller de aforo reducido.',
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
        $fullDayDate = Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString();
        $partialDate = Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString();
        $closureDate = Carbon::today()->next(Carbon::SUNDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Mirador Atlántico']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $fullDayDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Mantenimiento del mirador y revisión de climatización.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala Privada do Lagar']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $partialDate,
                'hora_inicio' => '18:45:00',
                'hora_fin' => '20:30:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Evento privado corporativo ya cerrado.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala Ondas']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['cierre']->id,
                'fecha' => $closureDate,
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:30:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Cierre puntual por montaje de cata profesional.',
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
                'localizador' => 'BVA-ORI-001',
                'cliente' => $clientes['Lucía Varela'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Sala Orixe'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 4,
                'precio_calculado' => 88.00,
                'precio_total' => 88.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo que viene desde Pontevedra para una escapada de mañana.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 88.00,
                        'referencia_externa' => 'DEMO-BVA-ORI-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(18, 30, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-ORI-002',
                'cliente' => $clientes['Miguel Vázquez'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Mirador Atlántico'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 2,
                'precio_calculado' => 44.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de confirmación definitiva por cambio de plan.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'BVA-MAR-001',
                'cliente' => $clientes['Alba Touriñán'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala Ondas'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:30:00',
                'numero_personas' => 6,
                'precio_calculado' => 192.00,
                'precio_total' => 192.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Celebración familiar tras paseo por Cambados.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 192.00,
                        'referencia_externa' => 'DEMO-BVA-MAR-001',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(11, 10, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-CAN-001',
                'cliente' => $clientes['Sara Lema'],
                'servicio' => $servicios['Cata Cantiga de Amigo'],
                'recurso' => $recursos['Sala Cantiga'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString(),
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:30:00',
                'numero_personas' => 8,
                'precio_calculado' => 224.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo de amigos de Vigo para despedida tranquila.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'BVA-AMO-001',
                'cliente' => $clientes['Diego Casal'],
                'servicio' => $servicios['Cata Cantiga de Amor'],
                'recurso' => $recursos['Sala Privada do Lagar'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek(2)->toDateString(),
                'hora_inicio' => '18:45:00',
                'hora_fin' => '20:30:00',
                'numero_personas' => 6,
                'precio_calculado' => 288.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Reserva premium pendiente de completar señal.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pendiente']->id,
                        'concepto_pago_id' => $conceptosPago['senal']->id,
                        'importe' => 72.00,
                        'referencia_externa' => 'DEMO-BVA-AMO-001',
                        'fecha_pago' => null,
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-SIN-001',
                'cliente' => $clientes['Noelia Rey'],
                'servicio' => $servicios['Creaciones Singulares'],
                'recurso' => $recursos['Aula Singular'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeeks(2)->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 5,
                'precio_calculado' => 290.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo interesado en vinos de parcela y edición limitada.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['senal']->id,
                        'importe' => 87.00,
                        'referencia_externa' => 'DEMO-BVA-SIN-001',
                        'fecha_pago' => Carbon::today()->setTime(10, 15, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-ORI-090',
                'cliente' => $clientes['Marta Otero'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Sala Orixe'],
                'fecha' => Carbon::today()->previous(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 3,
                'precio_calculado' => 66.00,
                'precio_total' => 66.00,
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
                        'importe' => 66.00,
                        'referencia_externa' => 'DEMO-BVA-ORI-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::SATURDAY)->setTime(13, 0, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-MAR-090',
                'cliente' => $clientes['Claudia Ríos'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala Ondas'],
                'fecha' => Carbon::today()->previous(Carbon::SUNDAY)->subWeek()->toDateString(),
                'hora_inicio' => '13:00:00',
                'hora_fin' => '14:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 64.00,
                'precio_total' => 64.00,
                'estado_reserva_id' => $estadosReserva['completada']->id,
                'notas' => 'Pareja de visita de fin de semana.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 64.00,
                        'referencia_externa' => 'DEMO-BVA-MAR-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::WEDNESDAY)->subWeek()->setTime(9, 45, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'BVA-CAN-090',
                'cliente' => $clientes['Raúl Fariña'],
                'servicio' => $servicios['Cata Cantiga de Amigo'],
                'recurso' => $recursos['Sala Cantiga'],
                'fecha' => Carbon::today()->previous(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:30:00',
                'numero_personas' => 5,
                'precio_calculado' => 140.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'El grupo no llegó a presentarse.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'BVA-AMO-090',
                'cliente' => $clientes['Adrián Gómez'],
                'servicio' => $servicios['Cata Cantiga de Amor'],
                'recurso' => $recursos['Sala Privada do Lagar'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString(),
                'hora_inicio' => '18:45:00',
                'hora_fin' => '20:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 192.00,
                'precio_total' => 192.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por cambio de plan del grupo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 48.00,
                        'referencia_externa' => 'DEMO-BVA-AMO-090',
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
