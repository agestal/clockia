<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\Reserva;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index(): View
    {
        $negocios = Negocio::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.calendario.index', compact('negocios'));
    }

    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $negocioId = (int) $validated['negocio_id'];
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $negocio = Negocio::with('tipoNegocio:id,nombre')->findOrFail($negocioId);
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        // Get all recursos and their tipo
        $recursos = Recurso::where('negocio_id', $negocioId)->activos()->with('tipoRecurso:id,nombre')->get();
        $recursoIds = $recursos->pluck('id');

        // Resource label for this business
        $tipoRecursoNombre = $recursos->first()?->tipoRecurso?->nombre ?? 'Recursos';
        $resourceLabel = $this->pluralize($tipoRecursoNombre);
        $totalRecursos = $recursos->count();

        // Get availabilities to know which days are operational
        $disponibilidades = Disponibilidad::whereIn('recurso_id', $recursoIds)->activos()->get();
        $diasOperativos = $disponibilidades->pluck('dia_semana')->unique()->sort()->values()->all();

        // Get reservations for the month
        $reservas = Reserva::where('negocio_id', $negocioId)
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->whereHas('estadoReserva', fn ($q) => $q->whereNotIn('nombre', ['Cancelada']))
            ->with(['servicio:id,nombre,duracion_minutos', 'recurso:id,nombre', 'cliente:id,nombre', 'estadoReserva:id,nombre'])
            ->orderBy('hora_inicio')
            ->get();

        // Build day-by-day data
        $days = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $dow = $date->dayOfWeek;
            $isOperational = in_array($dow, $diasOperativos);

            $dayReservas = $reservas->filter(fn ($r) => $r->fecha->toDateString() === $dateStr);
            $ocupados = $dayReservas->pluck('recurso_id')->unique()->count();

            // Count unique slots occupied (a resource can have multiple reservations in different turnos)
            $totalSlotsDia = 0;
            $ocupadosSlotsDia = 0;

            if ($isOperational) {
                $turnosPorRecurso = $disponibilidades->where('dia_semana', $dow)->groupBy('recurso_id');

                foreach ($turnosPorRecurso as $recursoId => $turnos) {
                    $totalSlotsDia += $turnos->count();
                    foreach ($turnos as $turno) {
                        $tieneReserva = $dayReservas
                            ->where('recurso_id', $recursoId)
                            ->filter(function ($r) use ($turno) {
                                $ri = substr((string) $r->hora_inicio, 0, 5);
                                $ti = substr((string) $turno->hora_inicio, 0, 5);
                                $tf = substr((string) $turno->hora_fin, 0, 5);

                                return $ri >= $ti && $ri < $tf;
                            })
                            ->isNotEmpty();

                        if ($tieneReserva) {
                            $ocupadosSlotsDia++;
                        }
                    }
                }
            }

            $libresDia = max(0, $totalSlotsDia - $ocupadosSlotsDia);

            $status = 'closed';
            if ($isOperational) {
                if ($dayReservas->isEmpty()) {
                    $status = 'free';
                } elseif ($libresDia > 0) {
                    $status = 'partial';
                } else {
                    $status = 'full';
                }
            }

            $days[$dateStr] = [
                'date' => $dateStr,
                'dow' => $dow,
                'is_operational' => $isOperational,
                'status' => $status,
                'reservas_count' => $dayReservas->count(),
                'total_slots' => $totalSlotsDia,
                'occupied_slots' => $ocupadosSlotsDia,
                'free_slots' => $libresDia,
            ];
        }

        return response()->json([
            'negocio' => [
                'id' => $negocio->id,
                'nombre' => $negocio->nombre,
                'tipo' => $negocio->tipoNegocio?->nombre,
            ],
            'resource_label' => $resourceLabel,
            'total_resources' => $totalRecursos,
            'year' => $year,
            'month' => $month,
            'month_name' => $start->locale('es')->translatedFormat('F Y'),
            'days' => $days,
        ]);
    }

    public function dayDetail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'date' => ['required', 'date'],
        ]);

        $reservas = Reserva::where('negocio_id', $validated['negocio_id'])
            ->where('fecha', $validated['date'])
            ->whereHas('estadoReserva', fn ($q) => $q->whereNotIn('nombre', ['Cancelada']))
            ->with(['servicio:id,nombre', 'recurso:id,nombre', 'cliente:id,nombre,telefono', 'estadoReserva:id,nombre'])
            ->orderBy('hora_inicio')
            ->get();

        $items = $reservas->map(fn (Reserva $r) => [
            'id' => $r->id,
            'localizador' => $r->localizador,
            'hora_inicio' => substr((string) $r->hora_inicio, 0, 5),
            'hora_fin' => substr((string) $r->hora_fin, 0, 5),
            'servicio' => $r->servicio?->nombre,
            'recurso' => $r->recurso?->nombre,
            'cliente' => $r->cliente?->nombre,
            'telefono' => $r->cliente?->telefono,
            'personas' => $r->numero_personas,
            'estado' => $r->estadoReserva?->nombre,
            'notas' => $r->notas,
        ])->values();

        return response()->json([
            'date' => $validated['date'],
            'date_human' => Carbon::parse($validated['date'])->locale('es')->translatedFormat('l j \d\e F \d\e Y'),
            'total' => $items->count(),
            'reservas' => $items,
        ]);
    }

    private function pluralize(string $tipo): string
    {
        return match (mb_strtolower($tipo)) {
            'mesa' => 'Mesas',
            'sala' => 'Salas',
            'sala de catas' => 'Salas de catas',
            'cabina' => 'Cabinas',
            'profesional' => 'Profesionales',
            'puesto' => 'Puestos',
            'box' => 'Boxes',
            'vehículo' => 'Vehículos',
            'habitación' => 'Habitaciones',
            default => $tipo,
        };
    }
}
