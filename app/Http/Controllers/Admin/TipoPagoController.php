<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateTipoPagoRequest;
use App\Http\Requests\Admin\StoreTipoPagoRequest;
use App\Http\Requests\Admin\UpdateTipoPagoRequest;
use App\Models\TipoPago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipoPagoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tiposPago = TipoPago::query()
            ->withCount('pagos')
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

        return view('admin.tipos-pago.index', [
            'tiposPago' => $tiposPago,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.tipos-pago.create', [
            'tipoPago' => new TipoPago(),
        ]);
    }

    public function store(StoreTipoPagoRequest $request): RedirectResponse
    {
        $tipoPago = TipoPago::create($request->validated());

        return redirect()
            ->route('admin.tipos-pago.show', $tipoPago)
            ->with('success', 'El tipo de pago se ha creado correctamente.');
    }

    public function show(TipoPago $tipo_pago): View
    {
        $tipo_pago->loadCount('pagos');

        $pagos = $tipo_pago->pagos()
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'importe', 'referencia_externa', 'fecha_pago']);

        return view('admin.tipos-pago.show', [
            'tipoPago' => $tipo_pago,
            'pagos' => $pagos,
        ]);
    }

    public function edit(TipoPago $tipo_pago): View
    {
        return view('admin.tipos-pago.edit', [
            'tipoPago' => $tipo_pago,
        ]);
    }

    public function update(UpdateTipoPagoRequest $request, TipoPago $tipo_pago): RedirectResponse
    {
        $tipo_pago->update($request->validated());

        return redirect()
            ->route('admin.tipos-pago.edit', $tipo_pago)
            ->with('success', 'El tipo de pago se ha actualizado correctamente.');
    }

    public function destroy(TipoPago $tipo_pago): RedirectResponse
    {
        $tipo_pago->loadCount('pagos');

        if ($tipo_pago->pagos_count > 0) {
            return redirect()
                ->route('admin.tipos-pago.index')
                ->with('error', 'No puedes borrar este tipo de pago porque tiene pagos relacionados.');
        }

        $tipo_pago->delete();

        return redirect()
            ->route('admin.tipos-pago.index')
            ->with('success', 'El tipo de pago se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateTipoPagoRequest $request,
        TipoPago $tipo_pago
    ): JsonResponse {
        $tipo_pago->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $tipo_pago->id,
                'nombre' => $tipo_pago->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = TipoPago::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (TipoPago $tipoPago) => [
                'id' => $tipoPago->id,
                'text' => $tipoPago->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
