<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateNegocioRequest;
use App\Http\Requests\Admin\StoreNegocioRequest;
use App\Http\Requests\Admin\UpdateNegocioRequest;
use App\Models\Negocio;
use App\Models\TipoNegocio;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NegocioController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $negocios = Negocio::query()
            ->with('tipoNegocio')
            ->withCount(['servicios', 'recursos', 'reservas'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('zona_horaria', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.negocios.index', [
            'negocios' => $negocios,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.negocios.create', [
            'negocio' => new Negocio([
                'zona_horaria' => 'Europe/Madrid',
                'activo' => true,
            ]),
            'selectedTipoNegocio' => $this->resolveSelectedTipoNegocio(),
            'timezones' => $this->timezoneOptions(),
        ]);
    }

    public function store(StoreNegocioRequest $request): RedirectResponse
    {
        $negocio = Negocio::create($request->validated());

        return redirect()
            ->route('admin.negocios.show', $negocio)
            ->with('success', 'El negocio se ha creado correctamente.');
    }

    public function show(Negocio $negocio): View
    {
        $negocio->load('tipoNegocio')
            ->loadCount(['servicios', 'recursos', 'reservas']);

        $servicios = $negocio->servicios()
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre', 'activo']);

        $recursos = $negocio->recursos()
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre', 'activo']);

        $reservas = $negocio->reservas()
            ->with(['cliente', 'estadoReserva'])
            ->latest('fecha')
            ->limit(5)
            ->get(['id', 'cliente_id', 'fecha', 'hora_inicio', 'hora_fin', 'estado_reserva_id']);

        return view('admin.negocios.show', [
            'negocio' => $negocio,
            'servicios' => $servicios,
            'recursos' => $recursos,
            'reservas' => $reservas,
        ]);
    }

    public function edit(Negocio $negocio): View
    {
        $negocio->load('tipoNegocio');

        return view('admin.negocios.edit', [
            'negocio' => $negocio,
            'selectedTipoNegocio' => $this->resolveSelectedTipoNegocio($negocio),
            'timezones' => $this->timezoneOptions(),
        ]);
    }

    public function update(UpdateNegocioRequest $request, Negocio $negocio): RedirectResponse
    {
        $negocio->update($request->validated());

        return redirect()
            ->route('admin.negocios.edit', $negocio)
            ->with('success', 'El negocio se ha actualizado correctamente.');
    }

    public function destroy(Negocio $negocio): RedirectResponse
    {
        $negocio->loadCount(['servicios', 'recursos', 'reservas']);

        if ($negocio->servicios_count > 0 || $negocio->recursos_count > 0 || $negocio->reservas_count > 0) {
            return redirect()
                ->route('admin.negocios.index')
                ->with('error', 'No puedes borrar este negocio porque tiene servicios, recursos o reservas relacionadas.');
        }

        $negocio->delete();

        return redirect()
            ->route('admin.negocios.index')
            ->with('success', 'El negocio se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdateNegocioRequest $request, Negocio $negocio): JsonResponse
    {
        $negocio->update($request->validated());

        return response()->json([
            'message' => 'El estado del negocio se ha actualizado correctamente.',
            'data' => [
                'id' => $negocio->id,
                'activo' => $negocio->activo,
                'activo_label' => $negocio->activo ? 'Activo' : 'Inactivo',
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Negocio::query()
            ->with('tipoNegocio:id,nombre')
            ->select(['id', 'nombre', 'tipo_negocio_id', 'activo'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(function (Negocio $negocio) {
                $parts = array_filter([
                    $negocio->nombre,
                    $negocio->tipoNegocio?->nombre,
                    $negocio->activo ? 'Activo' : 'Inactivo',
                ]);

                return [
                    'id' => $negocio->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function timezoneOptions(): array
    {
        $timezones = DateTimeZone::listIdentifiers();
        $timezones = array_values(array_unique(array_merge(['Europe/Madrid'], array_diff($timezones, ['Europe/Madrid']))));

        return $timezones;
    }

    private function resolveSelectedTipoNegocio(?Negocio $negocio = null): ?TipoNegocio
    {
        $selectedId = session()->getOldInput('tipo_negocio_id', $negocio?->tipo_negocio_id);

        if (! $selectedId) {
            return null;
        }

        return TipoNegocio::query()->select(['id', 'nombre'])->find($selectedId);
    }
}
