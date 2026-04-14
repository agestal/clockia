<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIntegracionRequest;
use App\Http\Requests\Admin\UpdateIntegracionRequest;
use App\Models\Integracion;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntegracionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'nombre')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['nombre', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nombre';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $integraciones = Integracion::query()
            ->with(['negocio'])
            ->withCount(['cuentas', 'mapeos', 'ocupacionesExternas'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhereHas('negocio', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.integraciones.index', [
            'integraciones' => $integraciones,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.integraciones.create', [
            'integracion' => new Integracion(['activo' => true, 'estado' => 'pendiente']),
            'negocios' => $this->negocioOptions(),
            'proveedorOptions' => $this->proveedorOptions(),
            'modoOperacionOptions' => $this->modoOperacionOptions(),
            'estadoOptions' => $this->estadoOptions(),
        ]);
    }

    public function store(StoreIntegracionRequest $request): RedirectResponse
    {
        $integracion = Integracion::create($request->validated());

        return redirect()
            ->route('admin.integraciones.show', $integracion)
            ->with('success', 'La integración se ha creado correctamente.');
    }

    public function show(Integracion $integracion): View
    {
        $integracion->load('negocio');
        $integracion->loadCount(['cuentas', 'mapeos', 'ocupacionesExternas']);

        return view('admin.integraciones.show', [
            'integracion' => $integracion,
        ]);
    }

    public function edit(Integracion $integracion): View
    {
        $integracion->load('negocio');

        return view('admin.integraciones.edit', [
            'integracion' => $integracion,
            'negocios' => $this->negocioOptions(),
            'proveedorOptions' => $this->proveedorOptions(),
            'modoOperacionOptions' => $this->modoOperacionOptions(),
            'estadoOptions' => $this->estadoOptions(),
        ]);
    }

    public function update(UpdateIntegracionRequest $request, Integracion $integracion): RedirectResponse
    {
        $integracion->update($request->validated());

        return redirect()
            ->route('admin.integraciones.edit', $integracion)
            ->with('success', 'La integración se ha actualizado correctamente.');
    }

    public function destroy(Integracion $integracion): RedirectResponse
    {
        $integracion->loadCount(['cuentas', 'mapeos', 'ocupacionesExternas']);

        if ($integracion->cuentas_count > 0 || $integracion->mapeos_count > 0 || $integracion->ocupaciones_externas_count > 0) {
            return redirect()
                ->route('admin.integraciones.index')
                ->with('error', 'No puedes borrar esta integración porque tiene cuentas, mapeos u ocupaciones externas relacionadas.');
        }

        $integracion->delete();

        return redirect()
            ->route('admin.integraciones.index')
            ->with('success', 'La integración se ha eliminado correctamente.');
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Integracion::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where('nombre', 'like', "%{$term}%");
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(fn (Integracion $integracion) => [
                'id' => $integracion->id,
                'text' => $integracion->nombre,
            ])->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function negocioOptions()
    {
        return Negocio::orderBy('nombre')->get(['id', 'nombre']);
    }

    private function proveedorOptions(): array
    {
        return ['google_calendar' => 'Google Calendar'];
    }

    private function modoOperacionOptions(): array
    {
        return ['solo_clockia' => 'Solo Clockia', 'coexistencia' => 'Coexistencia', 'migracion' => 'Migración'];
    }

    private function estadoOptions(): array
    {
        return ['pendiente' => 'Pendiente', 'conectada' => 'Conectada', 'error' => 'Error', 'desactivada' => 'Desactivada'];
    }
}
