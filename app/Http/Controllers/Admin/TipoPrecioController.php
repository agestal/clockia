<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateTipoPrecioRequest;
use App\Http\Requests\Admin\StoreTipoPrecioRequest;
use App\Http\Requests\Admin\UpdateTipoPrecioRequest;
use App\Models\TipoPrecio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipoPrecioController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tiposPrecio = TipoPrecio::query()
            ->withCount('servicios')
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

        return view('admin.tipos-precio.index', [
            'tiposPrecio' => $tiposPrecio,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.tipos-precio.create', [
            'tipoPrecio' => new TipoPrecio(),
        ]);
    }

    public function store(StoreTipoPrecioRequest $request): RedirectResponse
    {
        $tipoPrecio = TipoPrecio::create($request->validated());

        return redirect()
            ->route('admin.tipos-precio.show', $tipoPrecio)
            ->with('success', 'El tipo de precio se ha creado correctamente.');
    }

    public function show(TipoPrecio $tipo_precio): View
    {
        $tipo_precio->loadCount('servicios');

        $servicios = $tipo_precio->servicios()
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'precio_base', 'activo']);

        return view('admin.tipos-precio.show', [
            'tipoPrecio' => $tipo_precio,
            'servicios' => $servicios,
        ]);
    }

    public function edit(TipoPrecio $tipo_precio): View
    {
        return view('admin.tipos-precio.edit', [
            'tipoPrecio' => $tipo_precio,
        ]);
    }

    public function update(UpdateTipoPrecioRequest $request, TipoPrecio $tipo_precio): RedirectResponse
    {
        $tipo_precio->update($request->validated());

        return redirect()
            ->route('admin.tipos-precio.edit', $tipo_precio)
            ->with('success', 'El tipo de precio se ha actualizado correctamente.');
    }

    public function destroy(TipoPrecio $tipo_precio): RedirectResponse
    {
        $tipo_precio->loadCount('servicios');

        if ($tipo_precio->servicios_count > 0) {
            return redirect()
                ->route('admin.tipos-precio.index')
                ->with('error', 'No puedes borrar este tipo de precio porque tiene servicios relacionados.');
        }

        $tipo_precio->delete();

        return redirect()
            ->route('admin.tipos-precio.index')
            ->with('success', 'El tipo de precio se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateTipoPrecioRequest $request,
        TipoPrecio $tipo_precio
    ): JsonResponse {
        $tipo_precio->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $tipo_precio->id,
                'nombre' => $tipo_precio->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = TipoPrecio::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (TipoPrecio $tipoPrecio) => [
                'id' => $tipoPrecio->id,
                'text' => $tipoPrecio->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
