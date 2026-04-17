<?php

namespace App\Mail\Admin;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EncuestaRespondidaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
        public readonly array $respuestas,
        public readonly ?string $comentario,
    ) {}

    public function envelope(): Envelope
    {
        $cliente = $this->reserva->nombre_responsable ?? $this->reserva->cliente?->nombre ?? 'Un cliente';

        return new Envelope(
            subject: '📋 Encuesta respondida por '.$cliente,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin.encuesta-respondida');
    }
}
