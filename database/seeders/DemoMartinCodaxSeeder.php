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

class DemoMartinCodaxSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeLegacyDemoBusinesses([
            'Bodegas Martín Códax',
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
            ['nombre' => 'Bodegas Martín Códax'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'enoturismo@martincodax.example',
                'telefono' => '+34 986 526 040',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Cooperativa de mas de 550 familias viticultures del Valle del Salnes. Desde su bodega sobre la colina que domina la Ria de Arousa, elaboran Albariños premiados que capturan la esencia del terroir atlantico gallego.',
                'direccion' => 'Burgáns 91, 36633 Vilariño, Cambados, Pontevedra',
                'url_publica' => 'https://martincodax.example',
                'politica_cancelacion' => 'Cancelacion gratuita hasta 48 horas antes. Entre 48 y 24 horas se reembolsa el 50%. Con menos de 24 horas no se garantiza reembolso.',
                'horas_minimas_cancelacion' => 48,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Eres un anfitrion de la bodega Martin Codax, experto en Albariño y en el terroir del Valle del Salnes. Representas una cooperativa con mas de 550 familias viticultoras, lo que te da un conocimiento profundo y cercano del territorio. Hablas con orgullo del proyecto colectivo, con naturalidad y calidez gallega, ajustando el nivel tecnico al cliente.',
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
                    'La oferta publica se presenta como experiencias, visitas guiadas, catas y maridajes; no como inventario interno de salas o recursos.',
                    'Esta bodega esta en Cambados, Valle del Salnes, en el corazon de la D.O. Rias Baixas, y el Albariño es la uva protagonista absoluta.',
                    'Martin Codax es una cooperativa de mas de 550 familias viticultoras; transmite ese espiritu colectivo cuando hables del proyecto.',
                    'Si el usuario pregunta por vinos, cepas, terroir, denominaciones de origen o maridajes, responde con conocimiento real sin inventar añadas ni premios concretos.',
                    'Puedes explicar una cata de forma pedagogica: vista, nariz, boca, final y contexto del vino, adaptando el nivel tecnico al cliente.',
                    'Si una experiencia premium requiere señal o aprobacion manual, explicalo con naturalidad solo cuando sea relevante.',
                    'Para cerrar una reserva, intenta recoger nombre, telefono y email en el mismo bloque. Como enviamos confirmacion por email, el email es importante.',
                    'Si no hay plazas en una experiencia, ofrece otra experiencia cercana en estilo o franja si la disponibilidad lo permite.',
                    'No inventes bodegas, vinos concretos, añadas o premios que no esten respaldados por el contexto del negocio.',
                    'Avisa sobre alergenos (marisco/conservas) en las experiencias Ondas do Mar y Creaciones Singulares cuando sea relevante.',
                ]),
                'chat_behavior_overrides' => [
                    'human_role' => 'Anfitrion de bodega cooperativa, guia de enoturismo y embajador del Albariño del Salnes',
                    'default_register' => 'Amable, cercano y orgulloso del territorio, con conocimiento del vino sin sonar academico ni distante.',
                    'question_style' => 'Haz preguntas breves y utiles. Si faltan varios datos para cerrar una visita, intenta pedirlos juntos en uno o dos turnos como maximo.',
                    'option_style' => 'Da opciones solo cuando ayuden a elegir entre experiencias o sesiones realmente distintas. Si hay una sola opcion clara, proponla directamente.',
                    'offer_naming_style' => 'Habla de experiencias, visitas, catas, maridajes o recorridos entre vinedos; evita lenguaje de backoffice.',
                    'inventory_exposure_policy' => 'show_only_customer_safe_descriptors',
                    'no_availability_policy' => 'Si no hay plazas para esa experiencia o fecha, dilo claro y ofrece una sesion proxima o una experiencia parecida si existe.',
                    'vocabulary_hints' => ['experiencia', 'cata', 'visita', 'viñedo', 'Albariño', 'cooperativa', 'Valle del Salnes', 'maridaje', 'plazas'],
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
            ['nombre' => 'Xoán Doval', 'email' => 'xoan.doval@example.com', 'telefono' => '600 520 001', 'notas' => 'Socio de la cooperativa, suele traer visitas familiares.'],
            ['nombre' => 'Iria Nogueira', 'email' => 'iria.nogueira@example.com', 'telefono' => '600 520 002', 'notas' => null],
            ['nombre' => 'Breixo Carballo', 'email' => 'breixo.carballo@example.com', 'telefono' => '600 520 003', 'notas' => 'Interesado en experiencias premium y vinos de parcela.'],
            ['nombre' => 'Antía Feijóo', 'email' => null, 'telefono' => '600 520 004', 'notas' => 'Viene con grupo de amigas desde Santiago.'],
            ['nombre' => 'Roi Vilar', 'email' => 'roi.vilar@example.com', 'telefono' => '600 520 005', 'notas' => null],
            ['nombre' => 'Sabela Pombo', 'email' => 'sabela.pombo@example.com', 'telefono' => '600 520 006', 'notas' => 'Alergia a mariscos, avisar en experiencias con conservas.'],
            ['nombre' => 'Uxía Loureiro', 'email' => null, 'telefono' => '600 520 007', 'notas' => 'Familia con nenos, prefiere experiencia Orixe.'],
            ['nombre' => 'Marcos Salgado', 'email' => 'marcos.salgado@example.com', 'telefono' => '600 520 008', 'notas' => null],
            ['nombre' => 'Noa Estévez', 'email' => 'noa.estevez@example.com', 'telefono' => null, 'notas' => 'Repite visita cada verano.'],
            ['nombre' => 'Celso Figueiras', 'email' => 'celso.figueiras@example.com', 'telefono' => '600 520 010', 'notas' => null],
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
                'descripcion' => 'Visita guiada al viñedo y la bodega con cata comentada de 2 Albariños de la casa y 2 quesos artesanos gallegos. Ideal para familias y primeras visitas a Rias Baixas.',
                'duracion_minutos' => 75,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 24,
                'precio_base' => 21.00,
                'precio_menor' => 8.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => true,
                'edad_minima' => null,
                'activo' => true,
                'notas_publicas' => 'Menores de 5 a 17 años: 8 EUR. Menores de 5 años gratis. Maximo 2 menores por adulto.',
                'instrucciones_previas' => 'Recomendamos llegar 10 minutos antes para comenzar puntuales.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita guiada al viñedo', 'Recorrido por la bodega', 'Cata de 2 vinos Albariño', '2 quesos artesanos gallegos'],
                'no_incluye' => ['Transporte hasta la bodega'],
                'accesibilidad_notas' => 'Acceso adaptado en la zona de bodega y sala de catas; el recorrido por viñedo tiene tramos de tierra.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Ondas do Mar',
                'descripcion' => 'Recorrido por la bodega con cata comentada de 3 Albariños y maridaje con 3 creaciones gastronomicas elaboradas con conservas del mar gallego. Solo mayores de 18.',
                'duracion_minutos' => 90,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 24,
                'precio_base' => 28.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => false,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Contiene alergenos de marisco y pescado (conservas del mar). Consultar si hay restricciones alimentarias.',
                'instrucciones_previas' => 'Si hay alergias alimentarias, conviene avisar con antelacion.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl', 'en'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Visita guiada a la bodega', 'Cata comentada de 3 Albariños', '3 creaciones gastronomicas con conservas del mar'],
                'no_incluye' => ['Comida completa', 'Transporte'],
                'accesibilidad_notas' => 'Sala accesible en planta baja; consultar si se necesita apoyo adicional.',
                'requiere_aprobacion_manual' => false,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Creaciones Singulares',
                'descripcion' => 'Experiencia premium de edicion limitada con vinos de produccion reducida, proyectos experimentales, diversidad de terroir, viñedos centenarios, vendimia tardia y maridajes gourmet. Solo mayores de 18.',
                'duracion_minutos' => 120,
                'numero_personas_minimo' => 1,
                'numero_personas_maximo' => 24,
                'precio_base' => 50.00,
                'precio_menor' => null,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'permite_menores' => false,
                'edad_minima' => 18,
                'activo' => true,
                'notas_publicas' => 'Experiencia de aforo reducido y formato exclusivo. Contiene alergenos de marisco. Requiere aprobacion y señal del 30%.',
                'instrucciones_previas' => 'Plazas muy limitadas; recomendamos cerrar la reserva con antelacion. La señal asegura la plaza.',
                'documentacion_requerida' => null,
                'idiomas' => ['es', 'gl'],
                'punto_encuentro' => 'Recepcion de la bodega',
                'incluye' => ['Cata de vinos de produccion limitada', 'Vinos experimentales y de viñedos centenarios', 'Vendimia tardia', 'Maridaje gourmet'],
                'no_incluye' => ['Envio de botellas a domicilio', 'Transporte'],
                'accesibilidad_notas' => 'Espacio accesible en interior; consultar necesidades especificas al reservar.',
                'requiere_aprobacion_manual' => true,
                'horas_minimas_cancelacion' => 48,
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
                'nombre' => 'Sala de Catas Principal',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 24,
                'capacidad_minima' => 1,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala principal de catas con vistas a la Ria de Arousa.',
            ],
            [
                'nombre' => 'Terraza Panoramica',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 24,
                'capacidad_minima' => 2,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Terraza al aire libre con vistas panoramicas al Valle del Salnes, ideal con buen tiempo.',
            ],
            [
                'nombre' => 'Reservado Martin Codax',
                'tipo_recurso_id' => $tipoRecursoSalaCatas->id,
                'capacidad' => 12,
                'capacidad_minima' => 4,
                'combinable' => false,
                'activo' => true,
                'notas_publicas' => 'Sala privada reservada para eventos y experiencias premium de aforo reducido.',
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
            $recursos['Sala de Catas Principal']->id,
            $recursos['Terraza Panoramica']->id,
        ]);

        $servicios['Ondas do Mar']->recursos()->sync([
            $recursos['Sala de Catas Principal']->id,
        ]);

        $servicios['Creaciones Singulares']->recursos()->sync([
            $recursos['Reservado Martin Codax']->id,
        ]);
    }

    private function seedDisponibilidades(Collection $recursos): void
    {
        // Sala de Catas Principal: Tue-Sat 11:00-12:15 (Orixe)
        $this->seedWeeklyAvailability($recursos['Sala de Catas Principal'], [2, 3, 4, 5, 6], '11:00', '12:15', 'Orixe');
        // Sala de Catas Principal: Tue-Sat 12:00-13:30 (Ondas do Mar)
        $this->seedWeeklyAvailability($recursos['Sala de Catas Principal'], [2, 3, 4, 5, 6], '12:00', '13:30', 'Ondas do Mar');
        // Sala de Catas Principal: Tue-Thu 17:00-18:30 (Ondas do Mar afternoon)
        $this->seedWeeklyAvailability($recursos['Sala de Catas Principal'], [2, 3, 4], '17:00', '18:30', 'Ondas do Mar');
        // Terraza Panoramica: Sat-Sun 11:00-12:15 (Orixe)
        $this->seedWeeklyAvailability($recursos['Terraza Panoramica'], [6, 0], '11:00', '12:15', 'Orixe');
        // Reservado Martin Codax: Friday 12:00-14:00 (Creaciones Singulares)
        $this->seedWeeklyAvailability($recursos['Reservado Martin Codax'], [5], '12:00', '14:00', 'Creaciones Singulares');
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
            // Orixe in Sala de Catas Principal: Tue-Sat 11:00-12:15
            [
                'service' => $servicios['Orixe'],
                'resources' => [$recursos['Sala de Catas Principal']],
                'weekdays' => [2, 3, 4, 5, 6],
                'start' => '11:00',
                'end' => '12:15',
                'note' => 'Visita guiada con cata de Albariño y quesos gallegos.',
            ],
            // Orixe in Terraza Panoramica: Sat-Sun 11:00-12:15
            [
                'service' => $servicios['Orixe'],
                'resources' => [$recursos['Terraza Panoramica']],
                'weekdays' => [6, 0],
                'start' => '11:00',
                'end' => '12:15',
                'note' => 'Visita con cata en la terraza panoramica.',
            ],
            // Ondas do Mar in Sala de Catas Principal: Tue-Sat 12:00-13:30
            [
                'service' => $servicios['Ondas do Mar'],
                'resources' => [$recursos['Sala de Catas Principal']],
                'weekdays' => [2, 3, 4, 5, 6],
                'start' => '12:00',
                'end' => '13:30',
                'note' => 'Maridaje de Albariño con conservas del mar.',
            ],
            // Ondas do Mar in Sala de Catas Principal: Tue-Thu 17:00-18:30 (afternoon)
            [
                'service' => $servicios['Ondas do Mar'],
                'resources' => [$recursos['Sala de Catas Principal']],
                'weekdays' => [2, 3, 4],
                'start' => '17:00',
                'end' => '18:30',
                'note' => 'Sesion de tarde: maridaje de Albariño con conservas del mar.',
            ],
            // Creaciones Singulares in Reservado Martin Codax: Friday 12:00-14:00
            [
                'service' => $servicios['Creaciones Singulares'],
                'resources' => [$recursos['Reservado Martin Codax']],
                'weekdays' => [5],
                'start' => '12:00',
                'end' => '14:00',
                'note' => 'Experiencia premium de edicion limitada.',
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
        // Full-day maintenance on Sala de Catas Principal on upcoming Friday
        $fullDayDate = Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString();
        // Partial event block on Reservado Martin Codax on upcoming Saturday evening
        $partialDate = Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString();
        // Closure on a Sunday
        $closureDate = Carbon::today()->next(Carbon::SUNDAY)->addWeek()->toDateString();

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Sala de Catas Principal']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['mantenimiento']->id,
                'fecha' => $fullDayDate,
            ],
            [
                'negocio_id' => $negocio->id,
                'hora_inicio' => null,
                'hora_fin' => null,
                'motivo' => 'Mantenimiento y limpieza profunda de la sala de catas principal.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Reservado Martin Codax']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['evento']->id,
                'fecha' => $partialDate,
                'hora_inicio' => '18:00:00',
                'hora_fin' => '22:00:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Evento corporativo privado en el reservado.',
                'activo' => true,
                'es_recurrente' => false,
                'dia_semana' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]
        );

        Bloqueo::updateOrCreate(
            [
                'recurso_id' => $recursos['Terraza Panoramica']->id,
                'tipo_bloqueo_id' => $tiposBloqueo['cierre']->id,
                'fecha' => $closureDate,
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
            ],
            [
                'negocio_id' => $negocio->id,
                'motivo' => 'Cierre puntual de la terraza por prevision de temporal.',
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
            // 1. Confirmed Orixe - upcoming Saturday morning
            [
                'localizador' => 'BMC-ORI-001',
                'cliente' => $clientes['Xoán Doval'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 4,
                'precio_calculado' => 84.00,
                'precio_total' => 84.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Familia del socio cooperativista, primera visita con nenos.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tarjeta']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 84.00,
                        'referencia_externa' => 'DEMO-BMC-ORI-001',
                        'fecha_pago' => Carbon::today()->subDay()->setTime(19, 15, 0),
                    ],
                ],
            ],
            // 2. Pending Orixe - upcoming Sunday on Terraza
            [
                'localizador' => 'BMC-ORI-002',
                'cliente' => $clientes['Roi Vilar'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Terraza Panoramica'],
                'fecha' => Carbon::today()->next(Carbon::SUNDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 2,
                'precio_calculado' => 42.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Pendiente de confirmacion, posible cambio de fecha.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            // 3. Confirmed Ondas do Mar - upcoming Saturday midday
            [
                'localizador' => 'BMC-ODM-001',
                'cliente' => $clientes['Iria Nogueira'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '13:30:00',
                'numero_personas' => 6,
                'precio_calculado' => 168.00,
                'precio_total' => 168.00,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Grupo de amigas de Pontevedra, celebracion de cumpleaños.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 168.00,
                        'referencia_externa' => 'DEMO-BMC-ODM-001',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(10, 30, 0),
                    ],
                ],
            ],
            // 4. Confirmed Ondas do Mar - upcoming Tuesday afternoon
            [
                'localizador' => 'BMC-ODM-002',
                'cliente' => $clientes['Marcos Salgado'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->next(Carbon::TUESDAY)->toDateString(),
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:30:00',
                'numero_personas' => 3,
                'precio_calculado' => 84.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Visita entre semana con colegas de trabajo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            // 5. Pending Creaciones Singulares - upcoming Friday (requires payment + manual approval)
            [
                'localizador' => 'BMC-CSI-001',
                'cliente' => $clientes['Breixo Carballo'],
                'servicio' => $servicios['Creaciones Singulares'],
                'recurso' => $recursos['Reservado Martin Codax'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek(2)->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 6,
                'precio_calculado' => 300.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['pendiente']->id,
                'notas' => 'Grupo de enologos aficionados, pendiente de señal y aprobacion.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pendiente']->id,
                        'concepto_pago_id' => $conceptosPago['senal']->id,
                        'importe' => 90.00,
                        'referencia_externa' => 'DEMO-BMC-CSI-001',
                        'fecha_pago' => null,
                    ],
                ],
            ],
            // 6. Confirmed Creaciones Singulares - next Friday
            [
                'localizador' => 'BMC-CSI-002',
                'cliente' => $clientes['Noa Estévez'],
                'servicio' => $servicios['Creaciones Singulares'],
                'recurso' => $recursos['Reservado Martin Codax'],
                'fecha' => Carbon::today()->next(Carbon::FRIDAY)->addWeek()->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '14:00:00',
                'numero_personas' => 4,
                'precio_calculado' => 200.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['confirmada']->id,
                'notas' => 'Repite visita premium, grupo reducido de conocedores.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['senal']->id,
                        'importe' => 60.00,
                        'referencia_externa' => 'DEMO-BMC-CSI-002',
                        'fecha_pago' => Carbon::today()->setTime(11, 0, 0),
                    ],
                ],
            ],
            // 7. Completed Orixe - past Sunday
            [
                'localizador' => 'BMC-ORI-090',
                'cliente' => $clientes['Uxía Loureiro'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->previous(Carbon::SATURDAY)->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 5,
                'precio_calculado' => 105.00,
                'precio_total' => 105.00,
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
                        'importe' => 105.00,
                        'referencia_externa' => 'DEMO-BMC-ORI-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::FRIDAY)->setTime(14, 0, 0),
                    ],
                ],
            ],
            // 8. Completed Ondas do Mar - past week
            [
                'localizador' => 'BMC-ODM-090',
                'cliente' => $clientes['Celso Figueiras'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->previous(Carbon::THURSDAY)->subWeek()->toDateString(),
                'hora_inicio' => '12:00:00',
                'hora_fin' => '13:30:00',
                'numero_personas' => 2,
                'precio_calculado' => 56.00,
                'precio_total' => 56.00,
                'estado_reserva_id' => $estadosReserva['completada']->id,
                'notas' => 'Pareja en escapada por Cambados.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['transferencia']->id,
                        'estado_pago_id' => $estadosPago['pagado']->id,
                        'concepto_pago_id' => $conceptosPago['final']->id,
                        'importe' => 56.00,
                        'referencia_externa' => 'DEMO-BMC-ODM-090',
                        'fecha_pago' => Carbon::today()->previous(Carbon::MONDAY)->subWeek()->setTime(9, 30, 0),
                    ],
                ],
            ],
            // 9. No-show Ondas do Mar - past Tuesday
            [
                'localizador' => 'BMC-ODM-091',
                'cliente' => $clientes['Antía Feijóo'],
                'servicio' => $servicios['Ondas do Mar'],
                'recurso' => $recursos['Sala de Catas Principal'],
                'fecha' => Carbon::today()->previous(Carbon::TUESDAY)->toDateString(),
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:30:00',
                'numero_personas' => 4,
                'precio_calculado' => 112.00,
                'precio_total' => null,
                'estado_reserva_id' => $estadosReserva['no_presentada']->id,
                'notas' => 'El grupo no se presento a la sesion de tarde.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [],
            ],
            // 10. Cancelled Orixe - upcoming week, with reembolso
            [
                'localizador' => 'BMC-ORI-091',
                'cliente' => $clientes['Sabela Pombo'],
                'servicio' => $servicios['Orixe'],
                'recurso' => $recursos['Terraza Panoramica'],
                'fecha' => Carbon::today()->next(Carbon::SATURDAY)->addWeek()->toDateString(),
                'hora_inicio' => '11:00:00',
                'hora_fin' => '12:15:00',
                'numero_personas' => 3,
                'precio_calculado' => 63.00,
                'precio_total' => 63.00,
                'estado_reserva_id' => $estadosReserva['cancelada']->id,
                'notas' => 'Cancelada por problemas de agenda del grupo.',
                'origen_reserva' => 'seed_demo',
                'tipo_documento_responsable' => null,
                'documento_responsable' => null,
                'pagos' => [
                    [
                        'tipo_pago_id' => $tiposPago['tpv']->id,
                        'estado_pago_id' => $estadosPago['reembolsado']->id,
                        'concepto_pago_id' => $conceptosPago['reembolso']->id,
                        'importe' => 63.00,
                        'referencia_externa' => 'DEMO-BMC-ORI-091',
                        'fecha_pago' => Carbon::today()->subDays(2)->setTime(15, 45, 0),
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
