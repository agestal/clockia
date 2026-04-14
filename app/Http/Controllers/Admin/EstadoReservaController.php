<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateEstadoReservaRequest;
use App\Http\Requests\Admin\StoreEstadoReservaRequest;
use App\Http\Requests\Admin\UpdateEstadoReservaRequest;
use App\Models\EstadoReserva;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EstadoReservaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $estadosReserva = EstadoReserva::query()
            ->withCount('reservas')
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

        return view('admin.estados-reserva.index', [
            'estadosReserva' => $estadosReserva,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.estados-reserva.create', [
            'estadoReserva' => new EstadoReserva(),
        ]);
    }

    public function store(StoreEstadoReservaRequest $request): RedirectResponse
    {
        $estadoReserva = EstadoReserva::create($request->validated());

        return redirect()
            ->route('admin.estados-reserva.show', $estadoReserva)
            ->with('success', 'El estado de reserva se ha creado correctamente.');
    }

    public function show(EstadoReserva $estado_reserva): View
    {
        $estado_reserva->loadCount('reservas');

        $reservas = $estado_reserva->reservas()
            ->latest('fecha')
            ->limit(10)
            ->get(['id', 'fecha', 'hora_inicio', 'hora_fin', 'precio_calculado', 'precio_total']);

        return view('admin.estados-reserva.show', [
            'estadoReserva' => $estado_reserva,
            'reservas' => $reservas,
        ]);
    }

    public function edit(EstadoReserva $estado_reserva): View
    {
        return view('admin.estados-reserva.edit', [
            'estadoReserva' => $estado_reserva,
        ]);
    }

    public function update(UpdateEstadoReservaRequest $request, EstadoReserva $estado_reserva): RedirectResponse
    {
        $estado_reserva->update($request->validated());

        return redirect()
            ->route('admin.estados-reserva.edit', $estado_reserva)
            ->with('success', 'El estado de reserva se ha actualizado correctamente.');
    }

    public function destroy(EstadoReserva $estado_reserva): RedirectResponse
    {
        $estado_reserva->loadCount('reservas');

        if ($estado_reserva->reservas_count > 0) {
            return redirect()
                ->route('admin.estados-reserva.index')
                ->with('error', 'No puedes borrar este estado de reserva porque tiene reservas relacionadas.');
        }

        $estado_reserva->delete();

        return redirect()
            ->route('admin.estados-reserva.index')
            ->with('success', 'El estado de reserva se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateEstadoReservaRequest $request,
        EstadoReserva $estado_reserva
    ): JsonResponse {
        $estado_reserva->update($request->validated());

        return response()->json([
            'message' => 'El nombre se ha actualizado correctamente.',
            'data' => [
                'id' => $estado_reserva->id,
                'nombre' => $estado_reserva->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = EstadoReserva::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (EstadoReserva $estadoReserva) => [
                'id' => $estadoReserva->id,
                'text' => $estadoReserva->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
