<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEncuestaPlantillaRequest;
use App\Http\Requests\Admin\UpdateEncuestaPlantillaRequest;
use App\Models\EncuestaItem;
use App\Models\EncuestaPlantilla;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EncuestaPlantillaController extends Controller
{
    use InteractsWithAdminAccess;

    public function index(Request $request): View
    {
        $negocios = $this->accessibleBusinesses($request);

        $negocios->each(static function (Negocio $negocio): void {
            EncuestaPlantilla::ensureDefaultForBusiness($negocio);
        });

        $selectedBusinessId = (int) $request->integer('negocio_id');

        $plantillas = EncuestaPlantilla::query()
            ->with('negocio')
            ->withCount([
                'encuestas',
                'preguntas as preguntas_activas_count' => fn ($query) => $query->where('activo', true),
            ])
            ->tap(fn ($query) => $this->scopeAccessibleBusinesses($query, $request, 'negocio_id'))
            ->when($selectedBusinessId > 0, fn ($query) => $query->where('negocio_id', $selectedBusinessId))
            ->orderBy('negocio_id')
            ->orderByDesc('predeterminada')
            ->orderBy('nombre')
            ->get();

        return view('admin.encuesta-plantillas.index', [
            'negocios' => $negocios,
            'plantillas' => $plantillas,
            'selectedBusinessId' => $selectedBusinessId,
        ]);
    }

    public function create(Request $request): View
    {
        $negocios = $this->accessibleBusinesses($request);
        $selectedBusinessId = (int) ($request->integer('negocio_id') ?: $negocios->first()?->id);

        $plantilla = new EncuestaPlantilla([
            'negocio_id' => $selectedBusinessId,
            'activo' => true,
            'predeterminada' => $selectedBusinessId > 0
                ? ! EncuestaPlantilla::query()->where('negocio_id', $selectedBusinessId)->exists()
                : false,
            'escala_min' => 0,
            'escala_max' => 10,
            'permite_comentario_final' => true,
            'titulo_publico' => 'Comparte tu valoracion',
            'intro_publica' => 'Nos ayuda mucho saber como ha ido la experiencia.',
            'agradecimiento_titulo' => 'Gracias por tu valoracion',
            'agradecimiento_texto' => 'Tu opinion nos ayuda a seguir mejorando.',
        ]);

        return view('admin.encuesta-plantillas.create', [
            'encuestaPlantilla' => $plantilla,
            'negocios' => $negocios,
            'preguntas' => old('preguntas', [
                ['id' => null, 'etiqueta' => 'Valoracion general de la experiencia', 'descripcion' => '¿Como valorarias tu visita en general?', 'activo' => true],
            ]),
        ]);
    }

    public function store(StoreEncuestaPlantillaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->abortUnlessBusinessAccessible($request, (int) $validated['negocio_id']);

        $plantilla = DB::transaction(function () use ($validated) {
            $preguntas = $validated['preguntas'];
            unset($validated['preguntas']);

            $plantilla = EncuestaPlantilla::create($validated);
            $this->syncQuestions($plantilla, $preguntas);
            $this->ensureDefaultTemplate($plantilla->negocio_id, $plantilla->predeterminada ? $plantilla->id : null);

            return $plantilla->fresh();
        });

        return redirect()
            ->route('admin.encuesta-plantillas.edit', $plantilla)
            ->with('success', 'La encuesta se ha creado correctamente.');
    }

    public function edit(Request $request, EncuestaPlantilla $encuestaPlantilla): View
    {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $encuestaPlantilla),
            Response::HTTP_FORBIDDEN
        );

        $encuestaPlantilla->load(['negocio', 'preguntas']);

        return view('admin.encuesta-plantillas.edit', [
            'encuestaPlantilla' => $encuestaPlantilla,
            'negocios' => $this->accessibleBusinesses($request),
            'preguntas' => old('preguntas', $encuestaPlantilla->preguntas
                ->map(static fn (EncuestaItem $pregunta) => [
                    'id' => $pregunta->id,
                    'etiqueta' => $pregunta->etiqueta,
                    'descripcion' => $pregunta->descripcion,
                    'activo' => $pregunta->activo,
                ])
                ->values()
                ->all()),
        ]);
    }

    public function update(
        UpdateEncuestaPlantillaRequest $request,
        EncuestaPlantilla $encuestaPlantilla
    ): RedirectResponse {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $encuestaPlantilla),
            Response::HTTP_FORBIDDEN
        );

        $validated = $request->validated();
        $this->abortUnlessBusinessAccessible($request, (int) $validated['negocio_id']);

        DB::transaction(function () use ($encuestaPlantilla, $validated) {
            $preguntas = $validated['preguntas'];
            unset($validated['preguntas']);

            $encuestaPlantilla->update($validated);
            $this->syncQuestions($encuestaPlantilla, $preguntas);
            $this->ensureDefaultTemplate(
                $encuestaPlantilla->negocio_id,
                $encuestaPlantilla->predeterminada ? $encuestaPlantilla->id : null
            );
        });

        return redirect()
            ->route('admin.encuesta-plantillas.edit', $encuestaPlantilla)
            ->with('success', 'La encuesta se ha actualizado correctamente.');
    }

    public function destroy(Request $request, EncuestaPlantilla $encuestaPlantilla): RedirectResponse
    {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $encuestaPlantilla),
            Response::HTTP_FORBIDDEN
        );

        $encuestaPlantilla->loadCount('encuestas');

        if ($encuestaPlantilla->encuestas_count > 0) {
            return redirect()
                ->route('admin.encuesta-plantillas.index')
                ->with('error', 'No puedes eliminar esta encuesta porque ya tiene envios asociados.');
        }

        $siblingsCount = EncuestaPlantilla::query()
            ->where('negocio_id', $encuestaPlantilla->negocio_id)
            ->count();

        if ($siblingsCount <= 1) {
            return redirect()
                ->route('admin.encuesta-plantillas.index')
                ->with('error', 'Cada negocio debe conservar al menos una encuesta disponible.');
        }

        $businessId = $encuestaPlantilla->negocio_id;

        DB::transaction(function () use ($encuestaPlantilla, $businessId) {
            $encuestaPlantilla->preguntas()->delete();
            $encuestaPlantilla->delete();
            $this->ensureDefaultTemplate($businessId);
        });

        return redirect()
            ->route('admin.encuesta-plantillas.index')
            ->with('success', 'La encuesta se ha eliminado correctamente.');
    }

    private function accessibleBusinesses(Request $request)
    {
        return $this->adminAccess()
            ->accessibleBusinessesQuery($request->user())
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function syncQuestions(EncuestaPlantilla $plantilla, array $preguntas): void
    {
        $existing = $plantilla->preguntas()->get()->keyBy('id');
        $keptIds = [];

        foreach (array_values($preguntas) as $index => $pregunta) {
            $questionId = isset($pregunta['id']) ? (int) $pregunta['id'] : null;
            $payload = [
                'negocio_id' => $plantilla->negocio_id,
                'encuesta_plantilla_id' => $plantilla->id,
                'etiqueta' => $pregunta['etiqueta'],
                'descripcion' => $pregunta['descripcion'] ?? null,
                'orden' => $index + 1,
                'activo' => (bool) ($pregunta['activo'] ?? false),
            ];

            if ($questionId && $existing->has($questionId)) {
                $item = $existing->get($questionId);
                $item->update($payload);
                $keptIds[] = $item->id;
                continue;
            }

            $payload['clave'] = $this->buildQuestionKey($plantilla, $pregunta['etiqueta'], $index + 1);
            $item = EncuestaItem::create($payload);
            $keptIds[] = $item->id;
        }

        $plantilla->preguntas()
            ->whereNotIn('id', $keptIds)
            ->update(['activo' => false]);
    }

    private function buildQuestionKey(EncuestaPlantilla $plantilla, string $label, int $position): string
    {
        $base = Str::slug($label, '_');
        $base = $base !== '' ? Str::limit($base, 50, '') : 'pregunta';
        $candidate = "tpl_{$plantilla->id}_{$position}_{$base}";
        $suffix = 1;

        while (EncuestaItem::query()
            ->where('negocio_id', $plantilla->negocio_id)
            ->where('clave', $candidate)
            ->exists()) {
            $candidate = "tpl_{$plantilla->id}_{$position}_{$base}_{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function ensureDefaultTemplate(int $businessId, ?int $preferredId = null): void
    {
        $query = EncuestaPlantilla::query()->where('negocio_id', $businessId);
        $default = (clone $query)->where('predeterminada', true)->where('activo', true)->first();

        if (! $default && $preferredId) {
            $preferred = (clone $query)->find($preferredId);

            if ($preferred) {
                if (! $preferred->activo) {
                    $preferred->update(['activo' => true]);
                }

                $default = $preferred->fresh();
            }
        }

        if (! $default) {
            $default = (clone $query)->where('activo', true)->orderBy('id')->first()
                ?? (clone $query)->orderBy('id')->first();
        }

        if (! $default) {
            return;
        }

        EncuestaPlantilla::query()
            ->where('negocio_id', $businessId)
            ->whereKeyNot($default->id)
            ->update(['predeterminada' => false]);

        if (! $default->predeterminada || ! $default->activo) {
            $default->update([
                'predeterminada' => true,
                'activo' => true,
            ]);
        }
    }
}
