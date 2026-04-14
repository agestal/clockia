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
use App\Models\TipoBloqueo;
use App\Models\TipoNegocio;
use App\Models\TipoPago;
use App\Models\TipoPrecio;
use App\Models\TipoRecurso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Demo: Bodega etnoturística inspirada en Bodegas Martín Códax (Cambados).
 * Datos basados en información pública real, adaptados para testing.
 */
class DemoBodegaSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Ensure catalog types exist ───

        $tipoNegocioBodega = TipoNegocio::firstOrCreate(
            ['nombre' => 'Bodega'],
            ['descripcion' => 'Bodega con actividad enoturística, catas y experiencias.']
        );

        $tipoRecursoSalaCatas = TipoRecurso::firstOrCreate(
            ['nombre' => 'Sala de catas'],
            ['descripcion' => 'Espacio dedicado a catas y experiencias enoturísticas.']
        );

        $tipoRecursoSala = TipoRecurso::where('nombre', 'Sala')->firstOrFail();

        $tipoPrecioPorPersona = TipoPrecio::where('nombre', 'Por persona')->firstOrFail();
        $tipoPrecioFijo = TipoPrecio::where('nombre', 'Fijo')->firstOrFail();

        $estadoReservaPendiente = EstadoReserva::where('nombre', 'Pendiente')->firstOrFail();
        $estadoReservaConfirmada = EstadoReserva::where('nombre', 'Confirmada')->firstOrFail();
        $estadoReservaCompletada = EstadoReserva::where('nombre', 'Completada')->firstOrFail();

        $tipoPagoTarjeta = TipoPago::where('nombre', 'Tarjeta')->firstOrFail();
        $tipoPagoTpvOnline = TipoPago::where('nombre', 'TPV online')->firstOrFail();
        $estadoPagoPagado = EstadoPago::where('nombre', 'Pagado')->firstOrFail();

        // ─── Negocio ───

        $negocio = Negocio::updateOrCreate(
            ['nombre' => 'Bodegas Viña Atlántica'],
            [
                'tipo_negocio_id' => $tipoNegocioBodega->id,
                'email' => 'enoturismo@vinaatlantica.demo',
                'telefono' => '986 526 040',
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
                'descripcion_publica' => 'Bodega cooperativa de Albariño D.O. Rías Baixas en Cambados, con más de 400 familias vitícolas. Ofrecemos experiencias enoturísticas con vistas privilegiadas a la Ría de Arousa, donde descubrirás el origen y elaboración del mejor Albariño gallego acompañado de productos locales de km 0.',
                'direccion' => 'Burgáns, 91, 36633 Cambados, Pontevedra',
                'url_publica' => 'https://www.vinaatlantica.demo',
                'politica_cancelacion' => 'Cancelación gratuita hasta 48 horas antes de la experiencia. Cancelaciones con menos de 48 horas no tendrán reembolso. Grupos de más de 10 personas deben cancelar con 72 horas de antelación.',
                'horas_minimas_cancelacion' => 48,
                'permite_modificacion' => true,
                'max_recursos_combinables' => 1,
                'chat_personality' => 'Tono cálido y apasionado por el vino, pero accesible y sin esnobismo. Habla con orgullo de la tierra, del Albariño y de la Ría de Arousa. Sé cercano, breve y orientado a que el cliente viva una experiencia inolvidable. Usa vocabulario vinícola básico pero sin tecnicismos excesivos.',
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
                    'Las experiencias son solo para mayores de 18 años, excepto ORIXE que admite niños de 5-17 años a 8€ (máximo 2 niños por adulto, menores de 5 gratis avisando).',
                    'Los maridajes incluyen productos del mar (mariscos). Preguntar siempre por alergias alimentarias.',
                    'Idioma de las visitas: español/gallego. Inglés bajo consulta previa.',
                    'Capacidad máxima por experiencia: 24 personas. Para grupos mayores, indicar que se contacte directamente.',
                    'Accesibilidad: consultar previamente con la bodega las condiciones de acceso.',
                    'La experiencia Creaciones Singulares solo está disponible los viernes a las 12:00.',
                    'Si alguien pregunta por la travesía en barco por la ría, indicar que se gestiona aparte y deben contactar por teléfono.',
                ]),
            ]
        );

        User::query()->each(function (User $user) use ($negocio): void {
            $user->negocios()->syncWithoutDetaching([$negocio->id]);
        });

        // ─── Clientes demo ───

        $clientes = collect([
            ['nombre' => 'Patricia López', 'email' => 'patricia.lopez@example.com', 'telefono' => '600 200 001', 'notas' => 'Alergia a mariscos.'],
            ['nombre' => 'Miguel Fernández', 'email' => 'miguel.fernandez@example.com', 'telefono' => '600 200 002', 'notas' => null],
            ['nombre' => 'Ana Rodríguez', 'email' => 'ana.rodriguez@example.com', 'telefono' => '600 200 003', 'notas' => 'Viene con grupo de empresa habitualmente.'],
            ['nombre' => 'Roberto Iglesias', 'email' => null, 'telefono' => '600 200 004', 'notas' => null],
            ['nombre' => 'Carmen Piñeiro', 'email' => 'carmen.pineiro@example.com', 'telefono' => '600 200 005', 'notas' => 'Interesada en vendimia tardía.'],
        ])->mapWithKeys(function (array $data) {
            $cliente = Cliente::updateOrCreate(['nombre' => $data['nombre']], $data);

            return [$data['nombre'] => $cliente];
        });

        // ─── Servicios / Experiencias ───

        $servicios = collect([
            [
                'nombre' => 'Orixe',
                'descripcion' => 'Experiencia enoturística de 75 minutos donde descubrirás el origen y los secretos de la elaboración del Albariño D.O. Rías Baixas. Incluye paseo por viñedos, visita a la bodega y cata maridada de 2 vinos con 2 quesos artesanales gallegos con denominación de origen, todo con vistas a la Ría de Arousa.',
                'duracion_minutos' => 75,
                'precio_base' => 21.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Niños de 5 a 17 años: 8€ (máximo 2 niños por adulto). Menores de 5 años gratis avisando con antelación. Experiencia diseñada para despertar todos los sentidos.',
                'instrucciones_previas' => 'Llegar 10 minutos antes del inicio. Llevar calzado cómodo para el paseo por viñedos. En caso de lluvia, la visita al viñedo se adapta.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Ondas do Mar',
                'descripcion' => 'Experiencia premium de 90 minutos. Recorrido por la bodega descubriendo la elaboración de los vinos D.O. Rías Baixas 100% Albariño, culminando con una cata maridada de 3 vinos con elaboraciones a base de productos del mar gallego, todo con vistas privilegiadas a la Ría de Arousa.',
                'duracion_minutos' => 90,
                'precio_base' => 28.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Solo mayores de 18 años. Los maridajes se elaboran con productos del mar. Consultar alergias alimentarias.',
                'instrucciones_previas' => 'Llegar 10 minutos antes. Recomendamos no usar perfumes fuertes que interfieran con la cata.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 48,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Creaciones Singulares',
                'descripcion' => 'La experiencia más exclusiva de la bodega. 120 minutos explorando la diversidad del viñedo, los viñedos patrimoniales y las producciones de vendimia tardía. Maridaje gastronómico con vinos de edición limitada acompañados de creaciones gastro locales. Una inmersión total en el universo del Albariño más especial.',
                'duracion_minutos' => 120,
                'precio_base' => 50.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Solo mayores de 18 años. Disponible únicamente los viernes a las 12:00. Plazas muy limitadas.',
                'instrucciones_previas' => 'Llegar 15 minutos antes. Esta experiencia incluye paseo largo por viñedos patrimoniales — calzado cómodo imprescindible. Recomendamos no usar perfumes fuertes.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 72,
                'es_reembolsable' => false,
                'porcentaje_senal' => 50.00,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cata Cantiga de Amigo',
                'descripcion' => 'Cata comentada "La versatilidad del Albariño". Degustación guiada de 3 vinos: Albariño clásico, Sobre Lías y Organistrum, con vistas a la Ría de Arousa. Ideal para descubrir las distintas expresiones del Albariño.',
                'duracion_minutos' => 45,
                'precio_base' => 15.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Solo mayores de 18 años. No incluye visita a bodega. Solo cata comentada.',
                'instrucciones_previas' => 'Llegar 5 minutos antes. La cata se realiza en la sala con vistas a la Ría.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 24,
                'es_reembolsable' => true,
                'porcentaje_senal' => null,
                'precio_por_unidad_tiempo' => false,
            ],
            [
                'nombre' => 'Cata Cantiga de Amor',
                'descripcion' => 'Cata comentada "La influencia del terruño". Degustación guiada centrada en vinos de parcela: Arousa y Finca Xieles, dos expresiones únicas del terroir gallego.',
                'duracion_minutos' => 45,
                'precio_base' => 18.00,
                'tipo_precio_id' => $tipoPrecioPorPersona->id,
                'requiere_pago' => true,
                'activo' => true,
                'notas_publicas' => 'Solo mayores de 18 años. Cata orientada a conocedores que buscan profundizar en la influencia del terroir.',
                'instrucciones_previas' => 'Llegar 5 minutos antes.',
                'documentacion_requerida' => null,
                'horas_minimas_cancelacion' => 24,
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

        // ─── Recursos (espacios de la bodega) ───

        $recursos = collect([
            ['nombre' => 'Sala de catas principal', 'tipo_recurso_id' => $tipoRecursoSalaCatas->id, 'capacidad' => 24, 'activo' => true, 'notas_publicas' => 'Sala principal con vistas panorámicas a la Ría de Arousa.'],
            ['nombre' => 'Sala de catas privada', 'tipo_recurso_id' => $tipoRecursoSalaCatas->id, 'capacidad' => 12, 'activo' => true, 'notas_publicas' => 'Sala íntima para grupos reducidos o experiencias premium.'],
            ['nombre' => 'Sala de eventos', 'tipo_recurso_id' => $tipoRecursoSala->id, 'capacidad' => 40, 'activo' => true, 'notas_publicas' => 'Espacio polivalente para eventos corporativos y presentaciones.'],
        ])->mapWithKeys(function (array $data) use ($negocio) {
            $recurso = Recurso::updateOrCreate(
                ['negocio_id' => $negocio->id, 'nombre' => $data['nombre']],
                $data + ['negocio_id' => $negocio->id]
            );

            return [$data['nombre'] => $recurso];
        });

        // ─── Servicio ↔ Recurso ───

        $servicios['Orixe']->recursos()->sync([$recursos['Sala de catas principal']->id]);
        $servicios['Ondas do Mar']->recursos()->sync([$recursos['Sala de catas principal']->id]);
        $servicios['Creaciones Singulares']->recursos()->sync([$recursos['Sala de catas privada']->id]);
        $servicios['Cata Cantiga de Amigo']->recursos()->sync([$recursos['Sala de catas principal']->id, $recursos['Sala de catas privada']->id]);
        $servicios['Cata Cantiga de Amor']->recursos()->sync([$recursos['Sala de catas principal']->id, $recursos['Sala de catas privada']->id]);

        // ─── Disponibilidades ───

        $this->seedDisponibilidades($recursos, $servicios);

        // ─── Reservas demo ───

        $today = Carbon::today();

        $reservaItems = [
            ['cliente' => 'Patricia López', 'servicio' => 'Orixe', 'recurso' => 'Sala de catas principal', 'fecha' => $today->copy()->addDays(3), 'hora_inicio' => '11:00:00', 'hora_fin' => '12:15:00', 'numero_personas' => 4, 'precio_calculado' => 84.00, 'estado' => $estadoReservaConfirmada, 'notas' => 'Incluye 2 niños (8€ c/u).'],
            ['cliente' => 'Miguel Fernández', 'servicio' => 'Ondas do Mar', 'recurso' => 'Sala de catas principal', 'fecha' => $today->copy()->addDays(4), 'hora_inicio' => '12:00:00', 'hora_fin' => '13:30:00', 'numero_personas' => 2, 'precio_calculado' => 56.00, 'estado' => $estadoReservaConfirmada, 'notas' => null],
            ['cliente' => 'Ana Rodríguez', 'servicio' => 'Creaciones Singulares', 'recurso' => 'Sala de catas privada', 'fecha' => $today->copy()->addDays(5)->next(Carbon::FRIDAY), 'hora_inicio' => '12:00:00', 'hora_fin' => '14:00:00', 'numero_personas' => 8, 'precio_calculado' => 400.00, 'estado' => $estadoReservaPendiente, 'notas' => 'Grupo de empresa. Señal pagada.'],
            ['cliente' => 'Roberto Iglesias', 'servicio' => 'Cata Cantiga de Amigo', 'recurso' => 'Sala de catas principal', 'fecha' => $today->copy()->addDays(2), 'hora_inicio' => '17:00:00', 'hora_fin' => '17:45:00', 'numero_personas' => 6, 'precio_calculado' => 90.00, 'estado' => $estadoReservaCompletada, 'notas' => null],
            ['cliente' => 'Carmen Piñeiro', 'servicio' => 'Cata Cantiga de Amor', 'recurso' => 'Sala de catas privada', 'fecha' => $today->copy()->addDays(7), 'hora_inicio' => '12:00:00', 'hora_fin' => '12:45:00', 'numero_personas' => 2, 'precio_calculado' => 36.00, 'estado' => $estadoReservaConfirmada, 'notas' => 'Interesada en comprar vino tras la cata.'],
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
                    'precio_total' => null,
                    'estado_reserva_id' => $item['estado']->id,
                    'notas' => $item['notas'],
                    'localizador' => Reserva::generarLocalizador(),
                ]
            );

            return ['reserva_'.$reserva->id => $reserva];
        });

        // ─── Pagos demo ───

        $pagoItems = [
            ['reserva' => $reservas->values()[0], 'tipo_pago_id' => $tipoPagoTpvOnline->id, 'estado_pago_id' => $estadoPagoPagado->id, 'importe' => 84.00, 'referencia_externa' => 'TPV-BOD-001', 'fecha_pago' => now()->subDay()->format('Y-m-d H:i:s')],
            ['reserva' => $reservas->values()[1], 'tipo_pago_id' => $tipoPagoTarjeta->id, 'estado_pago_id' => $estadoPagoPagado->id, 'importe' => 56.00, 'referencia_externa' => 'TPV-BOD-002', 'fecha_pago' => now()->format('Y-m-d H:i:s')],
            ['reserva' => $reservas->values()[2], 'tipo_pago_id' => $tipoPagoTpvOnline->id, 'estado_pago_id' => $estadoPagoPagado->id, 'importe' => 200.00, 'referencia_externa' => 'TPV-BOD-003-SENAL', 'fecha_pago' => now()->format('Y-m-d H:i:s')],
        ];

        foreach ($pagoItems as $item) {
            Pago::updateOrCreate(
                ['reserva_id' => $item['reserva']->id, 'tipo_pago_id' => $item['tipo_pago_id'], 'importe' => number_format((float) $item['importe'], 2, '.', '')],
                ['estado_pago_id' => $item['estado_pago_id'], 'referencia_externa' => $item['referencia_externa'], 'fecha_pago' => $item['fecha_pago']]
            );
        }
    }

    private function seedDisponibilidades($recursos, $servicios): void
    {
        $salaPrincipal = $recursos['Sala de catas principal'];
        $salaPrivada = $recursos['Sala de catas privada'];

        // Sala principal: Martes a Sábado
        foreach (range(2, 6) as $dia) {
            // Turno mañana
            $this->upsertDisp($salaPrincipal->id, $dia, '11:00:00', '13:30:00', true, 'Turno de mañana', 15);

            // Turno tarde (excepto viernes que tiene Creaciones Singulares)
            if ($dia !== 5) {
                $this->upsertDisp($salaPrincipal->id, $dia, '17:00:00', '19:00:00', true, 'Turno de tarde', 15);
            }
        }

        // Sala privada: Martes a Sábado mañana
        foreach (range(2, 6) as $dia) {
            $this->upsertDisp($salaPrivada->id, $dia, '11:00:00', '14:00:00', true, 'Turno de mañana', 30);
        }

        // Sala privada: Viernes especial para Creaciones Singulares
        $this->upsertDisp($salaPrivada->id, 5, '12:00:00', '14:00:00', true, 'Creaciones Singulares', 0);
    }

    private function upsertDisp(int $recursoId, int $diaSemana, string $horaInicio, string $horaFin, bool $activo, ?string $nombreTurno = null, ?int $bufferMinutos = null): void
    {
        Disponibilidad::updateOrCreate(
            ['recurso_id' => $recursoId, 'dia_semana' => $diaSemana, 'hora_inicio' => $horaInicio, 'hora_fin' => $horaFin],
            ['activo' => $activo, 'nombre_turno' => $nombreTurno, 'buffer_minutos' => $bufferMinutos]
        );
    }
}
