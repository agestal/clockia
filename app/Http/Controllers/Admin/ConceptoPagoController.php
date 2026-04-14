<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateConceptoPagoRequest;
use App\Http\Requests\Admin\StoreConceptoPagoRequest;
use App\Http\Requests\Admin\UpdateConceptoPagoRequest;
use App\Models\ConceptoPago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConceptoPagoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $conceptosPago = ConceptoPago::query()
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

        return view('admin.conceptos-pago.index', [
            'conceptosPago' => $conceptosPago,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.conceptos-pago.create', [
            'conceptoPago' => new ConceptoPago(),
        ]);
    }

    public function store(StoreConceptoPagoRequest $request): RedirectResponse
    {
        $conceptoPago = ConceptoPago::create($request->validated());

        return redirect()
            ->route('admin.conceptos-pago.show', $conceptoPago)
            ->with('success', 'El concepto de pago se ha creado correctamente.');
    }

    public function show(ConceptoPago $concepto_pago): View
    {
        $concepto_pago->loadCount('pagos');

        $pagos = $concepto_pago->pagos()
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'importe', 'referencia_externa', 'fecha_pago']);

        return view('admin.conceptos-pago.show', [
            'conceptoPago' => $concepto_pago,
            'pagos' => $pagos,
        ]);
    }

    public function edit(ConceptoPago $concepto_pago): View
    {
        return view('admin.conceptos-pago.edit', [
            'conceptoPago' => $concepto_pago,
        ]);
    }

    public function update(UpdateConceptoPagoRequest $request, ConceptoPago $concepto_pago): RedirectResponse
    {
        $concepto_pago->update($request->validated());

        return redirect()
            ->route('admin.conceptos-pago.edit', $concepto_pago)
            ->with('success', 'El concepto de pago se ha actualizado correctamente.');
    }

    public function destroy(ConceptoPago $concepto_pago): RedirectResponse
    {
        $concepto_pago->loadCount('pagos');

        if ($concepto_pago->pagos_count > 0) {
            return redirect()
                ->route('admin.conceptos-pago.index')
                ->with('error', 'No puedes borrar este concepto de pago porque tiene pagos relacionados.');
        }

        $concepto_pago->delete();

        return redirect()
            ->route('admin.conceptos-pago.index')
            ->with('success', 'El concepto de pago se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateConceptoPagoRequest $request,
        ConceptoPago $concepto_pago
    ): JsonResponse {
        $concepto_pago->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $concepto_pago->id,
                'nombre' => $concepto_pago->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = ConceptoPago::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (ConceptoPago $conceptoPago) => [
                'id' => $conceptoPago->id,
                'text' => $conceptoPago->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
