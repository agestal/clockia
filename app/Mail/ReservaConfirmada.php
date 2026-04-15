<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
    ) {}

    public function envelope(): Envelope
    {
        $negocio = $this->reserva->negocio?->nombre ?? 'Clockia';

        return new Envelope(
            subject: "Confirmación de reserva — {$negocio}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-confirmada',
        );
    }
}
