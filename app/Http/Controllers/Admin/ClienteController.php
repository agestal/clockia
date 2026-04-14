<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateClienteRequest;
use App\Http\Requests\Admin\StoreClienteRequest;
use App\Http\Requests\Admin\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'email', 'telefono', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $clientes = Cliente::query()
            ->withCount('reservas')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('notas', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.clientes.index', [
            'clientes' => $clientes,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.clientes.create', [
            'cliente' => new Cliente(),
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->validated());

        return redirect()
            ->route('admin.clientes.show', $cliente)
            ->with('success', 'El cliente se ha creado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->loadCount('reservas');

        $reservas = $cliente->reservas()
            ->with(['negocio', 'servicio', 'estadoReserva'])
            ->latest('fecha')
            ->limit(10)
            ->get([
                'id',
                'negocio_id',
                'servicio_id',
                'fecha',
                'hora_inicio',
                'hora_fin',
                'precio_calculado',
                'precio_total',
                'estado_reserva_id',
            ]);

        return view('admin.clientes.show', [
            'cliente' => $cliente,
            'reservas' => $reservas,
        ]);
    }

    public function edit(Cliente $cliente): View
    {
        return view('admin.clientes.edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return redirect()
            ->route('admin.clientes.edit', $cliente)
            ->with('success', 'El cliente se ha actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->loadCount('reservas');

        if ($cliente->reservas_count > 0) {
            return redirect()
                ->route('admin.clientes.index')
                ->with('error', 'No puedes borrar este cliente porque tiene reservas relacionadas.');
        }

        $cliente->delete();

        return redirect()
            ->route('admin.clientes.index')
            ->with('success', 'El cliente se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $validated = $request->validated();
        $cliente->update($validated);

        return response()->json([
            'message' => 'El cliente se ha actualizado correctamente.',
            'data' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Cliente::query()
            ->select(['id', 'nombre', 'email', 'telefono'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where(function ($innerQuery) use ($term) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('telefono', 'like', "%{$term}%");
                });
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(function (Cliente $cliente) {
                $parts = array_filter([$cliente->nombre, $cliente->email, $cliente->telefono]);

                return [
                    'id' => $cliente->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }
}
