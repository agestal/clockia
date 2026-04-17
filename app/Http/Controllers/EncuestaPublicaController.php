<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\EncuestaRespuesta;
use App\Services\Notifications\AdminNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EncuestaPublicaController extends Controller
{
    public function show(string $token): View
    {
        $encuesta = Encuesta::query()
            ->where('token', $token)
            ->with(['negocio', 'reserva.servicio', 'plantilla'])
            ->first();

        if (! $encuesta || ! $encuesta->puedeResponder()) {
            throw new NotFoundHttpException();
        }

        $survey = $encuesta->contenidoEncuesta();

        return view('encuesta.formulario', [
            'encuesta' => $encuesta,
            'survey' => $survey,
        ]);
    }

    public function submit(Request $request, string $token): View
    {
        [$encuesta, $survey, $validated] = DB::transaction(function () use ($request, $token) {
            $encuesta = Encuesta::query()
                ->where('token', $token)
                ->lockForUpdate()
                ->with(['negocio', 'reserva.servicio', 'plantilla'])
                ->first();

            if (! $encuesta || ! $encuesta->puedeResponder()) {
                throw new NotFoundHttpException();
            }

            $survey = $encuesta->contenidoEncuesta();
            $preguntas = collect($survey['preguntas'] ?? []);
            $scaleMin = (int) ($survey['escala_min'] ?? 0);
            $scaleMax = (int) ($survey['escala_max'] ?? 10);

            if ($preguntas->isEmpty()) {
                throw new NotFoundHttpException();
            }

            $rules = $preguntas->mapWithKeys(
                static fn (array $pregunta) => [
                    "item_{$pregunta['id']}" => ['required', 'integer', "min:{$scaleMin}", "max:{$scaleMax}"],
                ]
            )->all();

            $rules['comentario_general'] = ['nullable', 'string', 'max:5000'];

            $validated = $request->validate(
                $rules,
                [],
                ['comentario_general' => 'comentario final']
            );

            foreach ($preguntas as $pregunta) {
                EncuestaRespuesta::updateOrCreate(
                    [
                        'encuesta_id' => $encuesta->id,
                        'encuesta_item_id' => (int) $pregunta['id'],
                    ],
                    [
                        'puntuacion' => (int) $validated["item_{$pregunta['id']}"],
                    ]
                );
            }

            $encuesta->update([
                'activo' => false,
                'respondida_en' => now(),
                'comentario_general' => $validated['comentario_general'] ?? null,
            ]);

            return [$encuesta->fresh(['negocio', 'reserva.servicio']), $survey, $validated];
        });

        if ($encuesta->reserva) {
            $preguntas = collect($survey['preguntas'] ?? []);
            $respuestasResumen = $preguntas->map(fn (array $p) => [
                'pregunta' => $p['texto'] ?? $p['label'] ?? 'Pregunta',
                'valor' => $validated["item_{$p['id']}"] ?? '-',
            ])->values()->all();

            app(AdminNotificationService::class)->encuestaRespondida(
                $encuesta->reserva,
                $respuestasResumen,
                $validated['comentario_general'] ?? null,
            );
        }

        return view('encuesta.gracias', [
            'encuesta' => $encuesta,
            'survey' => $survey,
        ]);
    }
}
