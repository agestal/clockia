<?php

namespace App\Jobs;

use App\Mail\EncuestaSatisfaccion;
use App\Models\Encuesta;
use App\Models\EncuestaItem;
use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarMailEncuesta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
    ) {}

    public function handle(): void
    {
        $this->reserva->load(['negocio', 'servicio']);

        $email = $this->reserva->email_responsable;

        if (! $email) {
            return;
        }

        // Ensure the negocio has survey items, create default if not
        $negocioId = $this->reserva->negocio_id;
        $itemsExist = EncuestaItem::where('negocio_id', $negocioId)->where('activo', true)->exists();

        if (! $itemsExist) {
            EncuestaItem::create([
                'negocio_id' => $negocioId,
                'clave' => 'servicio_general',
                'etiqueta' => 'Valoración general del servicio',
                'descripcion' => '¿Cómo valorarías tu experiencia en general?',
                'orden' => 1,
                'activo' => true,
            ]);
        }

        // Create encuesta
        $encuesta = Encuesta::create([
            'reserva_id' => $this->reserva->id,
            'negocio_id' => $negocioId,
            'token' => Encuesta::generarToken(),
            'enviada_en' => now(),
        ]);

        Mail::to($email)->send(new EncuestaSatisfaccion($this->reserva, $encuesta));

        $this->reserva->update(['mail_encuesta_enviado_en' => now()]);
    }
}
