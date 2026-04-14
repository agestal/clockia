<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOcupacionExternaRequest;
use App\Http\Requests\Admin\UpdateOcupacionExternaRequest;
use App\Models\Integracion;
use App\Models\Negocio;
use App\Models\OcupacionExterna;
use App\Models\Recurso;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OcupacionExternaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'inicio_datetime')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['inicio_datetime', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'inicio_datetime';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $ocupaciones = OcupacionExterna::query()
            ->with(['negocio', 'recurso', 'integracion'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('titulo', 'like', "%{$search}%")
                        ->orWhere('external_id', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhereHas('negocio', function ($negocioQuery) use ($search) {
                            $negocioQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.ocupaciones-externas.index', [
            'ocupaciones' => $ocupaciones,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.ocupaciones-externas.create', [
            'ocupacion' => new OcupacionExterna(),
            'negocios' => $this->negocioOptions(),
            'recursos' => $this->recursoOptions(),
            'integraciones' => $this->integracionOptions(),
            'proveedorOptions' => $this->proveedorOptions(),
            'horaInicioValue' => old('hora_inicio'),
            'horaFinValue' => old('hora_fin'),
        ]);
    }

    public function store(StoreOcupacionExternaRequest $request): RedirectResponse
    {
        $ocupacion = OcupacionExterna::create($request->validated());

        return redirect()
            ->route('admin.ocupaciones-externas.show', $ocupacion)
            ->with('success', 'La ocupación externa se ha creado correctamente.');
    }

    public function show(OcupacionExterna $ocupacionExterna): View
    {
        $ocupacionExterna->load(['negocio', 'recurso', 'integracion', 'integracionMapeo']);

        return view('admin.ocupaciones-externas.show', [
            'ocupacion' => $ocupacionExterna,
            'horaInicioValue' => $this->timeForDisplay($ocupacionExterna->hora_inicio),
            'horaFinValue' => $this->timeForDisplay($ocupacionExterna->hora_fin),
        ]);
    }

    public function edit(OcupacionExterna $ocupacionExterna): View
    {
        $ocupacionExterna->load(['negocio', 'recurso', 'integracion', 'integracionMapeo']);

        return view('admin.ocupaciones-externas.edit', [
            'ocupacion' => $ocupacionExterna,
            'negocios' => $this->negocioOptions(),
            'recursos' => $this->recursoOptions(),
            'integraciones' => $this->integracionOptions(),
            'proveedorOptions' => $this->proveedorOptions(),
            'horaInicioValue' => $this->timeForDisplay($ocupacionExterna->hora_inicio),
            'horaFinValue' => $this->timeForDisplay($ocupacionExterna->hora_fin),
        ]);
    }

    public function update(UpdateOcupacionExternaRequest $request, OcupacionExterna $ocupacionExterna): RedirectResponse
    {
        $ocupacionExterna->update($request->validated());

        return redirect()
            ->route('admin.ocupaciones-externas.edit', $ocupacionExterna)
            ->with('success', 'La ocupación externa se ha actualizado correctamente.');
    }

    public function destroy(OcupacionExterna $ocupacionExterna): RedirectResponse
    {
        $ocupacionExterna->delete();

        return redirect()
            ->route('admin.ocupaciones-externas.index')
            ->with('success', 'La ocupación externa se ha eliminado correctamente.');
    }

    private function negocioOptions()
    {
        return Negocio::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function recursoOptions()
    {
        return Recurso::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function integracionOptions()
    {
        return Integracion::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function proveedorOptions(): array
    {
        return ['google_calendar' => 'Google Calendar', 'manual' => 'Manual'];
    }

    private function timeForDisplay(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
    }
}
