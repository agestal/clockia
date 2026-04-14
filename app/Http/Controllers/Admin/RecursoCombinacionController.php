<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecursoCombinacionRequest;
use App\Http\Requests\Admin\UpdateRecursoCombinacionRequest;
use App\Models\Recurso;
use App\Models\RecursoCombinacion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecursoCombinacionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $combinaciones = RecursoCombinacion::query()
            ->with(['recurso', 'recursoCombinado'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->whereHas('recurso', function ($recursoQuery) use ($search) {
                            $recursoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('recursoCombinado', function ($recursoQuery) use ($search) {
                            $recursoQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.recurso-combinaciones.index', [
            'combinaciones' => $combinaciones,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.recurso-combinaciones.create', [
            'combinacion' => new RecursoCombinacion(),
            'recursos' => $this->resourceOptions(),
        ]);
    }

    public function store(StoreRecursoCombinacionRequest $request): RedirectResponse
    {
        RecursoCombinacion::create($request->validated());

        return redirect()
            ->route('admin.recurso-combinaciones.index')
            ->with('success', 'La combinación de recursos se ha creado correctamente.');
    }

    public function edit(RecursoCombinacion $recursoCombinacion): View
    {
        $recursoCombinacion->load(['recurso', 'recursoCombinado']);

        return view('admin.recurso-combinaciones.edit', [
            'combinacion' => $recursoCombinacion,
            'recursos' => $this->resourceOptions(),
        ]);
    }

    public function update(UpdateRecursoCombinacionRequest $request, RecursoCombinacion $recursoCombinacion): RedirectResponse
    {
        $recursoCombinacion->update($request->validated());

        return redirect()
            ->route('admin.recurso-combinaciones.index')
            ->with('success', 'La combinación de recursos se ha actualizado correctamente.');
    }

    public function destroy(RecursoCombinacion $recursoCombinacion): RedirectResponse
    {
        $recursoCombinacion->delete();

        return redirect()
            ->route('admin.recurso-combinaciones.index')
            ->with('success', 'La combinación de recursos se ha eliminado correctamente.');
    }

    private function resourceOptions()
    {
        return Recurso::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
}
