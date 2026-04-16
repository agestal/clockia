<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateServicioRequest;
use App\Http\Requests\Admin\StoreServicioRequest;
use App\Http\Requests\Admin\UpdateServicioRequest;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\Servicio;
use App\Models\TipoPrecio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    use InteractsWithAdminAccess;

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $servicios = Servicio::query()
            ->with(['negocio', 'tipoPrecio'])
            ->withCount(['recursos', 'reservas'])
            ->tap(fn ($query) => $this->scopeAccessibleBusinesses($query, $request, 'negocio_id'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhereHas('negocio', function ($negocioQuery) use ($search) {
                            $negocioQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tipoPrecio', function ($tipoPrecioQuery) use ($search) {
                            $tipoPrecioQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.servicios.index', [
            'servicios' => $servicios,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.servicios.create', $this->formViewData(new Servicio([
            'requiere_pago' => false,
            'activo' => true,
        ]), collect()));
    }

    public function store(StoreServicioRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->abortUnlessBusinessAccessible($request, $validated['negocio_id'] ?? null);

        $recursos = $validated['recursos'] ?? [];
        unset($validated['recursos']);
        foreach ($recursos as $recursoId) {
            $this->abortUnlessModelAccessible($request, Recurso::class, (int) $recursoId);
        }

        $servicio = DB::transaction(function () use ($validated, $recursos) {
            $servicio = Servicio::create($validated);
            $servicio->recursos()->sync($recursos);

            return $servicio;
        });

        return redirect()
            ->route('admin.servicios.show', $servicio)
            ->with('success', 'El servicio se ha creado correctamente.');
    }

    public function show(Servicio $servicio): View
    {
        $servicio->load([
            'negocio',
            'tipoPrecio',
            'recursos' => fn ($query) => $query->orderBy('nombre'),
            'reservas' => fn ($query) => $query->with(['cliente', 'estadoReserva'])->latest('fecha'),
        ]);

        return view('admin.servicios.show', [
            'servicio' => $servicio,
            'reservas' => $servicio->reservas->take(5),
        ]);
    }

    public function edit(Servicio $servicio): View
    {
        $servicio->load([
            'negocio',
            'tipoPrecio',
            'recursos' => fn ($query) => $query->orderBy('nombre'),
        ]);

        return view('admin.servicios.edit', $this->formViewData($servicio, $servicio->recursos));
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $validated = $request->validated();
        $this->abortUnlessBusinessAccessible($request, $validated['negocio_id'] ?? null);

        $recursos = $validated['recursos'] ?? [];
        unset($validated['recursos']);
        foreach ($recursos as $recursoId) {
            $this->abortUnlessModelAccessible($request, Recurso::class, (int) $recursoId);
        }

        DB::transaction(function () use ($servicio, $validated, $recursos) {
            $servicio->update($validated);
            $servicio->recursos()->sync($recursos);
        });

        return redirect()
            ->route('admin.servicios.edit', $servicio)
            ->with('success', 'El servicio se ha actualizado correctamente.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $servicio->loadCount(['reservas', 'servicioRecursos']);

        if ($servicio->reservas_count > 0) {
            return redirect()
                ->route('admin.servicios.index')
                ->with('error', 'No puedes borrar este servicio porque tiene reservas relacionadas.');
        }

        DB::transaction(function () use ($servicio) {
            if ($servicio->servicioRecursos_count > 0) {
                $servicio->recursos()->detach();
            }

            $servicio->delete();
        });

        return redirect()
            ->route('admin.servicios.index')
            ->with('success', 'El servicio se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdateServicioRequest $request, Servicio $servicio): JsonResponse
    {
        $servicio->update($request->validated());
        $servicio->refresh();

        return response()->json([
            'message' => 'El servicio se ha actualizado correctamente.',
            'data' => [
                'id' => $servicio->id,
                'precio_base' => $servicio->precio_base,
                'precio_base_label' => number_format((float) $servicio->precio_base, 2, ',', '.'),
                'requiere_pago' => $servicio->requiere_pago,
                'requiere_pago_label' => $servicio->requiere_pago ? 'Sí' : 'No',
                'activo' => $servicio->activo,
                'activo_label' => $servicio->activo ? 'Activo' : 'Inactivo',
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $negocioId = $request->integer('negocio_id');
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Servicio::query()
            ->with(['negocio:id,nombre', 'tipoPrecio:id,nombre'])
            ->select(['id', 'nombre', 'negocio_id', 'tipo_precio_id', 'activo'])
            ->tap(fn ($builder) => $this->scopeAccessibleBusinesses($builder, $request, 'negocio_id'))
            ->when($negocioId > 0, function ($builder) use ($negocioId) {
                $builder->where('negocio_id', $negocioId);
            })
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(function (Servicio $servicio) {
                $parts = array_filter([
                    $servicio->nombre,
                    $servicio->negocio?->nombre,
                    $servicio->tipoPrecio?->nombre,
                ]);

                return [
                    'id' => $servicio->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function formViewData(Servicio $servicio, Collection $selectedRecursos): array
    {
        return [
            'servicio' => $servicio,
            'selectedNegocio' => $this->resolveSelectedNegocio($servicio),
            'selectedTipoPrecio' => $this->resolveSelectedTipoPrecio($servicio),
            'selectedRecursos' => $this->resolveSelectedRecursos($servicio, $selectedRecursos),
        ];
    }

    private function resolveSelectedNegocio(?Servicio $servicio = null): ?Negocio
    {
        $selectedId = session()->getOldInput('negocio_id', $servicio?->negocio_id);

        $query = $this->adminAccess()
            ->scopeBusinesses(Negocio::query(), auth()->user(), 'id')
            ->select(['id', 'nombre'])
            ->orderBy('nombre');

        if ($selectedId) {
            return $query->find($selectedId);
        }

        if (! auth()->user()?->hasFullAdminAccess()) {
            return $query->first();
        }

        return null;
    }

    private function resolveSelectedTipoPrecio(?Servicio $servicio = null): ?TipoPrecio
    {
        $selectedId = session()->getOldInput('tipo_precio_id', $servicio?->tipo_precio_id);

        if (! $selectedId) {
            return null;
        }

        return TipoPrecio::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedRecursos(?Servicio $servicio = null, ?Collection $loadedRecursos = null): Collection
    {
        $selectedIds = session()->getOldInput('recursos');

        if (is_array($selectedIds)) {
            return $this->adminAccess()
                ->scopeBusinesses(Recurso::query(), auth()->user(), 'negocio_id')
                ->with(['negocio:id,nombre', 'tipoRecurso:id,nombre'])
                ->whereIn('id', $selectedIds)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'negocio_id', 'tipo_recurso_id']);
        }

        return $loadedRecursos ?? collect();
    }
}
