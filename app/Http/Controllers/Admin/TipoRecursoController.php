<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateTipoRecursoRequest;
use App\Http\Requests\Admin\StoreTipoRecursoRequest;
use App\Http\Requests\Admin\UpdateTipoRecursoRequest;
use App\Models\TipoRecurso;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipoRecursoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tiposRecurso = TipoRecurso::query()
            ->withCount('recursos')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.tipos-recurso.index', [
            'tiposRecurso' => $tiposRecurso,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.tipos-recurso.create', [
            'tipoRecurso' => new TipoRecurso(),
        ]);
    }

    public function store(StoreTipoRecursoRequest $request): RedirectResponse
    {
        $tipoRecurso = TipoRecurso::create($request->validated());

        return redirect()
            ->route('admin.tipos-recurso.show', $tipoRecurso)
            ->with('success', 'El tipo de recurso se ha creado correctamente.');
    }

    public function show(TipoRecurso $tipo_recurso): View
    {
        $tipo_recurso->loadCount('recursos');

        $recursos = $tipo_recurso->recursos()
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'capacidad', 'activo']);

        return view('admin.tipos-recurso.show', [
            'tipoRecurso' => $tipo_recurso,
            'recursos' => $recursos,
        ]);
    }

    public function edit(TipoRecurso $tipo_recurso): View
    {
        return view('admin.tipos-recurso.edit', [
            'tipoRecurso' => $tipo_recurso,
        ]);
    }

    public function update(UpdateTipoRecursoRequest $request, TipoRecurso $tipo_recurso): RedirectResponse
    {
        $tipo_recurso->update($request->validated());

        return redirect()
            ->route('admin.tipos-recurso.edit', $tipo_recurso)
            ->with('success', 'El tipo de recurso se ha actualizado correctamente.');
    }

    public function destroy(TipoRecurso $tipo_recurso): RedirectResponse
    {
        $tipo_recurso->loadCount('recursos');

        if ($tipo_recurso->recursos_count > 0) {
            return redirect()
                ->route('admin.tipos-recurso.index')
                ->with('error', 'No puedes borrar este tipo de recurso porque tiene recursos relacionados.');
        }

        $tipo_recurso->delete();

        return redirect()
            ->route('admin.tipos-recurso.index')
            ->with('success', 'El tipo de recurso se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateTipoRecursoRequest $request,
        TipoRecurso $tipo_recurso
    ): JsonResponse {
        $tipo_recurso->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $tipo_recurso->id,
                'nombre' => $tipo_recurso->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = TipoRecurso::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (TipoRecurso $tipoRecurso) => [
                'id' => $tipoRecurso->id,
                'text' => $tipoRecurso->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
