<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudCancelacion extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $cancelUrl;

    public function __construct(
        public readonly Reserva $reserva,
        public readonly string $token,
    ) {
        $this->cancelUrl = url("/cancelar-reserva/{$token}");
    }

    public function envelope(): Envelope
    {
        $negocio = $this->reserva->negocio?->nombre ?? 'Clockia';

        return new Envelope(
            subject: "Confirma la cancelación de tu reserva — {$negocio}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.solicitud-cancelacion');
    }
}
