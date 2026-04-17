<?php

namespace App\Mail\Admin;

use App\Models\Reserva;
use App\Models\Sesion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AforoLlenoExperienciaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Sesion $sesion,
        public readonly Reserva $reserva,
    ) {}

    public function envelope(): Envelope
    {
        $hora = substr((string) $this->sesion->hora_inicio, 0, 5);

        return new Envelope(
            subject: '🔴 Aforo completo — sesión de las '.$hora,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin.aforo-lleno-experiencia');
    }
}
