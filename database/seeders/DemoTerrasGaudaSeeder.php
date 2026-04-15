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

class DemoTerrasGaudaSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Terras Gauda',
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
            ['nombre' => 'Terras Gauda'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'enoturismo@terrasgauda.example',
                'telefono' => '+34 986 621 001',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Bodega de referencia en la subzona de O Rosal de Rias Baixas, elaborando vinos distintivos que combinan Albariño con variedades autoctonas como Caino Blanco y Loureira. Entre los vinedos del valle del Miño, cerca de la frontera portuguesa, ofrece experiencias inmersivas de enoturismo.',
                'direccion' => 'Carretera Tui - A Guarda, Km 55, 36760 O Rosal, Pontevedra',
                'url_publica' => 'https://terrasgauda.example',
                'politica_cancelacion' => 'Cancelaciones con mas de 48 horas de antelacion reciben reembolso completo por transferencia. Sin reembolso ni cambio de fecha dentro de las 48 horas. Pago obligatorio al reservar.',
                'horas_minimas_cancelacion' => 48,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Eres un anfitrion experto en enologia y enoturismo en la subzona de O Rosal. Conoces bien el Albariño, el Caino Blanco, la Loureira y los coupage tipicos de la zona. Hablas con cercania, elegancia y pasion por el territorio del valle del Miño.',
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
                    'La oferta publica se presenta como experiencias, visitas guiadas, catas y recorridos; no como inventario interno de salas.',
                    'Esta bodega esta en O Rosal, en la subzona mas meridional de D.O. Rias Baixas, y sus vinos combinan Albariño con variedades autoctonas como Caino Blanco y Loureira.',
                    'Si el usuario pregunta por denominaciones de origen, vinos gallegos, variedades autoctonas o maridajes, responde con naturalidad y conocimiento real sin salirte del contexto del negocio.',
                    'Puedes explicar una cata de forma pedagogica: vista, nariz, boca, final y contexto del vino, adaptando el nivel tecnico al cliente.',
                    'Si el cliente quiere algo mas festivo o relajado, no respondas como una ficha tecnica; si quiere detalle, si puedes elevar el nivel.',
                    'Si el usuario pide algo para este fin de semana, intenta orientar hacia sabado o domingo antes de enfriar la conversacion.',
                    'Para cerrar una reserva, intenta recoger nombre, telefono y email en el mismo bloque. Como enviamos confirmacion por email, el email es importante.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                    'No inventes bodegas, vinos concretos, añadas o premios que no esten respaldados por el contexto del negocio.',
                    'Los vinos de referencia de la bodega son Terras Gauda, Abadia de San Campio, Etiqueta Negra y La Mar.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrion de bodega, guia de enoturismo o sumiller divulgativo',
                    'default_register' => 'Amable, elegante y cercano, con conocimiento del vino y del territorio del Miño sin sonar pedante.',
                    'question_style' => 'Haz preguntas breves y utiles. Si faltan varios datos para cerrar una visita, intenta pedirlos juntos en uno o dos turnos como maximo.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias o sesiones realmente distintas. Si hay una sola opcion clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de experiencias, visitas, catas, maridajes o recorridos entre vinedos; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesion proxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'viñedo', 'Albariño', 'Caino Blanco', 'Loureira', 'coupage', 'denominacion de origen', 'maridaje', 'plazas'],
                ],
                'mail_confirmacion_activo' => true,
                'mail_recordatorio_activo' => true,
                'mail_recordatorio_horas_antes' => 24,
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
            ['nombre' => 'Iria Nogueira', 'email' => 'iria.nogueira@example.com', 'telefono' => '600 520 001', 'notas' => 'Repite visita cada verano con amigos.'],
            ['nombre' => 'Brais Dominguez', 'email' => 'brais.dominguez@example.com', 'telefono' => '600 520 002', 'notas' => null],
            ['nombre' => 'Antia Paz', 'email' => 'antia.paz@example.com', 'telefono' => '600 520 003', 'notas' => 'Interesada en experiencias premium y vinos de coupage.'],
            ['nombre' => 'Xoan Riveira', 'email' => null, 'telefono' => '600 520 004', 'notas' => 'Viene en pareja desde Vigo.'],
            ['nombre' => 'Lara Souto', 'email' => 'lara.souto@example.com', 'telefono' => '600 520 005', 'notas' => null],
            ['nombre' => 'Hugo Carballo', 'email' => 'hugo.carballo@example.com', 'telefono' => '600 520 006', 'notas' => null],
            ['nombre' => 'Nerea Doval', 'email' => 'nerea.doval@example.com', 'telefono' => null, 'notas' => 'Familia con dos niños pequeños.'],
            ['nombre' => 'Marcos Iglesias', 'email' => 'marcos.iglesias@example.com', 'telefono' => '600 520 008', 'notas' => null],
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
                'nombre' => 'Dejate Conquistar',
                'descripcion' => 'Recorrido por los viñedos y la bodega con cata comentada de dos vinos emblematicos: Terras Gauda y Abadia de San Campio, acompañados de picos artesanos.',
                'duracion_minutos' => 60,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 20,
                'precio_base' => 16.00,
                'precio_menor' => 0.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Experiencia ideal para una primera toma de contacto con la bodega y los vinos de O Rosal.',
                'instrucciones_previas' => 'Punto de encuentro en la recepcion de la bodega. Recomendamos llegar 10 minutos antes.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita al viñedo', 'Visita a la bodega', 'Cata de 2 vinos', 'Picos artesanos'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Recorrido adaptado para personas con movilidad reducida.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Terras Gauda para Dos',
                'descripcion' => 'Experiencia privada en pareja con recorrido exclusivo por viñedos y bodega, cata de Abadia de San Campio y Terras Gauda, aperitivo y una botella de regalo.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 2,
                'precio_base' => 25.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Formato intimo para parejas. Incluye una botella de regalo para llevar a casa.',
                'instrucciones_previas' => 'Experiencia sujeta a confirmacion manual. Se confirmara en un plazo maximo de 24 horas.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en', 'fr'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita privada al viñedo', 'Visita privada a la bodega', 'Cata de Abadia de San Campio y Terras Gauda', 'Aperitivo', '1 botella de regalo'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Consultar disponibilidad de recorrido adaptado al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'En Buena Compañia',
                'descripcion' => 'Cata premium de tres vinos selectos incluyendo Etiqueta Negra y La Mar, con aperitivo cuidado. Exclusiva para mayores de 18 años.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 3,
                'numero_personas_maximo' => 8,
                'precio_base' => 29.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Experiencia premium para grupos pequeños que buscan conocer los vinos mas exclusivos de la bodega.',
                'instrucciones_previas' => 'Experiencia sujeta a confirmacion manual. Si hay alergias alimentarias, conviene avisar con antelacion.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en', 'fr'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita guiada', 'Cata de 3 vinos premium', 'Aperitivo'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Consultar al reservar si se necesita apoyo adicional.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Plan Familiar',
                'descripcion' => 'Visita a la bodega adaptada a familias con niños: cata para adultos, zumo de uva para los pequenos, aperitivo, taller educativo y sacacorchos de regalo.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 2,
                'numero_personas_maximo' => 10,
                'precio_base' => 22.00,
                'precio_menor' => 9.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Maximo 6 menores por grupo. Incluye actividades educativas para niños y un sacacorchos de regalo.',
                'instrucciones_previas' => 'Experiencia sujeta a confirmacion manual. Indicar numero de adultos y menores al reservar.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita a la bodega', 'Cata para adultos', 'Zumo de uva para niños', 'Aperitivo', 'Taller educativo', 'Sacacorchos de regalo'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Espacios adaptados para familias con carritos.',
                'requiere_aprobacion_manual' => true,
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
                'capacidad' => 20,
                'capacidad_minima' => 1,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala principal de catas para visitas guiadas y grupos.',
            ],
            [
                'nombre' => 'Salon Privado',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 8,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Espacio reservado para experiencias premium y parejas.',
            ],
            [
                'nombre' => 'Sala Familiar',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 10,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala adaptada para visitas familiares con niños.',
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
        $servicios['Dejate Conquistar']->recursos()->sync([
            $recursos['Sala de Catas']->id,
        ]);

        $servicios['Terras Gauda para Dos']->recursos()->sync([
            $recursos['Salon Privado']->id,
        ]);

        $servicios['En Buena Compañia']->recursos()->sync([
            $recursos['Salon Privado']->id,
        ]);

        $servicios['Plan Familiar']->recursos()->sync([
            $recursos['Sala Familiar']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        $this->seedWeeklyAvailability($recursos['Sala de Catas'], [1, 2, 3, 4, 5, 6], '16:00', '17:00', 'Dejate Conquistar');
        $this->seedWeeklyAvailability($recursos['Salon Privado'], [2, 3, 4, 5, 6], '11:00', '12:30', 'Premium');
        $this->seedWeeklyAvailability($recursos['Sala Familiar'], [6, 0], '11:00', '12:30', 'Plan Familiar');
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
                'service' => $servicios['Dejate Conquistar'],
                'resources' => [$recursos['Sala de Catas']],
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'start' => '16:00',
                'end' => '17:00',
                'note' => 'Visita guiada y cata de tarde.',
            ],
            [
                'service' => $servicios['Terras Gauda para Dos'],
                'resources' => [$recursos['Salon Privado']],
                'weekdays' => [2, 3, 4, 5, 6],
                'start' => '11:00',
                'end' => '12:30',
                'note' => 'Experiencia privada para parejas.',
            ],
            [
                'service' => $servicios['En Buena Compañia'],
                'resources' => [$recursos['Salon Privado']],
                'weekdays' => [2, 3, 4, 5, 6],
                'start' => '11:00',
                'end' => '12:30',
                'note' => 'Cata premium de grupo reducido.',
            ],
            [
                'service' => $servicios['Plan Familiar'],
                'resources' => [$recursos['Sala Familiar']],
                'weekdays' => [6, 0],
                'start' => '11:00',
                'end' => '12:30',
                'note' => 'Visita familiar con taller educativo.',
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
        $fullDayDate = Carbon::today()->next(Carbon::WEDNESDAY)->addWeek()->toDateString();
        $partialDate = Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala de Catas']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $fullDayDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Mantenimiento de climatizacion y revision de equipamiento de la sala.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Salon Privado']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $partialDate,
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Evento privado corporativo reservado con antelacion.',
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
                'localizador' => 'TGA-CON-001',
                'cliente' => $clientes['Iria Nogueira'],
                'servicio' => $servicios['Dejate Conquistar'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '17:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 64.00,
                'precio_total' => 64.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo de amigas de Pontevedra, primera visita a O Rosal.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 64.00,
                        'referencia_externa' => 'DEMO-TGA-CON-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(18, 30, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'TGA-CON-002',
                'cliente' => $clientes['Brais Dominguez'],
                'servicio' => $servicios['Dejate Conquistar'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->next(Carbon::THURSDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '17:00:00',
                'numero_personas' => 2,
                'precio_calculado' => 32.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de confirmacion por cambio de fecha.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'TGA-DOS-001',
                'cliente' => $clientes['Xoan Riveira'],
                'servicio' => $servicios['Terras Gauda para Dos'],
                'recurso' => $recursos['Salon Privado'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 50.00,
                'precio_total' => 50.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Pareja de Vigo, aniversario.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 50.00,
                        'referencia_externa' => 'DEMO-TGA-DOS-001',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(11, 10, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'TGA-BUE-001',
                'cliente' => $clientes['Antia Paz'],
                'servicio' => $servicios['En Buena Compañia'],
                'recurso' => $recursos['Salon Privado'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 116.00,
                'precio_total' => 116.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo premium interesado en Etiqueta Negra y La Mar.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 116.00,
                        'referencia_externa' => 'DEMO-TGA-BUE-001',
                        'fecha_pago' => Carbon::today()->subDays(3)->setTime(14, 20, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'TGA-FAM-001',
                'cliente' => $clientes['Nerea Doval'],
                'servicio' => $servicios['Plan Familiar'],
                'recurso' => $recursos['Sala Familiar'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 5,
                'precio_calculado' => 84.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Familia con 2 niños. Pendiente de aprobacion manual.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            [
                'localizador' => 'TGA-CON-090',
                'cliente' => $clientes['Lara Souto'],
                'servicio' => $servicios['Dejate Conquistar'],
                'recurso' => $recursos['Sala de Catas'],
                'fecha' => Carbon::today()->previous(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '16:00:00',
                'hora_fin' => '17:00:00',
                'numero_personas' => 6,
                'precio_calculado' => 96.00,
                'precio_total' => 96.00,
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
                        'importe' => 96.00,
                        'referencia_externa' => 'DEMO-TGA-CON-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::FRIDAY)->setTime(13, 0, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'TGA-DOS-090',
                'cliente' => $clientes['Hugo Carballo'],
                'servicio' => $servicios['Terras Gauda para Dos'],
                'recurso' => $recursos['Salon Privado'],
                'fecha' => Carbon::today()->previous(Carbon::FRIDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 50.00,
                'precio_total' => 50.00,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'La pareja no llego a presentarse.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 50.00,
                        'referencia_externa' => 'DEMO-TGA-DOS-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::WEDNESDAY)->setTime(9, 45, 0),
                    ],
                ],
            ],
            [
                'localizador' => 'TGA-BUE-090',
                'cliente' => $clientes['Marcos Iglesias'],
                'servicio' => $servicios['En Buena Compañia'],
                'recurso' => $recursos['Salon Privado'],
                'fecha' => Carbon::today()->next(Carbon::TUESDAY)->addWeek()->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:30:00',
                'numero_personas' => 3,
                'precio_calculado' => 87.00,
                'precio_total' => 87.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por cambio de planes del grupo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 87.00,
                        'referencia_externa' => 'DEMO-TGA-BUE-090',
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
