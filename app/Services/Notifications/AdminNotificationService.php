<?php

namespace App\Services\Notifications;

use App\Mail\Admin\AforoLlenoDiaAdmin;
use App\Mail\Admin\AforoLlenoExperienciaAdmin;
use App\Mail\Admin\AnulacionReservaAdmin;
use App\Mail\Admin\EncuestaRespondidaAdmin;
use App\Mail\Admin\ReservaModificadaAdmin;
use App\Mail\Admin\ReservaNuevaAdmin;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Sesion;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    public function reservaNueva(Reserva $reserva): void
    {
        $negocio = $reserva->negocio;

        if (! $negocio?->notif_reserva_nueva) {
            return;
        }

        $email = $this->resolveEmail($negocio);

        if ($email === null) {
            return;
        }

        $reserva->loadMissing(['servicio', 'recurso', 'cliente', 'estadoReserva', 'sesion']);

        Mail::to($email)->send(new ReservaNuevaAdmin($reserva));

        $this->checkAforoLleno($reserva);
    }

    public function anulacionReserva(Reserva $reserva): void
    {
        $negocio = $reserva->negocio;

        if (! $negocio?->notif_anulacion_reserva) {
            return;
        }

        $email = $this->resolveEmail($negocio);

        if ($email === null) {
            return;
        }

        $reserva->loadMissing(['servicio', 'recurso', 'cliente', 'estadoReserva']);

        Mail::to($email)->send(new AnulacionReservaAdmin($reserva));
    }

    public function reservaModificada(Reserva $reserva, array $changeSummary): void
    {
        $negocio = $reserva->negocio;

        if (! $negocio?->notif_reserva_modificada) {
            return;
        }

        $email = $this->resolveEmail($negocio);

        if ($email === null) {
            return;
        }

        $reserva->loadMissing(['servicio', 'recurso', 'cliente', 'estadoReserva', 'sesion']);

        Mail::to($email)->send(new ReservaModificadaAdmin($reserva, $changeSummary));

        $this->checkAforoLleno($reserva);
    }

    public function encuestaRespondida(Reserva $reserva, array $respuestas, ?string $comentario): void
    {
        $negocio = $reserva->negocio;

        if (! $negocio?->notif_encuesta_respondida) {
            return;
        }

        $email = $this->resolveEmail($negocio);

        if ($email === null) {
            return;
        }

        $reserva->loadMissing(['servicio', 'cliente']);

        Mail::to($email)->send(new EncuestaRespondidaAdmin($reserva, $respuestas, $comentario));
    }

    private function checkAforoLleno(Reserva $reserva): void
    {
        $negocio = $reserva->negocio;

        if ($reserva->sesion_id === null) {
            return;
        }

        $sesion = $reserva->sesion ?? Sesion::find($reserva->sesion_id);

        if ($sesion === null) {
            return;
        }

        $reservados = Reserva::where('sesion_id', $sesion->id)
            ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
            ->sum('numero_personas');

        $aforoRestante = max(0, ($sesion->aforo_total ?? 0) - (int) $reservados);

        if ($aforoRestante <= 0 && $negocio->notif_aforo_lleno_experiencia) {
            $email = $this->resolveEmail($negocio);

            if ($email !== null) {
                Mail::to($email)->send(new AforoLlenoExperienciaAdmin($sesion, $reserva));
            }
        }

        if ($aforoRestante <= 0 && $negocio->notif_aforo_lleno_dia) {
            $this->checkAforoLlenoDia($negocio, $reserva, $sesion);
        }
    }

    private function checkAforoLlenoDia(Negocio $negocio, Reserva $reserva, Sesion $sesionLlena): void
    {
        $sesionesDelDia = Sesion::where('negocio_id', $negocio->id)
            ->where('servicio_id', $reserva->servicio_id)
            ->where('fecha', $reserva->fecha)
            ->where('activo', true)
            ->get();

        if ($sesionesDelDia->isEmpty()) {
            return;
        }

        $todasLlenas = $sesionesDelDia->every(function (Sesion $sesion) {
            $reservados = Reserva::where('sesion_id', $sesion->id)
                ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
                ->sum('numero_personas');

            return $reservados >= ($sesion->aforo_total ?? 0);
        });

        if (! $todasLlenas) {
            return;
        }

        $email = $this->resolveEmail($negocio);

        if ($email !== null) {
            $reserva->loadMissing('servicio');
            Mail::to($email)->send(new AforoLlenoDiaAdmin(
                $negocio,
                $reserva->servicio,
                $reserva->fecha,
                $sesionesDelDia
            ));
        }
    }

    private function resolveEmail(Negocio $negocio): ?string
    {
        if (filled($negocio->notif_email_destino)) {
            return $negocio->notif_email_destino;
        }

        return $negocio->users()->first()?->email ?? $negocio->email;
    }

    private function estadosCancelados(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = EstadoReserva::query()
                ->whereIn('nombre', ['Cancelada', 'No presentada'])
                ->pluck('id')
                ->all();
        }

        return $ids;
    }
}
