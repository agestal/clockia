<?php

namespace App\Mail\Admin;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnulacionReservaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reserva $reserva,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Reserva anulada — '.$this->reserva->localizador,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin.anulacion-reserva');
    }
}
