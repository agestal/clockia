<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateReservaRequest;
use App\Http\Requests\Admin\StoreReservaRequest;
use App\Http\Requests\Admin\UpdateReservaRequest;
use App\Models\Cliente;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Recurso;
use App\Models\Servicio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'fecha')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['fecha', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'fecha';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $reservas = Reserva::query()
            ->with(['negocio', 'servicio', 'recurso', 'cliente', 'estadoReserva'])
            ->withCount('pagos')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('fecha', 'like', "%{$search}%")
                        ->orWhere('hora_inicio', 'like', "%{$search}%")
                        ->orWhere('hora_fin', 'like', "%{$search}%")
                        ->orWhere('localizador', 'like', "%{$search}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('negocio', function ($negocioQuery) use ($search) {
                            $negocioQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('servicio', function ($servicioQuery) use ($search) {
                            $servicioQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('recurso', function ($recursoQuery) use ($search) {
                            $recursoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('estadoReserva', function ($estadoQuery) use ($search) {
                            $estadoQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.reservas.index', [
            'reservas' => $reservas,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'estadoReservaOptions' => EstadoReserva::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create(): View
    {
        return view('admin.reservas.create', [
            'reserva' => new Reserva(),
            'selectedNegocio' => $this->resolveSelectedNegocio(),
            'selectedServicio' => $this->resolveSelectedServicio(),
            'selectedRecurso' => $this->resolveSelectedRecurso(),
            'selectedCliente' => $this->resolveSelectedCliente(),
            'selectedEstadoReserva' => $this->resolveSelectedEstadoReserva(),
        ]);
    }

    public function store(StoreReservaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['localizador'] = Reserva::generarLocalizador();

        $reserva = DB::transaction(function () use ($validated) {
            $reserva = Reserva::create($validated);
            $this->sincronizarRecursoPrincipal($reserva);

            return $reserva;
        });

        return redirect()
            ->route('admin.reservas.show', $reserva)
            ->with('success', 'La reserva se ha creado correctamente.');
    }

    public function show(Reserva $reserva): View
    {
        $reserva->load([
            'negocio',
            'servicio',
            'recurso',
            'cliente',
            'estadoReserva',
            'pagos.tipoPago',
            'pagos.estadoPago',
            'pagos.conceptoPago',
            'reservaRecursos.recurso',
            'reservaIntegraciones',
        ]);

        $policy = app(\App\Services\PolicyResolver::class)->resolverParaReserva($reserva);

        return view('admin.reservas.show', [
            'reserva' => $reserva,
            'policy' => $policy,
        ]);
    }

    public function edit(Reserva $reserva): View
    {
        $reserva->load(['negocio', 'servicio', 'recurso', 'cliente', 'estadoReserva']);

        return view('admin.reservas.edit', [
            'reserva' => $reserva,
            'selectedNegocio' => $this->resolveSelectedNegocio($reserva),
            'selectedServicio' => $this->resolveSelectedServicio($reserva),
            'selectedRecurso' => $this->resolveSelectedRecurso($reserva),
            'selectedCliente' => $this->resolveSelectedCliente($reserva),
            'selectedEstadoReserva' => $this->resolveSelectedEstadoReserva($reserva),
        ]);
    }

    public function update(UpdateReservaRequest $request, Reserva $reserva): RedirectResponse
    {
        DB::transaction(function () use ($request, $reserva) {
            $reserva->update($request->validated());
            $this->sincronizarRecursoPrincipal($reserva);
        });

        return redirect()
            ->route('admin.reservas.edit', $reserva)
            ->with('success', 'La reserva se ha actualizado correctamente.');
    }

    public function destroy(Reserva $reserva): RedirectResponse
    {
        $reserva->loadCount('pagos');

        if ($reserva->pagos_count > 0) {
            return redirect()
                ->route('admin.reservas.index')
                ->with('error', 'No puedes borrar esta reserva porque tiene pagos relacionados.');
        }

        $reserva->delete();

        return redirect()
            ->route('admin.reservas.index')
            ->with('success', 'La reserva se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdateReservaRequest $request, Reserva $reserva): JsonResponse
    {
        $reserva->update($request->validated());
        $reserva->load('estadoReserva');

        return response()->json([
            'message' => 'El estado de la reserva se ha actualizado correctamente.',
            'data' => [
                'id' => $reserva->id,
                'estado_reserva_id' => $reserva->estado_reserva_id,
                'estado_reserva_label' => $reserva->estadoReserva?->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Reserva::query()
            ->with(['cliente:id,nombre', 'servicio:id,nombre'])
            ->select(['id', 'cliente_id', 'servicio_id', 'fecha', 'hora_inicio', 'hora_fin'])
            ->latest('fecha')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where(function ($innerQuery) use ($term) {
                    if (ctype_digit($term)) {
                        $innerQuery->orWhere('id', (int) $term);
                    }

                    $innerQuery
                        ->orWhere('fecha', 'like', "%{$term}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($term) {
                            $clienteQuery->where('nombre', 'like', "%{$term}%");
                        })
                        ->orWhereHas('servicio', function ($servicioQuery) use ($term) {
                            $servicioQuery->where('nombre', 'like', "%{$term}%");
                        });
                });
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(function (Reserva $reserva) {
                $parts = array_filter([
                    'Reserva #'.$reserva->id,
                    optional($reserva->fecha)->format('d/m/Y'),
                    substr((string) $reserva->hora_inicio, 0, 5),
                    $reserva->cliente?->nombre,
                    $reserva->servicio?->nombre,
                ]);

                return [
                    'id' => $reserva->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function sincronizarRecursoPrincipal(Reserva $reserva): void
    {
        if ($reserva->recurso_id === null) {
            return;
        }

        $reserva->reservaRecursos()->updateOrCreate(
            ['recurso_id' => $reserva->recurso_id],
            [
                'fecha' => $reserva->fecha,
                'hora_inicio' => $reserva->hora_inicio,
                'hora_fin' => $reserva->hora_fin,
                'fecha_inicio_datetime' => $reserva->inicio_datetime,
                'fecha_fin_datetime' => $reserva->fin_datetime,
            ]
        );
    }

    private function resolveSelectedNegocio(?Reserva $reserva = null): ?Negocio
    {
        $selectedId = session()->getOldInput('negocio_id', $reserva?->negocio_id);

        if (! $selectedId) {
            return null;
        }

        return Negocio::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedServicio(?Reserva $reserva = null): ?Servicio
    {
        $selectedId = session()->getOldInput('servicio_id', $reserva?->servicio_id);

        if (! $selectedId) {
            return null;
        }

        return Servicio::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedRecurso(?Reserva $reserva = null): ?Recurso
    {
        $selectedId = session()->getOldInput('recurso_id', $reserva?->recurso_id);

        if (! $selectedId) {
            return null;
        }

        return Recurso::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedCliente(?Reserva $reserva = null): ?Cliente
    {
        $selectedId = session()->getOldInput('cliente_id', $reserva?->cliente_id);

        if (! $selectedId) {
            return null;
        }

        return Cliente::query()->select(['id', 'nombre', 'email', 'telefono'])->find($selectedId);
    }

    private function resolveSelectedEstadoReserva(?Reserva $reserva = null): ?EstadoReserva
    {
        $selectedId = session()->getOldInput('estado_reserva_id', $reserva?->estado_reserva_id);

        if (! $selectedId) {
            return null;
        }

        return EstadoReserva::query()->select(['id', 'nombre'])->find($selectedId);
    }
}
