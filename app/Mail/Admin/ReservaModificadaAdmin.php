<?php

namespace App\Mail\Admin;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaModificadaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
        public readonly array $changeSummary = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reserva modificada - '.$this->reserva->localizador,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.reserva-modificada',
            with: [
                'changeSummary' => $this->changeSummary,
            ],
        );
    }
}
