<?php

namespace App\Jobs;

use App\Mail\EncuestaSatisfaccion;
use App\Models\Encuesta;
use App\Models\EncuestaPlantilla;
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
        $negocio = $this->reserva->negocio;

        if (! $email || ! $negocio) {
            return;
        }

        $plantilla = EncuestaPlantilla::defaultForBusiness($negocio);
        $snapshot = $plantilla->buildSnapshot();

        if (($snapshot['preguntas'] ?? []) === []) {
            return;
        }

        $encuesta = Encuesta::create([
            'reserva_id' => $this->reserva->id,
            'negocio_id' => $negocio->id,
            'encuesta_plantilla_id' => $plantilla->id,
            'token' => Encuesta::generarToken(),
            'activo' => true,
            'enviada_en' => now(),
            'contenido_snapshot' => $snapshot,
        ]);

        Mail::to($email)->send(new EncuestaSatisfaccion($this->reserva, $encuesta));

        $this->reserva->update(['mail_encuesta_enviado_en' => now()]);
    }
}
