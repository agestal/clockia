<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateEstadoPagoRequest;
use App\Http\Requests\Admin\StoreEstadoPagoRequest;
use App\Http\Requests\Admin\UpdateEstadoPagoRequest;
use App\Models\EstadoPago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EstadoPagoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $estadosPago = EstadoPago::query()
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

        return view('admin.estados-pago.index', [
            'estadosPago' => $estadosPago,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.estados-pago.create', [
            'estadoPago' => new EstadoPago(),
        ]);
    }

    public function store(StoreEstadoPagoRequest $request): RedirectResponse
    {
        $estadoPago = EstadoPago::create($request->validated());

        return redirect()
            ->route('admin.estados-pago.show', $estadoPago)
            ->with('success', 'El estado de pago se ha creado correctamente.');
    }

    public function show(EstadoPago $estado_pago): View
    {
        $estado_pago->loadCount('pagos');

        $pagos = $estado_pago->pagos()
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'importe', 'referencia_externa', 'fecha_pago']);

        return view('admin.estados-pago.show', [
            'estadoPago' => $estado_pago,
            'pagos' => $pagos,
        ]);
    }

    public function edit(EstadoPago $estado_pago): View
    {
        return view('admin.estados-pago.edit', [
            'estadoPago' => $estado_pago,
        ]);
    }

    public function update(UpdateEstadoPagoRequest $request, EstadoPago $estado_pago): RedirectResponse
    {
        $estado_pago->update($request->validated());

        return redirect()
            ->route('admin.estados-pago.edit', $estado_pago)
            ->with('success', 'El estado de pago se ha actualizado correctamente.');
    }

    public function destroy(EstadoPago $estado_pago): RedirectResponse
    {
        $estado_pago->loadCount('pagos');

        if ($estado_pago->pagos_count > 0) {
            return redirect()
                ->route('admin.estados-pago.index')
                ->with('error', 'No puedes borrar este estado de pago porque tiene pagos relacionados.');
        }

        $estado_pago->delete();

        return redirect()
            ->route('admin.estados-pago.index')
            ->with('success', 'El estado de pago se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateEstadoPagoRequest $request,
        EstadoPago $estado_pago
    ): JsonResponse {
        $estado_pago->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $estado_pago->id,
                'nombre' => $estado_pago->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = EstadoPago::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (EstadoPago $estadoPago) => [
                'id' => $estadoPago->id,
                'text' => $estadoPago->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
