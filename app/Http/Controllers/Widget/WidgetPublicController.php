<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\Servicio;
use App\Models\Sesion;
use App\Services\Reservations\DynamicExperienceAvailabilityService;
use App\Services\Reservations\ReservationFinalizationService;
use App\Tools\Data\CreateBookingInput;
use App\Tools\Reservations\CreateQuoteTool;
use App\Tools\Reservations\ListBookableServicesTool;
use App\Tools\Reservations\SearchAvailabilityTool;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WidgetPublicController extends Controller
{
    public function __construct(
        private readonly ListBookableServicesTool $listBookableServicesTool,
        private readonly SearchAvailabilityTool $searchAvailabilityTool,
        private readonly CreateQuoteTool $createQuoteTool,
        private readonly ReservationFinalizationService $reservationFinalizationService,
        private readonly DynamicExperienceAvailabilityService $dynamicExperienceAvailability,
    ) {}

    public function config(Negocio $business): JsonResponse
    {
        $settings = $business->widgetSettingsResolved();

        return response()->json([
            'business' => [
                'id' => $business->id,
                'name' => $business->nombre,
                'timezone' => $business->zona_horaria,
                'description' => $business->descripcion_publica,
            ],
            'widget' => [
                'locale' => $settings['locale'],
                'timezone' => $business->zona_horaria,
                'currency' => 'EUR',
                'primary_color' => $settings['primary_color'],
                'secondary_color' => $settings['secondary_color'],
                'text_color' => $settings['text_color'],
                'background_color' => $settings['background_color'],
                'font_family' => $settings['font_family'],
                'font_size_base' => $settings['font_size_base'],
                'border_radius' => $settings['border_radius'],
            ],
        ]);
    }

    public function calendar(Request $request, Negocio $business): JsonResponse
    {
        $validated = Validator::make($request->query(), [
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'service_id' => ['nullable', 'integer'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $participants = isset($validated['participants']) ? (int) $validated['participants'] : null;
        $firstDay = Carbon::create($year, $month, 1, 0, 0, 0, $business->zona_horaria);
        $lastDay = $firstDay->copy()->endOfMonth();
        $today = Carbon::today($business->zona_horaria);

        $servicios = Servicio::query()
            ->where('negocio_id', $business->id)
            ->activos()
            ->when(isset($validated['service_id']), function ($query) use ($validated) {
                $query->where('id', (int) $validated['service_id']);
            })
            ->get();

        if ($servicios->isEmpty()) {
            return response()->json([
                'year' => $year,
                'month' => $month,
                'days' => [],
            ]);
        }

        $dynamicServices = $servicios
            ->filter(fn (Servicio $servicio) => $this->dynamicExperienceAvailability->supports($servicio))
            ->values();
        $legacyServices = $servicios
            ->reject(fn (Servicio $servicio) => $this->dynamicExperienceAvailability->supports($servicio))
            ->values();

        // Precompute which (dayOfWeek, servicio) pairs have any active schedule
        $serviceHasScheduleByDow = $this->buildServiceScheduleIndex($legacyServices);

        // Precompute which dates have active sessions for any of these services
        $sessionDates = $legacyServices->isEmpty()
            ? collect()
            : Sesion::query()
                ->where('negocio_id', $business->id)
                ->whereIn('servicio_id', $legacyServices->pluck('id'))
                ->where('activo', true)
                ->whereBetween('fecha', [$firstDay->toDateString(), $lastDay->toDateString()])
                ->pluck('fecha')
                ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : (string) $d)
                ->unique()
                ->flip();

        $days = [];
        $cursor = $firstDay->copy();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            $dateString = $cursor->toDateString();
            $isPast = $cursor->lessThan($today);
            $available = false;
            $dynamicSummary = null;

            if (! $isPast) {
                if ($dynamicServices->isNotEmpty()) {
                    $dynamicSummary = $this->dynamicExperienceAvailability->daySummaryForServices(
                        $business,
                        $dynamicServices,
                        $cursor,
                        $participants
                    );

                    $available = (bool) ($dynamicSummary['available'] ?? false);
                }

                if (! $available && isset($sessionDates[$dateString])) {
                    $available = true;
                } elseif (! $available) {
                    $dow = (int) $cursor->dayOfWeek;
                    foreach ($legacyServices as $servicio) {
                        if (! empty($serviceHasScheduleByDow[$servicio->id][$dow])) {
                            $available = true;
                            break;
                        }
                    }
                }
            }

            $days[] = [
                'date' => $dateString,
                'available' => $available,
                'is_past' => $isPast,
                'occupancy_percent' => $dynamicSummary['occupancy_percent'] ?? null,
                'available_slots' => $dynamicSummary['available_slots'] ?? null,
                'total_slots' => $dynamicSummary['total_slots'] ?? null,
                'service_occupancy' => $dynamicSummary['service_occupancy'] ?? [],
            ];

            $cursor->addDay();
        }

        return response()->json([
            'year' => $year,
            'month' => $month,
            'days' => $days,
        ]);
    }

    public function date(Request $request, Negocio $business): JsonResponse
    {
        $validated = Validator::make($request->query(), [
            'date' => ['required', 'date_format:Y-m-d'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        $date = $validated['date'];
        $participants = isset($validated['participants']) ? (int) $validated['participants'] : null;

        $servicesResult = $this->listBookableServicesTool->execute([
            'negocio_id' => $business->id,
        ]);

        if (! $servicesResult->success) {
            return response()->json(['error' => $servicesResult->error ?? 'Error listando servicios.'], 500);
        }

        $services = collect($servicesResult->data['servicios'] ?? [])->map(function (array $servicio) use ($business, $date, $participants) {
            $availability = $this->searchAvailabilityTool->execute([
                'negocio_id' => $business->id,
                'servicio_id' => (int) $servicio['id'],
                'fecha' => $date,
                'numero_personas' => $participants,
            ]);

            $rawSlots = $availability->success ? (array) ($availability->data['slots'] ?? []) : [];
            $mode = $availability->success ? (string) ($availability->data['availability_mode'] ?? 'precise') : 'error';
            $slotsSummary = $this->summarizeSlots(
                $rawSlots,
                $availability->success ? (array) ($availability->data ?? []) : []
            );
            $requiresTimeslot = $mode !== 'simple' && ($slotsSummary['total_slots'] > 0 || count($rawSlots) > 0);

            $timeslots = collect($rawSlots)
                ->unique(fn (array $slot) => ($slot['hora_inicio'] ?? '').'|'.($slot['hora_fin'] ?? ''))
                ->map(fn (array $slot) => [
                    'time' => $slot['hora_inicio'] ?? null,
                    'end_time' => $slot['hora_fin'] ?? null,
                    'slot_key' => $slot['slot_key'] ?? null,
                    'available' => true,
                    'session_id' => $slot['sesion_id'] ?? null,
                    'seats_remaining' => $slot['aforo_restante'] ?? null,
                    'capacity' => $slot['aforo_total'] ?? $slot['capacidad_total'] ?? null,
                    'occupancy_percent' => $slot['ocupacion_porcentaje'] ?? null,
                ])
                ->values()
                ->all();

            return [
                'id' => (int) $servicio['id'],
                'name' => $servicio['nombre'] ?? null,
                'description' => $servicio['descripcion'] ?? null,
                'duration_minutes' => $servicio['duracion_minutos'] ?? null,
                'price' => $servicio['precio_base'] ?? null,
                'currency' => 'EUR',
                'min_participants' => $servicio['numero_personas_minimo'] ?? null,
                'max_participants' => $servicio['numero_personas_maximo'] ?? null,
                'capacity' => $servicio['aforo'] ?? null,
                'start_time' => $servicio['hora_inicio'] ?? null,
                'end_time' => $servicio['hora_fin'] ?? null,
                'is_dynamic_experience' => (bool) ($servicio['usa_programacion_dinamica'] ?? false),
                'requires_timeslot' => $requiresTimeslot,
                'requires_manual_approval' => (bool) ($servicio['requiere_aprobacion_manual'] ?? false),
                'requires_documentation' => filled($servicio['documentacion_requerida'] ?? null),
                'public_notes' => $servicio['notas_publicas'] ?? null,
                'meeting_point' => $servicio['punto_encuentro'] ?? null,
                'includes' => $servicio['incluye'] ?? null,
                'languages' => $servicio['idiomas'] ?? null,
                'timeslots' => $timeslots,
                'availability_mode' => $mode,
                'occupancy_percent' => $slotsSummary['occupancy_percent'],
                'available_slots' => $slotsSummary['available_slots'],
                'total_slots' => $slotsSummary['total_slots'],
                'capacity_total' => $slotsSummary['capacity_total'],
                'seats_available_total' => $slotsSummary['seats_available_total'],
                'seats_reserved_total' => $slotsSummary['seats_reserved_total'],
            ];
        })
            ->filter(fn (array $service) => $service['availability_mode'] === 'simple' || ! empty($service['timeslots']) || ($service['total_slots'] ?? 0) > 0)
            ->values()
            ->all();

        return response()->json([
            'date' => $date,
            'services' => $services,
        ]);
    }

    public function check(Request $request, Negocio $business): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'participants' => ['required', 'integer', 'min:1'],
        ])->validate();

        $serviceId = (int) $validated['service_id'];
        $participants = (int) $validated['participants'];

        $servicio = Servicio::query()
            ->where('negocio_id', $business->id)
            ->where('id', $serviceId)
            ->activos()
            ->first();

        if (! $servicio) {
            return response()->json(['error' => 'Servicio no encontrado.'], 404);
        }

        if ($servicio->numero_personas_minimo !== null && $participants < $servicio->numero_personas_minimo) {
            return response()->json([
                'available' => false,
                'error' => 'Este servicio requiere al menos '.$servicio->numero_personas_minimo.' participantes.',
            ], 422);
        }

        if ($servicio->numero_personas_maximo !== null && $participants > $servicio->numero_personas_maximo) {
            return response()->json([
                'available' => false,
                'error' => 'Este servicio admite como máximo '.$servicio->numero_personas_maximo.' participantes.',
            ], 422);
        }

        $availability = $this->searchAvailabilityTool->execute([
            'negocio_id' => $business->id,
            'servicio_id' => $serviceId,
            'fecha' => $validated['date'],
            'numero_personas' => $participants,
        ]);

        if (! $availability->success) {
            return response()->json(['available' => false, 'error' => $availability->error], 422);
        }

        $slots = (array) ($availability->data['slots'] ?? []);

        if (isset($validated['time'])) {
            $slots = array_values(array_filter($slots, fn (array $slot) => ($slot['hora_inicio'] ?? null) === $validated['time']));
        }

        if (empty($slots)) {
            return response()->json([
                'available' => false,
                'error' => 'No hay huecos disponibles con los datos indicados.',
            ], 200);
        }

        $firstSlot = $slots[0];

        $quote = $this->createQuoteTool->execute([
            'negocio_id' => $business->id,
            'servicio_id' => $serviceId,
            'numero_personas' => $participants,
            'inicio_datetime' => $firstSlot['inicio_datetime'] ?? null,
            'fin_datetime' => $firstSlot['fin_datetime'] ?? null,
        ]);

        $total = $quote->success ? (float) ($quote->data['precio_calculado'] ?? 0) : ((float) $servicio->precio_base * $participants);
        $unitPrice = (float) $servicio->precio_base;

        return response()->json([
            'available' => true,
            'currency' => 'EUR',
            'summary' => [
                'unit_price' => $unitPrice,
                'participants' => $participants,
                'total_price' => round($total, 2),
            ],
            'slot' => [
                'slot_key' => $firstSlot['slot_key'] ?? null,
                'start_time' => $firstSlot['hora_inicio'] ?? null,
                'end_time' => $firstSlot['hora_fin'] ?? null,
                'seats_remaining' => $firstSlot['aforo_restante'] ?? null,
                'occupancy_percent' => $firstSlot['ocupacion_porcentaje'] ?? null,
            ],
        ]);
    }

    public function book(Request $request, Negocio $business): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'slot_key' => ['nullable', 'string'],
            'participants' => ['required', 'integer', 'min:1'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.last_name' => ['nullable', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:40'],
            'customer.document_type' => ['nullable', 'string', 'max:40'],
            'customer.document_value' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $customer = $validated['customer'];
        $fullName = trim(($customer['name'] ?? '').' '.($customer['last_name'] ?? ''));

        $dto = CreateBookingInput::fromArray([
            'negocio_id' => $business->id,
            'servicio_id' => (int) $validated['service_id'],
            'fecha' => $validated['date'],
            'hora_inicio' => $validated['time'] ?? null,
            'numero_personas' => (int) $validated['participants'],
            'slot_key' => $validated['slot_key'] ?? null,
            'contact_name' => $fullName !== '' ? $fullName : ($customer['name'] ?? null),
            'contact_phone' => $customer['phone'] ?? null,
            'contact_email' => $customer['email'] ?? null,
            'document_type' => $customer['document_type'] ?? null,
            'document_value' => $customer['document_value'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        try {
            $reserva = $this->reservationFinalizationService->finalize($dto);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $reserva->id,
                'reference' => $reserva->localizador,
                'status' => $reserva->estadoReserva?->nombre,
                'service_name' => $reserva->servicio?->nombre,
                'date' => $reserva->fecha?->toDateString(),
                'time' => substr((string) $reserva->hora_inicio, 0, 5),
                'end_time' => substr((string) $reserva->hora_fin, 0, 5),
                'participants' => $reserva->numero_personas,
                'total_price' => (string) $reserva->precio_calculado,
                'currency' => 'EUR',
            ],
            'messages' => [
                'Reserva creada correctamente.',
            ],
        ], 201);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Servicio>  $servicios
     * @return array<int, array<int, bool>>
     */
    private function buildServiceScheduleIndex($servicios): array
    {
        $index = [];

        foreach ($servicios as $servicio) {
            $index[$servicio->id] = [];

            $recursoIds = $servicio->recursos()->activos()->pluck('recursos.id');

            if ($recursoIds->isEmpty()) {
                continue;
            }

            $dows = Disponibilidad::query()
                ->whereIn('recurso_id', $recursoIds)
                ->activos()
                ->pluck('dia_semana')
                ->unique()
                ->values();

            foreach ($dows as $dow) {
                $index[$servicio->id][(int) $dow] = true;
            }
        }

        return $index;
    }

    private function summarizeSlots(array $slots, array $toolData = []): array
    {
        if (array_key_exists('occupancy_percent', $toolData)) {
            return [
                'occupancy_percent' => $toolData['occupancy_percent'],
                'available_slots' => $toolData['available_slots'] ?? count($slots),
                'total_slots' => $toolData['total_slots'] ?? count($slots),
                'capacity_total' => $toolData['capacity_total'] ?? null,
                'seats_available_total' => $toolData['seats_available_total'] ?? null,
                'seats_reserved_total' => $toolData['seats_reserved_total'] ?? null,
            ];
        }

        $capacityTotal = collect($slots)
            ->map(fn (array $slot) => $slot['aforo_total'] ?? $slot['capacidad_total'] ?? null)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->sum();

        $availableTotal = collect($slots)
            ->map(fn (array $slot) => $slot['aforo_restante'] ?? null)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->sum();

        $reservedTotal = $capacityTotal > 0 ? max(0, $capacityTotal - $availableTotal) : null;

        return [
            'occupancy_percent' => $capacityTotal > 0 && $reservedTotal !== null
                ? (int) round(($reservedTotal / $capacityTotal) * 100)
                : null,
            'available_slots' => count($slots),
            'total_slots' => count($slots),
            'capacity_total' => $capacityTotal > 0 ? $capacityTotal : null,
            'seats_available_total' => $capacityTotal > 0 ? $availableTotal : null,
            'seats_reserved_total' => $reservedTotal,
        ];
    }
}
