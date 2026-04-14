<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIntegracionMapeoRequest;
use App\Http\Requests\Admin\UpdateIntegracionMapeoRequest;
use App\Models\IntegracionMapeo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntegracionMapeoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['created_at', 'nombre_externo', 'external_id'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $mapeos = IntegracionMapeo::query()
            ->with(['integracion', 'negocio', 'recurso', 'servicio'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nombre_externo', 'like', "%{$search}%")
                        ->orWhere('external_id', 'like', "%{$search}%")
                        ->orWhereHas('integracion', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.integracion-mapeos.index', [
            'mapeos' => $mapeos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.integracion-mapeos.create', [
            'mapeo' => new IntegracionMapeo(['activo' => true]),
            'integraciones' => $this->integracionOptions(),
            'negocios' => $this->negocioOptions(),
            'recursos' => $this->recursoOptions(),
            'servicios' => $this->servicioOptions(),
            'tipoOrigenOptions' => $this->tipoOrigenOptions(),
        ]);
    }

    public function store(StoreIntegracionMapeoRequest $request): RedirectResponse
    {
        $mapeo = IntegracionMapeo::create($request->validated());

        return redirect()
            ->route('admin.integracion-mapeos.show', $mapeo)
            ->with('success', 'El mapeo de integración se ha creado correctamente.');
    }

    public function show(IntegracionMapeo $integracion_mapeo): View
    {
        $integracion_mapeo->load(['integracion', 'negocio', 'recurso', 'servicio']);
        $integracion_mapeo->loadCount('ocupacionesExternas');

        return view('admin.integracion-mapeos.show', [
            'mapeo' => $integracion_mapeo,
        ]);
    }

    public function edit(IntegracionMapeo $integracion_mapeo): View
    {
        $integracion_mapeo->load(['integracion', 'negocio', 'recurso', 'servicio']);

        return view('admin.integracion-mapeos.edit', [
            'mapeo' => $integracion_mapeo,
            'integraciones' => $this->integracionOptions(),
            'negocios' => $this->negocioOptions(),
            'recursos' => $this->recursoOptions(),
            'servicios' => $this->servicioOptions(),
            'tipoOrigenOptions' => $this->tipoOrigenOptions(),
        ]);
    }

    public function update(UpdateIntegracionMapeoRequest $request, IntegracionMapeo $integracion_mapeo): RedirectResponse
    {
        $integracion_mapeo->update($request->validated());

        return redirect()
            ->route('admin.integracion-mapeos.edit', $integracion_mapeo)
            ->with('success', 'El mapeo de integración se ha actualizado correctamente.');
    }

    public function destroy(IntegracionMapeo $integracion_mapeo): RedirectResponse
    {
        $integracion_mapeo->loadCount('ocupacionesExternas');

        if ($integracion_mapeo->ocupaciones_externas_count > 0) {
            return redirect()
                ->route('admin.integracion-mapeos.index')
                ->with('error', 'No puedes borrar este mapeo porque tiene ocupaciones externas relacionadas.');
        }

        $integracion_mapeo->delete();

        return redirect()
            ->route('admin.integracion-mapeos.index')
            ->with('success', 'El mapeo de integración se ha eliminado correctamente.');
    }

    private function integracionOptions()
    {
        return \App\Models\Integracion::orderBy('nombre')->get(['id', 'nombre']);
    }

    private function negocioOptions()
    {
        return \App\Models\Negocio::orderBy('nombre')->get(['id', 'nombre']);
    }

    private function recursoOptions()
    {
        return \App\Models\Recurso::orderBy('nombre')->get(['id', 'nombre']);
    }

    private function servicioOptions()
    {
        return \App\Models\Servicio::orderBy('nombre')->get(['id', 'nombre']);
    }

    private function tipoOrigenOptions(): array
    {
        return ['calendario' => 'Calendario', 'sala' => 'Sala', 'profesional' => 'Profesional'];
    }
}
