<?php

namespace App\Mail;

use App\Models\Reserva;
use App\Support\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public array $template;

    public function __construct(
        public readonly Reserva $reserva,
    ) {
        $this->template = app(EmailTemplateRenderer::class)->forConfirmation($this->reserva);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template['asunto'] ?? 'Confirmacion de reserva',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-confirmada',
            with: [
                'template' => $this->template,
            ],
        );
    }
}
