<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateRecursoRequest;
use App\Http\Requests\Admin\StoreRecursoRequest;
use App\Http\Requests\Admin\UpdateRecursoRequest;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\TipoRecurso;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecursoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $recursos = Recurso::query()
            ->with(['negocio', 'tipoRecurso'])
            ->withCount(['disponibilidades', 'bloqueos', 'reservas'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhereHas('negocio', function ($negocioQuery) use ($search) {
                            $negocioQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tipoRecurso', function ($tipoQuery) use ($search) {
                            $tipoQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.recursos.index', [
            'recursos' => $recursos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.recursos.create', [
            'recurso' => new Recurso([
                'activo' => true,
            ]),
            'selectedNegocio' => $this->resolveSelectedNegocio(),
            'selectedTipoRecurso' => $this->resolveSelectedTipoRecurso(),
            'servicios' => collect(),
        ]);
    }

    public function store(StoreRecursoRequest $request): RedirectResponse
    {
        $recurso = Recurso::create($request->validated());

        return redirect()
            ->route('admin.recursos.show', $recurso)
            ->with('success', 'El recurso se ha creado correctamente.');
    }

    public function show(Recurso $recurso): View
    {
        $recurso->load([
            'negocio',
            'tipoRecurso',
            'servicios' => fn ($query) => $query->orderBy('nombre'),
            'disponibilidades',
            'bloqueos',
            'recursosCombinables',
            'reservas',
        ]);

        return view('admin.recursos.show', [
            'recurso' => $recurso,
        ]);
    }

    public function edit(Recurso $recurso): View
    {
        $recurso->load([
            'negocio',
            'tipoRecurso',
            'servicios' => fn ($query) => $query->orderBy('nombre'),
        ]);

        return view('admin.recursos.edit', [
            'recurso' => $recurso,
            'selectedNegocio' => $this->resolveSelectedNegocio($recurso),
            'selectedTipoRecurso' => $this->resolveSelectedTipoRecurso($recurso),
            'servicios' => $recurso->servicios,
        ]);
    }

    public function update(UpdateRecursoRequest $request, Recurso $recurso): RedirectResponse
    {
        $recurso->update($request->validated());

        return redirect()
            ->route('admin.recursos.edit', $recurso)
            ->with('success', 'El recurso se ha actualizado correctamente.');
    }

    public function destroy(Recurso $recurso): RedirectResponse
    {
        $recurso->loadCount(['disponibilidades', 'bloqueos', 'reservas', 'servicioRecursos']);

        if ($recurso->disponibilidades_count > 0 || $recurso->bloqueos_count > 0 || $recurso->reservas_count > 0) {
            return redirect()
                ->route('admin.recursos.index')
                ->with('error', 'No puedes borrar este recurso porque tiene disponibilidades, bloqueos o reservas relacionadas.');
        }

        DB::transaction(function () use ($recurso) {
            if ($recurso->servicioRecursos_count > 0) {
                $recurso->servicios()->detach();
            }

            $recurso->delete();
        });

        return redirect()
            ->route('admin.recursos.index')
            ->with('success', 'El recurso se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdateRecursoRequest $request, Recurso $recurso): JsonResponse
    {
        $recurso->update($request->validated());
        $recurso->refresh();

        return response()->json([
            'message' => 'El recurso se ha actualizado correctamente.',
            'data' => [
                'id' => $recurso->id,
                'capacidad' => $recurso->capacidad,
                'capacidad_label' => $recurso->capacidad ?: 'Sin definir',
                'activo' => $recurso->activo,
                'activo_label' => $recurso->activo ? 'Activo' : 'Inactivo',
                'combinable' => $recurso->combinable,
                'combinable_label' => $recurso->combinable ? 'Sí' : 'No',
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $negocioId = $request->integer('negocio_id');
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Recurso::query()
            ->with(['negocio:id,nombre', 'tipoRecurso:id,nombre'])
            ->select(['id', 'nombre', 'negocio_id', 'tipo_recurso_id', 'activo'])
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
            'results' => $results->take($perPage)->map(function (Recurso $recurso) {
                $parts = array_filter([
                    $recurso->nombre,
                    $recurso->negocio?->nombre,
                    $recurso->tipoRecurso?->nombre,
                ]);

                return [
                    'id' => $recurso->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function resolveSelectedNegocio(?Recurso $recurso = null): ?Negocio
    {
        $selectedId = session()->getOldInput('negocio_id', $recurso?->negocio_id);

        if (! $selectedId) {
            return null;
        }

        return Negocio::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedTipoRecurso(?Recurso $recurso = null): ?TipoRecurso
    {
        $selectedId = session()->getOldInput('tipo_recurso_id', $recurso?->tipo_recurso_id);

        if (! $selectedId) {
            return null;
        }

        return TipoRecurso::query()->select(['id', 'nombre'])->find($selectedId);
    }
}
