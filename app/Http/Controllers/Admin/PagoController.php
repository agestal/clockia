<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdatePagoRequest;
use App\Http\Requests\Admin\StorePagoRequest;
use App\Http\Requests\Admin\UpdatePagoRequest;
use App\Models\ConceptoPago;
use App\Models\EstadoPago;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\TipoPago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    use InteractsWithAdminAccess;

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value();

        $allowedSorts = ['created_at', 'importe'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $pagos = Pago::query()
            ->with(['reserva', 'tipoPago', 'estadoPago', 'conceptoPago'])
            ->tap(fn ($query) => $this->scopeAccessibleBusinessRelation($query, $request, 'reserva', 'negocio_id'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('referencia_externa', 'like', "%{$search}%")
                        ->orWhere('importe', 'like', "%{$search}%")
                        ->orWhereHas('tipoPago', function ($tipoPagoQuery) use ($search) {
                            $tipoPagoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('estadoPago', function ($estadoPagoQuery) use ($search) {
                            $estadoPagoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('conceptoPago', function ($conceptoQuery) use ($search) {
                            $conceptoQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reserva', function ($reservaQuery) use ($search) {
                            if (ctype_digit($search)) {
                                $reservaQuery->orWhere('id', (int) $search);
                            }

                            $reservaQuery->where('fecha', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.pagos.index', [
            'pagos' => $pagos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'estadoPagoOptions' => EstadoPago::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create(): View
    {
        return view('admin.pagos.create', [
            'pago' => new Pago(),
            'selectedReserva' => $this->resolveSelectedReserva(),
            'selectedTipoPago' => $this->resolveSelectedTipoPago(),
            'selectedEstadoPago' => $this->resolveSelectedEstadoPago(),
            'conceptosPago' => $this->paymentConceptOptions(),
            'selectedConceptoPago' => $this->resolveSelectedConceptoPago(),
        ]);
    }

    public function store(StorePagoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->abortUnlessModelAccessible($request, Reserva::class, $validated['reserva_id'] ?? null);

        $pago = DB::transaction(fn () => Pago::create($validated));

        return redirect()
            ->route('admin.pagos.show', $pago)
            ->with('success', 'El pago se ha creado correctamente.');
    }

    public function show(Pago $pago): View
    {
        $pago->load(['reserva', 'tipoPago', 'estadoPago', 'conceptoPago']);

        return view('admin.pagos.show', [
            'pago' => $pago,
        ]);
    }

    public function edit(Pago $pago): View
    {
        $pago->load(['reserva', 'tipoPago', 'estadoPago', 'conceptoPago']);

        return view('admin.pagos.edit', [
            'pago' => $pago,
            'selectedReserva' => $this->resolveSelectedReserva($pago),
            'selectedTipoPago' => $this->resolveSelectedTipoPago($pago),
            'selectedEstadoPago' => $this->resolveSelectedEstadoPago($pago),
            'conceptosPago' => $this->paymentConceptOptions(),
            'selectedConceptoPago' => $this->resolveSelectedConceptoPago($pago),
        ]);
    }

    public function update(UpdatePagoRequest $request, Pago $pago): RedirectResponse
    {
        $validated = $request->validated();
        $this->abortUnlessModelAccessible($request, Reserva::class, $validated['reserva_id'] ?? null);

        DB::transaction(function () use ($request, $pago) {
            $pago->update($request->validated());
        });

        return redirect()
            ->route('admin.pagos.edit', $pago)
            ->with('success', 'El pago se ha actualizado correctamente.');
    }

    public function destroy(Pago $pago): RedirectResponse
    {
        $pago->delete();

        return redirect()
            ->route('admin.pagos.index')
            ->with('success', 'El pago se ha eliminado correctamente.');
    }

    public function inlineUpdate(InlineUpdatePagoRequest $request, Pago $pago): JsonResponse
    {
        $pago->update($request->validated());
        $pago->load('estadoPago');

        return response()->json([
            'message' => 'El estado del pago se ha actualizado correctamente.',
            'data' => [
                'id' => $pago->id,
                'estado_pago_id' => $pago->estado_pago_id,
                'estado_pago_label' => $pago->estadoPago?->nombre,
            ],
        ]);
    }

    public function searchOptions(Request $request): JsonResponse
    {
        $term = $request->string('term')->trim()->value();
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;

        $query = Pago::query()
            ->with(['reserva:id,fecha,hora_inicio,hora_fin', 'estadoPago:id,nombre'])
            ->select(['id', 'reserva_id', 'estado_pago_id', 'importe', 'referencia_externa'])
            ->tap(fn ($builder) => $this->scopeAccessibleBusinessRelation($builder, $request, 'reserva', 'negocio_id'))
            ->latest('created_at')
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where(function ($innerQuery) use ($term) {
                    $innerQuery
                        ->where('referencia_externa', 'like', "%{$term}%")
                        ->orWhere('importe', 'like', "%{$term}%");

                    if (ctype_digit($term)) {
                        $innerQuery->orWhere('id', (int) $term);
                    }
                });
            });

        $results = $query->forPage($page, $perPage + 1)->get();
        $hasMore = $results->count() > $perPage;

        return response()->json([
            'results' => $results->take($perPage)->map(function (Pago $pago) {
                $parts = array_filter([
                    '#'.$pago->id,
                    $pago->reserva ? 'Reserva #'.$pago->reserva->id : null,
                    number_format((float) $pago->importe, 2, ',', '.'),
                    $pago->estadoPago?->nombre,
                    $pago->referencia_externa,
                ]);

                return [
                    'id' => $pago->id,
                    'text' => implode(' · ', $parts),
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function resolveSelectedReserva(?Pago $pago = null): ?Reserva
    {
        $selectedId = session()->getOldInput('reserva_id', $pago?->reserva_id);

        if (! $selectedId) {
            return null;
        }

        return Reserva::query()
            ->with(['cliente:id,nombre', 'servicio:id,nombre'])
            ->tap(fn ($query) => $this->adminAccess()->scopeBusinesses($query, auth()->user(), 'negocio_id'))
            ->select(['id', 'cliente_id', 'servicio_id', 'fecha', 'hora_inicio', 'hora_fin'])
            ->find($selectedId);
    }

    private function resolveSelectedTipoPago(?Pago $pago = null): ?TipoPago
    {
        $selectedId = session()->getOldInput('tipo_pago_id', $pago?->tipo_pago_id);

        if (! $selectedId) {
            return null;
        }

        return TipoPago::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function resolveSelectedEstadoPago(?Pago $pago = null): ?EstadoPago
    {
        $selectedId = session()->getOldInput('estado_pago_id', $pago?->estado_pago_id);

        if (! $selectedId) {
            return null;
        }

        return EstadoPago::query()->select(['id', 'nombre'])->find($selectedId);
    }

    private function paymentConceptOptions()
    {
        return ConceptoPago::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function resolveSelectedConceptoPago(?Pago $pago = null): ?ConceptoPago
    {
        $selectedId = session()->getOldInput('concepto_pago_id', $pago?->concepto_pago_id);

        if (! $selectedId) {
            return null;
        }

        return ConceptoPago::query()->select(['id', 'nombre'])->find($selectedId);
    }
}
