<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBloqueoRequest;
use App\Http\Requests\Admin\UpdateBloqueoRequest;
use App\Models\Bloqueo;
use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\TipoBloqueo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BloqueoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'fecha')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['fecha', 'hora_inicio', 'hora_fin', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'fecha';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $bloqueos = Bloqueo::query()
            ->with(['recurso', 'tipoBloqueo', 'negocio'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('motivo', 'like', "%{$search}%")
                        ->orWhere('fecha', 'like', "%{$search}%")
                        ->orWhereHas('recurso', function ($recursoQuery) use ($search) {
                            $recursoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tipoBloqueo', function ($tipoBloqueoQuery) use ($search) {
                            $tipoBloqueoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('negocio', function ($negocioQuery) use ($search) {
                            $negocioQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.bloqueos.index', [
            'bloqueos' => $bloqueos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.bloqueos.create', [
            'bloqueo' => new Bloqueo(),
            'recursos' => $this->resourceOptions(),
            'tiposBloqueo' => $this->blockTypeOptions(),
            'negocios' => $this->businessOptions(),
            'dayOptions' => $this->dayOptions(),
            'horaInicioValue' => old('hora_inicio'),
            'horaFinValue' => old('hora_fin'),
        ]);
    }

    public function store(StoreBloqueoRequest $request): RedirectResponse
    {
        $bloqueo = Bloqueo::create($request->validated());

        return redirect()
            ->route('admin.bloqueos.show', $bloqueo)
            ->with('success', 'El bloqueo se ha creado correctamente.');
    }

    public function show(Bloqueo $bloqueo): View
    {
        $bloqueo->load(['recurso', 'tipoBloqueo', 'negocio']);

        return view('admin.bloqueos.show', [
            'bloqueo' => $bloqueo,
            'horaInicioValue' => $this->timeForDisplay($bloqueo->hora_inicio),
            'horaFinValue' => $this->timeForDisplay($bloqueo->hora_fin),
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    public function edit(Bloqueo $bloqueo): View
    {
        $bloqueo->load(['recurso', 'tipoBloqueo', 'negocio']);

        return view('admin.bloqueos.edit', [
            'bloqueo' => $bloqueo,
            'recursos' => $this->resourceOptions(),
            'tiposBloqueo' => $this->blockTypeOptions(),
            'negocios' => $this->businessOptions(),
            'dayOptions' => $this->dayOptions(),
            'horaInicioValue' => $this->timeForDisplay($bloqueo->hora_inicio),
            'horaFinValue' => $this->timeForDisplay($bloqueo->hora_fin),
        ]);
    }

    public function update(UpdateBloqueoRequest $request, Bloqueo $bloqueo): RedirectResponse
    {
        $bloqueo->update($request->validated());

        return redirect()
            ->route('admin.bloqueos.edit', $bloqueo)
            ->with('success', 'El bloqueo se ha actualizado correctamente.');
    }

    public function destroy(Bloqueo $bloqueo): RedirectResponse
    {
        $bloqueo->delete();

        return redirect()
            ->route('admin.bloqueos.index')
            ->with('success', 'El bloqueo se ha eliminado correctamente.');
    }

    private function resourceOptions()
    {
        return Recurso::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function blockTypeOptions()
    {
        return TipoBloqueo::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function businessOptions()
    {
        return Negocio::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function dayOptions(): array
    {
        return [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
    }

    private function timeForDisplay(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
    }
}
