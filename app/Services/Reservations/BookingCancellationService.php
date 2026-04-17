<?php

namespace App\Services\Reservations;

use App\Events\BookingCancelled;
use App\Mail\ConfirmacionCancelacion;
use App\Mail\SolicitudCancelacion;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class BookingCancellationService
{
    public function lookupByLocator(int $negocioId, string $localizador): ?Reserva
    {
        return Reserva::query()
            ->where('negocio_id', $negocioId)
            ->where('localizador', $localizador)
            ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
            ->with(['servicio:id,nombre,duracion_minutos', 'estadoReserva:id,nombre'])
            ->first();
    }

    public function lookupByEmail(int $negocioId, string $email): Collection
    {
        return Reserva::query()
            ->where('negocio_id', $negocioId)
            ->where(function ($q) use ($email) {
                $q->whereRaw('LOWER(email_responsable) = ?', [mb_strtolower($email, 'UTF-8')])
                    ->orWhereHas('cliente', fn ($inner) => $inner->whereRaw('LOWER(email) = ?', [mb_strtolower($email, 'UTF-8')]));
            })
            ->whereNotIn('estado_reserva_id', $this->estadosCancelados())
            ->where('fecha', '>=', Carbon::today())
            ->with(['servicio:id,nombre,duracion_minutos', 'estadoReserva:id,nombre'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function isCancellable(Reserva $reserva): bool
    {
        if (in_array($reserva->estado_reserva_id, $this->estadosCancelados(), true)) {
            return false;
        }

        if ($reserva->fecha === null || $reserva->fecha->isPast()) {
            return false;
        }

        if ($reserva->horas_minimas_cancelacion !== null && $reserva->horas_minimas_cancelacion > 0) {
            $inicio = $reserva->inicio_datetime ?? Carbon::parse($reserva->fecha->toDateString().' '.substr((string) $reserva->hora_inicio, 0, 5).':00');
            $hoursUntil = now()->diffInHours($inicio, false);

            if ($hoursUntil < $reserva->horas_minimas_cancelacion) {
                return false;
            }
        }

        return true;
    }

    public function hoursUntilDeadline(Reserva $reserva): ?int
    {
        if ($reserva->horas_minimas_cancelacion === null || $reserva->horas_minimas_cancelacion <= 0) {
            return null;
        }

        $inicio = $reserva->inicio_datetime ?? Carbon::parse($reserva->fecha->toDateString().' '.substr((string) $reserva->hora_inicio, 0, 5).':00');

        return max(0, (int) now()->diffInHours($inicio, false));
    }

    public function requestCancellation(Reserva $reserva): string
    {
        if (! $this->isCancellable($reserva)) {
            throw new RuntimeException('Esta reserva no puede cancelarse en este momento.');
        }

        $email = $reserva->email_responsable ?? $reserva->cliente?->email;

        if (! filled($email)) {
            throw new RuntimeException('No hay email asociado a esta reserva para enviar la confirmación de cancelación.');
        }

        $token = Str::random(48);

        $reserva->forceFill([
            'token_cancelacion' => $token,
            'token_cancelacion_expira_en' => now()->addHours(24),
        ])->save();

        $reserva->loadMissing(['negocio', 'servicio']);

        Mail::to($email)->send(new SolicitudCancelacion($reserva, $token));

        return $token;
    }

    public function confirmCancellation(string $token): Reserva
    {
        $reserva = Reserva::query()
            ->where('token_cancelacion', $token)
            ->with(['negocio', 'servicio', 'cliente', 'estadoReserva'])
            ->first();

        if ($reserva === null) {
            throw new RuntimeException('El enlace de cancelación no es válido.');
        }

        if ($reserva->token_cancelacion_expira_en !== null && Carbon::parse($reserva->token_cancelacion_expira_en)->isPast()) {
            $reserva->forceFill(['token_cancelacion' => null, 'token_cancelacion_expira_en' => null])->save();
            throw new RuntimeException('El enlace de cancelación ha expirado. Solicita uno nuevo.');
        }

        if (in_array($reserva->estado_reserva_id, $this->estadosCancelados(), true)) {
            throw new RuntimeException('Esta reserva ya está cancelada.');
        }

        $estadoCancelada = EstadoReserva::where('nombre', 'Cancelada')->value('id');

        if ($estadoCancelada === null) {
            throw new RuntimeException('No se encontró el estado de cancelación.');
        }

        $reserva->forceFill([
            'estado_reserva_id' => $estadoCancelada,
            'fecha_cancelacion' => now(),
            'cancelada_por' => 'cliente',
            'token_cancelacion' => null,
            'token_cancelacion_expira_en' => null,
        ])->save();

        BookingCancelled::dispatch($reserva);

        $email = $reserva->email_responsable ?? $reserva->cliente?->email;

        if (filled($email)) {
            try {
                Mail::to($email)->send(new ConfirmacionCancelacion($reserva));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $reserva;
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
