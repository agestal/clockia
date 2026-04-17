<?php

namespace App\Jobs;

use App\Mail\ReservaModificada;
use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarMailReservaModificada implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
        public readonly array $changeSummary = [],
    ) {}

    public function handle(): void
    {
        $this->reserva->load(['negocio', 'servicio']);

        $email = $this->reserva->emailResponsableEfectivo();

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new ReservaModificada($this->reserva, $this->changeSummary));

        $this->reserva->update(['mail_modificacion_enviado_en' => now()]);
    }
}
