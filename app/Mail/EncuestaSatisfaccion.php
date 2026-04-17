<?php

namespace App\Mail;

use App\Models\Encuesta;
use App\Models\Reserva;
use App\Support\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EncuestaSatisfaccion extends Mailable
{
    use Queueable, SerializesModels;

    public array $template;

    public function __construct(
        public readonly Reserva $reserva,
        public readonly Encuesta $encuesta,
    ) {
        $this->template = app(EmailTemplateRenderer::class)->forSurvey($this->reserva, $this->encuesta);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template['asunto'] ?? 'Queremos saber como fue tu experiencia',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.encuesta-satisfaccion',
            with: [
                'template' => $this->template,
            ],
        );
    }
}
