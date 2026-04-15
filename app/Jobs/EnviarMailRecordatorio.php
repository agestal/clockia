<?php

namespace App\Jobs;

use App\Mail\ReservaRecordatorio;
use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarMailRecordatorio implements ShouldQueue
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

        Mail::to($email)->send(new ReservaRecordatorio($this->reserva));

        $this->reserva->update(['mail_recordatorio_enviado_en' => now()]);
    }
}
