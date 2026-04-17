<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingModified
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Reserva $booking,
        public readonly array $changeSummary = [],
    ) {}
}
