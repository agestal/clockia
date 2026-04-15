<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\EncuestaItem;
use App\Models\EncuestaRespuesta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EncuestaPublicaController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $encuesta = Encuesta::where('token', $token)->with(['negocio', 'reserva.servicio'])->first();

        if (! $encuesta) {
            abort(404);
        }

        if ($encuesta->estaRespondida()) {
            return view('encuesta.gracias', ['encuesta' => $encuesta]);
        }

        $items = EncuestaItem::where('negocio_id', $encuesta->negocio_id)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return view('encuesta.formulario', [
            'encuesta' => $encuesta,
            'items' => $items,
        ]);
    }

    public function submit(Request $request, string $token): View
    {
        $encuesta = Encuesta::where('token', $token)->with('negocio')->firstOrFail();

        if ($encuesta->estaRespondida()) {
            return view('encuesta.gracias', ['encuesta' => $encuesta]);
        }

        $items = EncuestaItem::where('negocio_id', $encuesta->negocio_id)->where('activo', true)->get();

        $validated = $request->validate(
            $items->mapWithKeys(fn ($item) => ["item_{$item->id}" => ['required', 'integer', 'min:0', 'max:10']])->all()
        );

        foreach ($items as $item) {
            EncuestaRespuesta::create([
                'encuesta_id' => $encuesta->id,
                'encuesta_item_id' => $item->id,
                'puntuacion' => (int) $validated["item_{$item->id}"],
            ]);
        }

        $encuesta->update([
            'respondida_en' => now(),
            'comentario_general' => $request->input('comentario_general'),
        ]);

        return view('encuesta.gracias', ['encuesta' => $encuesta]);
    }
}
