<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateTipoBloqueoRequest;
use App\Http\Requests\Admin\StoreTipoBloqueoRequest;
use App\Http\Requests\Admin\UpdateTipoBloqueoRequest;
use App\Models\TipoBloqueo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipoBloqueoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tiposBloqueo = TipoBloqueo::query()
            ->withCount('bloqueos')
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

        return view('admin.tipos-bloqueo.index', [
            'tiposBloqueo' => $tiposBloqueo,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.tipos-bloqueo.create', [
            'tipoBloqueo' => new TipoBloqueo(),
        ]);
    }

    public function store(StoreTipoBloqueoRequest $request): RedirectResponse
    {
        $tipoBloqueo = TipoBloqueo::create($request->validated());

        return redirect()
            ->route('admin.tipos-bloqueo.show', $tipoBloqueo)
            ->with('success', 'El tipo de bloqueo se ha creado correctamente.');
    }

    public function show(TipoBloqueo $tipo_bloqueo): View
    {
        $tipo_bloqueo->loadCount('bloqueos');

        $bloqueos = $tipo_bloqueo->bloqueos()
            ->latest('fecha')
            ->limit(10)
            ->get(['id', 'fecha', 'hora_inicio', 'hora_fin', 'motivo']);

        return view('admin.tipos-bloqueo.show', [
            'tipoBloqueo' => $tipo_bloqueo,
            'bloqueos' => $bloqueos,
        ]);
    }

    public function edit(TipoBloqueo $tipo_bloqueo): View
    {
        return view('admin.tipos-bloqueo.edit', [
            'tipoBloqueo' => $tipo_bloqueo,
        ]);
    }

    public function update(UpdateTipoBloqueoRequest $request, TipoBloqueo $tipo_bloqueo): RedirectResponse
    {
        $tipo_bloqueo->update($request->validated());

        return redirect()
            ->route('admin.tipos-bloqueo.edit', $tipo_bloqueo)
            ->with('success', 'El tipo de bloqueo se ha actualizado correctamente.');
    }

    public function destroy(TipoBloqueo $tipo_bloqueo): RedirectResponse
    {
        $tipo_bloqueo->loadCount('bloqueos');

        if ($tipo_bloqueo->bloqueos_count > 0) {
            return redirect()
                ->route('admin.tipos-bloqueo.index')
                ->with('error', 'No puedes borrar este tipo de bloqueo porque tiene bloqueos relacionados.');
        }

        $tipo_bloqueo->delete();

        return redirect()
            ->route('admin.tipos-bloqueo.index')
            ->with('success', 'El tipo de bloqueo se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateTipoBloqueoRequest $request,
        TipoBloqueo $tipo_bloqueo
    ): JsonResponse {
        $tipo_bloqueo->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $tipo_bloqueo->id,
                'nombre' => $tipo_bloqueo->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = TipoBloqueo::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (TipoBloqueo $tipoBloqueo) => [
                'id' => $tipoBloqueo->id,
                'text' => $tipoBloqueo->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
