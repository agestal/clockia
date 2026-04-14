<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateTipoNegocioRequest;
use App\Http\Requests\Admin\StoreTipoNegocioRequest;
use App\Http\Requests\Admin\UpdateTipoNegocioRequest;
use App\Models\TipoNegocio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipoNegocioController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tiposNegocio = TipoNegocio::query()
            ->withCount('negocios')
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

        return view('admin.tipos-negocio.index', [
            'tiposNegocio' => $tiposNegocio,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.tipos-negocio.create', [
            'tipoNegocio' => new TipoNegocio(),
        ]);
    }

    public function store(StoreTipoNegocioRequest $request): RedirectResponse
    {
        $tipoNegocio = TipoNegocio::create($request->validated());

        return redirect()
            ->route('admin.tipos-negocio.show', $tipoNegocio)
            ->with('success', 'El tipo de negocio se ha creado correctamente.');
    }

    public function show(TipoNegocio $tipo_negocio): View
    {
        $tipo_negocio->loadCount('negocios');

        $negocios = $tipo_negocio->negocios()
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'email', 'telefono', 'activo']);

        return view('admin.tipos-negocio.show', [
            'tipoNegocio' => $tipo_negocio,
            'negocios' => $negocios,
        ]);
    }

    public function edit(TipoNegocio $tipo_negocio): View
    {
        return view('admin.tipos-negocio.edit', [
            'tipoNegocio' => $tipo_negocio,
        ]);
    }

    public function update(UpdateTipoNegocioRequest $request, TipoNegocio $tipo_negocio): RedirectResponse
    {
        $tipo_negocio->update($request->validated());

        return redirect()
            ->route('admin.tipos-negocio.edit', $tipo_negocio)
            ->with('success', 'El tipo de negocio se ha actualizado correctamente.');
    }

    public function destroy(TipoNegocio $tipo_negocio): RedirectResponse
    {
        $tipo_negocio->loadCount('negocios');

        if ($tipo_negocio->negocios_count > 0) {
            return redirect()
                ->route('admin.tipos-negocio.index')
                ->with('error', 'No puedes borrar este tipo de negocio porque tiene negocios relacionados.');
        }

        $tipo_negocio->delete();

        return redirect()
            ->route('admin.tipos-negocio.index')
            ->with('success', 'El tipo de negocio se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateTipoNegocioRequest $request,
        TipoNegocio $tipo_negocio
    ): JsonResponse {
        $tipo_negocio->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $tipo_negocio->id,
                'nombre' => $tipo_negocio->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = TipoNegocio::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query
            ->forPage($page, $perPage + 1)
            ->get();

        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results
                ->take($perPage)
                ->map(fn (TipoNegocio $tipoNegocio) => [
                    'id' => $tipoNegocio->id,
                    'text' => $tipoNegocio->nombre,
                ])
                ->values(),
            'pagination' => [
                'more' => $hasMore,
            ],
        ]);
    }
}
