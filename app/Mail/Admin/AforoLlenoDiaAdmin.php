<?php

namespace App\Mail\Admin;

use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AforoLlenoDiaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $fechaHumana;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly ?Servicio $servicio,
        public readonly Carbon|string|null $fecha,
        public readonly Collection $sesiones,
    ) {
        $f = $fecha instanceof Carbon ? $fecha : Carbon::parse((string) $fecha);
        $this->fechaHumana = $f->locale('es')->translatedFormat('l j \d\e F');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔴 Día completo — '.($this->servicio?->nombre ?? 'todas las experiencias').' el '.$this->fechaHumana,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin.aforo-lleno-dia');
    }
}
