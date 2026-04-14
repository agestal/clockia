<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateDisponibilidadRequest;
use App\Http\Requests\Admin\StoreDisponibilidadRequest;
use App\Http\Requests\Admin\UpdateDisponibilidadRequest;
use App\Models\Disponibilidad;
use App\Models\Recurso;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'dia_semana')->value();
        $direction = $request->string('direction', 'asc')->value();

        $allowedSorts = ['dia_semana', 'hora_inicio', 'hora_fin', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'dia_semana';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $dayOptions = $this->dayOptions();

        $disponibilidades = Disponibilidad::query()
            ->with('recurso')
            ->when($search !== '', function ($query) use ($search, $dayOptions) {
                $query->where(function ($innerQuery) use ($search, $dayOptions) {
                    $innerQuery->whereHas('recurso', function ($recursoQuery) use ($search) {
                        $recursoQuery->where('nombre', 'like', "%{$search}%");
                    });

                    foreach ($dayOptions as $value => $label) {
                        if (stripos($label, $search) !== false) {
                            $innerQuery->orWhere('dia_semana', $value);
                        }
                    }
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.disponibilidades.index', [
            'disponibilidades' => $disponibilidades,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'dayOptions' => $dayOptions,
        ]);
    }

    public function create(): View
    {
        return view('admin.disponibilidades.create', [
            'disponibilidad' => new Disponibilidad([
                'activo' => true,
            ]),
            'recursos' => $this->resourceOptions(),
            'dayOptions' => $this->dayOptions(),
            'horaInicioValue' => old('hora_inicio'),
            'horaFinValue' => old('hora_fin'),
        ]);
    }

    public function store(StoreDisponibilidadRequest $request): RedirectResponse
    {
        $disponibilidad = Disponibilidad::create($request->validated());

        return redirect()
            ->route('admin.disponibilidades.show', $disponibilidad)
            ->with('success', 'La disponibilidad se ha creado correctamente.');
    }

    public function show(Disponibilidad $disponibilidad): View
    {
        $disponibilidad->load('recurso');

        return view('admin.disponibilidades.show', [
            'disponibilidad' => $disponibilidad,
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    public function edit(Disponibilidad $disponibilidad): View
    {
        $disponibilidad->load('recurso');

        return view('admin.disponibilidades.edit', [
            'disponibilidad' => $disponibilidad,
            'recursos' => $this->resourceOptions(),
            'dayOptions' => $this->dayOptions(),
            'horaInicioValue' => $this->timeForForm($disponibilidad->hora_inicio),
            'horaFinValue' => $this->timeForForm($disponibilidad->hora_fin),
        ]);
    }

    public function update(UpdateDisponibilidadRequest $request, Disponibilidad $disponibilidad): RedirectResponse
    {
        $disponibilidad->update($request->validated());

        return redirect()
            ->route('admin.disponibilidades.edit', $disponibilidad)
            ->with('success', 'La disponibilidad se ha actualizado correctamente.');
    }

    public function destroy(Disponibilidad $disponibilidad): RedirectResponse
    {
        $disponibilidad->delete();

        return redirect()
            ->route('admin.disponibilidades.index')
            ->with('success', 'La disponibilidad se ha eliminado correctamente.');
    }

    public function inlineUpdate(
        InlineUpdateDisponibilidadRequest $request,
        Disponibilidad $disponibilidad
    ): JsonResponse {
        $disponibilidad->update($request->validated());

        return response()->json([
            'message' => 'El estado de la disponibilidad se ha actualizado correctamente.',
            'data' => [
                'id' => $disponibilidad->id,
                'activo' => $disponibilidad->activo,
                'activo_label' => $disponibilidad->activo ? 'Activa' : 'Inactiva',
            ],
        ]);
    }

    private function resourceOptions()
    {
        return Recurso::query()
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

    private function timeForForm(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
    }
}
