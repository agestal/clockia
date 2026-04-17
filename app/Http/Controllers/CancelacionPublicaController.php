<?php

namespace App\Http\Controllers;

use App\Services\Reservations\BookingCancellationService;
use Illuminate\Contracts\View\View;

class CancelacionPublicaController extends Controller
{
    public function __construct(
        private readonly BookingCancellationService $cancellationService,
    ) {}

    public function confirm(string $token): View
    {
        try {
            $reserva = $this->cancellationService->confirmCancellation($token);

            return view('cancelacion.confirmada', [
                'reserva' => $reserva,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            return view('cancelacion.confirmada', [
                'reserva' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
